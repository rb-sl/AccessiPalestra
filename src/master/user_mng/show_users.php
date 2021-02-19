<?php 
// Frontend page for administrators to show users
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);
show_premain("Utenti");

$prop = get_prop();

// Query to retrieve the users whose status can be downgraded by the current one
$dgrade_stmt = prepare_stmt("SELECT user_id FROM users WHERE granted_by=?");
$dgrade_stmt->bind_param("i", $id);
$res = [];

// Depth-first recursive search to find the masters whose access 
// was granted by the current user, that acts as root of the subtree
function recursive_dg($cur_id) 
{
    global $dgrade_stmt;
    global $id;
    global $res;

    // The recursion breaks if the current id was already found,
    // that implies that the granted_by graph has a cycle
    // (Might just be the root self-loop if its the actual root)
    if(!in_array($cur_id, $res))
    {
        $res[] = $cur_id;
        $id = $cur_id;
        $dg = execute_stmt($dgrade_stmt);

        while($row = $dg->fetch_assoc())
            recursive_dg($row['user_id']);
    }
}
recursive_dg($_SESSION['id']);
?>

<h2>Utenti dell'applicazione</h2>

<div class="tablewrap">
    <table class="table">
        <tr><th>Utente</th><th>Atleti registrati</th><th>Ultimo accesso</th><th colspan=2>Accesso maestro</th></tr>
<?php
// Query to retrieve users
$outer_stmt = prepare_stmt("SELECT user_id, username, master, last_login FROM users 
    ORDER BY master DESC, last_login DESC, username ASC");
$ret = execute_stmt($outer_stmt);

// Query to retrieve each user's athletes
$inner_stmt = prepare_stmt("SELECT name,surname FROM athletes 
    WHERE active=1 AND user_fk=?
    ORDER BY surname ASC, name ASC");
$inner_stmt->bind_param("i", $user);

while($outer_row = $ret->fetch_assoc())
{
    // Row building based on user status
    if($outer_row['master'] == 1)
    {
        $color = "successbg";
        if(in_array($outer_row['user_id'], $res) and $outer_row['user_id'] != $_SESSION['id'])
            $button = "<a href='revoke_master.php?id=".$outer_row['user_id']."' class='btn btn-danger' "
                .confirm("All\'utente ".$outer_row['username']." verrà rimosso l\'accesso maestro. Procedere?").">Revoca</a>";
        else
            $button = "";
    }
    else
    {
        $color = "dangerbg";
        $button = "<a href='grant_master.php?id=".$outer_row['user_id']."' class='btn btn-primary' "
            .confirm("All\'utente ".$outer_row['username']." verrà concesso l\'accesso maestro. Procedere?").">Concedi</button>";
    }
    echo "<tr><td>".$outer_row['username']."</td><td>";

    // Fetching the current user's athletes
    $user = $outer_row['user_id'];
    $inner = execute_stmt($inner_stmt);

    if($inner->num_rows > 0)
        while($inner_row = $inner->fetch_assoc())
            echo $inner_row['name']." ".$inner_row['surname']."<br>";
    else
        echo "-";
    
    echo "</td><td>".$outer_row['last_login']."</td><td><div class='colorbox $color'></div></td><td>$button</td></tr>";
}
?>
    </table>
</div>
<?php show_postmain(); ?>