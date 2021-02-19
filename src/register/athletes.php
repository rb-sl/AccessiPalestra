<?php 
// Front end page to display the user's list of athletes
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access();
connect();
show_premain("Gestione atleti");
?>

<h2>Gestione atleti</h2>
<form method="POST" action="/register/athlete_update.php">
    <button type="button" id="addathlete" class="btn btn-info marginunder">Aggiungi atleta</button>

    <div id="athlist" class="column">
<?php
// Gets all the user's active athletes
$stmt = prepare_stmt("SELECT * FROM athletes WHERE user_fk=? AND active=1");
$stmt->bind_param("i", $_SESSION['id']);
$res = execute_stmt($stmt);

if($res->num_rows == 0)
    echo "<div id='noathl' class='column marginunder'>Nessun atleta presente</div>";
else
    while($row = $res->fetch_assoc())
        echo "<div id='athl".$row['athlete_id']."' class='column marginunder athletecard'>
            <input type='text' name='name[".$row['athlete_id']."]' value=\"".$row['name']."\" required>
            <input type='text' name='surname[".$row['athlete_id']."]' class='marginunder' value=\"".$row['surname']."\" required><br>
            <button type='button' id='dlt".$row['athlete_id']."' class='btn btn-danger btndel'>Rimuovi</button>
        </div>";
?>
    </div>

    <a href="/register/register.php" class="btn btn-warning">Annulla</a>
    <input type="submit" class="btn btn-primary" value="Salva modifiche">
</form>

<script>
$(function(){
    var newathl = 0;
    // Shows a new field to input an athlete
    $("#addathlete").click(function(){
        $("#noathl").hide();
        $("#athlist").append("<div id='new" + newathl + "' class='column marginunder athletecard'>"
            + "<input type='text' id='newname" + newathl + "' name='newname[" + newathl + "]' placeholder='Nome' required>"
            + "<input type='text' id='newsurname" + newathl + "' name='newsurname[" + newathl + "]' placeholder='Cognome' required><br>"
            + "<button type='button' id='dlt" + newathl + "' class='btn btn-danger btndel'>Rimuovi</button>"
            + "</div>");
        newathl++;        
    });

    // Removes the connected athlete
    $(document).on("click", ".btndel", function(){
        $(this).closest("div").remove();
    });
});
</script>

<?php show_postmain(); ?>