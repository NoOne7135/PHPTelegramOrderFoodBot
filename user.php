<?php

    include_once('db.php');


	function userOne(array $fields) : array{
		$sql = "SELECT * FROM users WHERE user_id = :id";
		$query = dbQuery($sql, $fields); 
			
    	return $query->fetchAll()[0];
	}
    function checkUserExists($id) {
        $sql = "SELECT COUNT(*) FROM users WHERE user_id = :id";
        $query = dbQuery($sql, ['id' => $id]);
        $result = $query->fetchColumn();
    
        return $result > 0;
    }
	function userAdd(array $fields) : bool {
        $sql = "INSERT INTO users (`user_id`, `city_id`, `company_id`, `category_id`, `phone`) VALUES (:id, NULL, NULL, NULL, NULL)";
        $params = [
            ':id' => $fields['id'],
        ];
        dbQuery($sql, $fields);
        return true;
    }


	// function userEdit(array $fields) : bool{
	// 	$sql = "UPDATE users SET city_id = :city_id, company_id = :company_id, category_id = :category_id WHERE id = :id";
	// 	dbQuery($sql, $fields);
	// 	return true;
	// }

    function userEditSity(array $fields) : bool{
		$sql = "UPDATE users SET city_id = :city_id, company_id = NULL, category_id = NULL WHERE user_id = :id";
		dbQuery($sql, $fields);
		return true;
	}
    function userEditCompany(array $fields) : bool{
		$sql = "UPDATE users SET company_id = :company_id, category_id = NULL WHERE user_id = :id";
		dbQuery($sql, $fields);
		return true;
	}
    function userEditCategory(array $fields) : bool{
		$sql = "UPDATE users SET category_id = :category_id WHERE user_id = :id";
		dbQuery($sql, $fields);
		return true;
	}
	function userEditPhone(array $fields) : bool{
		$sql = "UPDATE users SET phone = :phone WHERE user_id = :id";
		dbQuery($sql, $fields);
		return true;
	}

