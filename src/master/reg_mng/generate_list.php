<?php
// Script used to generate the list of participant to be printed
include $_SERVER['DOCUMENT_ROOT']."/libraries/general.php";
require(FPDF_ROOT."/fpdf.php");
chk_access(true);
connect();

// Extended class to add headers and footers
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont("Times", "B", 15);
        $this->Cell(30, 10, DISPLAY_NAME." - Allenamento del ".date("d/m/Y", strtotime($_GET['date'])));
        $this->Ln();

        $this->SetFont("Times", "", 13);

        $this->Cell(7, 10);
        $this->Cell(45, 10, "Cognome", 1, 0);
        $this->Cell(45, 10, "Nome", 1, 0);
        $this->Cell(25, 10, "Ora inizio", 1, 0);
        $this->Cell(25, 10, "Ora fine", 1, 0);
        $this->Cell(40, 10, "Temperatura (".utf8_decode("°C").")", 1, 0);
        $this->Cell(53, 10, "Firma", 1, 0);
        $this->Ln();

        $this->Cell(190, 5);
        $this->Ln();
    }

    function Footer()
    {
        $this->SetY(-25);
        $this->SetFont("Times", "B", 12);
        $this->Cell(0, 10, $this->PageNo(), 0, 0, "C");
    }

    function PrintEmpty($i, $prevrow)
    {
        $extra = 0;
        while($i <= $prevrow['places'] and $extra < 5)
        {
            $this->Cell(7, 10, $i, 1);
            $this->Cell(45, 10, "", 1);
            $this->Cell(45, 10, "", 1);
            $this->Cell(25, 10, substr($prevrow['start_time'], 0, 5), 1);
            $this->Cell(25, 10, substr($prevrow['end_time'], 0, 5), 1);
            $this->Cell(40, 10, "", 1);
            $this->Cell(53, 10, "", 1);
            $this->Ln();

            $i++;
            $extra++;
        }
    }
}

$pdf = new PDF();
$pdf->SetFont("Times", "", 13);
$pdf->SetMargins(25, 20);
$pdf->AddPage("L", "A4");

// Gets the whole time to print for the master
$master_st = prepare_stmt("SELECT MIN(start_time) AS min, MAX(end_time) AS max FROM slots INNER JOIN register WHERE date=?");
$master_st->bind_param("s", $_GET['date']);
$rm = execute_stmt($master_st);
$master_st->close();

$row = $rm->fetch_assoc();

// REPLICATE THIS BLOCK TO ADD MASTERS
$pdf->Cell(7, 10);
$pdf->Cell(45, 10, "\$COGNOMEMAESTRO", 1);
$pdf->Cell(45, 10, "\$NOMEMAESTRO", 1);
$pdf->Cell(25, 10, substr($row['min'], 0, 5), 1);
$pdf->Cell(25, 10, substr($row['max'], 0, 5), 1);
$pdf->Cell(40, 10, "", 1);
$pdf->Cell(53, 10, "", 1);
$pdf->Ln();
// END MASTER BLOCK

$pdf->Cell(190, 5);
$pdf->Ln();

// Gets the participants
$reg_st = prepare_stmt("SELECT start_time, end_time, places, name, surname FROM slots INNER JOIN register INNER JOIN athletes 
    ON slot_fk=slot_id AND athlete_fk=athlete_id 
    WHERE date=?
    ORDER BY start_time, surname, name");
$reg_st->bind_param("s", $_GET['date']);
$ret = execute_stmt($reg_st);
$reg_st->close();

$prevtime = "";
while($row = $ret->fetch_assoc())
{
    if($prevtime != $row['start_time'])
    {
        if($prevtime != "")
        {
            $pdf->PrintEmpty($i, $prevrow);
            $pdf->Cell(190, 5);
            $pdf->Ln();
        }
            
        $prevtime = $row['start_time'];
        $i = 1;
    }

    $prevrow = $row;
    
    $pdf->Cell(7, 10, $i, 1);
    $pdf->Cell(45, 10, utf8_decode($row['surname']), 1);
    $pdf->Cell(45, 10, utf8_decode($row['name']), 1);
    $pdf->Cell(25, 10, substr($row['start_time'], 0, 5), 1);
    $pdf->Cell(25, 10, substr($row['end_time'], 0, 5), 1);
    $pdf->Cell(40, 10, "", 1);
    $pdf->Cell(53, 10, "", 1);
    $pdf->Ln();

    $i++;
}

$pdf->PrintEmpty($i, $prevrow);

$pdf->Output("I", $_GET['date']."_da_stampare.pdf");
$mysqli->close();
?>