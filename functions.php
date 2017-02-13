<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 16.01.2017
 * Time: 16:26
 */
const MD5_ADD = 'lo9g$4&';

try {
    $mydb = new PDO("mysql:host=localhost:8889;dbname=mboldyrev;charset=UTF8","root","root");
    //"mboldyrev","neto0801");
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}


function userExist($userName) {
    global $mydb;

    $findUser = $mydb->prepare("SELECT id, login FROM user WHERE login = ?");
    $findUser->execute([$userName]);
    $result = $findUser->fetch();
    if ($result) {
        return (int) $result["id"];
    }
    else {
        return false;
    }
}

function checkPassword ($usr, $psw) {
    global $mydb;
    $sql = "SELECT login, password FROM `user` WHERE login = ?";
    $checkPass = $mydb->prepare($sql);
    $checkPass->execute([$usr]);
    $gotPass = $checkPass->fetch();
    $passdata = md5($usr . $psw . MD5_ADD);
    return strcmp($passdata, $gotPass["password"]) === 0 ? true : false; // true если строки равны

}
