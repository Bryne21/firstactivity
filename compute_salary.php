<?php
session_start();
if (!isset($_SESSION["fldUsername"])) { header("Location: loginform.php"); exit; }

$conn = new mysqli("localhost", "root", "root", "test_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$month = $_POST['month_val'] ?? date('F');
$year = $_POST['year_val'] ?? date('Y');
$period = $_POST['period_val'] ?? '1-15';

$sql = "SELECT employeeid, firstname, lastname, rateperday FROM employeeid ORDER BY lastname ASC";
$result = $conn->query($sql);

function countWeekdays($m, $y, $p) {
    $month_num = date('n', strtotime($m));
    $start_day = ($p == '1-15') ? 1 : 16;
    $end_day = ($p == '1-15') ? 15 : cal_days_in_month(CAL_GREGORIAN, $month_num, $y);
    
    $weekdays = 0;
    for ($d = $start_day; $d <= $end_day; $d++) {
        $timestamp = strtotime("$y-$month_num-$d");
        $day_of_week = date('N', $timestamp); 
        if ($day_of_week <= 5) { $weekdays++; }
    }
    return $weekdays;
}

$max_weekdays = countWeekdays($month, $year, $period);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Computation UI</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 1500px; margin: auto; }
        h2 { color: #1a73e8; text-align: center; margin-bottom: 25px; }
        
        .controls { background: #e8f0fe; padding: 15px; border-radius: 8px; display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .custom-select { padding: 8px; border-radius: 4px; border: 1px solid #1a73e8; color: #1a73e8; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1a73e8; color: white; padding: 15px 10px; text-align: center; font-size: 14px; white-space: nowrap; }
        td { padding: 12px 10px; border-bottom: 1px solid #ddd; text-align: center; }
        
        .max-indicator { display: block; font-size: 10px; color: #ffeb3b; margin-top: 4px; font-weight: normal; }
        
        /* Styles for the boxes to match your screenshot */
        input[type="number"] { width: 80px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; text-align: center; }
        .deduct-box { background: #f9f9f9; border: none; font-weight: bold; color: #444; width: 85px; text-align: center; border-radius: 4px; padding: 6px; }
        
        .total-deduct-text { font-weight: bold; color: #d32f2f; }
        .net-salary-text { font-weight: bold; color: #2e7d32; font-size: 1.1em; }
        
        .btn { padding: 10px 20px; border-radius: 5px; cursor: pointer; border: none; font-weight: bold; text-decoration: none; }
        .btn-save { background: #34a853; color: white; }
        .btn-back { background: #5f6368; color: white; }
    </style>
</head>
<body>

<div class="card">
    <h2>Payroll Computation</h2>

    <form method="POST" action="save_to_db.php">
        <div class="controls">
            <select name="year_val" class="custom-select" onchange="this.form.action=''; this.form.submit();">
                <option value="2026" <?= $year == '2026' ? 'selected' : '' ?>>2026</option>
                <option value="2025" <?= $year == '2025' ? 'selected' : '' ?>>2025</option>
            </select>
            <select name="month_val" class="custom-select" onchange="this.form.action=''; this.form.submit();">
                <?php
                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                foreach ($months as $m) { echo "<option value='$m' ".($m == $month ? "selected" : "").">$m</option>"; }
                ?>
            </select>
            <select name="period_val" class="custom-select" onchange="this.form.action=''; this.form.submit();">
                <option value="1-15" <?= $period == '1-15' ? 'selected' : '' ?>>1st Half (1-15)</option>
                <option value="16-31" <?= $period == '16-31' ? 'selected' : '' ?>>2nd Half (16-End)</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="text-align: left;">Employee Name</th>
                    <th>Rate</th>
                    <th>Days Worked <span class="max-indicator">Limit: <?= $max_weekdays ?> Weekdays</span></th>
                    <th>SSS (4.5%)</th>
                    <th>PhilHealth (2.5%)</th>
                    <th>Pag-IBIG</th>
                    <th>Total Deduct</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): $id = $row['employeeid']; ?>
                <tr>
                    <td style="text-align: left;">
                        <strong><?= $row['firstname']." ".$row['lastname']; ?></strong>
                        <input type="hidden" name="emp_id[]" value="<?= $id; ?>">
                    </td>
                    <td>₱<?= number_format($row['rateperday'], 2); ?>
                        <input type="hidden" id="rate_<?= $id; ?>" value="<?= $row['rateperday']; ?>">
                    </td>
                    <td>
                        <input type="number" name="days[]" id="days_<?= $id; ?>" value="0" step="0.5" min="0" max="<?= $max_weekdays ?>" oninput="calculatePayroll(this, <?= $id ?>)">
                    </td>
                    
                    <td><input type="number" name="sss[]" id="sss_<?= $id; ?>" class="deduct-box" value="0.00" readonly></td>
                    <td><input type="number" name="philhealth[]" id="philhealth_<?= $id; ?>" class="deduct-box" value="0.00" readonly></td>
                    <td><input type="number" name="pagibig[]" id="pagibig_<?= $id; ?>" class="deduct-box" value="0.00" readonly></td>
                    
                    <td class="total-deduct-text">₱<span id="total_deduct_<?= $id; ?>">0.00</span></td>
                    
                    <td class="net-salary-text">₱<span id="net_display_<?= $id; ?>">0.00</span></td>
                    <input type="hidden" name="net[]" id="net_input_<?= $id; ?>" value="0">
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <a href="main.php" class="btn btn-back">Back to Main</a>
            <button type="submit" name="save_btn" class="btn btn-save">Process Payroll & Save</button>
        </div>
    </form>
</div>

<script>
function calculatePayroll(input, id) {
    const maxVal = parseFloat(input.getAttribute('max'));
    let days = parseFloat(input.value) || 0;

    // Boundary check
    if (days > maxVal) {
        alert("Attention: Only " + maxVal + " working days in this period.");
        input.value = maxVal;
        days = maxVal;
    }

    const rate = parseFloat(document.getElementById('rate_' + id).value) || 0;
    const period = "<?= $period ?>";
    const gross = rate * days;

    let sss = 0, philhealth = 0, pagibig = 0;

    // Apply government deductions only for the 1st half of the month
    if (period === "1-15" && gross > 0) {
        sss = gross * 0.045;
        philhealth = gross * 0.025;
        pagibig = 100.00; // Flat monthly rate
    }

    const totalDeductions = sss + philhealth + pagibig;
    const net = gross - totalDeductions;

    // Update the UI boxes
    document.getElementById('sss_' + id).value = sss.toFixed(2);
    document.getElementById('philhealth_' + id).value = philhealth.toFixed(2);
    document.getElementById('pagibig_' + id).value = pagibig.toFixed(2);
    
    // Update the calculated text displays
    document.getElementById('total_deduct_' + id).innerText = totalDeductions.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('net_display_' + id).innerText = net.toLocaleString('en-PH', {minimumFractionDigits: 2});
    
    // Set hidden field value for database submission
    document.getElementById('net_input_' + id).value = net.toFixed(2);
}
</script>
</body>
</html>