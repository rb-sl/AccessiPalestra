<?php 
// Backend script to allow an administrator to remove dates
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
include $_SERVER['DOCUMENT_ROOT']."/libraries/fix_lib.php";
chk_access(true);
connect();

$log = "";

// Prepares the existence check for the date
$forb_st = prepare_stmt("SELECT forb_id, permanent FROM forbidden WHERE date=?");
$forb_st->bind_param("s", $datestring);

// Prepares the statement to upgrade a deleted date and remove its connected slots
$up_st = prepare_stmt("UPDATE forbidden SET permanent=1 WHERE forb_id=?");
$up_st->bind_param("i", $id);

$rem_st = prepare_stmt("DELETE FROM forbidden_slots WHERE forb_fk=?");
$rem_st->bind_param("i", $id);

// Prepares the date insert statement
$in_st = prepare_stmt("INSERT INTO forbidden(date, permanent) VALUES(?, ?)");
$in_st->bind_param("si", $datestring, $_POST['permanent']);

// Prepares the slot insert statement
$slot_st = prepare_stmt("INSERT INTO forbidden_slots(forb_fk, slot_fk) VALUES(?, ?) ON DUPLICATE KEY UPDATE forb_fk=forb_fk");
$slot_st->bind_param("ii", $id, $slot_id);

// Prepares the delete statement for the register
$del_st = prepare_stmt("DELETE FROM register WHERE date=?");
$del_st->bind_param("s", $datestring);

if($_POST['multidate'] == 0)
{
    // If only one date needs to be removed, it is added to the table and, if needed, slots are removed too
    $datestring = $_POST['date1'];
    $r = execute_stmt($forb_st);
    $forb_st->close();

    if($r->num_rows !== 0)
    {
        $d = $r->fetch_assoc();
        $id = $d['forb_id'];
        if($d['permanent'] < $_POST['permanent'])
        {
            execute_stmt($up_st);
            execute_stmt($rem_st);
        }
    }
    else
    {
        execute_stmt($in_st);
        $in_st->close();
        $id = $mysqli->insert_id;
    }
    
    $log .= "[cancellato] $datestring";

    // Slots are removed iff not permanent, otherwise deletes only the date
    if($_POST['permanent'] == 0 and (!isset($d) or $d['permanent'] == 0))
    {
        // Prepares the select query from the register
        $reg_st = prepare_stmt("SELECT * FROM register WHERE date=? AND slot_fk=?");
        $reg_st->bind_param("si", $datestring, $slot_id);

        // Prepares the delete statement for the register
        $rd_st = prepare_stmt("DELETE FROM register WHERE reserv_id=?");
        $rd_st->bind_param("i", $reserv_id);

        foreach($_POST['slot'] as $slot_id => $val)
        {
            execute_stmt($slot_st);
            $log .= "\n>>>[slot] $slot_id";

            // Fixing deleted registrations
            $ret = execute_stmt($reg_st);
            while($row = $ret->fetch_assoc())
            {
                $reserv_id = $row['reserv_id'];
                execute_stmt($rd_st);
                
                $log .= "\n>>>>>>[-registrazione] [atleta] ".$row['athlete_fk']." [slot] $slot_id";
                fix_on_delete($row['athlete_fk'], $_POST['date1'], $row['probability']);
            }
        }
        $reg_st->close();
        $rd_st->close();
    }
    else
        execute_stmt($del_st);
}
else
{
    // For each date from date1 to date2 the ones hosting a session are removed
    $date1 = new DateTime($_POST['date1']);
    $date2 = new DateTime($_POST['date2']);
    $date2->setTime(0,0,1);

    $interval = DateInterval::createFromDateString("1 day");
    $period = new DatePeriod($date1, $interval, $date2);

    $log .= "[cancellato] ".$_POST['date1']." -> ".$_POST['date2'];

    // Prepares the query to get the slots given the weekday
    $ws_st = prepare_stmt("SELECT slot_id FROM slots WHERE weekday=? AND active=1");
    $ws_st->bind_param("i", $weekday);

    foreach ($period as $date) 
    {
        $datestring = $date->format("Y-m-d");
        $weekday = date("w", strtotime($datestring));
        $r = execute_stmt($ws_st);

        if($r->num_rows != 0)
        {
            // Checks if the date already exists, if not it is inserted
            $rforb = execute_stmt($forb_st);
            if($rforb->num_rows !== 0)
            {
                $d = $rforb->fetch_assoc();
                $id = $d['forb_id'];
                if($d['permanent'] < $_POST['permanent'])
                {
                    execute_stmt($up_st);
                    execute_stmt($rem_st);
                }
            }
            else
            {
                execute_stmt($in_st);
                $id = $mysqli->insert_id;
            }

            // Slots are removed iff not permanent
            if($_POST['permanent'] == 0 && (!isset($d) || $d['permanent'] == 0))
                while($row = $r->fetch_assoc())
                {
                    $slot_id = $row['slot_id'];
                    execute_stmt($slot_st);
                }

            // Removes connected registrations
            execute_stmt($del_st);
        }
    }
    $ws_st->close();
}
$forb_st->close();
$in_st->close();
$slot_st->close();
$del_st->close();

writelog($log);

$_SESSION['alert'] = "Date rimosse correttamente";
$mysqli->close();
header("Location: /master/date_mng/date_manager.php");
exit;
?>