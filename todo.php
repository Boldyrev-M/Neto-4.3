<?php
/**
 * Created by PhpStorm.
 * User: Max
 * Date: 05.02.2017
 * Time: 15:15
 */

error_reporting(E_ALL);
include_once "functions.php";

echo "<h3>Домашнее задание к лекции 4.3 «SELECT из нескольких таблиц»</h3>";
echo "<h2>Список дел</h2>";
$dbtabl = 'task';

$sqlToRun = strlen($_POST["tasksSelect"]) ?  $_POST["tasksSelect"] : "";

$sql = "SELECT id,	user_id, assigned_user_id, description, is_done, date_added  FROM ".$dbtabl;

if ($sqlToRun=="mine") {
    $sql.=" WHERE user_id = ".$_COOKIE["logged_user"];
}
else
    if ($sqlToRun=="toMe") {
        $sql .= " WHERE assigned_user_id = " . $_COOKIE["logged_user"];
    }

if( strlen($_POST["add_new"])) {

    //  task: id - user_id - assigned_user_id - description - is_done - date_added
    $addnewtask = $mydb->prepare("INSERT INTO task (user_id, assigned_user_id, description, is_done, date_added) VALUES ( ?, ?, ?, false, NOW())");

    $newtaskdescription = (string)$_POST["add_new"];
    $addnewtask->bindParam(1, $_COOKIE["logged_user"]);
    $addnewtask->bindParam(2, $_POST["assignToUser"]);
    $addnewtask->bindParam(3, $newtaskdescription);
    $addnewtask->execute();

//    echo "\nPDOStatement::errorInfo():\n";
//    $arr = $mydb->errorInfo();
//    print_r($arr);
}
if (!empty($_POST["taskID"])) {
    $getid = (int) $_POST["taskID"];
    if (isset($_POST["butdel"])) {
        $sqlbutton = $mydb->prepare("DELETE FROM ".$dbtabl." WHERE id = ?");
        $sqlbutton->execute([$getid]);
    } // удалить задачу
    if (isset($_POST["butdone"])) {
        $sqlbutton = $mydb->prepare("UPDATE ".$dbtabl." SET is_done = !(is_done) WHERE id = ?");
        $sqlbutton->execute([$getid]);
    } // изменяем статус
    if (isset($_POST["butsave"])) {
        $sqlbutton = $mydb->prepare("UPDATE ".$dbtabl." SET description = ? WHERE id = ?");
        $sqlbutton->execute([$_POST["newdescr"],$getid]);
        unset($_POST["butsave"]);
        unset($_POST["newdescr"]);

    } // изменить  задачу

} // выбрана одна из трех кнопок


$html1 = <<< FormSearchHead
<form method="POST">
    <input type="text" name="add_new" placeholder="Описание" />
    <input type="hidden" name="tasksSelect" value = "$sqlToRun">
    <select name="assignToUser">
FormSearchHead;
$html2 = "<option value=".$_COOKIE["logged_user"].">Назначить другому пользователю</option>\r\n";

$selUsers = "SELECT id, login FROM `user`";
$usersList= $mydb->prepare($selUsers);
$usersList->execute();
$arra = $usersList->fetchAll(PDO::FETCH_ASSOC);
foreach ($arra as $usernum) {
    $html2 .= "<option value=\"".$usernum["id"]."\">" . $usernum["login"] . "</option>\r\n";
}

$html3 = <<< FormSearchEnd
    </select>
    <input type="submit" value="Добавить" />
</form>
FormSearchEnd;

echo $html1.$html2.$html3;

$htmlWhom = '<form name="tasksSelected" method="POST">
 <p><b>Какие отображать задачи:</b></p>
  <p><input type="radio" '.
     (($sqlToRun=="mine")? "checked":"").
    ' name="tasksSelect" value="mine">Созданные мной<br>
  <input type="radio" '.
    (($sqlToRun=="toMe")? "checked":"").
    ' name="tasksSelect" value="toMe">Назначенные мне<br>
  <p><input type="submit"></p>
</form>';
echo $htmlWhom;

//  `tasks`
//  1	id,	description, is_done, date_added	datetime
$tabhead = <<< TABH
<table border=1>
    <tr>
        <th>ID</th>
        <th>Кто добавил</th>
        <th>Кому добавлена</th>
        <th>Описание</th>
        <th>Статус</th>
        <th>Когда добавлена</th>
        <th>Сделать</th>
    </tr>
<tr>
TABH;

echo $tabhead;
foreach ($mydb->query($sql) as $row) {
    $eta = false;
    if ( isset($_POST["butedit"]) && $getid==$row['id']) {
        $eta = true;
    }
    echo "<tr><form name=adr" . $row['id'] . " method=\"POST\"><td>".$row['id'] ."</td>
    <td>".$row['user_id'] ."</td>
    <td>".$row['assigned_user_id'] ."</td>
    <td>". ( $eta
            ? "<input type=text name=\"newdescr\" value=\"" . $row['description'] . "\" autofocus>"
            : $row['description'] ) ."</td>
    <td><span style='color: ".($row['is_done']?"green;'>Выполнена":"red;'>В процессе") ."</span></td>
    <td>".$row['date_added'] ."</td><td>
    <input type=hidden name=\"taskID\" value=" . $row['id'] . ">
    <button name=". ($eta? ("\"butsave\" <span style='color: red;'>Сохранить</span>" ):"\"butedit\">Изменить")."</button>";
    if ($row['is_done']==false) {
        echo "<button name=\"butdone\">Выполнить</button>";
    } // эта задача выполнена
    else {
        echo "<button name=\"butdone\">Переделать</button>";
    } // эта задача не выполнена
    echo "<button name=\"butdel\">Удалить</button>";
    echo "</form></td></tr>\r\n";
}
echo "</table>";
