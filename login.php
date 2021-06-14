<!--Zona de pruebas-->

<form action="" method="post">
    <button>Dale</button>
</form>

<?php
session_start();
$_SESSION["userid"] = 2;
header("location: wargame.php")
?>