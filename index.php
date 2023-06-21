<?php

require_once('config.php');

include_once('api.php');
include_once('telegram.php');
include_once('user.php');
include_once('cart.php');
include_once('webhook.php');
include_once('log.php');


$CitysInfo = getCitys();
$Citys = array();
foreach ($CitysInfo->items as $item) {
$Citys[$item->id] = $item->name;
}

$data = file_get_contents('php://input');
$data = json_decode($data, true);

writeLogFile($data, true);

$textMessage = mb_strtolower($data["message"]["text"]);
$dataMessage = mb_strtolower($data['callback_query']['data']);


print_r($textMessage);
if($textMessage == '/start') {
    $chatId = $data["message"]["chat"]["id"];
    if(checkUserExists($chatId) !== true){
        userAdd(['id' => $chatId]);
    }
    
    $buttons[] = array(
        array(
            'text' => 'Почати роботу',
            'callback_data' => 'start'
        )
    );
    $arrayQuery = array(
        'chat_id' 		=> $chatId,
        'text'			=> "Вас вітає бот для замовлення їжі",
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons
        )),
    );
        TG_sendMessage($arrayQuery);
}


if($textMessage == '/cart') {
    $chatId = $data["message"]["chat"]["id"];
    $cart = cartAll(['user_id' => $chatId]);
    $user = userOne(['id' => $chatId]);
    $cart = NULL;
    $priceInfo = '';
    $cart = cartAll(['user_id' => $chatId]);
    if($user['city_id'] !== NULL && $user['company_id'] !== NULL && $cart !== array()){
        $info = getCartPrice($user['city_id'], $user['company_id'], convertToCartItemFormat($cart));
        $priceInfo = "\nЦіна замовлення: " . $info->price . 
        " грн\nЦіна доставки: " . $info->deliveryPrice . 
        " грн\nЗагальна ціна: " . $info->totalPrice . " грн\n";
    }
    $result = '';
    foreach ($cart as $item) {
        $foodName = $item['name'];
        $foodCount = $item['count'];

        $result .= "Назва: $foodName, Кількість: $foodCount\n";
    }
    $buttons[] = array();
    if($cart !== array()){
    $buttons[] = array(
        array(
            'text' => 'Очистити кошик',
            'callback_data' => 'clear'
        ),
        array(
            'text' => 'Оформити замовлення',
            'callback_data' => 'payment'
        )
    );
    }
    $arrayQuery = array(
        'chat_id' 		=> $chatId,
        'text'			=> "Ваш кошик:\n" . $result . $priceInfo . 
        "\nДля замовлення буде уточнено адреc",
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons
        )),
    );
        TG_sendMessage($arrayQuery);
}
  
if(strpos($textMessage, '/info') !== false){
    $chatId = $data["message"]["chat"]["id"];
    $orderId = trim(str_replace('/info', '', $textMessage));
    $response = OrderInfo($orderId);
    $id = $response->id;
    $status = $response->status;
    $number = $response->number;
    $cityName = getCityInformation($response->cityId)->name;
    $companyName = getCompanyInformation($response->companyId)->name;
    $fullPrice = $response->prices->fullPrice;
    $deliveryAddress = $response->delivery->deliveryAddress;
    $textMessage_bot = "\nID замовлення: " . $orderId . "\nСтатус: " . $status ."\nНомер замовлення: " . $number . "\nМісто: " . $cityName . "\nКомпанія: " . $companyName . "\nДе забирати: " . $deliveryAddress . "\nПовна ціна: " . $fullPrice;
    $arrayQuery = array(
        'chat_id' 		=> $chatId,
        'text'			=> $textMessage_bot,
        );
        TG_sendMessage($arrayQuery);
}

if(strpos($dataMessage, 'start')!== false){
    
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $textMessage_bot = "Виберіть місто для замовлення";
    $buttons = array();

    foreach ($Citys as $id => $name) {
        $buttons[] = array(
            array(
                'text' => $name,
                'callback_data' => 'city/'. $id
            )
        );
    }

    $arrayQuery = array(
	'chat_id' 		=> $chatId,
	'text'			=> $textMessage_bot,
    'reply_markup' => json_encode(array(
        'inline_keyboard' => $buttons
    )),
    );
    TG_sendMessage($arrayQuery);
}
if(strpos($dataMessage, 'city') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $textMessage_bot = "Виберіть компанію для замовлення";
    $idCity = str_replace('city/', '', $dataMessage);
    $CompanyInfo = getCompanyByCityId($idCity);
    $fields = [
        ':id' => $chatId,
        ':city_id' => $idCity,
    ];
    userEditSity($fields);
    $Companies = array();
    foreach ($CompanyInfo->items as $item) {
        $Companies[$item->id] = $item->name;
    }
    foreach ($Companies as $id => $name) {
        $buttons[] = array(
            array(
                'text' => $name,
                'callback_data' => 'company/'.$id,
            )
        );
    }
    $buttons[] = array(
        array(
            'text' => 'Назад',
            'callback_data' => 'start',
        ),
    );
    $arrayQuery = array(
	'chat_id' 		=> $chatId,
	'text'			=> $textMessage_bot,
    'reply_markup' => json_encode(array(
        'inline_keyboard' => $buttons
    )),
    );
    TG_sendMessage($arrayQuery);

}


if(strpos($dataMessage, 'company') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $textMessage_bot = "Оберіть категорію страв для замовлення";
    $idCompany = str_replace('company/', '', $dataMessage);
    $user = userOne(['id' => $chatId]);
    $idCity = $user['city_id'];
    if(cartAll(['user_id' => $chatId]) === array()){
        $fields = [
            ':id' => $chatId,
            ':company_id' => $idCompany,
        ];
        userEditCompany($fields);
        $CategoriesInfo = getItemsByCompany($idCompany);

        $categoryPairs = array();

        foreach ($CategoriesInfo->items as $item) {
            $categoryPairs[$item->id] = $item->name;
        }

        $buttons = array();
        foreach ($categoryPairs as $id => $name) {
            $callback_data = 'categories/' . $id;
            $buttons[] = array(
                array(
                    'text' => $name,
                    'callback_data' => $callback_data,
                ),
            );
        }
        $buttons[] = array(
            array(
                'text' => 'Назад',
                'callback_data' => 'city/' . $idCity,
            ),
        );

        $arrayQuery = array(
            'chat_id' => $chatId,
            'text' => $textMessage_bot,
            'reply_markup' => json_encode(array(
                'inline_keyboard' => $buttons,
            )),
        );

    TG_sendMessage($arrayQuery);
    }else{
        $user = userOne(['id' => $chatId]);
        $buttons[] = array(
            array(
                'text' => 'Видалити кошик',
                'callback_data' => 'clear',
            ),
        );
        $arrayQuery = array(
            'chat_id' => $chatId,
            'text' => 'Ви хочете змінити компанію для замовлення, але у вас у кошуку вже є страви, якщо ви бажаєте змінити компанію то кошик буде видалено.',
            'reply_markup' => json_encode(array(
                'inline_keyboard' => $buttons,
            )),
        );
    TG_sendMessage($arrayQuery);
    }
}
if(strpos($dataMessage, 'categories') !== false) {
    
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $textMessage_bot = "Оберіть страву для додання в корзину";
    $idCategory= str_replace('categories/', '', $dataMessage);
    $fields = [
        ':id' => $chatId,
        ':category_id' => $idCategory,
    ];
    userEditCategory($fields);
    $user = userOne(['id' => $chatId]);
    $idCompany = $user['company_id'];
    $CategoriesInfo = getItemsByCompany($idCompany);
    $foods = array();
    
    foreach ($CategoriesInfo->items as $category) {
        if ($category->id === $idCategory) { 
            foreach ($category->items as $food) {
                $foods[$food->id] = $food->name;
            }
        }
    }
    $buttons = array();
    foreach ($foods as $id => $name) {
        $buttons[] = array(
            array(
                'text' => $name,
                'callback_data' => 'food/' . $id,
            ),
        );
    }
    $buttons[] = array(
        array(
            'text' => 'Назад',
            'callback_data' => 'company/' . $idCompany,
        ),
    );
    $arrayQuery = array(
        'chat_id' => $chatId,
        'text' => $textMessage_bot,
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons,
        )),
    );

    TG_sendMessage($arrayQuery);
}

if(strpos($dataMessage, 'food') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $idFood= str_replace('food/', '', $dataMessage);
    $user = userOne(['id' => $chatId]);
    $idCompany = $user['company_id'];
    $idCategory = $user['category_id'];
    $CategoriesInfo = getItemsByCompany($idCompany);


    $food = null;
    foreach ($CategoriesInfo->items as $category) {
        if ($category->id === $idCategory) {
            foreach ($category->items as $item) {
                if ($item->id === $idFood) {
                    $food = $item;
                    break 2;
                }
            }
        }
    }

    if ($food !== null) {
        $textMessage_bot = "Назва:\n " . $food->name . "\nОпис:\n " . $food->description . "\nЦіна:\n " . $food->price . ' грн';
        $image = $food->image;
    } else {
        $textMessage_bot = "Помилка";
    }
    

    
    $arrayQuery = array(
        'chat_id' => $chatId,
        'photo' => $image,
        'caption' => $textMessage_bot,
    );
    TG_sendPhoto($arrayQuery);

    $buttons[] = array(
        array(
            'text' => 'Додати у кошик',
            'callback_data' => 'cart/' . $idFood,
        ),
        array(
            'text' => 'Назад',
            'callback_data' => 'categories/' . $idCategory,
        ),
    );
    $arrayQuery = array(
        'chat_id' => $chatId,
        'text' => 'Бажаєте додати кошик?',
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons,
        )),
    );

    TG_sendMessage($arrayQuery);
}

if(strpos($dataMessage, 'cart') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $idFood= str_replace('cart/', '', $dataMessage);
    $user = userOne(['id' => $chatId]);
    $idCompany = $user['company_id'];
    $idCategory = $user['category_id'];
    $CategoriesInfo = getItemsByCompany($idCompany);
    $foodName = '';
    $count = 1;
    foreach ($CategoriesInfo->items as $item) {
        if ($item->id === $idCategory) {
            foreach ($item->items as $item){
                if ($item->id === $idFood) {
                    $foodName = $item->name;
                break 2;
                }
            }
        }
    }
    $fields = [
        ':food_id' => $idFood,
        ':name' => $foodName,
        ':count' => $count,
        ':id' => $chatId,
    ];
    if(checkCartExists($idFood) !== true){
        cartAdd($fields);
    }else{
        $food = cartOne(['food_id'=> $idFood, 'id' => $chatId]);
        $fields[':count'] = $food['count'] + 1;
        cartEdit($fields);
    }
        
    
    if(checkCartExists($idFood)){
        $textMessage_bot = 'Додано у кошик ( Відкрити кошик /cart )';
    }
    else{
        $textMessage_bot = 'Помилка';
    }

    $arrayQuery = array(
        'chat_id' => $chatId,
        'text' => $textMessage_bot,
    );
    TG_sendMessage($arrayQuery);
}
if(strpos($dataMessage, 'clear') !== false) {

    $chatId = $data['callback_query']["message"]["chat"]["id"];
    
    if(cartDelete(['id' => $chatId])){
        $textMessage_bot = 'Кошик видалено';
    }
    else{
        $textMessage_bot = 'Помилка';
    }

    $arrayQuery = array(
        'chat_id' => $chatId,
        'text' => $textMessage_bot,
    );
    TG_sendMessage($arrayQuery);
}


if(strpos($dataMessage, 'payment') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $cart = cartAll(['user_id' => $chatId]);
    if($cart === array()){
        $arrayQuery = array(
            'chat_id' 		=> $chatId,
            'text'			=> 'Кошик порожній',
        );
        TG_sendMessage($arrayQuery);
        exit;
    }
    $buttons[] = array(
        array(
            'text' => 'Оплатити готівкою',
            'callback_data' => 'address/1',
            'request_contact'=>true
        )
    );
    $buttons[] = array(
        array(
            'text' => 'Оплатити онлайн',
            'callback_data' => 'address/2',
            'request_contact'=>true
        )
    );
    $buttons[] = array(
        array(
            'text' => 'Оплатити терміналом',
            'callback_data' => 'address/3',
            'request_contact'=>true
        )
    );
    $arrayQuery = array(
        'chat_id' 		=> $chatId,
        'text'			=> "Обиріть спосіб оплати",
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons
        )),
    );
        TG_sendMessage($arrayQuery);
}
if(strpos($dataMessage, 'address') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $user = userOne(['id' => $chatId]);
    $companyInfo = getCompanyInformation($user['company_id']);
    $payment = str_replace('address/', '', $dataMessage);
    if(empty($companyInfo->addresses) || $companyInfo->addresses === NULL){
        $arrayQuery = array(
            'chat_id' 		=> $chatId,
            'text'			=> "Вибачте, ця компанія не має функції самовивозу",
        );
        TG_sendMessage($arrayQuery);
        exit();
    }
    foreach ($companyInfo->addresses as $address) {
        $addressData = [
            'id' => $address->id,
            'title' => $address->title
        ];
        $addressesArray[] = $addressData;
    }
    foreach ($addressesArray as $addresses => $address) {
        $buttons[] = array(
            array(
                'text' => $address['title'],
                'callback_data' => 'order/' . $payment . '/'. $address['id'],
            ),
        );
    }
    $arrayQuery = array(
        'chat_id' 		=> $chatId,
        'text'			=> "Обиріть заклад",
        'reply_markup' => json_encode(array(
            'inline_keyboard' => $buttons
        )),
    );
        TG_sendMessage($arrayQuery);
}

if(strpos($dataMessage, 'order') !== false) {
    $chatId = $data['callback_query']["message"]["chat"]["id"];
    $name = $data['callback_query']['from']['first_name'];
    $parts = explode("/", $dataMessage);
    $cart = convertToCartItemFormat(cartAll(['user_id' => $chatId]));
    if($cart === array()){
        $arrayQuery = array(
            'chat_id' 		=> $chatId,
            'text'			=> 'Кошик порожній',
        );
        TG_sendMessage($arrayQuery);
        exit;
    }
    $user = userOne(['id' => $chatId]);
    $companyInfo = getCompanyInformation($user['company_id']);
    $companyAdressId = $parts[2];
    $payment = $parts[1];
    $str = '';
    foreach ($cart as $item) {
        $str = $str . " " . $item['id'];
    }
    if($user['city_id'] !== NULL && $user['company_id'] !== NULL && $user['phone'] !== NULL && $companyAdressId !== NULL){
        $response = Order($user['city_id'], $user['company_id'], $cart, str_replace("+", "", $user['phone']), $name, $payment, $companyAdressId);
        $textMessage = "Замовлення оформлене, код замовлення:\n" . $response->id ;
        cartDelete(['id' => $chatId]);
        $arrayQuery = array(
            'chat_id' 		=> $chatId,
            'text'			=> $textMessage,
        );
        TG_sendMessage($arrayQuery);
    }else{
        if($user['phone'] == NULL){
        $button = [
            'text' => 'Відправте номер телефону',
            'request_contact' => true
        ];
        
        $keyboard = [
            'keyboard' => [[$button]],
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        ];
        
        $replyMarkup = json_encode($keyboard);
        
        TG_sendMessage([
            'chat_id' => $chatId,
            'text' => 'Будь ласка, надішліть ваш номер телефону',
            'reply_markup' => $replyMarkup,
        ]);
    }
    if($companyAdressId !== NULL){
        $arrayQuery = array(
            'chat_id' 		=> $chatId,
            'text'			=> 'Помилка, нема адреси компанії',
        );
        TG_sendMessage($arrayQuery);
        exit();
    }
    if(['city_id']  == NULL){
        TG_sendMessage([
            'chat_id' => $chatId,
            'text' => 'Не обране місто',
        ]);
    }
    if($user['company_id'] == NULL){
        TG_sendMessage([
            'chat_id' => $chatId,
            'text' => 'Не обрана компанія',
        ]);
    }
}
}

if($data["message"]["contact"]["phone_number"] !== NULL) {
    $chatId = $data["message"]["chat"]["id"];
    $phone = $data["message"]["contact"]["phone_number"];
    $fields = [
        ':id' => $chatId,
        ':phone' => $phone,
    ];
    userEditPhone($fields);
    TG_sendMessage([
        'chat_id' => $chatId,
        'text' => 'Телефон записано',
    ]);
}

