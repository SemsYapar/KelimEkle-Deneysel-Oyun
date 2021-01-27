<?php
require "auth_admin.inc.php";
?>
<ul>
<?php
require "config.inc.php";

$db = new mysqli(
    MYSQL_HOST,
    MYSQL_USER,
    MYSQL_PASSWORD,
    MYSQL_DATABASE);

$sql = "SELECT * FROM accounts";
$results = $db->query($sql);

foreach($results as $row){
    printf("<li>%s
            <a href='update.php?id=%s'>güncelle</a>
            <a href='delete.php?id=%s'>sil</a>
            </li>",
        htmlspecialchars($row["account"]),
        htmlspecialchars($row["id"]),
        htmlspecialchars($row["id"]));

}

$db->close();
?>
</ul>