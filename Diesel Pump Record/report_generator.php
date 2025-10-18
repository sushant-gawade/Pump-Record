<?php
require_once 'includes/db_config.php';

// --- CRITICAL PATH CHECK ---
// The script requires 'fpdf/fpdf.php' to be present.
$fpdf_path = 'fpdf/fpdf.php';
if (!file_exists($fpdf_path)) {
    // This is the message you are seeing. If you still see it, the file path is WRONG.
    die("❌ **FATAL ERROR:** FPDF library file not found. Please download FPDF and place 'fpdf.php' inside the 'fpdf' folder in your project root.");
}
require_once $fpdf_path; 
// ----------------------------


if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}

// =========================================================================
// FPDF Class Extension (Handles Header, Footer, and Table drawing)
// =========================================================================

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        global $title;
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10, $title, 0, 1, 'C');
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C'); 
    }

    // Table renderer
    function FancyTable($header, $data, $type)
    {
        // Define Column Widths based on report type
        if ($type == 'stock_history') {
             $w = array(35, 40, 115); 
        } else { // nozzle_list
             $w = array(30, 40, 40, 60); 
        }

        // Header Style
        $this->SetFillColor(200, 220, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 10);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();

        // Data Style
        $this->SetFillColor(240, 240, 240);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 10);
        $fill = false;
        
        foreach($data as $row)
        {
            $this->Cell($w[0], 6, $row[0], 'LR', 0, 'L', $fill); 
            
            if ($type == 'nozzle_list') {
                // Nozzle Report (4 columns)
                $this->Cell($w[1], 6, number_format($row[1], 2) . ' L', 'LR', 0, 'R', $fill);
                $this->Cell($w[2], 6, number_format($row[2], 2) . ' L', 'LR', 0, 'R', $fill); 
                $this->Cell($w[3], 6, number_format($row[3], 2) . ' L', 'LR', 0, 'R', $fill); 
            } else {
                // Stock Report (3 columns)
                $this->Cell($w[1], 6, number_format($row[1], 2) . ' L', 'LR', 0, 'R', $fill); 
                // Truncate notes for display
                $notes = (strlen($row[2]) > 70) ? substr($row[2], 0, 67) . '...' : $row[2];
                $this->Cell($w[2], 6, $notes, 'LR', 0, 'L', $fill); 
            }
            $this->Ln();
            $fill = !$fill;
        }
        // Closing line
        $this->Cell(array_sum($w), 0, '', 'T');
    }
}

// =========================================================================
// Data Fetching and Report Setup
// =========================================================================

$report_type = $_GET['type'] ?? 'stock_history';
$title = "";
$headers = [];
$query = "";

if ($report_type == 'stock_history') {
    $title = "Petrol Stock Addition History Report";
    $query = "SELECT add_date, liters_added, notes FROM stock ORDER BY add_date DESC";
    $headers = ['Date', 'Liters Added', 'Notes'];
} elseif ($report_type == 'nozzle_list') {
    $title = "Daily Nozzle Readings & Sales Report";
    $query = "
        SELECT
            reading_date,
            (n1_close - n1_open) AS n1_dispensed,
            (n2_close - n2_open) AS n2_dispensed,
            ((n1_close - n1_open) + (n2_close - n2_open)) AS total_dispensed
        FROM nozzle_readings
        ORDER BY reading_date DESC
    ";
    $headers = ['Date', 'N1 Disp. (L)', 'N2 Disp. (L)', 'Total Disp. (L)'];
}

$result = $conn->query($query);
$data = [];
if ($result) {
    // Fetch data rows as numerical arrays for FPDF
    while ($row = $result->fetch_row()) { 
        $data[] = $row;
    }
}

// =========================================================================
// PDF Generation
// =========================================================================

// Instantiate PDF object
$pdf = new PDF();
$pdf->AliasNbPages(); // Enable page number {nb} in the footer
$pdf->AddPage('P'); // Portrait orientation

// Render Table
if (empty($data)) {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'No data found for this report.', 0, 1, 'C');
} else {
    $pdf->FancyTable($headers, $data, $report_type);
}

// Output the PDF (forces download)
$pdf_filename = str_replace(' ', '_', $title) . '_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $pdf_filename); // 'D' forces download

// Close the database connection
$conn->close();
?>