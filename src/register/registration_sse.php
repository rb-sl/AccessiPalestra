<?php
// Page used to send Server-Side Events regarding available places
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
chk_access();
connect();

$prop = get_prop();

// Class representing the data sent to the client
class places
{
    public $slotId;
    public $placesLeft;
    public $date;
    
    public function __construct($slot, $places, $date)
    {
        $this->slotId = $slot;
        $this->placesLeft = $places;
        $this->date = $date;
    }
}

$prop = get_prop();

// Sends continously the state of the slots
$pl_st = prepare_stmt("SELECT date, weekday, start_time, slot_id, places, places-taken AS placesleft 
    FROM slots LEFT JOIN placestaken ON slot_fk=slot_id
    AND (date IS NULL OR (date>CURDATE() OR (date=CURDATE() AND DATE_SUB(start_time, INTERVAL ? HOUR) > CURTIME())))
    WHERE active=1");
$pl_st->bind_param("i", $prop['hours_before']);

$ret = execute_stmt($pl_st);
$slot = null;
while($row = $ret->fetch_assoc())
{
    // Generates the date to send
    if($row['date'] !== null)
        $date = $row['date'];
    else if(date("w") == $row['weekday'] && time() < strtotime($row['start_time']) - ($prop['hours_before'] * 3600))
        $date = date("Y-m-d");
    else
        $date = date("Y-m-d", strtotime("next ".getWeekdayEN($row['weekday'])));

    if(isset($row['placesleft']))
        $slot[] = new places($row['slot_id'], $row['placesleft'], $date);
    else
        $slot[] = new places($row['slot_id'], $row['places'], $date);
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

echo "data: ".json_encode($slot)."\n\n";
flush();
$mysqli->close();
exit;
?>