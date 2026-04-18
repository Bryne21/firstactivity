<?php
session_start();
if (!isset($_SESSION["fldUsername"])) { header("Location: loginform.php"); exit; }

$conn = new mysqli("localhost", "root", "root", "test_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 1. Capture filters
$selected_month = $_POST['month_select'] ?? date('F');
$selected_year = $_POST['year_select'] ?? date('Y');
$selected_period = $_POST['period_select'] ?? '1-15';

// 2. Logic for SQL Query - Now including all deductions
if ($selected_period == '1-31') {
    // FULL MONTH: Group and SUM everything
    $sql = "SELECT employee_name, 
                   MAX(rate_per_day) as rate_per_day, 
                   SUM(days_worked) as total_days, 
                   SUM(sss_deduction) as total_sss, 
                   SUM(philhealth_deduction) as total_philhealth, 
                   SUM(pagibig_deduction) as total_pagibig, 
                   SUM(net_salary) as total_net 
            FROM tblsalary 
            WHERE month = '$selected_month' AND year = '$selected_year'
            GROUP BY employee_name 
            ORDER BY employee_name ASC";
} else {
    // SPECIFIC PERIOD
    $sql = "SELECT employee_name, 
                   rate_per_day, 
                   days_worked as total_days, 
                   sss_deduction as total_sss, 
                   philhealth_deduction as total_philhealth, 
                   pagibig_deduction as total_pagibig, 
                   net_salary as total_net 
            FROM tblsalary 
            WHERE month = '$selected_month' 
            AND year = '$selected_year' 
            AND period = '$selected_period' 
            ORDER BY employee_name ASC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Salary Report</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #4facfe, #00f2fe); min-height: 100vh; padding: 40px; }
        .container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 1300px; margin: auto; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .filter-section { background: #f8fbff; padding: 20px; border-radius: 10px; display: flex; gap: 15px; justify-content: center; align-items: center; border: 1px solid #d4e6f1; margin-bottom: 30px; }
        .custom-select { padding: 10px; border-radius: 6px; border: 1px solid #ccc; background: white; font-weight: bold; color: #3498db; }
        table { width: 100%; border-collapse: collapse; background: white; font-size: 13px; }
        th { background: #3498db; color: white; padding: 12px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        .discount { color: #e74c3c; font-weight: bold; font-size: 0.9em; }
        .net { color: #27ae60; font-weight: bold; font-size: 1.1em; }
        .btn-filter { background: #e67e22; color: white; padding: 11px 25px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-back { background: #34495e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block; margin-top: 20px; }
        .report-header-info { text-align: center; background: #e8f6f3; padding: 10px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #27ae60; }
        
        @media print {
            .filter-section, .btn-back, .btn-filter { display: none; }
            body { background: white; padding: 0; }
            .container { box-shadow: none; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Archived Salary Report</h2>

    <form method="POST" action="" class="filter-section">
        <select name="year_select" class="custom-select">
            <option value="2025" <?= ($selected_year == "2025") ? "selected" : "" ?>>2025</option>
            <option value="2026" <?= ($selected_year == "2026") ? "selected" : "" ?>>2026</option>
        </select>

        <select name="month_select" class="custom-select">
            <?php
            $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            foreach ($months as $m) {
                echo "<option value='$m' ".($m == $selected_month ? "selected" : "").">$m</option>";
            }
            ?>
        </select>

        <select name="period_select" class="custom-select">
            <option value="1-15" <?= ($selected_period == "1-15") ? "selected" : "" ?>>1st Half (With Kaltas)</option>
            <option value="16-31" <?= ($selected_period == "16-31") ? "selected" : "" ?>>2nd Half (No Kaltas)</option>
            <option value="1-31" <?= ($selected_period == "1-31") ? "selected" : "" ?>>Full Month (Combined)</option>
        </select>

        <button type="submit" class="btn-filter">Search Records</button>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="report-header-info">
            Records Found for: <strong><?= $selected_month ?> <?= $selected_year ?></strong> | 
            Period: <strong><?= ($selected_period == '1-31') ? 'Full Month (Combined)' : ($selected_period == '1-15' ? '1st Half' : '2nd Half') ?></strong>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Rate/Day</th>
                    <th>Total Days</th>
                    <th>Gross Pay</th>
                    <th>SSS</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                while($row = $result->fetch_assoc()): 
                    $rate = $row['rate_per_day'];
                    $days = $row['total_days'];
                    $gross = $rate * $days;
                    $sss = $row['total_sss'];
                    $philhealth = $row['total_philhealth'];
                    $pagibig = $row['total_pagibig'];
                    $net = $row['total_net'];
                    $grand_total += $net;
                ?>
                <tr>
                    <td style="text-align: left;"><strong><?= htmlspecialchars($row['employee_name']) ?></strong></td>
                    <td>₱<?= number_format($rate, 2) ?></td>
                    <td><?= $days ?></td>
                    <td>₱<?= number_format($gross, 2) ?></td>
                    <td class="discount">-₱<?= number_format($sss, 2) ?></td>
                    <td class="discount">-₱<?= number_format($philhealth, 2) ?></td>
                    <td class="discount">-₱<?= number_format($pagibig, 2) ?></td>
                    <td class="net">₱<?= number_format($net, 2) ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="background: #f1f1f1; font-weight: bold;">
                    <td colspan="7" style="text-align: right; padding-right: 20px;">GRAND TOTAL PAYOUT:</td>
                    <td class="net">₱<?= number_format($grand_total, 2) ?></td>
                </tr>
            </tbody>
        </table>


    <?php else: ?>
        <div class="empty-state">
            <h3>No Records Found</h3>
            <p>No payroll data found for <strong><?= $selected_month ?> <?= $selected_year ?></strong>.</p>
        </div>
    <?php endif; ?>

    <br>
    <a href="main.php" class="btn-back">Back to Dashboard</a>
</div>

</body>
</html>