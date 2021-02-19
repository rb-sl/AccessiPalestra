<?php 
// Backend page to revoke master grants from a user
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
connect();
chk_access(true);

// Bottom up search in the granted_by tree, to see if the
// current user is in the path towards the root
function check_granter($cur_id)
{
    global $already_checked;
    global $chk_stmt;
    global $id;
    
    $id = $cur_id;
    $r = execute_stmt($chk_stmt);
    if($r->num_rows > 0)
    {
        $row = $r->fetch_assoc();
        if($row['granted_by'] == $_SESSION['id'])
            return true;
        else if($row['granted_by'] == $row['user_id'] or in_array($row['user_id'], $already_checked))
            return false;
        else
        {
            $already_checked[] = $cur_id;
            return check_granter($row['granted_by']);
        }
    }
}

// The script terminates immediately if a master tries to removes themself
if($_GET['id'] != $_SESSION['id'])
{
    // Query to retrieve a user given the id
    $chk_stmt = prepare_stmt("SELECT * FROM users WHERE user_id=?");
    $chk_stmt->bind_param("i", $id);
    $already_checked = [];

    $id = $_GET['id'];
    $ret = execute_stmt($chk_stmt);
    $row = $ret->fetch_assoc();

    // The access is removed if the retrieved user is actually a master
    // and their access was granted in the current user's subtree
    if($row && $row['master'] == 1 && check_granter($_SESSION['id']))
    {
        $revoke_stmt = prepare_stmt("UPDATE users SET master=0, granted_by=NULL WHERE user_id=?");
        $revoke_stmt->bind_param("i", $_GET['id']);
        execute_stmt($revoke_stmt);
        writelog("Rimosso l'Accesso Maestro a ".$row['username']);
        $_SESSION['alert'] = "Accesso maestro rimosso correttamente";
    }
    else
        $_SESSION['alert'] = "Utente selezionato non valido";
}
else
    $_SESSION['alert'] = "Utente selezionato non valido";

$mysqli->close();
header("Location: /master/user_mng/show_users.php");
?>