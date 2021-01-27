<?php
//database config dosyası sayfaya dahil ediliyor
require "config.inc.php";

session_start();

if (isset($_SESSION["account"])){
  header("Location: start.php");
}

if (isset($_POST["account"]) && isset($_POST["password"])){

  $db = new mysqli(
      MYSQL_HOST,
      MYSQL_USER,
      MYSQL_PASSWORD,
      MYSQL_DATABASE);

  $sql = sprintf("SELECT * FROM accounts WHERE account=BINARY('%s')",
                  $db->real_escape_string($_POST["account"]));

  $result = $db->query($sql);
  $row = $result->fetch_object();
  if ($row){
      $hash = $row->hash;
      if (password_verify($_POST["password"], $hash)){
          $_SESSION["isAdmin"] = $row->isAdmin;
          $_SESSION["account"] = $row->account;
          $sql = sprintf("UPDATE accounts SET isOnline=1 WHERE account='%s'",
          $_SESSION["account"]);
          $db->query($sql);
          //Doğrulanan kullanıcı ana menünün olduğu sayfaya alınıyor
          header("Location: start.php");
          } else{
              echo "Kullanıcı adı veya şifre hatalı.";
          }   
      } else{
          echo "Kullanıcı adı veya şifre hatalı.";
      }
  $db->close();
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
    <div class="container-fluid bg-secondary">
      <div style="height: 100vh" class="row">
        <div style="position: absolute;top: 10vh" class="col d-flex justify-content-center ">
          <div class="shadow-lg p-3 bg-white rounded border border-primary border border-2">
          <form action="" method="post" novalidate>
            <p class="text-center fs-2 fw-bold">Giriş YAP</p>
            <div class="form-floating mb-3">
              <input
                type="text"
                class="form-control"
                id="account_name_input"
                placeholder=""
                name="account"
                value=""
              required>
              <label for="account_name_input" class="form-label"
                >Kullanıcı Adı</label
              >
            </div>
            <div class="form-floating mb-3">
              <input
                type="password"
                class="form-control"
                id="exampleInputPassword1"
                placeholder="Parola"
                name="password"
              required>
              <label for="exampleInputPassword1" class="form-label"
                >Parola</label
              >
            </div>
            <button type="submit" class="btn btn-outline-primary" name="submit">Login</button>
          </form>
          <a style="position: relative; left: 70%;bottom: 22px;" href="index.php">Kayıt Ol</a>
          </div>
        </div>
      </div>
    </div>
    <script src="bootstrap.js"></script>
    <script>
    form = document.getElementsByTagName("form")[0];
    form.addEventListener('submit', function (event) {
      if (!this.checkValidity()) {
        console.log(event);
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
      }
    }, false);
    </script>
  </body>
</html>
