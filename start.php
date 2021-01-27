<?php 
//Sadece login olup cookie oluşturanlar girsin diye gerekli dosya dahil ediliyor
require "auth_login.inc.php";
require "config.inc.php";

$db = new mysqli(
    MYSQL_HOST,
    MYSQL_USER,
    MYSQL_PASSWORD,
    MYSQL_DATABASE);
//Eğer online değilse login olmamıştır, login sayfasına yönlendirilir
$sql = sprintf("SELECT isOnline FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
if ($row->isOnline == 0){
    header("Location: login.php");
}
//eğer hazırsa waitroomda beklemesi beklenmelidir, yaramazı waitroom.php'ye yönlendirelim 
$sql = sprintf("SELECT isReady FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
if ($row->isReady == 1){
  header("Location: waitroom.php");
}
//kazanma ve bozgun verileri çekilir ve html in içine gömülür
$sql = sprintf("SELECT win, lose FROM accounts WHERE account='%s'", $_SESSION["account"]);
$result = $db->query($sql);
$row = $result->fetch_object();
$win_count = $row->win;
$lose_count = $row->lose;


?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet" />

    <title>KelimEkle</title>
  </head>
  <body>
    <div class="container-fluid al">
      <div style="height: 100vh" class="row justify-content-center">
        <div style="position: absolute ;top: 20vh" class="col-12 d-flex align-items-center flex-column">
          <div class="text-center">
            <p class="fs-2 fw-bold">Hoşgeldin <?php echo $_SESSION["account"]?></p>
            <a href="waitroom.php" class="btn btn-success btn-lg">Oyuncu Bul</a>
          </div>
          <div class="d-flex flex-row mt-3 mb-3">
            <ul class="list-group me-1">
              <li class="list-group-item text-center disabled">WİN</li>
              <li class="list-group-item text-center"><?php echo $win_count; ?></li>
            </ul> 
            <ul class="list-group ms-1">
              <li class="list-group-item text-center disabled">LOSE</li>
              <li class="list-group-item text-center"><?php echo $lose_count; ?></li>
            </ul>
          </div>
          <form action="logout.php" method="post">
            <button name="logout_game" class="btn btn-outline-danger">Oturumu Kapat</button>
          </form>
        </div>
      </div>
    </div>
    <script src="bootstrap.js"></script>
  </body>
</html>
