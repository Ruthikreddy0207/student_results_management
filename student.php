<?php
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require 'fpdf/fpdf.php';

function getGradePoints($grade) {
    $points = [
        'O' => 10, 'A+' => 9, 'A' => 8, 'B+' => 7,
        'B' => 6, 'C' => 5, 'D' => 4, 'F' => 3
    ];
    return $points[$grade];
}

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    $sql = "SELECT * FROM results WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
    $conn->close();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Student Results');
    $pdf->Ln();
    $pdf->Ln();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(40, 10, 'Subject');
    $pdf->Cell(40, 10, 'Grade');
    $pdf->Ln();

    $totalPoints = 0;
    $hasFail = false;
    foreach ($results as $result) {
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, $result['subject']);
        $pdf->Cell(40, 10, $result['grade']);
        $pdf->Ln();
        $points = getGradePoints($result['grade']);
        $totalPoints += $points;
        if ($result['grade'] == 'F') {
            $hasFail = true;
        }
    }

    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 12);
    if ($hasFail) {
        $pdf->Cell(40, 10, 'Overall Grade: Fail');
    } else {
        $overallGrade = $totalPoints / count($results);
        $pdf->Cell(40, 10, 'Overall Grade: ' . number_format($overallGrade, 2));
    }

    $pdf->Output('D', 'Student_Results.pdf');
} else {
    header("Location: student.html");
    exit();
}