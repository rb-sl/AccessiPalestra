<?php
// Script to create a new profile
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();

// Does not allow a registration with an active profile
if(isset($_SESSION['id']))
{
    $_SESSION['alert'] = "Utente già registrato";
    header("Location: /");
    exit;
}

if(!isset($_POST['user']) or !isset($_POST['psw']));
{
    $_SESSION['alert'] = "Inserire le informazioni richieste";
    header("Location: /user/signup.php");
    exit;
}

$stmt = prepare_stmt("INSERT INTO users (username, password, master) VALUES(?, ?, 0)");
$stmt->bind_param("ss", $_POST['user'], $password);
$password = md5($_POST['psw']);
$ret = execute_stmt($stmt);

if($mysqli->errno == 1062)
{
    // A SQL error 1062 reports the violation of a unique key; the user is asked to repeat the procedure
	$_SESSION['alert'] = "Username già in uso, per favore sceglierne un altro";
    header("Location: /user/signup.php");
}
else
{
    $_SESSION['user'] = $_POST['user'];
    $_SESSION['id'] = $mysqli->insert_id;
    writelog("Registrazione nuovo profilo");
    $_SESSION['alert'] = "Registrazione completata";
    header("Location: /guide.php");
}

$mysqli->close();
exit;
?>