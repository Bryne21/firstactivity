<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; // To store success/error alerts

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = ($_POST['password']);

    // 2. Check for Duplicates
    $check_stmt = $conn->prepare("SELECT fldUsername, fldEmail FROM tblusers WHERE fldUsername = ? OR fldEmail = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert error'>Error: Username or Email already exists.</div>";
    } else {
        // 3. Insert
        $insert_stmt = $conn->prepare("INSERT INTO tblusers (fldName, fldUsername, fldEmail, fldPassword) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("ssss", $name, $username, $email, $password);

        if ($insert_stmt->execute()) {
            $message = "<div class='alert success'>User added successfully!</div>";
        } else {
            $message = "<div class='alert error'>Error: " . $conn->error . "</div>";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <style>
        :root {
            --primary: #4a90e2;
            --bg: #f4f7f6;
            --text: #333;
            --white: #ffffff;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg); color: var(--text); margin: 0; display: flex; justify-content: center; padding: 40px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1000px; width: 100%; }
        
        /* Card Styles */
        .card { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 10px; }

        /* Form Styling */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; transition: 0.3s; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 5px rgba(74, 144, 226, 0.3); }
        button { width: 100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #357abd; }

        /* List Styling */
        .user-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .user-item:last-child { border: none; }
        .user-name { font-weight: bold; display: block; }
        .user-email { font-size: 0.85rem; color: #666; }

        /* Alerts */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

          
            
        </form>
    </div>

    <div class="card">
        <h2>Registered Users</h2>
        <div class="user-list">
            <?php
            $result = $conn->query("SELECT fldName, fldEmail FROM tblusers ORDER BY fldName ASC");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='user-item'>";
                    echo "<span class='user-name'>" . htmlspecialchars($row["fldName"]) . "</span>";
                    echo "<span class='user-email'>" . htmlspecialchars($row["fldEmail"]) . "</span>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color:#999;'>No users found.</p>";
            }
            $conn->close();
            ?>
            <a href="main.php" class="logout-link">Back to dashboard</a>
        </div>
    </div>
</div>

</body>
</html>