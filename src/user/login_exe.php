<?php
// Script to log in a user
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();

// Login is disabled if there is an active already 
if(isset($_SESSION['id']))
{
    $_SESSION['alert'] = "Accesso già effettuato";
    header("Location: /");
	exit;
}

$stmt = prepare_stmt("SELECT * FROM users WHERE BINARY username=? AND password=?");
$stmt->bind_param("ss", $_POST['user'], $password);
$password = md5($_POST['psw']);
$ret = execute_stmt($stmt);

if($ret->num_rows != 0)
{
	$row = $ret->fetch_assoc();
    $_SESSION['user'] = $row['username'];
    $_SESSION['id'] = $row['user_id'];
    if($row['master'] == 1)
	    $_SESSION['master'] = true;
	
    writelog("Accesso");
    
    $stmt = prepare_stmt("UPDATE users SET last_login=NOW() WHERE user_id=".$row['user_id']);
    execute_stmt($stmt);
    
    if(!$row['master'])
        header("Location: /register/register.php");
    else
        header("Location: /");
    exit;
}

$_SESSION['err'] = 2;
$mysqli->close();
header("Location: /user/login.php");
exit;
?>