<?php 
// Front end page used to show the current registrations of a day
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);

$date = date("d/m/Y", strtotime($_GET['date']));
show_premain("Lista non definitiva $date");
?>

<h2>Lista non definitiva <br> <?=$date?></h2>

<?php
$reg_st = prepare_stmt("SELECT name, surname, start_time, end_time, probability FROM register INNER JOIN slots ON slot_fk=slot_id
    INNER JOIN athletes ON athlete_fk=athlete_id  
    WHERE date=? AND slots.active=1 AND athletes.active=1 
    ORDER BY start_time ASC, probability DESC, surname ASC, name ASC");
$reg_st->bind_param("s", $_GET['date']);
$ret = execute_stmt($reg_st);
$reg_st->close();

if($ret->num_rows == 0)
    echo "Nessun atleta iscritto";
else
{
    $time = -1;
    // Prints a table that can scroll horizontally
    echo "<div class='tablewrap'>
        <table class='table'>
            <tr><th></th><th>Cognome</th><th>Nome</th><th>Orario</th><th>Stato iscrizione</th></tr>";
    while($row = $ret->fetch_assoc())
    {
        if($time !== $row['start_time'])
        {
            if($time !== -1)
                echo "<tr><td></td><td></td><td></td><td></td><td></td></tr>";
            $i = 1;
            $time = $row['start_time'];
        }

        if($row['probability'] == 1)
        {
            $color = "successbg";
            $iout = $i;
        }
        else
        {
            $color = "warningbg";
            $iout = "";
        }
        echo "<tr><td>$iout</td><td>".$row['surname']."</td><td>".$row['name']."</td><td>".date("G:i", strtotime($row['start_time']))." - "
            .date("G:i", strtotime($row['end_time']))."</td><td><div class='colorbox $color'></div></td></tr>";
        $i++;
    }
    echo "</table>
        </div>";
}
?>

<?php show_postmain(); ?>