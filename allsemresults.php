<?php
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['from_student_id'], $_POST['to_student_id'])) {
    $from_student_id = $_POST['from_student_id'];
    $to_student_id = $_POST['to_student_id'];

    // Fetch all results for the student ID range
    $sql = "SELECT student_id, student_name, subject, grade, semester FROM results WHERE student_id BETWEEN ? AND ? ORDER BY student_id, semester";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $from_student_id, $to_student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Array to store results grouped by student
    $student_results = [];

    // Fetch and group results by student ID
    while ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        if (!isset($student_results[$student_id])) {
            $student_results[$student_id] = [
                'student_name' => $row['student_name'],
                'results' => []
            ];
        }
        $student_results[$student_id]['results'][] = [
            'subject' => $row['subject'],
            'grade' => $row['grade'],
            'semester' => $row['semester']
        ];
    }

    // Close statement
    $stmt->close();

    // Display results for each student
    if (!empty($student_results)) {
        foreach ($student_results as $student_id => $student_data) {
            echo "<center><h2>Student ID: {$student_id}</h2></center>";
            echo "<center><h3>Student Name: {$student_data['student_name']}</h3></center>";
            echo "<div style='display: flex; justify-content: space-between;'>"; // Flex container for table and GPA display

            // Table for displaying results
            echo "<table border='1' class='table'>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                            <th>Semester</th>
                        </tr>
                    </thead>
                    <tbody>";

            // Variables to calculate semester and overall GPA
            $semester_gpas = [];
            $total_grade_points = 0;
            $total_subjects = 0;

            // Display individual results
            $previous_semester = null;
            foreach ($student_data['results'] as $result) {
                // Check if semester has changed
                if ($previous_semester !== $result['semester']) {
                    // Display "End of Semester" row if it's not the first row
                    if ($previous_semester !== null) {
                        echo "<tr><td colspan='3'><strong>End of Semester {$previous_semester}</strong></td></tr>";
                    }
                    $previous_semester = $result['semester'];
                }

                echo "<tr>";
                echo "<td>{$result['subject']}</td>";
                echo "<td>{$result['grade']}</td>";
                echo "<td>{$result['semester']}</td>";
                echo "</tr>";

                // Calculate grade points for GPA
                $grade_points = getGradePoints($result['grade']);
                $total_grade_points += $grade_points;
                $total_subjects++;

                // Store semester GPA
                if (!isset($semester_gpas[$result['semester']])) {
                    $semester_gpas[$result['semester']] = [
                        'total_grade_points' => 0,
                        'total_subjects' => 0
                    ];
                }
                $semester_gpas[$result['semester']]['total_grade_points'] += $grade_points;
                $semester_gpas[$result['semester']]['total_subjects']++;
            }

            // Display "End of Semester" for the last semester
            if ($previous_semester !== null) {
                echo "<tr><td colspan='3'><strong>End of Semester {$previous_semester}</strong></td></tr>";
            }

            echo "</tbody></table>";

            // Display GPA to the right of the table
            echo "<div class='gpa'>";
            foreach ($semester_gpas as $semester => $data) {
                $semester_gpa = $data['total_grade_points'] / $data['total_subjects'];
                echo "<p><strong>Semester {$semester} GPA:</strong> " . number_format($semester_gpa, 2) . "</p>";
            }

            // Display overall GPA for all semesters
            $overall_gpa = $total_grade_points / $total_subjects;
            echo "<p><strong>Overall GPA:</strong> " . number_format($overall_gpa, 2) . "</p>";
            echo "</div>";

            echo "</div>"; // Close flex container
        }
    } else {
        echo "No results found for the provided Student ID range.";
    }

    // Close connection
    $conn->close();
} else {
    echo "No Student ID range provided.";
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
    <title>Student Results</title>
    <style>
        .table {
            width: 80%;
            margin-left: 7%;
        }
        .gpa {
            margin-top: 15px;
            padding-left: 20px;
        }
        button a{
            text-decoration: none;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <br>
    <br>
    <center><button><a href="staff1.php"> GO BACK</a></button></center>
</body>
</html>