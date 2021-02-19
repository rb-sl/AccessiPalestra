<?php
// Page called by a cron job to resolve any registration with probability < 1
// Needs to be in the same folder as general.php and fix_lib.php
include "general.php";
include "fix_lib.php";
connect();

// Gets the application's properties
$prop = get_prop();

// Gets the start time of the day
$start_st = prepare_stmt("SELECT MIN(start_time) AS start_time FROM slots LEFT JOIN register ON slot_fk=slot_id WHERE date=CURDATE() AND active=1");
$ret = execute_stmt($start_st);
$time = $ret->fetch_assoc();

// Exits if the time is wrong
if($ret->num_rows==0 or !isset($time['start_time']) or time_diff(date("H:i", strtotime($time['start_time'])), date("H:i")) > $prop['hours_before'])
{
    writesolver("conflict_solver: wrong time");
	echo "conflict_solver: wrong time";
    exit;
} 

// Starts a MySQL transaction 
$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

// Prepares the outer statement
$res_st = prepare_stmt("SELECT reserv_id, slot_fk, registration_datetime, start_time FROM register INNER JOIN slots ON slot_fk=slot_id 
    WHERE date=CURDATE() AND probability<1 AND active=1
    ORDER BY probability DESC, registration_datetime ASC, start_time DESC LIMIT 1");

// Prepares the inner statement
$up_st = prepare_stmt("UPDATE register SET probability=1 WHERE reserv_id=?");
$up_st->bind_param("i", $reserv_id);

// Sets 1 to the oldest registration's probability and adjusts consequently, then repeats until
// all registrations have probability 1
$ret = execute_stmt($res_st);
while($ret->num_rows > 0)
{
    $row = $ret->fetch_assoc();
    
    $reserv_id = $row['reserv_id'];
    execute_stmt($up_st);
    adjust_probability(date("Y-m-d"), $row['slot_fk']);
    
    $ret = execute_stmt($res_st);
}

$res_st->close();
$up_st->close();

// Integrity check, rolls back if needed
$chk_st = prepare_stmt("SELECT places, taken FROM slots INNER JOIN placestaken ON slot_fk=slot_id WHERE date=CURDATE() AND active=1");
$rchk = execute_stmt($chk_st);
$commit = true;
while($commit and $check = $rchk->fetch_assoc())
    $commit = ($check['places'] - $check['taken'] >= 0);

if($commit)
{
    $mysqli->commit();
    echo "conflict_solver: conflicts solved";
    writesolver("conflict_solver: conflicts solved");
}
else
{
    $mysqli->rollback();
    echo "conflict_solver: aborted";
    writesolver("[!] rollback conflict solver");
}

$mysqli->close();
?>