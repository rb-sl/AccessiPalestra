<?php 
// Frontend page to allow an administrator to cancel or restore a date
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);
show_premain("Gestione date");
?>

<h2>Gestione date degli allenamenti</h2>
<div class="flexcolumn">
    <a href="/master/master.php" class="btn btn-warning">Annulla</a>
    <form action="date_remove.php" method="POST" id="removeform" class="flexcolumn">
        <div class="flexcolumn">
            Rimuovi: 
            <label id="lsingle" class="btn btn-secondary ">
                <input type="radio" id="rsingle" name="multidate" class="multidate rad" value=0> Singola data
            </label>
            <label id="lmulti" class="btn btn-secondary ">
                <input type="radio" id="rmulti" name="multidate" class="multidate rad" value=1> Periodo
            </label>
        </div>
        <div id="when" class="flexcolumn tohide">
            <span id="from">Data</span>
            <input type="date" id="toremove1" class="date" name="date1" required>
            <span id="to" class="tohide">al <input type="date" id="toremove2" class="date" name="date2" disabled></span>
        </div>
        <div id="freq" class="flexcolumn tohide">
            Da rimuovere:
            <label id="per0" class="btn btn-secondary">
                <input type="radio" id="once" class="perm rad" name="permanent" value="0"> Una volta sola
            </label>
            <label id="per1" class="btn btn-secondary">
                <input type="radio" id="perm" class="perm rad" name="permanent" value="1"> Ogni anno
            </label>
        </div>
        <div id="slots" class="flexcolumn tohide">
            Orari:
            <div id="loading">Caricamento...</div>
            <div id="slotlist" class="flexcolumn">
            </div>
        </div>
    <input type="submit" id="subremove" class="btn btn-danger tohide" value="Rimuovi date e orari selezionati" 
    <?=confirm("Le registrazioni per date e orari selezionati saranno perse. Procedere?") ?>>
    </form>
</div>

<h3>Date rimosse e ripristino</h3>

<form action="date_restore.php" method="POST">
    <input type="submit" id="subrestore" class="btn btn-success marginunder" value="Ripristina date e orari selezionati" disabled>

    <h4>Date rimosse una sola volta

<?php
// Gets and prints the one-time-removed dates
$once_st = prepare_stmt("SELECT forb_id, date FROM forbidden WHERE date>=CURDATE() AND permanent=0 ORDER BY date ASC");
$ret = execute_stmt($once_st);
$once_st->close();

// Prepares the query to retrieve slots
$slot_st = prepare_stmt("SELECT slot_id, start_time, end_time FROM forbidden_slots INNER JOIN slots ON slot_fk=slot_id 
    WHERE forb_fk=? AND active=1");
$slot_st->bind_param("i", $forb_id);

if($ret->num_rows == 0)
    echo "</h4>
        <div class='marginunder'>
            <u>Nessuna data rimossa una sola volta</u>
        </div>";
else
{
    echo "<button type='button' id='showonce' class='btn btn-primary'>Mostra</button></h4><div id='remsingle' class='tohide marginunder'>";
    while($row = $ret->fetch_assoc())
    {
        echo "<fieldset class='flexcolumn marginunder'>
                <legend class=''>".date("j/m/Y", strtotime($row['date']))."</legend>";
        
        $forb_id = $row['forb_id'];
        $rslot = execute_stmt($slot_st);

        while($slot = $rslot->fetch_assoc())
            echo "<label id='slot".$slot['slot_id']."' class='btn btn-secondary slotlbl marginunder'>
            <input type='checkbox' id='chk".$slot['slot_id']."' class='chk cres'
            name='slot[".$row['forb_id']."][".$slot['slot_id']."]'> ".date("G:i", strtotime($slot['start_time']))
            ."-".date("G:i", strtotime($slot['end_time']))."</label>";
        echo "</fieldset>";
    }
    echo "</div>";
}

$slot_st->close();
?>

<h4>Date rimosse tutti gli anni

<?php
// Gets and prints the dates removed from all years
$date_st = prepare_stmt("SELECT forb_id, date FROM forbidden WHERE permanent=1 ORDER BY MONTH(date) ASC, DAY(date) ASC");
$ret = execute_stmt($date_st);

if($ret->num_rows == 0)
    echo "</h4>
        <div class='marginunder'>
            <u>Nessuna data rimossa permanentemente</u>
        </div>";
else
{
    echo "<button type='button' id='showperm' class='btn btn-primary'>Mostra</button></h4><div id='remperm' class='tohide'>";
    while($row = $ret->fetch_assoc())
        echo "<label id='date".$row['forb_id']."' class='btn btn-secondary marginunder'>
            <input type='checkbox' id='chk".$row['forb_id']."' class='chk cres' name='perm[".$row['forb_id']."]'> 
            ".date("j/m", strtotime($row['date']))."</label> ";
    echo "</div>";
}
$date_st->close();
?>

</form>

<script src="/master/date_mng/date_manager.js"></script>

<?php show_postmain(); ?>