<?php 
// Front end page to handle the modification of properties
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);
show_premain("Periodo iscrizioni");

$param = get_prop();
?>

<h3>Gestisci il periodo delle iscrizioni</h3>
<form action="/master/parameters_update.php" method="POST">
    <p>
        Deve essere possibile registrarsi a un allenamento a partire da 
        <input type="number" name="days_before" value="<?=$param['days_before']?>" max="7"> giorni prima e fino a 
        <input type="number" name="hours_before" value="<?=$param['hours_before']?>"> ore prima del primo allenamento della giornata.
    </p>
    <p>
        Deve essere possibile scaricare le liste dei precedenti <input type="number" name="days_list" value="<?=$param['days_list']?>" min="1"> 
        giorni.
    </p>
    <a href="/master/master.php" class="btn btn-warning">Indietro</a>
    <input type="submit" class="btn btn-primary" value="Salva">
</form>

<?php show_postmain(); ?>