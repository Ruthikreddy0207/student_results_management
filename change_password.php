<?php
session_start();
$conn = new mysqli('localhost:3306', 'root', '', 'resultdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "New passwords do not match.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($old_password == $user['password']) {
                $sql = "UPDATE users SET password = ? WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_password, $username);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo "Password successfully updated.";
                } else {
                    echo "Failed to update password.";
                }
            } else {
                echo "Old password is incorrect.";
            }
        } else {
            echo "User not found.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="login1.css">
</head>
<body class="pic"> 
    <div class="login-container">
        <center><h2>Change Password</h2></center> 
        <form action="change_password.php" method="post">
            <label for="old_password">Old Password:</label>
            <input type="password" id="old_password" name="old_password" required>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Change Password</button>
        </form>
    </div>
</body>
</html>