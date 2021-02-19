<?php
// Script used to support the ajax request for slots on a given day
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access(true);
connect();

if(isset($_GET['date']))
{
    $w = date("w", strtotime($_GET['date']));
    $w_st = prepare_stmt("SELECT slot_id, start_time, end_time FROM slots WHERE weekday=? AND active=1 ORDER BY start_time ASC");
    $w_st->bind_param("i", $w);
    $ret = execute_stmt($w_st);
    
    // If no session is present on the given day returns null
    if($ret->num_rows === 0)
    {
        echo "null";
        exit;
    }   
    
    while($row = $ret->fetch_assoc())
        $data[$row['slot_id']] = $row;
        
    echo json_encode($data);
}

$mysqli->close();
?>