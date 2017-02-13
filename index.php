<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 13.02.2017
 * Time: 14:00
 */
error_reporting(E_ALL);
include_once "functions.php";
/*
 * ввести имя юзера
 * если нет - зарегистрировать
 * если есть - запрос пароля
 * и открыть todo.php
 *
 * назначить задачу другому юзеру
 *
 * отображать только созданные мной задачи
 *
 * отображать назначенные мне задачи
 */



$html = '';
setcookie('logged_user',"",-1); // обнуляем куку если была
session_start();

//task : id - user_id - assigned_user_id - description - is_done - date_added
//usr: id - login - password


if ( !empty($_POST) && ($_POST['login'] != "") ) {
    // имя уже получено, проверяем пароль

    $not_valid_chars = preg_match('/[^a-zA-Z0-9]/',$_POST['login']);
    if ( $not_valid_chars == 1 ) {
        $html = <<<INVALID_NAME
        <form action="" method = "post" >
        <p><p><b>Введите имя (только латиница без пробелов):</b><br><input name="login" type="text" autofocus></p>
        <p><input type="submit" value="Войти"></p>
        </form>
INVALID_NAME;
        echo $html;
    } // в имени чтото кроме латиницы и цифр
    else // имя подходящее
    {
        $login = $_POST['login'];
        if (userExist($login)) { // пользователь найден
            if (!empty($_POST['pass'])) {
                if (checkPassword($login, $_POST['pass'])) {
                    setcookie('logged_user', userExist($login)); // установлена кука
                    header('location: todo.php '); // переход на страницу с тасками
                } // пароль проверен
                else { // введен неверный пароль, заново
                    $html = <<<WRONG_PASS
                    <form action="" method = "post" >
                    <p><p><b>Ваше имя: $login </b><br>
                    <input type="hidden" name="login" value="$login">
                    <label for="pass">Пароль неверный!</label>
                    <input id= "pass" name="pass" type="password" placeholder="Введите пароль" autofocus><br>
                    <input type="submit" value="Отправить">
                    </form>
WRONG_PASS;

                } // пароль неверный
                echo $html;
            }
            else {
                //echo "ЗАПРОС ПАРОЛЯ";
                $html = <<<LOGIN_EXISTS
            <form action="" method = "post" >
            <p><p><b>Ваше имя: $login </b><br>
            <input type="hidden" name="login" value="$login">
            <label for="pass">Пароль:</label>
            <input id= "pass" name="pass" type="password" placeholder="Введите пароль" autofocus><br>
            <input type="submit" value="Отправить">
            </form>
LOGIN_EXISTS;
                echo $html;

            }
        } // пользователь такой найден
        else { // пользователь НЕ найден - зарегистрироваться
            if (isset($_POST['pass']) && strlen($_POST['pass']) > 7 && strlen($_POST['pass']) < 12) {
                // сохранить пару логин-пароль
                $addUser = $mydb->prepare("INSERT INTO user (id, login, password) VALUES (NULL, ?, ?)");

                $newUserPass = md5($login . $_POST['pass'] . MD5_ADD);

                $addUser->execute([$login, $newUserPass]);
                $userId = $mydb->lastInsertId();
//
//                echo "\nPDOStatement::errorInfo():\n";
//                $arr = $mydb->errorInfo();
//                print_r($arr);

                setcookie('logged_user', $userId);
                //echo "ЮЗЕР ЗАПИСАН: ". $userId;
                header('location: todo.php ');
                // установить куку и перейти на стр todo.php


            } // сохранить пароль нового юзера в базу

            else {
                $html = <<<NEW_USER
                        <form action="" method = "post" >
                        <p>Новый пользователь</p>
                        <p><b>Ваше имя: $login </b><br>
                        <input type="hidden" name="login" value="$login">
                        
                        <label for="pass">Пароль придумайте (8-12 символов):</label>
                        <input id= "pass" name="pass" type="password" placeholder="Введите пароль" autofocus><br>
                        <input type="submit" value="Запомнить">
                        </form>
NEW_USER;
                echo $html;

            } // придумай пароль



        } // пользователь не найден
    }
} // имя уже получено, проверяем пароль
else {

    // <p><p><b>Ваше имя:</b><br><input name="user_name" type="text"></p>
    $html = <<<NO_NAME
        <form action="" method = "post">
        <p><p><b>Ваше имя (только латиница):</b><br><input name="login" type="text" autofocus></p>
        <p><input type="submit" value="Войти"></p>
    </form>
NO_NAME;
    echo $html;
} // имя еще не введено! Как вас зовут