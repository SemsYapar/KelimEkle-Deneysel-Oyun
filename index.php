<?php

require "config.inc.php";

$account = "";
$password = "";
$rules = "";
if (isset($_POST["submit"])){
  $ok = True;
	
	if (!isset($_POST["account"]) || $_POST["account"] === ""){
		$ok = False;
	} else{
		$account = $_POST["account"];
	} 
	if (!isset($_POST["password"]) || $_POST["password"] === ""){
		$ok = False;
	} else{
		$password = $_POST["password"];
	}
	if (!isset($_POST["rules"]) || $_POST["rules"] === ""){
    $ok = False;
	} else{
    $rules = $_POST["rules"];
  }
	if ($ok){
		$hash = password_hash($password, PASSWORD_DEFAULT);

		$db = new mysqli(
			MYSQL_HOST,
			MYSQL_USER,
			MYSQL_PASSWORD,
			MYSQL_DATABASE);

		$sql = sprintf(
			"INSERT INTO accounts (account, hash) VALUES('%s','%s')",
			$db->real_escape_string($account), $db->real_escape_string($hash));
      $db->query($sql);
      $db->close(); 
      echo "lol";
      header("Location: login.php");
} 
} 
?>

<!DOCTYPE html>
<html lang="tr">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap CSS -->
    <link href="bootstrap.css" rel="stylesheet" />

    <title>KelimEkle</title>
  </head>
  <body>
    <div class="container-fluid bg-secondary">
      <div style="height: 100vh" class="row">
        <div style="position: absolute;top: 10vh" class="col d-flex justify-content-center ">
          <form action="" method="post" style="" class="shadow-lg p-3 bg-white rounded border border-primary border border-2" novalidate>
            <p class="text-center fs-2 fw-bold">Kayıt OL</p>
            <div class="form-floating mb-3">
              <input
                type="text"
                class="form-control"
                id="account_name_input"
                placeholder=""
                name="account"
                value="<?php if(isset($_POST["account"])){echo $_POST["account"];} else{ echo "";}?>"
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
            <div class="form-check mb-3">
              <input
                type="checkbox"
                class="form-check-input"
                id="exampleCheck1"
                name="rules"
              required>
              <label class="form-check-label" for="exampleCheck1"
                >Kurallarınıza uyacağıma sadakatim ve onurum adına söz
                veriyorum</label
              >
            </div>
            <button type="submit" class="btn btn-outline-success" name="submit">Register</button>
          </form>
          <form action="login.php" method="post" class="btn-group-vertical">
            <button type="submit" class="btn btn-primary" name="login"><p class="fw-bold">L<br>O<br>G<br>I<br>N</p></button>
          </form>
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
