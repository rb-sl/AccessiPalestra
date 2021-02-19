<?php 
// Page used to allow the registration of athletes
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access();
show_premain("Nuova registrazione");
?>

<h2>Registrazione allenamenti

<?php
if(isset($_GET['id']))
{
    $ath_st = prepare_stmt("SELECT * FROM athletes WHERE athlete_id=? AND user_fk=? AND active=1");
    $ath_st->bind_param("ii", $_GET['id'], $_SESSION['id']);
    $ret = execute_stmt($ath_st);

    if($ret->num_rows == 0)
    {
        $_SESSION['alert'] = "Utente non autorizzato";
        header("Location: /register/register.php");
        exit();
    }

    $athl = $ret->fetch_assoc();
    $n = 1;
    echo " di ".$athl['name']." ".$athl['surname'];

    // Prepares the statement with the athlete id
    $reg_st = prepare_stmt("SELECT * FROM register WHERE athlete_fk=? AND date=?");
    $reg_st->bind_param("is", $_GET['id'], $activedate);
    
    $get = "?id=".$_GET['id'];

    $ath_st->close();
}
else
{
    // If no athlete id is given, all user's athletes are registered
    if(!isset($_GET['n']))
    {
        $ath_st = prepare_stmt("SELECT * FROM athletes WHERE user_fk=? AND active=1");
        $ath_st->bind_param("i", $_SESSION['id']);
        $ret = execute_stmt($ath_st);

        $n = $ret->num_rows;
        if($n==0)
        {
            echo "</h2><div class='marginunder'><u>Nessun atleta presente</u></div>
            <a href='/register/athletes.php' class='btn btn-info marginunder'>Aggiungi atleti</a>";
            show_postmain();
            exit;
        }
        $ath_st->close();
    }
    else
        $n = $_GET['n'];
    
    // Prepares the statement with the athlete id
    $reg_st = prepare_stmt("SELECT * FROM register WHERE athlete_fk IN (SELECT athlete_id FROM athletes WHERE user_fk=? AND active=1) AND date=?");
    $reg_st->bind_param("is", $_SESSION['id'], $activedate);

    $get = "";
    
    echo " di <span id='n'>".$n."</span> atleti";
}
?>
</h2>

<form action="/register/registration_exe.php<?=$get?>" method="POST">
    <div class="column">
        <h4>Allenamenti disponibili</h4>
<?php
$prop = get_prop();

// Gets the weekdays hosting a session
$dayt_st = prepare_stmt("SELECT DISTINCT weekday, MIN(start_time) AS start_t FROM slots WHERE active=1 GROUP BY weekday");
$ret = execute_stmt($dayt_st);

if($ret->num_rows == 0)
    echo "<div class='marginunder'><u>Nessuna lezione disponibile</u></div>";
else
{
    // Prepares the statement for slot retrieval
    $slot_st = prepare_stmt("SELECT slot_id, date, start_time, end_time, places, places-taken AS placesleft
        FROM slots LEFT JOIN placestaken ON slot_fk=slot_id 
        AND (date>CURDATE() OR (date=CURDATE() AND DATE_SUB(start_time, INTERVAL ? HOUR) > CURTIME()))
        WHERE (date IS NULL OR date=?)
        AND DATE_FORMAT(?, '%m-%d') NOT IN (SELECT DATE_FORMAT(date, '%m-%d') FROM forbidden WHERE permanent=1)
        AND slot_id NOT IN (SELECT slot_fk FROM forbidden_slots INNER JOIN forbidden ON forb_fk=forb_id WHERE date=?)
        AND active=1
        AND weekday=? ORDER BY start_time ASC");
    $slot_st->bind_param("isssi", $prop['hours_before'], $activedate, $activedate, $activedate, $weekday);

    while($row = $ret->fetch_assoc())
    {
        // The current day is shown only when before the closing time;
        // any other date is ignored if farther than defined
        if(date("w") == $row['weekday'] && time() < strtotime($row['start_t']) - ($prop['hours_before'] * 3600))
        { 
            $activedate = date("Y-m-d");
            $date = date("j/m");
        }
        else
        {
            $nextday = strtotime("next ".getWeekdayEN($row['weekday']));
            $date = date("j/m", $nextday);
            $activedate = date("Y-m-d", $nextday);

            $today = date_create(date("Y-m-d"));
            $date2 = date_create(date("Y-m-d", $nextday));
            if(date_diff($today, $date2)->format("%d") > $prop['days_before'])
                continue;
        }

        // Queries for register entries
        $m = execute_stmt($reg_st);

        // If some reservation for the athlete already exists for the day, it will be marked yellow whatsoever
        if($m->num_rows > 0) 
        {
            $multiple = "multiple";
            $startcolor = "btn-success";
        }
        else
        {
            $multiple = "";
            $startcolor = "btn-secondary";
        }

        $days[$activedate] = "<div class='marginunder'>
                <button type='button' id='btn".$row['weekday']."' class='btn $multiple $startcolor btnday'>"
                .getWeekdayIT($row['weekday'])." $date</button>
            </div>
            <div id='day".$row['weekday']."' class='column marginunder daydiv'>";
        
        // Queries for the day's slot
        $weekday = $row['weekday'];
        $slots = execute_stmt($slot_st);
        
        // In case all slots were manually removed the day won't appear
        if($slots->num_rows == 0)
            unset($days[$activedate]);
        else
        {
            while($slot = $slots->fetch_assoc())
            {
                // Checks the places left, based on the existence of previous registrations
                if(isset($slot['placesleft']))
                    $left = $slot['placesleft'];
                else
                    $left = $slot['places'];
                
                // Defines the behaviour of slots based on the places left
                if($left >= $n)
                {
                    $style = "btn-secondary";
                    $dis = "";
                }
                else
                {
                    $style = "btn-danger disabled";
                    $dis = "disabled";
                }
                    
                $days[$activedate] .= "<label id='slot".$slot['slot_id']."' class='btn $style slotlbl lbl".$row['weekday']." marginunder'>
                        <input type='checkbox' id='chk".$slot['slot_id']."' class='chk $multiple cslot day".$row['weekday']."' day='".$row['weekday']."' 
                        date='$activedate' name='slot[".$activedate."][".$slot['slot_id']."]' $dis> ".date("G:i", strtotime($slot['start_time']))
                        ."-".date("G:i", strtotime($slot['end_time']))." (<span id='left".$slot['slot_id']."'>$left</span>)</label> ";
            }
            $days[$activedate] .= "</div>";
        }
    }

    // Closes the statements
    $reg_st->close();
    $slot_st->close();

    // If all days were removed nothing is shown
    if(!empty($days))
    {
        // Sorts the days based on the closeness to the present one
        ksort($days);
        foreach($days as $day)
            echo $day;
    }
    else
        echo "<div class='marginunder'><u>Nessuna lezione disponibile</u></div>";
}

?>
    </div>

    <a href="/register/register.php" class="btn btn-warning">Annulla</a>
    <input type="submit" id="submit" class="btn btn-primary" value="Registra" disabled>
</form>

<script src="/register/registration_add.js"></script>

<?php show_postmain(); ?>