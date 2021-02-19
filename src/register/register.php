<?php 
// Frontend page used to display the existing registrations
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access();
show_premain("Registrazione allenamento");

$prop = get_prop();
?>

<h2>Registrazione agli allenamenti</h2>

<a href="/guide.php#before" class="btn btn-danger marginunder">IMPORTANTE: Prima degli allenamenti</a>
<br>
<a href="<?=AUTOCERT_PATH?>" target="_blank" class="btn btn-light">Scarica autocertificazione</a>
<br><br>
<a href="/register/athletes.php" class="btn btn-info marginunder">Aggiungi/Modifica atleti</a>

<div class="column">
<?php
// Gets the user's athletes
$ath_st = prepare_stmt("SELECT * FROM athletes WHERE user_fk=? AND active=1");
$ath_st->bind_param("i", $_SESSION['id']);
$ath = execute_stmt($ath_st);

if($ath->num_rows == 0)
    echo "<div class='marginunder'><u>Nessun atleta presente</u></div>";

// Query to get all user's reservations
$res_st = prepare_stmt("SELECT * FROM register INNER JOIN slots ON slot_fk=slot_id 
    WHERE date>=CURDATE() AND active=1 AND athlete_fk=?
    ORDER BY date ASC, start_time ASC");
$res_st->bind_param("i", $athlete_id);

// Query to get the day's start time
$start_st = prepare_stmt("SELECT MIN(start_time) AS min_time FROM slots WHERE weekday=? AND active=1");
$start_st->bind_param("i", $weekday);

while($row = $ath->fetch_assoc())
{
    // Gets all reservations from today on
    $athlete_id = $row['athlete_id'];
    $reg = execute_stmt($res_st);

    echo "<div id='card".$row['athlete_id']."' class='column athletecard marginunder'>".$row['name']." ".$row['surname'];

    $date = 0;
    if($reg->num_rows == 0)
        echo "<div class='marginunder'><u>Nessuna registrazione presente</u></div>";
    else
    {
        while($reservation = $reg->fetch_assoc())
        {
            // Gets the start time of the day
            if($date != $reservation['date'])
            {
                $date = $reservation['date']; 

                $weekday = $reservation['weekday'];
                $r = execute_stmt($start_st);
                $start = $r->fetch_assoc();
            }

            if($reservation['probability'] == 1)
                $color = "successbg";
            else
                $color = "warningbg";

            echo "<div id='res".$reservation['reserv_id']."' class='marginunder'><div class='colorbox $color'></div> "
                .getWeekdayIT($reservation['weekday'])." ".date_format(date_create($date), "j/m")." "
                .date("G:i", strtotime($reservation['start_time']))."-".date("G:i", strtotime($reservation['end_time']));
            
            // Prints the remove button iff in the future or on the same day, but only hours_before the start of the day
            if($date > date("Y-m-d") || 
                ($date == date("Y-m-d") and time_diff($start['min_time'], date("H:i")) > $prop['hours_before']))
                echo "<br><a href='/register/registration_del.php?res=".$reservation['reserv_id']."' class='btn btn-danger' "
                .confirm("Procedere con l\'eliminazione?").">Rimuovi</a>";

            echo "</div>";
        }
    }
    echo "<a href='/register/registration_add.php?id=".$row['athlete_id']."' class='btn btn-primary'>Nuova registrazione</a>
    </div>";
}

if($ath->num_rows > 1)
    echo "<a href='/register/registration_add.php?n=".$ath->num_rows."' class='btn btn-primary'>Registra Tutti</a>";
?>
</div>

<?php show_postmain(); ?>