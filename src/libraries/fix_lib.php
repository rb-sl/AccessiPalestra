<?php
// Function to adjust future slots
function adjust_slot($slot)
{
    $prop = get_prop();

    $st = prepare_stmt("SELECT weekday, MIN(start_time) AS start_t FROM slots 
        WHERE weekday=(SELECT weekday FROM slots WHERE slot_id=?) AND active=1");
    $st->bind_param("i", $slot);
    $ret = execute_stmt($st);
    $st->close();
    $row = $ret->fetch_assoc();

    if(date("w") === $row['weekday'] && time() < strtotime($row['start_t']) - ($prop['hours_before'] * 3600))
        $date = date("Y-m-d");
    else
        $date = date("Y-m-d", strtotime("next ".getWeekdayEN($row['weekday'])));

    return adjust_probability($date, $slot, "", true);
}

// Function that given a slot and a date adjusts the probabilities, if greater than the places
// Returns the log
function adjust_probability ($date, $slot = 0, $log = "", $avoidprob = false)
{
    // If no slot is given all of the date's are checked
    if($slot != 0)
    {
        $prob_st = prepare_stmt("SELECT slot_id, places, SUM(probability) AS p FROM register INNER JOIN slots ON slot_fk=slot_id 
            WHERE slot_id=? AND date=? AND active=1 GROUP BY slot_id");
        $prob_st->bind_param("is", $slot, $date);
    }
    else
    {
        $prob_st = prepare_stmt("SELECT slot_id, places, SUM(probability) AS p FROM register INNER JOIN slots ON slot_fk=slot_id 
            WHERE date=? AND active=1 GROUP BY slot_id");
        $prob_st->bind_param("s", $date);
    }

    // Checks if the probability exceeds the places for any given slot
    $ret = execute_stmt($prob_st);
    $prob_st->close();

    if(!$avoidprob)
        $probability = "AND probability<1";
    else
        $probability = "";

    // Prepares the inner statement to fix the probabilities
    $slot_st = prepare_stmt("SELECT reserv_id, athlete_fk, probability, registration_datetime, start_time 
        FROM register INNER JOIN slots ON slot_fk=slot_id
        WHERE slot_id=? AND date=? $probability AND active=1
        ORDER BY probability ASC, registration_datetime DESC, start_time ASC");
    $slot_st->bind_param("is", $slot, $date);

    // Prepares the delete statement
    $del_st = prepare_stmt("DELETE FROM register WHERE reserv_id=?");
    $del_st->bind_param("i", $reserv_id);

    while($toadjust = $ret->fetch_assoc())
        if($toadjust['p'] > $toadjust['places'])
        {
            $slot = $toadjust['slot_id'];
            $ret2 = execute_stmt($slot_st);
            
            // Deletes the exceeding reservations, starting from the most recent at a lower probability
            $regtodel = [];
            while($toadjust['p'] > $toadjust['places'] and $row = $ret2->fetch_assoc())
            {
                $regtodel[$row['athlete_fk']] = $row['probability'];
                $toadjust['p'] -= $row['probability'];
                
                $reserv_id = $row['reserv_id'];
                execute_stmt($del_st);
                $log .= "\n>>> [-reg] [athlete] ".$row['athlete_fk']." [date] $date [slot] $slot";
            }

            foreach($regtodel as $athl => $prob)
                if(fix_on_delete($athl, $date, $prob, "", $avoidprob) == -1)
                    return -1;
        }
    $slot_st->close();
    $del_st->close();

    return $log;
}

// Function to fix probabilities on a reservation delete
// Returns 0 if ok, -1 if rolled back
function fix_on_delete($athl, $date, $prob, $log = "", $avoidprob = false)
{
    // The other reservations from the same day and same athlete must increase their probability
    $n = floor(1 / $prob);
    if($prob != 1 and $n > 0)
    {
        $newprob = 1 / ($n - 1);
        $up_st = prepare_stmt("UPDATE register SET probability=? WHERE athlete_fk=? AND date=?");
        $up_st->bind_param("dis", $newprob, $athl, $date);
        execute_stmt($up_st);

        // If no entry is updated it probably was deleted by a parallel transaction;
        // This one will be rolled back by propagating -1
        if($up_st->affected_rows === 0 and !$avoidprob)
        {
            writelog("\n>>> [!] delete error");
            return -1;
        }

        $up_st->close();
    }
    
    // All the modified slots are then checked for integrity
    $chk_st = prepare_stmt("SELECT slot_fk FROM register WHERE athlete_fk=? AND date=?");
    $chk_st->bind_param("is", $athl, $date);
    $ret3 = execute_stmt($chk_st);
    $chk_st->close();
    
    $chk = 0;
    while($chk == 0 and $row3 = $ret3->fetch_assoc())
        $chk = adjust_probability($date, $row3['slot_fk'], $log, $avoidprob);
    
    return $chk;
}
?>