<?php
session_start();
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $_SESSION['id']=$id;
    $username = $_POST['username'];
    $_SESSION['username']=$username;
    $password = $_POST['password'];
    $_SESSION['password']=$password;
    $role = $_POST['role'];
    $_SESSION['role']=$role;

    $sql = "SELECT * FROM users WHERE username = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            if ($role == "student") {
                include "student2.php";
            } elseif ($role == "staff") {
                include "staff1.php";
            } elseif ($role == "admin") {
                include "admin.php";
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="pic"> 
    <div class="login-container">
        <center><h2>Login</h2></center> 
        <form action="login.php" method="post">
            <label for="id">Enter Id:</label>
            <input type="text" id="id" name="id" required>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="student">student</option>
                <option value="staff">staff</option>
                <option value="admin">admin</option>
            </select>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>