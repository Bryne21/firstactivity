<?php
// 1. Database Connection
$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    // Tip: Use password_hash() for better security!
    $password = $_POST['password']; 

    $check_stmt = $conn->prepare("SELECT fldUsername, fldEmail FROM tblusers WHERE fldUsername = ? OR fldEmail = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert error'>Error: Username or Email already exists.</div>";
    } else {
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

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: var(--bg); 
            color: var(--text); 
            margin: 0; 
            /* Centering Logic */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; /* Takes full height of the screen */
            padding: 20px;
        }

        .container { 
            width: 100%; 
            max-width: 450px; /* Limits width so it doesn't look too stretched */
        }
        
        .card { 
            background: var(--white); 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        }

        h2 { margin-top: 0; color: var(--primary); border-bottom: 2px solid #eee; padding-bottom: 10px; text-align: center; }

        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; transition: 0.3s; }
        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 5px rgba(74, 144, 226, 0.3); }
        
        button { 
            width: 100%; 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            margin-top: 10px; 
        }
        
        button:hover { background: #357abd; }

        .logout-link { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            color: #888; 
            text-decoration: none; 
            font-size: 0.9rem; 
        }
        .logout-link:hover { color: var(--primary); }

        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Add New User</h2>
        <?php echo $message; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="johndoe123" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Create Account</button>
            <a href="main.php" class="logout-link">Back to dashboard</a>
        </form>
    </div>
</div>

</body>
</html>