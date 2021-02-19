<?php
// Script used to generate the list of participant to share with a group
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
}

$pdf = new PDF();
$pdf->SetFont("Times", "", 13);
$pdf->SetMargins(20, 25);
$pdf->AddPage("P", "A4");

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
    $pdf->Ln();

    $i++;
}

$pdf->Output("I", $_GET['date']."_allenamento.pdf");
$mysqli->close();
?>