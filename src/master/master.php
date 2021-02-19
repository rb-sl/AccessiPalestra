<?php 
// Frontend page for the application's administration
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);
show_premain("Accesso maestro");

$prop = get_prop();
?>

<h2>Accesso maestro</h2>

<h3>Visualizza liste non definitive</h3>
<form action="/master/reg_mng/show_register.php" method="GET" class="row marginunder">

<?php
// Gets all the dates that are still open
$hours_before = ($prop['hours_before'] - 1).":55";

$date_st = prepare_stmt("SELECT DISTINCT(date) FROM register INNER JOIN (SELECT weekday, MIN(start_time) AS start_time 
    FROM slots WHERE active=1 GROUP BY weekday) AS T ON DAYOFWEEK(date)-1=weekday
    WHERE (date>CURDATE() OR (date=CURDATE() AND DATE_SUB(start_time, INTERVAL ? HOUR_MINUTE) > CURTIME())) 
    ORDER BY date ASC");
$date_st->bind_param("s", $hours_before);
$ret = execute_stmt($date_st);
$date_st->close();

// The returned dates are saved for the next query, as closed dates are complementary to these
$dates = "'0000-00-00'";

if($ret->num_rows == 0)
    echo "<u>Nessuna registrazione futura presente</u>";
else
{
    echo "Data: <select name='date'>";
    while($row = $ret->fetch_assoc())
    {
        $dates .= ", '".$row['date']."'";
        echo "<option>".$row['date']."</option>";
    }
    echo "</select> <input type='submit' class='btn btn-info' value='Visualizza'>";
}
?>
    
</form>

<h3>Scarica Partecipazioni</h3>
<?php
// Gets and prints the closed dates closer than 1 month, if they exist
$date_st = prepare_stmt("SELECT DISTINCT(date) FROM register 
    WHERE date NOT IN($dates) AND date>=(CURDATE() - INTERVAL ".$prop['days_list']." DAY) ORDER BY date DESC");
$ret = execute_stmt($date_st);
$date_st->close();

if($ret->num_rows === 0)
    echo "<u>Nessuna lista da visualizzare</u>";
else
{
    echo "<form id='lists' action='/master/reg_mng/generate_list.php' method='GET' class='flexcolumn marginunder'>
    <span>
        Data: <select name='date'>";
    
    while($row = $ret->fetch_assoc())
        echo "<option>".$row['date']."</option>";

    echo "</select>
        </span>
        <input type='submit' class='btn btn-primary' value='Scarica lista da stampare' onclick=\"submitForm('/master/reg_mng/generate_list.php')\">
        <input type='submit' class='btn btn-primary' value='Scarica lista per il gruppo' onclick=\"submitForm('/master/reg_mng/generate_forgroup.php')\">
    </form>";
}
?>

<h3>Gestisci applicazione</h3>

<div class="flexcolumn">
    <a href="/master/log.php" class="btn btn-light">Visualizza log di utilizzo</a>
    <a href="/master/date_mng/date_manager.php" class="btn btn-warning">Cancella o ripristina date</a>
    <a href="/master/slot_manager.php" class="btn btn-info">Modifica giorni e ore degli allenamenti</a>
    <a href="/master/parameters.php" class="btn btn-primary">Modifica periodo di iscrizione</a>
    <a href="/master/user_mng/show_users.php" class="btn btn-primary">Visualizza utenti</a>
</div>

<script>
    // Sends the request to the correct pdf generator 
    function submitForm(action) {
        var form = document.getElementById("lists");
        form.action = action;
        form.submit();
    }
</script>

<?php show_postmain(); ?>