<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <script>
        window.onload = function() {
            var message = "<?php echo $message; ?>";
            if (message) {
                alert(message);
            }
        };
    </script>
</head>
<body>
    <div class="admin-container">
        <h2>Admin Dashboard</h2>
        <center><button><a href="admin1.php">IMPORT FILE</a></button></center>
        
        <h3>Add New User</h3>
        <form action="add_user.php" method="post">
            <label for="user_id">User ID:</label>
            <input type="text" id="user_id" name="user_id" required>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="student">Student</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Add User</button>
        </form>
        
        <h3>Edit Existing Users</h3>
        <?php
        $conn = new mysqli('localhost:3306', 'root', '', 'resultdb');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$row['role']}</td>
                        <td>
                            <a href='edit_user.php?id={$row['id']}'>Edit</a> |
                            <a href='delete_user.php?id={$row['id']}'>Delete</a>
                        </td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "No users found.";
        }
        
        $conn->close();
        ?>
         <center><button><a href="homepage.html">HOME</a></button></center>
    </div>
   
</body>
</html>