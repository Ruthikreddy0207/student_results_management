<?php
require('C:\xampp\htdocs\minpro1/fpdf.php'); // Update the path to match your FPDF installation

// Function to generate PDF
class PDF extends FPDF
{
    // Header
    function Header()
    {
        // Title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Student Result Report', 0, 1, 'C');

        // Student Info
        global $student_info, $selected_semester;
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Student Name: ' . $student_info['student_name'], 0, 1, 'L');
        $this->Cell(0, 10, 'Student ID: ' . $student_info['student_id'], 0, 1, 'L');
        $this->Cell(0, 10, 'Selected Semester: ' . ($selected_semester === 'overall' ? 'Overall' : 'Semester ' . $selected_semester), 0, 1, 'L');

        // Line break
        $this->Ln(10);
    }

    // Semester Results
    function SemesterResults($semester_results, $semester)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Semester ' . $semester, 0, 1, 'L');

        // Table headers
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(80, 10, 'Subject', 1, 0, 'C');
        $this->Cell(40, 10, 'Grade', 1, 1, 'C');

        // Table data
        $this->SetFont('Arial', '', 12);
        foreach ($semester_results as $row) {
            $this->Cell(80, 10, $row['subject'], 1, 0, 'L');
            $this->Cell(40, 10, $row['grade'], 1, 1, 'C');
        }

        // Line break
        $this->Ln(10);
    }

    // Due Subjects
    function DueSubjects($due_subjects)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Due Subjects', 0, 1, 'L');

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Due Subjects Count: ' . $due_subjects, 0, 1, 'L');

        // Line break
        $this->Ln(10);
    }

    // Overall CGPA
    function OverallCGPA($overall_average_points)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Overall CGPA', 0, 1, 'L');

        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'CGPA: ' . number_format($overall_average_points, 2), 0, 1, 'L');
    }

    // Semester GPA
    function SemesterGPA($semester_results)
    {
        // Calculate GPA
        $total_grade_points = 0;
        $total_credits = 0;

        foreach ($semester_results as $row) {
            $grade_points = getGradePoints($row['grade']);
            $credits = 3; // Assuming each subject has 3 credits
            $total_grade_points += $grade_points * $credits;
            $total_credits += $credits;
        }

        if ($total_credits > 0) {
            $gpa = $total_grade_points / $total_credits;
        } else {
            $gpa = 0;
        }

        // Display GPA
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'GPA: ' . number_format($gpa, 2), 0, 1, 'L');

        // Line break
        $this->Ln(10);
    }
}

// Main script

// Start session and connect to database
session_start();
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve student details
$student_id = $_POST['student_id'];
$selected_semester = $_POST['semester'];

$sql_student = "SELECT student_id, student_name FROM results WHERE student_id = ? LIMIT 1";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param('s', $student_id);
$stmt_student->execute();
$result_student = $stmt_student->get_result();
$student_info = $result_student->fetch_assoc();

// Retrieve results based on selected semester
if ($selected_semester === 'overall') {
    $sql_results = "SELECT subject, grade, semester FROM results WHERE student_id = ? ORDER BY semester";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param('s', $student_id);
} else {
    $sql_results = "SELECT subject, grade, semester FROM results WHERE student_id = ? AND semester = ?";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param('ss', $student_id, $selected_semester);
}

$stmt_results->execute();
$result = $stmt_results->get_result();

$semesters_results = [];
$total_grade_points = 0;
$total_subjects = 0;
$due_subjects = 0;

while ($row = $result->fetch_assoc()) {
    $semester = $row['semester'];
    $semesters_results[$semester][] = $row;

    // Calculate grade points
    $grade_points = getGradePoints($row['grade']);
    $total_grade_points += $grade_points;
    $total_subjects++;

    // Count failed subjects
    if ($row['grade'] === 'F') {
        $due_subjects++;
    }
}

// Calculate overall average grade points if overall selected
$overall_average_points = 0;
if ($selected_semester === 'overall' && $total_subjects > 0) {
    $overall_average_points = $total_grade_points / $total_subjects;
}

// Create PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Iterate through each semester's results
foreach ($semesters_results as $semester => $results) {
    // Semester-wise results
    $pdf->SemesterResults($results, $semester);

    // Semester GPA
    $pdf->SemesterGPA($results);

    // Due subjects for the semester
    $due_subjects_semester = 0; // Initialize due subjects counter for this semester
    foreach ($results as $row) {
        if ($row['grade'] === 'F') {
            $due_subjects_semester++;
        }
    }
    $pdf->DueSubjects($due_subjects_semester);
}

// Overall CGPA if applicable
if ($selected_semester === 'overall') {
    $pdf->OverallCGPA($overall_average_points);
}

// Output PDF
$pdf->Output('D', 'Student_Result_Report.pdf');

// Close connections and statements
$stmt_student->close();
$stmt_results->close();
$conn->close();

// Function to get grade points based on grade
function getGradePoints($grade) {
    $points = [
        'O' => 10, 'A+' => 9, 'A' => 8, 'B+' => 7,
        'B' => 6, 'C' => 5, 'D' => 4, 'F' => 0
    ];
    return isset($points[$grade]) ? $points[$grade] : 0;
}
?>