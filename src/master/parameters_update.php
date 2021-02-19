<?php
// Backend page used to update the application's parameters
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);

$up_st = prepare_stmt("UPDATE properties SET days_before=?, hours_before=?, days_list=? WHERE prop_id=1");
$up_st->bind_param("iii", $_POST['days_before'], $_POST['hours_before'], $_POST['days_list']);
execute_stmt($up_st);

writelog("Parametri aggiornati [days_before] ".$_POST['days_before']." [hours_before] ".$_POST['hours_before']." [days_list] ".$_POST['days_list']);

$_SESSION['alert'] = "Modifica dei parametri completata";
$mysqli->close();
header("Location: /master/master.php");
exit;
?>