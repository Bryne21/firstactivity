<?php
session_start();

// 1. Security Check
if (!isset($_SESSION["fldUsername"])) {
    header("Location: loginform.php");
    exit;
}

// 2. Formatting: Sanitize data
$name     = htmlspecialchars($_SESSION['fldName']);
$username = htmlspecialchars($_SESSION['fldUsername']);
$email    = htmlspecialchars($_SESSION['fldEmail']);
$initial  = strtoupper(substr($name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .profile-card { background: #ffffff; width: 500px; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); text-align: center; }
        .avatar { width: 60px; height: 60px; background: #007bff; color: #fff; font-size: 1.8rem; font-weight: bold; line-height: 60px; border-radius: 50%; margin: 0 auto 10px; }
        h1 { font-size: 1.4rem; color: #333; margin: 0 0 15px 0; }
        .user-details { background: #f8f9fa; border-radius: 8px; padding: 10px; margin-bottom: 20px; border: 1px solid #eee; font-size: 0.85rem; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .label { color: #666; font-weight: 600; }

        .action-links {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 10px;
        }

        .btn { text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 10px; border-radius: 6px; color: white; transition: transform 0.1s, opacity 0.2s; display: block; text-align: center; border: none; cursor: pointer; }
        .btn:active { transform: scale(0.98); }
        .btn:hover { opacity: 0.9; }

        /* Color Palette */
        .btn-blue   { background-color: #007bff; }
        .btn-green  { background-color: #28a745; }
        .btn-purple { background-color: #6f42c1; } /* New color for Payroll */
        .btn-gray   { background-color: #6c757d; }
        .btn-red    { background-color: #dc3545; }
        
        .section-tag { grid-column: span 2; font-size: 0.7rem; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-top: 10px; text-align: left; }
    </style>
</head>
<body>

    <div class="profile-card">
        <div class="avatar"><?php echo $initial; ?></div>
        <h1>Welcome, <?php echo $name; ?></h1>

        <div class="user-details">
            <div class="detail-row">
                <span class="label">Username:</span>
                <span class="value">@<?php echo $username; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Email:</span>
                <span class="value"><?php echo $email; ?></span>
            </div>
        </div>

        <div class="action-links">
            <div class="section-tag">System Access</div>
            <a href="add_user.php" class="btn btn-blue">Add User</a> 
            <a href="viewuser.php" class="btn btn-blue">View All Users</a> 
            
            <div class="section-tag">Employee Management</div>
            <a href="addemployee.php" class="btn btn-green">Add Employee</a>
            <a href="viewemployee.php" class="btn btn-green">View Employee List</a>
            
            <div class="section-tag">Payroll & Kaltas</div>
            <a href="compute_salary.php" class="btn btn-purple">Compute Salary</a>
            <a href="salary_report.php" class="btn btn-purple">Monthly Report</a>
            
            <div class="section-tag">Session</div>
            <a href="main.php" class="btn btn-gray">Refresh Dashboard</a> 
            <a href="logout.php" class="btn btn-red">Logout</a>
        </div>
    </div>

</body>
</html>