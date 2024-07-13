<?php
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];

    // Retrieve student details
    $sql_student = "SELECT student_id, student_name FROM results WHERE student_id = ? LIMIT 1";
    $stmt_student = $conn->prepare($sql_student);
    $stmt_student->bind_param('s', $student_id);
    $stmt_student->execute();
    $result_student = $stmt_student->get_result();
    $student_info = $result_student->fetch_assoc();

    // Display student name and ID
    echo "<center><p><b>Student Name: " . $student_info['student_name'] . "</b></p>";
    echo "<p><b>Student ID: " . $student_info['student_id'] . "</b></p></center>";

    // Retrieve results sorted by semester
    $sql_results = "SELECT subject, grade, semester FROM results WHERE student_id = ? ORDER BY semester";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param('s', $student_id);
    $stmt_results->execute();
    $result = $stmt_results->get_result();

    $semester_results = []; // Array to store results grouped by semester
    $current_semester = null;
    $total_grade_points = 0;
    $total_subjects = 0;
    $due_subjects = 0; // Counter for failed subjects

    while ($row = $result->fetch_assoc()) {
        $semester = $row['semester'];
        if ($current_semester !== $semester) {
            // New semester encountered
            if (!empty($semester_results)) {
                // Display previous semester results
                displaySemesterResults($semester_results, $total_grade_points, $total_subjects, $current_semester);
                echo "<br><br>";
            }
            // Reset variables for new semester
            $semester_results = [];
            $total_grade_points = 0;
            $total_subjects = 0;
            $current_semester = $semester;
        }
        
        // Calculate grade points
        $grade_points = getGradePoints($row['grade']);
        $total_grade_points += $grade_points;
        $total_subjects++;

        // Count failed subjects
        if ($row['grade'] === 'F') {
            $due_subjects++;
        }

        // Add row to current semester results
        $semester_results[] = $row;
    }

    // Display results for the last semester
    if (!empty($semester_results)) {
        displaySemesterResults($semester_results, $total_grade_points, $total_subjects, $current_semester);
    } else {
        echo "No results found for the provided Student ID.";
    }

    // Calculate and display overall average grade points
    if ($total_subjects > 0) {
        $overall_average_points = $total_grade_points / $total_subjects;
        echo "<br><center><h2><strong>CGPA:</strong> " . number_format($overall_average_points, 2) . "</h2></center>";
    }

    // Display due subjects if there are failed subjects
    if ($due_subjects > 0) {
        echo "<div style='position: absolute; top: 20px; right: 20px;'><strong>DUE SUBJECTS:</strong> {$due_subjects}</div>";
    }

    $stmt_student->close();
    $stmt_results->close();
    $conn->close();
} else {
    echo "No Student ID provided.";
}

// Function to display semester results
function displaySemesterResults($semester_results, &$total_grade_points, &$total_subjects, $semester_number) {
    echo "<center><h2>Semester " . $semester_number . "</h2></center>";
    echo "<center><table border='1' style='width: 80%;'>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($semester_results as $row) {
        echo "<tr>";
        echo "<td>{$row['subject']}</td>";
        echo "<td>{$row['grade']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table></center>";

    // Calculate semester average grade points
    $semester_average = $total_grade_points / $total_subjects;
    echo "<center><p><strong>Semester GPA:</strong> " . number_format($semester_average, 2) . "</p></center>";
}

// Function to get grade points based on grade
function getGradePoints($grade) {
    $points = [
        'O' => 10, 'A+' => 9, 'A' => 8, 'B+' => 7,
        'B' => 6, 'C' => 5, 'D' => 4, 'F' => 0
    ];
    return isset($points[$grade]) ? $points[$grade] : 0;
}
?>