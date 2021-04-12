<?php 
require "auth_login.inc.php";
require "config.inc.php";
require 'vendor/autoload.php';


if (isset($_POST["control"])){
  $db = new mysqli(
      MYSQL_HOST,
      MYSQL_USER,
      MYSQL_PASSWORD,
      MYSQL_DATABASE);

  if ($_POST["control"] == "waitroom"){
      
      $sql = sprintf("SELECT isPlayer FROM accounts WHERE account='%s'", $_SESSION["account"]);  
      $result = $db->query($sql);
      $row = $result->fetch_object();
      if ($row->isPlayer == 1){
          echo "start";
      } else {
          echo "wait";
      }
  } 

  if ($_POST["control"] == "button"){
    $sql = sprintf("SELECT turn FROM rooms WHERE id=%s", $_SESSION["id"]);
    $result = $db->query($sql);
    $row = $result->fetch_object();
    if ($row->turn == $_SESSION["player"]){
      $sql = sprintf("SELECT words FROM rooms WHERE id=%s", $_SESSION["id"]);
      $result = $db->query($sql);
      $words = $result->fetch_object()->words;
      $son_kelime = implode(array_slice(explode(",", $words), -2, 1));
      $kelime = $_POST["kelime"];
      if ($words == "" || ($words != "" && mb_strtolower(mb_substr($kelime, 0, 1)) == mb_strtolower(mb_substr($son_kelime, -1, 1)))){
        if ($words == "" || !in_array(mb_strtolower($kelime), array_map("mb_strtolower", explode(",", $words)))){
          $out = sprintf('<li class="out">
                        <div class="chat-img">
                            <img alt="Avtar" src="icon/anonymous2.png">
                        </div>
                        <div class="chat-body">
                            <div class="chat-message">
                                <h5>%s</h5>
                                <p>%s</p>
                            </div>
                        </div>
                    </li>', $_SESSION["account"], $kelime);
          $sql = sprintf("UPDATE rooms SET words = CONCAT(words, '%s,') WHERE id=%s", $kelime, $_SESSION["id"]);
          $db->query($sql);
          if ($_SESSION["player"] == 1){
              $new_turn = 2;
          } else if ($_SESSION["player"] == 2){
              $new_turn = 1;
          }
          $sql = sprintf("UPDATE rooms SET turn=%s WHERE id=%s", $new_turn, $_SESSION["id"]);
          $db->query($sql); 
          echo $out;
        } else{ echo "repetitive_word";}
      } else{ echo "no_harmony";}
    } else{ echo "try_use_disabled_button";}
  }


  if($_POST["control"] == "turn"){
    $sql = sprintf("SELECT winner FROM rooms WHERE id=%s", $_SESSION["id"]);
    $result = $db->query($sql);
    $winner = $result->fetch_object()->winner;
    if ($winner != ""){
      $sql = sprintf("UPDATE accounts SET win=win+1, isReady=0, isPlayer=0, room_id=0 WHERE account='%s'", $_SESSION["account"]);
      $db->query($sql);
      foreach ($_SESSION as $key=>$value){
        if($key === ("player" || "enemy" || "id")){
          unset($_SESSION[$key]);
        }
      }
      echo "finish";
      exit();
    }
    $sql = sprintf("SELECT turn FROM rooms WHERE id=%s", $_SESSION["id"]);
      $result = $db->query($sql);
      $row = $result->fetch_object();
      if ($row->turn == $_SESSION["player"]){
        $sql = sprintf("SELECT words FROM rooms WHERE id=%s", $_SESSION["id"]);
        $result = $db->query($sql);
        $words = $result->fetch_object()->words;
        if ($words){
          $son_kelime = implode(array_slice(explode(",", $words), -2, 1));
          $in = sprintf('<li class="in">
                  <div class="chat-img">
                      <img alt="Avtar" src="icon/anonymous2.png">
                  </div>
                  <div class="chat-body">
                      <div class="chat-message">
                          <h5>%s</h5>
                          <p>%s</p>
                      </div>
                  </div>
              </li>', $_SESSION["enemy"], $son_kelime);
          echo $in;
        } else{ echo "start";}
      } else{ echo "wait";}
  }

}
?>
