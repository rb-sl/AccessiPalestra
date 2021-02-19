<?php 
// Backend page to grant master access to a user
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);

// Query to retrieve a user given the id
$chk_stmt = prepare_stmt("SELECT * FROM users WHERE user_id=?");
$chk_stmt->bind_param("i", $id);
$already_checked = [];

$id = $_GET['id'];
$ret = execute_stmt($chk_stmt);
$row = $ret->fetch_assoc();

if($row and $row['master'] == 0)
{
    $up_stmt = prepare_stmt("UPDATE users SET master=1, granted_by=? WHERE user_id=?");
    $up_stmt->bind_param("ii", $_SESSION['id'], $_GET['id']);
    execute_stmt($up_stmt);
    writelog("Concesso l'Accesso Maestro a ".$row['username']);
    $_SESSION['alert'] = "Acceso Maestro concesso correttamente";
}
else
    $_SESSION['alert'] = "Utente selezionato non valido";

$mysqli->close();
header("Location: /master/user_mng/show_users.php");
?>