<?php

require 'vendor/autoload.php';

function getCitys(){
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'https://api-lambda-release.dotsdev.live/api/v2/cities',
        [
            'headers' => [
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'v'=> '2.0.0',
            ],
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}

function getCompanyByCityId($id){
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'https://api-lambda-release.dotsdev.live/api/v2/cities/' . $id . '/companies',
        [
            'headers' => [
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'v'=> '2.0.0',
            ],
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}

function getItemsByCompany($id){
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'https://api-lambda-release.dotsdev.live/api/v2/companies/' . $id . '/items-by-categories',
        [
            'headers' => [
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'v'=> '2.0.0',
            ],
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}
function getCartInformation($companyId, $cartItems){
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
    'https://api-lambda-release.dotsdev.live/api/v2/cart/info',
    [
        'headers' => [
            'Api-Token' => API_TOKEN,
            'Api-Account-Token' => API_ACCOUNT_TOKEN,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'query' => [
            'v'=> '2.0.0',
        ],
        'json' => [
            'companyId' => $companyId,
            'cartItems' => $cartItems,
        ],
    ]
);
$body = $response->getBody();
print_r(json_decode((string) $body));
}
function getCartPrice($cityId, $companyId, $cartItems){
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://api-lambda-release.dotsdev.live/api/v2/cart/prices/resolve',
        [
            'headers' => [
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'v' => '2.0.0',
                'orderFields' => [
                    'cityId' => $cityId,
                    'companyId' => $companyId,
                    'deliveryType' => 0,
                    'paymentType' => 1,
                    'deliveryTime' => 0,
                    'cartItems' => $cartItems
                ]
            ]
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}
function Order($cityId, $companyId, $cartItems, $phone, $name, $paymentType = 1){
    $client = new \GuzzleHttp\Client();
    $response = $client->post(
        'https://api-lambda-release.dotsdev.live/api/v2/orders',
        [
            'headers' => [
                'Api-Auth-Token' => API_AUTH_TOKEN,
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'v' => '2.0.0',
                'orderFields' => [
                    'cityId' => $cityId,
                    'companyId' => $companyId,
                    'userName' => $name,
                    'userPhone' => $phone,
                    'deliveryType' => 0,
                    'paymentType' => $paymentType,
                    'deliveryTime' => 0,
                    'cartItems' => $cartItems
                ]
            ]
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}
function ValidateCart($cityId, $companyId, $cartItems){
$client = new \GuzzleHttp\Client();
$phont = '+380934010519';
$name = 'NoOne';
$response = $client->post(
    'https://api-lambda-release.dotsdev.live/api/v2/cart/prices/validate',
    [
        'headers' => [
            'Api-Token' => API_TOKEN,
            'Api-Account-Token' => API_ACCOUNT_TOKEN,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'json' => [
            'v' => '2.0.0',
            'orderFields' => [
                'cityId' => $cityId,
                'companyId' => $companyId,
                'userName' => $name,
                'userPhone' => $phone,
                'deliveryType' => 0,
                'paymentType' => 1,
                'deliveryTime' => 0,
                'cartItems' => $cartItems
            ]
        ]
    ]
);
$body = $response->getBody();
return json_decode((string) $body);
}
function OrderInfo($order_Id){
    $client = new \GuzzleHttp\Client();
    $response = $client->get(
        'clients-api.dots.live/api/v2/orders/et',
        [
            'headers' => [
                'Api-Auth-Token' => API_AUTH_TOKEN,
                'Api-Token' => API_TOKEN,
                'Api-Account-Token' => API_ACCOUNT_TOKEN,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'query' => [
                'v'=> '2.0.0',
            ],
        ]
    );
    $body = $response->getBody();
    return json_decode((string) $body);
}