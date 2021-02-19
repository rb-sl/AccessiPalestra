<?php 
// Script to delete a registration
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
include $_SERVER['DOCUMENT_ROOT']."/libraries/fix_lib.php";
connect();
chk_access();

// Gets the registration to be deleted
$reg_st = prepare_stmt("SELECT * FROM register INNER JOIN athletes ON athlete_fk=athlete_id WHERE user_fk=? AND reserv_id=?");
$reg_st->bind_param("ii", $_SESSION['id'], $_GET['res']);
$r = execute_stmt($reg_st);

// If it exists, it gets deleted and fixed
if($r->num_rows != 0)
{
    $res = $r->fetch_assoc();

    $del_st = prepare_stmt("DELETE FROM register WHERE reserv_id=?");
    $del_st->bind_param("i", $_GET['res']);
    execute_stmt($del_st);

    writelog("[-] [registrazione] ".$res['athlete_fk']." [data] ".$res['date']." [slot] ".$res['slot_fk']);

    fix_on_delete($res['athlete_fk'], $res['date'], $res['probability']);
    $del_st->close();
}

$reg_st->close();
$mysqli->close();
header("Location: /register/register.php");
exit;
?>