<?php
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve semesters from the database
$sql_semesters = "SELECT DISTINCT semester FROM results ORDER BY semester";
$result_semesters = $conn->query($sql_semesters);

$semesters = [];
if ($result_semesters) {
    while ($row = $result_semesters->fetch_assoc()) {
        $semesters[] = $row['semester'];
    }
} else {
    echo "Error: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        .due-subjects {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }

        .due-subjects.fail {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Result Management</h1>
        <form action="student2.php" method="post">
            <label for="studentId">Enter Student ID:</label>
            <input type="text" id="studentId" name="student_id" required>
            <br><br>
            <label for="semester">Select Semester:</label>
            <select id="semester" name="semester" required>
                <option value="overall">Overall</option>
                <?php foreach ($semesters as $semester) { ?>
                    <option value="<?php echo htmlspecialchars($semester); ?>">Semester <?php echo htmlspecialchars($semester); ?></option>
                <?php } ?>
            </select>
            <br><br>
            <button type="submit">Get Results</button>
        </form>

        <?php
        if (isset($_POST['student_id']) && isset($_POST['semester'])) {
            $student_id = $_POST['student_id'];
            $selected_semester = $_POST['semester'];

            // Retrieve student details
            $sql_student = "SELECT student_id, student_name FROM results WHERE student_id = ? LIMIT 1";
            $stmt_student = $conn->prepare($sql_student);
            $stmt_student->bind_param('s', $student_id);
            $stmt_student->execute();
            $result_student = $stmt_student->get_result();
            $student_info = $result_student->fetch_assoc();

            // Display student name and ID
            echo "<center><p><b>Student Name: " . htmlspecialchars($student_info['student_name']) . "</b></p>";
            echo "<p><b>Student ID: " . htmlspecialchars($student_info['student_id']) . "</b></p></center>";

            if ($selected_semester === 'overall') {
                // Retrieve all results
                $sql_results = "SELECT subject, grade, semester FROM results WHERE student_id = ? ORDER BY semester";
            } else {
                // Retrieve results for the selected semester
                $sql_results = "SELECT subject, grade, semester FROM results WHERE student_id = ? AND semester = ? ORDER BY semester";
            }

            $stmt_results = $conn->prepare($sql_results);
            if ($selected_semester === 'overall') {
                $stmt_results->bind_param('s', $student_id);
            } else {
                $stmt_results->bind_param('ss', $student_id, $selected_semester);
            }
            $stmt_results->execute();
            $result = $stmt_results->get_result();

            $semester_results = [];
            $total_grade_points = 0;
            $total_subjects = 0;
            $due_subjects = 0;

            while ($row = $result->fetch_assoc()) {
                $semester = $row['semester'];
                if (!isset($semester_results[$semester])) {
                    $semester_results[$semester] = [];
                }
                $semester_results[$semester][] = $row;

                // Calculate grade points
                $grade_points = getGradePoints($row['grade']);
                $total_grade_points += $grade_points;
                $total_subjects++;

                // Count failed subjects
                if ($row['grade'] === 'F') {
                    $due_subjects++;
                }
            }

            // Display results
            if (!empty($semester_results)) {
                foreach ($semester_results as $semester => $results) {
                    displaySemesterResults($results, $semester);
                    echo "<br><br>";
                }
                // Display overall CGPA if multiple semesters
                if ($selected_semester === 'overall') {
                    $overall_average_points = $total_grade_points / $total_subjects;
                    echo "<br><center><h2><strong>CGPA:</strong> " . number_format($overall_average_points, 2) . "</h2></center>";
                }
            } else {
                echo "No results found for the provided Student ID.";
            }

            // Display due subjects with green background even if 0
            $due_subjects_class = $due_subjects > 0 ? 'fail' : '';
            echo "<div class='due-subjects $due_subjects_class'><strong>DUE SUBJECTS:</strong> {$due_subjects}</div>";

            $stmt_student->close();
            $stmt_results->close();
            $conn->close();
        }

        // Function to display semester results
        function displaySemesterResults($results, $semester) {
            echo "<center><h2>Semester " . htmlspecialchars($semester) . "</h2></center>";
            echo "<center><table border='1' style='width: 80%;'>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($results as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                echo "<td>" . htmlspecialchars($row['grade']) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table></center>";

            // Calculate semester average grade points
            $total_grade_points = array_sum(array_map('getGradePoints', array_column($results, 'grade')));
            $total_subjects = count($results);
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

        <!-- Download PDF Button -->
        <?php if (isset($_POST['student_id']) && isset($_POST['semester'])) { ?>
            <form action="student1.php" method="post" style="text-align: center;">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
                <input type="hidden" name="semester" value="<?php echo htmlspecialchars($selected_semester); ?>">
                <button type="submit">Download Results as PDF</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>