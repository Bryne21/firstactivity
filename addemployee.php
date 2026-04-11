<?php
session_start();

if (!isset($_SESSION["fldUsername"])) {
    header("Location: loginform.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and Trim Data
    $firstname  = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname   = trim($_POST['lastname']);
    $position   = trim($_POST['position']);
    $address    = trim($_POST['address']);
    $dob        = $_POST['dateofbirth'];
    $rate       = $_POST['rateperday'];

    $errors = [];

    // --- VALIDATION RULES ---
    if (empty($firstname) || !preg_match("/^[a-zA-Z ]*$/", $firstname)) {
        $errors[] = "First Name is required and must contain only letters.";
    }
    
    if (!empty($middlename) && !preg_match("/^[a-zA-Z ]*$/", $middlename)) {
        $errors[] = "Middle Name must contain only letters.";
    }

    if (empty($lastname) || !preg_match("/^[a-zA-Z ]*$/", $lastname)) {
        $errors[] = "Last Name is required and must contain only letters.";
    }

    // --- CHECK FOR DUPLICATE NAME (Since ID is now auto-generated) ---
    if (empty($errors)) {
        $check_sql = "SELECT employeeid FROM employeeid WHERE firstname = ? AND lastname = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("ss", $firstname, $lastname);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $errors[] = "An employee named $firstname $lastname already exists.";
        }
        $stmt_check->close();
    }

    // --- FINAL PROCESS ---
    if (!empty($errors)) {
        $message = "<div class='alert error'><strong>Correct the following:</strong><br>• " . implode("<br>• ", $errors) . "</div>";
    } else {
        // NOTICE: 'employeeid' is NOT in the column list or values below. 
        // Database will assign it automatically.
        $insert_sql = "INSERT INTO employeeid (firstname, middlename, lastname, position, address, dateofbirth, rateperday) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ssssssi", $firstname, $middlename, $lastname, $position, $address, $dob, $rate);

        if ($stmt_insert->execute()) {
            // Get the ID that was just created
            $new_id = $conn->insert_id; 
            $message = "<div class='alert success'>Success! Employee registered with ID: <strong>#$new_id</strong></div>";
            $firstname = $middlename = $lastname = $position = $address = $dob = $rate = "";
        } else {
            $message = "<div class='alert error'>Database Error: " . $conn->error . "</div>";
        }
        $stmt_insert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee - Auto Increment</title>
    <style>
        :root { --primary: #28a745; --bg: #f4f7f6; --white: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); display: flex; justify-content: center; padding: 20px; }
        .card { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 600px; }
        h2 { color: var(--primary); text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .full-width { grid-column: span 2; }
        label { display: block; font-weight: 600; font-size: 0.75rem; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        input:focus { border-color: var(--primary); outline: none; }
        button { width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 20px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; text-align: center; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .footer-links { display: flex; justify-content: space-between; margin-top: 20px; }
        a { text-decoration: none; color: #4a90e2; font-weight: bold; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="card">
    <h2>Employee Registration</h2>
    
    <?php echo $message; ?>

    <form action="" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="firstname" value="<?php echo htmlspecialchars($firstname ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middlename" value="<?php echo htmlspecialchars($middlename ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="lastname" value="<?php echo htmlspecialchars($lastname ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Position</label>
                <input type="text" name="position" value="<?php echo htmlspecialchars($position ?? ''); ?>" required>
            </div>
            <div class="form-group full-width">
                <label>Home Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dateofbirth" value="<?php echo htmlspecialchars($dob ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Rate Per Day</label>
                <input type="number" name="rateperday" value="<?php echo htmlspecialchars($rate ?? ''); ?>" required>
            </div>
        </div>

        <button type="submit">Save Employee</button>
        
        <div class="footer-links">
            <a href="main.php">← Dashboard</a>
            <a href="viewemployee.php">View List →</a>
        </div>
    </form>
</div>

</body>
</html>