<?php
require "auth_login.inc.php";
require "config.inc.php";

$db = new mysqli(
    MYSQL_HOST,
    MYSQL_USER,
    MYSQL_PASSWORD,
    MYSQL_DATABASE);
//oyuncu numarası olmıyan ama buraya gelen yaramaz ana menüye postalanır
$sql = sprintf("SELECT isPlayer FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
if ($row->isPlayer == 0){
    header("Location: start.php");
}  else{
  //İşte burası'da çok işimize yarıyacak olan player,enemy ve id cookie'sinin yapıldığı yer..
  $_SESSION["player"] = $row->isPlayer;
  
  if ($_SESSION["player"] == 2){
      $enemy_player = "player1";
  } else{
      $enemy_player = "player2";
  }
  $sql = sprintf("SELECT %s FROM rooms WHERE player%s='%s'", $enemy_player, $_SESSION["player"], $_SESSION["account"]);
  $result = $db->query($sql);
  $row = $result->fetch_object();
  $_SESSION["enemy"] = $row->$enemy_player;

  $sql = sprintf("SELECT room_id FROM accounts WHERE account='%s'", $_SESSION["account"]);
  $result = $db->query($sql);
  $row = $result->fetch_object();
  $_SESSION["id"] = $row->room_id;
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
    <link href="style.css" rel="stylesheet"/>

    <title>KelimEkle</title>
  </head>
  <body>
    <div class="container-fluid">
      <form style="top: 10px; right: 10px;" class="position-absolute d-flex flex-column" method="post" action="logout.php">
        <button name="logout_match" type="submit" class="btn btn-dark" onmouseover="mouseover(this)" onmouseout="mouseout(this)">Maçı Terk Et</button>
      </form>
      <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
        	<div class="card mt-2">
        		<div class="card-header d-flex justify-content-between">
             <div>Kelime Eklemece</div>
             <div id="tiktak"></div>
            </div>
        		<div class="card-body chat-boyut overflow-auto" id="kap">
        			<ul class="chat-list" id="chat">
        				
              </ul>
            </div>
            <div class="card-footer input-group">
              <input type="text" class="form-control" id="kelime">
              <button class="btn btn-outline-secondary" type="button" id="gonder" onclick="send_word()">Ekle</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="bootstrap.js"></script>
    <script>
    //NOT: maçı terk eden eğer sıradaki rakip değilse diğeri hamle yapana kadar oyunun bittiğini anlayamıyor
    // Son harf ı olunca diğer adam ne yapsa ne etse kelime uyduramıyor
    //Bu sayfadaki hemen hemen her şeyde kullanıcağımıx xhr objesi..
    var xhttp = new XMLHttpRequest();
    //Oyundan çıkış düğmesi için renk değişimi fonksiyonları..(şimdi düşündümde css ile de yapılabilirdi.)
    function mouseover(elmnt){
      elmnt.classList.replace("btn-dark", "btn-danger");
    }
    function mouseout(elmnt){
      elmnt.classList.replace("btn-danger", "btn-dark");
    }
    //Oyun ekranındaki kelimeler artınca scrollun otomatik aşağıya inmesine yarıyan fonksiyon..
    function auto_down() {
      console.log("scroll fire");
      scroll_elmnt = document.getElementById("kap");
      y_default = scroll_elmnt.scrollHeight;
      y_currently = scroll_elmnt.scrollTop;
      y_client = scroll_elmnt.clientHeight;

      if (y_client + y_currently !== y_default){
        scroll_elmnt.scrollTop = scroll_elmnt.scrollHeight - scroll_elmnt.clientHeight;
      }
    }
    //ajax_control.php'deki control=button bloğunun altında yapılan işlemlerden sonra cevabın geldiği fonksiyon
    function hop_send(xhttp) {
      if (xhttp.responseText == "try_use_disabled_button"){
        console.log("disabled butona rağmen istek gönderenler buraya düşer.");
      } 
      else if(xhttp.responseText == "no_harmony") {
        console.log("uyuşmayan kelime ekliyenler buraya düşer.");
        document.getElementById("kelime").value = "";   
        document.getElementById("kelime").placeholder = "Eklediğin kelime bir önceki ile uyuşmuyor";
        document.getElementById("gonder").disabled = false;
      } 
      else if(xhttp.responseText == "repetitive_word"){
        console.log("daha önce kullanılan bir kelimeyi ekliyenler buraya düşer ve kaybeder(şimdilik).");
        alert("Daha önce eklenen bir kelimeyi tekrar ekleme girişiminiz,\noyunu kaybetmenizle sonuçlanmıştır\nBir sonraki el artık..");
        var form = document.createElement('form');
        var element1 = document.createElement("input");
        form.method = "POST";
        form.action = "logout.php";

        element1.name="logout_match";
        form.append(element1); 

        document.body.append(form);
        form.submit();
      } else{
          console.log("uyuşan kelime ekliyenler buraya gelir ve bekleme döngüsüne girerler(karşı tarafın hamle yapmasını)");
          document.getElementById("chat").innerHTML += xhttp.responseText;
          auto_down(this)
          xhttp.onreadystatechange = function (){
          if (this.readyState == 4 && this.status == 200){
            hop_turn(this);
          }
          }
          var param = "control=turn";
          xhttp.open("POST", "ajax_control.php", true);
          xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhttp.send(param);
      }
    }
    //Ekle butonuna basıldığında çalışan fonksiyon, kelimeyi süzer ve TDK da kontrol ettirir. Süzüp ajax_control.php'de control=button a atar kelimeyi..
    function send_word(){
      document.getElementById("gonder").disabled = true;
      var kelime = document.getElementById("kelime").value;
      if (kelime != ""){
        if (kelime.length != "1"){
          if (kelime.search(" ") == -1){
            xhttp.onreadystatechange = function (){
              if (this.readyState == 4 && this.status == 200){
                if (JSON.parse(this.responseText).error == "Sonuç bulunamadı"){
                  console.log("kelimede hata var.")
                  document.getElementById("kelime").value = "";
                  document.getElementById("kelime").placeholder = "Eklemek istediğiniz kelime anlamlı değil";
                  document.getElementById("gonder").disabled = false;
                } else{
                    console.log("kelimede hata yok.");
                    xhttp.onreadystatechange = function (){
                      if (this.readyState == 4 && this.status == 200){
                        hop_send(this);
                      }
                    }
                    param = "control=button&kelime="+kelime;
                    xhttp.open("POST", "ajax_control.php", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send(param);
                }
              }
            }
            xhttp.open("GET", "https://sozluk.gov.tr/yazim?ara="+kelime.toLowerCase(), true);
            xhttp.send();
          } else{ 
            document.getElementById("kelime").value = "";
            document.getElementById("kelime").placeholder = "Eklediğiniz kelimede boşluk olmamalı";
            document.getElementById("gonder").disabled = false;
            } 
        } else{
          document.getElementById("kelime").value = ""; 
          document.getElementById("kelime").placeholder = "Eklediğiniz kelime tek harf olmamalı";
          document.getElementById("gonder").disabled = false;
          }
      } else{ 
        document.getElementById("kelime").value = "";
        document.getElementById("kelime").placeholder = "Kelime eklemek için kelimeye ihtiyacınız var(!)";
        document.getElementById("gonder").disabled = false;
        }
    }
    //sıra kimdeyse diğeri burda bekler ve ajax_control.php'deki control=turn bloğuna atar 1 saniyede bir ve ordan gelen cevaba göre sıra bekliyene geçer.(Butona basıldığında databasedeki turn değeri, burda bekliyen kişinin oyuncu numarasıyla değişir burda sürekli istek yollıyan adamda bunu fark ettiğinde artık sıra ondandır yani yazı yazıp butona basabilir ve az önce butona basan kullanıcıda databasedeki turn değeri değiştiği için beklemeye koyulur ve döngü böyle devam eder)
    function hop_turn(xhttp) {
      if (xhttp.responseText == "wait"){
        console.log("stop");
        document.getElementById("kelime").value = "";   
        document.getElementById("kelime").placeholder = "Kelime Ekleme Sırası Karşıda...";
        setTimeout(function(){
          var param = "control=turn";
          xhttp.open("POST", "ajax_control.php", true);
          xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhttp.send(param);}, 1000);
      } 
      else if (xhttp.responseText == "start"){
        console.log("start");
        document.getElementById("gonder").disabled = false;
        document.getElementById("kelime").placeholder = "Hadi Kelime Ekle!";

      } 
      else if (xhttp.responseText == "finish"){
        alert("Rakibiniz sizin kelimenize ekleyemedi ve kazandınız!!");
        window.location = "start.php";
      } 
      else{
        document.getElementById("chat").innerHTML += xhttp.responseText;
        auto_down(this)
        document.getElementById("gonder").disabled = false;
        document.getElementById("kelime").placeholder = "Hadi Kelime Ekle!";

      }
    }
    //İlk başta kimin 1. kimin 2. oyuncu olduğunu anlamak için databasedeki turn değerini sorgulayan tetikleyici alan, bir nevi kod silsilesinin başladığı kısım..
    xhttp.onreadystatechange = function (){
        if (this.readyState == 4 && this.status == 200){
          hop_turn(this);
        }
      }
    var param = "control=turn";
    xhttp.open("POST", "ajax_control.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send(param);

    </script>
  </body>
</html>
