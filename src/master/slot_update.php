<?php 
// Backend script to allow an administrator to modify slots
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
include $_SERVER['DOCUMENT_ROOT']."/libraries/fix_lib.php";
chk_access(true);
connect();

$log = "Aggiornamento slot";

$prop = get_prop();

// Statement to update existing slots
$up_st = prepare_stmt("UPDATE slots SET start_time=?, end_time=?, places=? WHERE slot_id=?");
$up_st->bind_param("ssii", $start_time, $end_time, $places, $slot_id);

// Statement to insert new slots
$in_st = prepare_stmt("INSERT INTO slots(weekday, start_time, end_time, places, active)
    VALUES(?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE end_time=?, places=?, active=1");
$in_st->bind_param("issisi", $weekday, $start_time, $end_time, $places, $end_time, $places);

// Start of transaction
$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

// Slot update
$slots = "0";
if(isset($_POST['day']))
    foreach($_POST['day'] as $slot_id => $weekday)
    {
        $start_time = $_POST['start'][$slot_id];
        $end_time = $_POST['end'][$slot_id];
        $places = $_POST['places'][$slot_id];
        execute_stmt($up_st);

        // Fixing the registration if the places are diminished
        adjust_slot($slot_id);

        $log .= "\n>>> [mantenuto] $slot_id [giorno] $weekday [orario] $start_time -> $end_time [posti] $places";
        $slots .= ", ".$slot_id;
    }

// Slot disabling
$del_st = prepare_stmt("UPDATE slots SET active=0 WHERE slot_id NOT IN($slots)");
execute_stmt($del_st);

// Slot insertion
if(isset($_POST['newday']))
    foreach($_POST['newday'] as $id => $weekday)
    {
        $start_time = $_POST['newstart'][$id];
        $end_time = $_POST['newend'][$id];
        $places = $_POST['newplaces'][$id];
        execute_stmt($in_st);

        $log .= "\n>>> [inserito] [giorno] $weekday [orario] $start_time -> $end_time [posti] $places";
    }

// Deleting registrations for inactive slots
$todel_st = prepare_stmt("SELECT reserv_id FROM register INNER JOIN slots ON slot_fk=slot_id 
    WHERE (date>CURDATE() OR (date=CURDATE() AND DATE_SUB(start_time, INTERVAL ? HOUR) > CURTIME())) 
    AND active=0");
$todel_st->bind_param("i", $prop['hours_before']);
$rdel = execute_stmt($todel_st);
$todel = "0";
while($row = $rdel->fetch_assoc())
    $todel .= ", ".$row['reserv_id'];

$regdel_st = prepare_stmt("DELETE FROM register WHERE reserv_id IN ($todel)");
execute_stmt($regdel_st);

// Statement to check that there are no overlapping active slots
$chk_st = prepare_stmt("SELECT * FROM slots AS a INNER JOIN slots AS b ON a.weekday=b.weekday AND a.slot_id<>b.slot_id
    WHERE a.start_time>b.start_time AND a.start_time<b.end_time AND a.active=1 AND b.active=1");
$chk = execute_stmt($chk_st);

// The transaction gets committed iff no overlapping slots are found
if($chk->num_rows > 0)
{
    $mysqli->rollback();
    $_SESSION['alert'] = "Errore nell'inserimento: alcuni allenamenti sono sovrapposti. Per favore ripetere la procedura";
    writelog("[!] Errore: rollback [slots]");
}
else
{
    $mysqli->commit();
    $_SESSION['alert'] = "Modifica completata";
    writelog($log);
}
$mysqli->close();
header("Location: /master/master.php");
?>