<?php
// Backend page used to update the list of athletes connected to the user
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access();

$log = "Modifica atleti";

$prop = get_prop();

// Updates the existing athletes
$currentids = "0";
if(isset($_POST['name']))
{
    $up_st = prepare_stmt("UPDATE athletes SET name=?, surname=? WHERE athlete_id=?");
    $up_st->bind_param("ssi", $name, $surname, $id);

    foreach($_POST['name'] as $id => $name)
    {
        $surname = $_POST['surname'][$id];
        execute_stmt($up_st);
        $log .= "\n>>> [mantenuto] $id [nome] $name $surname";
        $currentids .= ", $id";
    }

    $up_st->close();
}

// Deletes the removed athletes
$da_st = prepare_stmt("UPDATE athletes SET active=0 WHERE user_fk=? AND athlete_id NOT IN ($currentids)");
$da_st->bind_param("i", $_SESSION['id']);
$ret = execute_stmt($da_st);
$da_st->close();

// Adds the new athletes
if(isset($_POST['newname']))
{
    $new_st = prepare_stmt("INSERT INTO athletes(name, surname, active, user_fk) VALUES(?, ?, 1, ?) ON DUPLICATE KEY UPDATE active=1");
    $new_st->bind_param("ssi", $name, $surname, $_SESSION['id']);
    foreach($_POST['newname'] as $id => $name)
    {
        $surname = $_POST['newsurname'][$id];
        execute_stmt($new_st);
        $log .= "\n>>> [aggiunto] [nome] $name $surname";
    }
    $new_st->close();
}

// Deletes the registrations of inactive athletes
$todel_st = prepare_stmt("SELECT reserv_id FROM register INNER JOIN slots ON slot_fk=slot_id
    INNER JOIN athletes ON athlete_fk=athlete_id
    WHERE athletes.active=0 
    AND (date>CURDATE() OR (date=CURDATE() AND DATE_SUB(start_time, INTERVAL ? HOUR) > CURTIME()))");
$todel_st->bind_param("i", $prop['hours_before']);
$rdel = execute_stmt($todel_st);
$todel = "0";
while($row = $rdel->fetch_assoc())
    $todel .= ", ".$row['reserv_id'];

$del_st = prepare_stmt("DELETE FROM register WHERE reserv_id IN ($todel)");
execute_stmt($del_st);
$del_st->close();

$_SESSION['alert'] = "Modifiche completate";
writelog($log);

$mysqli->close();
// Reloads the previous page
header("Location: /register/register.php");
exit;
?>