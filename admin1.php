<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import CSV</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .fileimport {
            text-align: center;
            margin-bottom: 20px;
        }

        .fileimport input[type="file"] {
            margin-top: 10px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
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

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button-container a {
            text-decoration: none;
            margin: 0 10px;
        }

        .button-container a button {
            display: block;
            width: 200px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="fileimport">
        <form method="post" action="admin2.php" enctype="multipart/form-data">
            <input type="file" name="excel_file" accept=".csv"><br><br>
            <input type="submit" name="import" value="Import"><br><br>
        </form>
    </div>
    <h1>STUDENT RESULTS</h1>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Subject</th>
            <th>Grade</th>
            <th>Student Name</th>
        </tr>
        <?php
        $db = mysqli_connect('localhost:3306','root','','resultdb');
        $query="SELECT * FROM users";
        $row = mysqli_query($db,$query);
        while($data = mysqli_fetch_array($row)){
        ?>
        <tr>
            <td><?=$data['id']?></td>
            <td><?=$data['username']?></td>
            <td><?=$data['password']?></td>
            <td><?=$data['role']?></td
        </tr>
        <?php } ?>
    </table>
</html>