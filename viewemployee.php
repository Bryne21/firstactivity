<?php
session_start();

// 1. Security Check
if (!isset($_SESSION["fldUsername"])) {
    header("Location: loginform.php");
    exit;
}

// 2. Database Connection
$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Fetch Records
$sql = "SELECT * FROM employeeid ORDER BY lastname ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Directory</title>
    <style>
        :root { --primary: #4a90e2; --bg: #f4f7f6; --white: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg); padding: 40px; display: flex; justify-content: center; }
        .container { width: 100%; max-width: 1000px; }
        .card { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        h2 { margin: 0; color: var(--primary); }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f8f9fa; color: #666; font-size: 0.75rem; padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .id-badge { background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-weight: bold; color: #333; }
        .back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #888; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header-flex">
            <h2>Employee Directory</h2>
        
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Address</th>
                    <th>Birth Date</th>
                    <th>Rate/Day</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        // This logic checks the 3 most likely column names to stop the "Undefined Key" error
                        $displayID = $row['employeeid'] ?? $row['id'] ?? $row['employeeId'] ?? 'N/A';
                    ?>
                    <tr>
                        <td><span class="id-badge">#<?php echo htmlspecialchars($displayID); ?></span></td>
                        <td><?php echo htmlspecialchars($row['firstname'] . " " . $row['lastname']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo date("M d, Y", strtotime($row['dateofbirth'])); ?></td>
                        <td>₱<?php echo number_format($row['rateperday'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="main.php" class="back-link">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>