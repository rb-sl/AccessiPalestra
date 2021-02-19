<?php
// Backend script used to update a user's profile
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access();
connect();

// Builds the query based on whether the user wants to modify the queries
if(!empty($_POST['psw']))
{
    $stmt = prepare_stmt("UPDATE users SET username=?, password=? WHERE user_id=?");
    $stmt->bind_param("ssi", $_POST['user'], $password, $_SESSION['id']);
    $password = md5($_POST['psw']);
}
else
{
    $stmt = prepare_stmt("UPDATE users SET username=? WHERE user_id=?");
    $stmt->bind_param("si", $_POST['user'], $_SESSION['id']);
}
execute_stmt($stmt);

if($mysqli->errno == 1062)
	$_SESSION['alert'] = "Username già in uso, per favore sceglierne un altro";
else
{
    $_SESSION['user'] = $_POST['user'];
    writelog("[modifica profilo] ".$_SESSION['id']);
    $_SESSION['alert'] = "Modifica completata";
}

$mysqli->close();
header("Location: /user/profile.php");
exit;
?>