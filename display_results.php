<?php
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['from_student_id'], $_POST['to_student_id'], $_POST['semester'])) {
    $from_student_id = $_POST['from_student_id'];
    $to_student_id = $_POST['to_student_id'];
    $semester = $_POST['semester'];

    $sql = "SELECT student_id, student_name, subject, grade, semester 
            FROM results 
            WHERE student_id BETWEEN ? AND ? 
            AND semester = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $from_student_id, $to_student_id, $semester);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<center><h2>Results of students from<br>"."<br>"." Roll numbers: $from_student_id to $to_student_id"."<br>"."<br>Semester: $semester</h2></center>";
        echo "<center><table border='3'>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Subject</th>
                        <th>Grade</th>
                        <th>Semester</th>
                        <th>Semester GPA</th>
                    </tr>
                </thead>
                <tbody>";
        
        $previous_student_id = null;
        $current_semester_results = [];
        
        while ($row = $result->fetch_assoc()) {
            $current_student_id = $row['student_id'];
            
            // Check if new student ID or end of results
            if ($previous_student_id !== $current_student_id && $previous_student_id !== null) {
                // Display semester averages for the previous student
                displaySemesterAverages($current_semester_results);
                $current_semester_results = []; // Reset for new student
            }
            
            // Store result for current semester
            $current_semester_results[] = $row;
            
            // Display result row
            echo "<tr>";
            if ($previous_student_id !== $current_student_id) {
                echo "<td>{$row['student_id']}</td>";
                echo "<td>{$row['student_name']}</td>";
            } else {
                echo "<td></td><td></td>";
            }
            echo "<td>{$row['subject']}</td>";
            echo "<td>{$row['grade']}</td>";
            echo "<td>{$row['semester']}</td>";
            echo "</tr>";
            
            $previous_student_id = $current_student_id;
        }
        
        // Display semester averages for the last student in the result set
        if (!empty($current_semester_results)) {
            displaySemesterAverages($current_semester_results);
        }
        
        echo "</tbody></table></center>";
    } else {
        echo "No results found for the provided Student ID range and semester.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Please provide From Student ID, To Student ID, and Semester.";
}

// Function to display semester averages
function displaySemesterAverages($results) {
    $semester_results = [];
    $total_grade_points = 0;
    $total_subjects = 0;
    
    foreach ($results as $row) {
        $semester = $row['semester'];
        if (!isset($semester_results[$semester])) {
            $semester_results[$semester] = [];
        }
        $semester_results[$semester][] = $row;
        
        // Calculate grade points
        $grade_points = getGradePoints($row['grade']);
        $total_grade_points += $grade_points;
        $total_subjects++;
    }
    
    // Display results
    if (!empty($semester_results)) {
        foreach ($semester_results as $semester => $results) {
            $average_grade_points = $total_grade_points / $total_subjects;
            echo "<tr>";
            echo "<td colspan='4'></td>"; // Empty cells for Student ID, Name, Subject, and Grade
            echo "<td><b>Semester GPA</b></td>";
            echo "<td><b>" . number_format($average_grade_points, 2) . "</b></td>";
            echo "</tr>";
        }
    }
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body><br>
    <center><button><a href="staff1.php">GO BACK</a></button></center>
</body>
</html>