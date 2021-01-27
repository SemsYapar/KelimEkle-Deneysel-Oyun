<?php
require "auth_login.inc.php";
require "config.inc.php";

$db = new mysqli(
    MYSQL_HOST,
    MYSQL_USER,
    MYSQL_PASSWORD,
    MYSQL_DATABASE);

$sql = sprintf("SELECT isOnline FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
if ($row->isOnline == 0){
    header("Location: login.php");
}
//eğer hangi oyuncu olduğunu söyliyen değer sıfır değil ise kullanıcının çoktan oyuna başladığı anlaşılır, yaramaz geri game.php'ye postalanır
$sql = sprintf("SELECT isPlayer FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
if ($row->isPlayer != 0){
    header("Location: game.php");
}
//waitrooma düşen kullanıcı artık hazırdır ve bunun anlaşılması için değeri bir nevi 'true' anlamına gelen '1' olur(daha önce 0'dı)
$sql = sprintf("UPDATE accounts SET isReady=1 WHERE account='%s'", $_SESSION["account"]);
$db->query($sql);
//Burası çok önemli: burda waitrooma düşen her kullanıcı aşağıdaki istek ile kendisi gibi hazır halde bekleyen birisi olup olmadığını sorgular. Varsayalım ilk oyuncu geldi, burda kendisine rakip bulamaz ve aşağıdaki javascript e takılıp ajax yardımıyla ajax_control.php'ye sürekli kendine ibr oyuncu numarası verilip verilmediğini sorgular, bunun sebebi ilk oyuncudan sonra gelen ikinci oyuncunun bekliyen oyuncuyu fark etmesiyle onu otomatik oyun odasına alıp ilk oyuncu olduğu için 1. oyuncu değerini atamasıdır.
$sql = sprintf("SELECT account FROM accounts WHERE account<>'%s' AND isOnline=1 AND isReady=1 AND isPlayer=0", $_SESSION["account"]);
$empty_room = $db->query($sql)->fetch_object();

if ($empty_room){
    //İşte burda 2. oyuncunun eriştiği yere bakıyorsunuz. Oyuncu burda kendisinden önce bekliyen oyuncu ve kendisinin oda numaralarını ve oyuncu numaralarını belirler.(tabiki kendisi 2. rakibi 1. oyuncu olucak şekilde..)
    $sql = sprintf("INSERT INTO rooms (player1, player2, words, winner) VALUES('%s', '%s', '', '')", $empty_room->account, $_SESSION["account"]);
    $db->query($sql);
    $last_id = mysqli_insert_id($db);
    $sql = sprintf("UPDATE accounts SET room_id=%s WHERE account='%s'", $last_id, $empty_room->account);
    $db->query($sql);
    $sql = sprintf("UPDATE accounts SET room_id=%s WHERE account='%s'", $last_id, $_SESSION["account"]);
    $db->query($sql);
    $sql = sprintf("UPDATE accounts SET isPlayer=1 WHERE account='%s'", $empty_room->account);
    $db->query($sql);
    $sql = sprintf("UPDATE accounts SET isPlayer=2 WHERE account='%s'", $_SESSION["account"]);
    $db->query($sql);
    $db->close(); 
    //İkinci oyuncu oyun odasına yönlendiriliyor burda, birinci oyuncu kendi oyuncu numarasının değiştiğini yani 1 olduğunu fark eder etmez oda oyun odasına gidicek.(1. oyuncunun bunu nasıl yaptığını anlamak için bu sayfanın aşağısındaki ajax ypaısına ve devamında ajax_control.php'nin ilk if bloğuna bakın)
    header("Location: game.php");

} 
?>


<!DOCTYPE html>
<html lang="tr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet"/>

    <title>KelimEkle</title>
  </head>
  <body>
    <p>Az sabrederseniz sizi mutlak rakibinize yönlendiriyoruz..</p>
    <p class="fw-bold text-info" id="bildiri">Rakip Aranıyor..</p>
  
    <script src="bootstrap.js"></script>
    <script>
    function hop(xhttp) {
      if (xhttp.responseText == "start"){
        document.getElementById("bildiri").innerHTML = "Rakip bulundu.";
        window.location = "game.php";
      } else if (xhttp.responseText == "wait"){
        setTimeout(function(){
          var param = "control=waitroom";
          xhttp.open("POST", "ajax_control.php?control=waitroom", true);
          xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhttp.send(param);
          }, 3000);
      }
    }

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function (){
      if (this.readyState == 4 && this.status == 200){
        hop(this);
      }
    }
    var param = "control=waitroom";
    xhttp.open("POST", "ajax_control.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(param);
    </script>
  </body>
</html>
