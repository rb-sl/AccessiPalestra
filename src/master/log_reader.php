<?php
// Ajax page to read a log entry
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access(true);
connect();

echo json_encode(file_get_contents(LOG_PATH."log_".$_GET['date'].".txt"));
$mysqli->close();
?>