<?php 
// Frontend page to allow an administrator to modify slot dates and times
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);
show_premain("Gestione allenamenti");

$slotn = 0;
?>

<h2>Gestione giorni e orari degli allenamenti</h2>

<form action="slot_update.php" method="POST">
    <a href="/master/master.php" class="btn btn-warning">Annulla</a>
    <input type="submit" id="submit" class="btn btn-success" value="Salva" disabled>

    <h3>Allenamenti attivi</h3>

<?php
// Gets the active sessions
$act_st = prepare_stmt("SELECT * FROM slots WHERE active=1 ORDER BY weekday ASC, start_time ASC");
$ret = execute_stmt($act_st);

if($ret->num_rows == 0)
    echo "<div class='marginunder'><u>Nessun allenamento presente</u></div>";
else
{
    echo "<div id='active' class='tdiv marginunder'>
            <div id='tos' class='inner'>
                <table id='tts' class='table table-striped'>";
  
    // Initializing the table rows
    $dayrow = "<th class='firstcol'>Giorno</th>";
    $strrow = "<th class='firstcol'>Ora inizio</th>";
    $endrow = "<th class='firstcol'>Ora fine</th>";
    $places = "<th class='firstcol'>Posti disponibili</th>";
    $hiddenday = "<div hidden>";

    $curday = -1;
    $colspan = 1;
    while($row = $ret->fetch_assoc())
    {
        $slotn = $row['slot_id'];
        $slots[] = $slotn;
        if($curday !== $row['weekday'])
        {
            if($curday != -1)
                $dayrow .= "<th class='$classes lateralborder' colspan='$colspan'>".getWeekdayIT($curday)."</th>";

            $curday = $row['weekday'];
            $colspan = 1;
            $classes = "col".$slotn;
        }
        else
        {
            $classes .= " col".$slotn;
            $colspan++;
        }
        $strrow .= "<td class='col$slotn lateralborder'>
                <input type='time' name='start[$slotn]' value='".$row['start_time']."' min='1' required>
            </td>";
        $endrow .= "<td class='col$slotn lateralborder'>
                <input type='time' name='end[$slotn]' value='".$row['end_time']."' min='1' required>
            </td>";
        $places .= "<td class='col$slotn lateralborder'>
                <input type='number' name='places[$slotn]' value='".$row['places']."' min='1' required>
            </td>";

        $hiddenday .= "<input type='number' id='day$slotn' name='day[$slotn]' value='".$row['weekday']."' required>";
    }
    $dayrow .= "<th class='$classes lateralborder' colspan='$colspan'>".getWeekdayIT($curday)."</th>";

    echo "<tr>$dayrow</tr><tr>$strrow</tr><tr>$endrow</tr><tr>$places</tr><tr><td class='darkrow'></td>";

    foreach($slots as $slot)
        echo "<td class='col$slot lateralborder'><button type='button' id='rem$slot' class='btn btn-danger btnremove'>Rimuovi</button></td>";
        
    echo "      </tr>
            </table> 
        </div> 
    </div>";

    echo $hiddenday."</div>";
}
?>
    <button type="button" id="btnnew" class="btn btn-primary marginunder">Aggiungi allenamento</button>
    <div id="new"></div>
</form>

<script>
$(function(){
    var cols = <?=$slotn?>;
    var next = 0;

    $(".btnremove").click(function(){
        var id = $(this).attr("id").substring(3);

        $("td.col" + id).remove();
        $("#day" + id).remove();

        $("th.col" + id).each(function(){
            if($(this).attr("colspan") > 1)
                $(this).attr("colspan", $("th.col" + id).attr("colspan") - 1);
            else
                $(this).remove();
            cols--;
        });
        
        if(cols == 0)
            $("#active").html("<u>Nessun allenamento presente</u>"); 
        
        $("#submit").removeAttr("disabled");
    });

    $("input").on("change keyup", function(){
        $("#submit").removeAttr("disabled");
    });

    $("#btnnew").click(function(){
        $("#new").append("<fieldset class='flexcolumn marginunder'>"
            + "<legend>Nuovo allenamento</legend>"
            + "<span class='row'>Giorno: "
            + "<select name='newday[" + next + "]' required>"
            + "<option selected disabled></option>"
            + "<option value='1'>Lunedì</option>"
            + "<option value='2'>Martedì</option>"
            + "<option value='3'>Mercoledì</option>"
            + "<option value='4'>Giovedì</option>"
            + "<option value='5'>Venerdì</option>"
            + "<option value='6'>Sabato</option>"
            + "<option value='0'>Domenica</option>"
            + "</select></span>"
            + "<span class='row'>Ora inizio: <input type='time' name='newstart[" + next + "]' required></span>"
            + "<span class='row'>Ora fine: <input type='time' name='newend[" + next + "]' required></span>"
            + "<span class='row'>Posti: <input type='number' name='newplaces[" + next + "]' min='1' required></span>"
            + "<button type='button' id='rem" + next + "' class='btn btn-danger btnremfld'>Rimuovi</button>"
            + "</fieldset>"
        );

        $("#submit").removeAttr("disabled");
        next++;
    });

    $(document).on("click", ".btnremfld", function(){
        var id = $(this).attr("id").substring(3);

        $(this).closest("fieldset").remove();
    });
});
</script>
        

<?php show_postmain(); ?>