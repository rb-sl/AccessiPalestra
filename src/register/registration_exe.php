<?php
// Backend page used to insert new registrations
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
include $_SERVER['DOCUMENT_ROOT']."/libraries/fix_lib.php";
chk_access();
connect();

$log = "Registrazione";

$commit = true;

// Defines behaviour if considering only one athlete or all; terminates the script if 
// no athletes connected to the user are found
if(isset($_GET['id']))
{
    $ath_st = prepare_stmt("SELECT athlete_id FROM athletes WHERE user_fk=? AND athlete_id=?");
    $ath_st->bind_param("ii", $_SESSION['id'], $_GET['id']);
}
else
{
    $ath_st = prepare_stmt("SELECT athlete_id FROM athletes WHERE user_fk=? AND active=1");
    $ath_st->bind_param("i", $_SESSION['id']);
}

// Blocks the user if is trying to access a wrong athlete
$ret = execute_stmt($ath_st);
if($ret->num_rows == 0)
{
    $_SESSION['alert'] = "Utente non autorizzato";
    header("Location: /register/register.php");
    exit();
}

while($row = $ret->fetch_assoc())
    $athletes[] = $row['athlete_id'];

// Starts a MySQL transaction
$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

// Prepares the insert query
$in_st = prepare_stmt("INSERT INTO register(athlete_fk, slot_fk, date, registration_datetime) VALUES(?, ?, ?, NOW()) 
    ON DUPLICATE KEY UPDATE athlete_fk=athlete_fk");
$in_st->bind_param("iis", $athlete, $slot, $date);

// Prepares the count query
$c_st = prepare_stmt("SELECT COUNT(*) AS n FROM register WHERE athlete_fk=? AND date=?");
$c_st->bind_param("is", $athlete, $date);

// Prepares the update query
$up_st = prepare_stmt("UPDATE register SET probability=1/? WHERE athlete_fk=? AND date=?");
$up_st->bind_param("iis", $count, $athlete, $date);

$dates = "'0000-00-00'";
foreach($athletes as $athlete)
{
    foreach($_POST['slot'] as $date => $slotlist)
    {
        $dates .= ", '".$date."'";
        foreach($slotlist as $slot => $vs)
        {
            // Inserts the given data into the register
            execute_stmt($in_st);
            
            $log .= "\n>>> [atleta] $athlete [data] $date [slot] $slot";
        }

        // Updates connected probabilities
        $r = execute_stmt($c_st);
        $n = $r->fetch_assoc();
        $count = $n['n'];
        execute_stmt($up_st);

        $chkdel = adjust_probability($date);
        if($chkdel === -1)
        {
            $commit = false;
            break 2;
        }
        else
            $log .= $chkdel; 
    }
}

$in_st->close();
$c_st->close();
$up_st->close();

// Integrity check, rolls back if needed
$chk_st = prepare_stmt("SELECT places, taken FROM slots INNER JOIN placestaken ON slot_fk=slot_id WHERE date IN($dates) AND active=1");
$rchk = execute_stmt($chk_st);

while($commit and $check = $rchk->fetch_assoc())
    $commit = ($check['places'] - $check['taken'] >= 0);

if($commit)
    $mysqli->commit();
else
{
    $mysqli->rollback();
    $log .= "\n[!] Errore: rollback";
    $_SESSION['alert'] = "Alcuni posti inseriti non sono più disponibili, per favore ripetere la procedura";
}

$chk_st->close();

writelog($log);
$mysqli->close();
header("Location: /register/register.php");
exit;
?>