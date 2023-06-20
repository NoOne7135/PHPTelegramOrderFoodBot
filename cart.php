<?php

function cartAdd(array $fields) : bool {
        $sql = "INSERT INTO cart (`food_id`, `name`, `count`, `user_id`) VALUES (:food_id, :name, :count, :id)";
        $params = [
            ':food_id' => $fields['food_id'],
            ':name' => $fields['name'],
            ':count' => $fields['count'],
            ':id' => $fields['id'],
        ];
        dbQuery($sql, $fields);
        return true;
    }
function cartEdit(array $fields) : bool{
		$sql = "UPDATE cart SET food_id = :food_id, name = :name, count = :count, user_id = :id WHERE food_id = :food_id AND user_id = :id";
		$params = [
            ':food_id' => $fields['food_id'],
            ':name' => $fields['name'],
            ':count' => $fields['count'],
            ':id' => $fields['id'],
        ];
        dbQuery($sql, $fields);
        return true;
	}
function cartAddCount(array $fields) : bool {
    $sql = "INSERT INTO cart (`food_id`, `count`, `user_id`) VALUES (:food_id, :count, :id)";
    $params = [
        ':food_id' => $fields['food_id'],
        ':count' => $fields['count'],
        ':id' => $fields['id'],
    ];
    dbQuery($sql, $fields);
    return true;
}
function cartDelete(array $fields) : bool{
    $sql = "DELETE FROM cart WHERE user_id = :id";
    dbQuery($sql, $fields);
    return true;
}
function cartAll(array $fields) : array{
    $sql = "SELECT * FROM cart WHERE user_id = :user_id";
    $query = dbQuery($sql, $fields);
    return $query->fetchAll();
}
function cartOne(array $fields) : array{
    $sql = "SELECT * FROM cart WHERE food_id = :food_id AND user_id = :id";
    $query = dbQuery($sql, $fields); 
        
    return $query->fetchAll()[0];
}
function checkCartExists($id) {
        $sql = "SELECT COUNT(*) FROM cart WHERE food_id = :food_id";
        $query = dbQuery($sql, [':food_id' => $id]);
        $result = $query->fetchColumn();
    
        return $result > 0;
}
function convertToCartItemFormat($jsonString) {
    $items = $jsonString;
    $cartItems = [];
    
    foreach ($items as $item) {
        $cartItem = [
            'id' => $item['food_id'],
            'count' => $item['count']
        ];
        $cartItems[] = $cartItem;
    }
     return $cartItems;
}