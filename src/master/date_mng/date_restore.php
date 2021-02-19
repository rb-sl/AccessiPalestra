<?php 
// Backend script to allow an administrator to restore dates
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);

$log = "Date ripristinate";

// Restores non-permanently eliminated dates
if(isset($_POST['slot']))
{
    $dsl_st = prepare_stmt("DELETE FROM forbidden_slots WHERE forb_fk=? AND slot_fk=?");
    $dsl_st->bind_param("ii", $forb_id, $slot_id);
    $log .= "\n>>> [singola] $forb_id";
    foreach($_POST['slot'] as $forb_id => $slot)
        foreach($slot as $slot_id => $val)
        {
            execute_stmt($dsl_st);
            $log .= "\n>>>>>> [slot] $slot_id";
        }
    $dsl_st->close();

    $del_st = prepare_stmt("DELETE FROM forbidden WHERE forb_id NOT IN (SELECT forb_fk FROM forbidden_slots) AND permanent=0");
    execute_stmt($del_st);
    $del_st->close();
}

// Restores permanently eliminated dates
if(isset($_POST['perm']))
{
    $del_st = prepare_stmt("DELETE FROM forbidden WHERE forb_id=?");
    $del_st->bind_param("i", $forb_id);

    foreach($_POST['perm'] as $forb_id => $val)
    {
        execute_stmt($del_st);
        $log .= "\n>>> [permanente] $forb_id";
    }
}

$_SESSION['alert'] = "Date ripristinate correttamente";
writelog($log);
$mysqli->close();
header("Location: /master/date_mng/date_manager.php");
exit;
?>