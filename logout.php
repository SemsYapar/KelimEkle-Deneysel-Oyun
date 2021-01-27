<?php
require "auth_login.inc.php";
require "config.inc.php";


$db = new mysqli(
  MYSQL_HOST,
  MYSQL_USER,
  MYSQL_PASSWORD,
  MYSQL_DATABASE);

if (isset($_POST["logout_match"])){
  $sql = sprintf("UPDATE rooms SET winner='%s' WHERE id=%s", $_SESSION["enemy"], $_SESSION["id"]);
  $db->query($sql);
  $sql = sprintf("UPDATE accounts SET lose=lose+1, isReady=0, isPlayer=0, room_id=0 WHERE account='%s'", $_SESSION["account"]);
  $db->query($sql);
  foreach ($_SESSION as $key=>$value){
    if(($key==="player") || ($key==="enemy") || ($key==="id")){
      echo $key;
      unset($_SESSION[$key]);
    }
  }
  header("Location: start.php");
}

if (isset($_POST["logout_game"])){
  echo "logout_game";
  $sql = sprintf("UPDATE accounts SET isOnline=0, isReady=0, isPlayer=0, room_id=0 WHERE account='%s'", $_SESSION["account"]);
  $db->query($sql);
  foreach ($_SESSION as $key=>$value){
    if(($key==="isAdmin") || ($key==="account")){
      unset($_SESSION[$key]);
    }
  }
  header("Location: login.php");
}

?>
