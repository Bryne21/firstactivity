<?php
session_start();
if (!isset($_SESSION["fldUsername"])) { header("Location: loginform.php"); exit; }

$conn = new mysqli("localhost", "root", "root", "test_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Current Date Context: April 2026
$month = $_POST['month_val'] ?? date('F');
$year = $_POST['year_val'] ?? date('Y');
$period = $_POST['period_val'] ?? '1-15';

$sql = "SELECT employeeid, firstname, lastname, rateperday FROM employeeid ORDER BY lastname ASC";
$result = $conn->query($sql);

// Function to calculate exact weekdays (Mon-Fri) for the period
function countWeekdays($m, $y, $p) {
    $month_num = date('n', strtotime($m));
    $start_day = ($p == '1-15') ? 1 : 16;
    $end_day = ($p == '1-15') ? 15 : cal_days_in_month(CAL_GREGORIAN, $month_num, $y);
    
    $weekdays = 0;
    for ($d = $start_day; $d <= $end_day; $d++) {
        $timestamp = strtotime("$y-$month_num-$d");
        $day_of_week = date('N', $timestamp); // 1 (Mon) through 7 (Sun)
        if ($day_of_week <= 5) {
            $weekdays++;
        }
    }
    return $weekdays;
}

$max_weekdays = countWeekdays($month, $year, $period);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll System - Weekday Validation</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 1000px; margin: auto; }
        h2 { color: #1a73e8; text-align: center; margin-bottom: 25px; }
        
        .controls { background: #e8f0fe; padding: 15px; border-radius: 8px; display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
        .custom-select { padding: 8px; border-radius: 4px; border: 1px solid #1a73e8; color: #1a73e8; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #1a73e8; color: white; padding: 12px; text-align: center; }
        td { padding: 10px; border-bottom: 1px solid #ddd; text-align: center; }
        
        .max-indicator { display: block; font-size: 11px; color: #ffeb3b; margin-top: 4px; }
        input[type="number"] { width: 80px; padding: 6px; border: 2px solid #ddd; border-radius: 4px; font-weight: bold; }
        input[type="number"]:focus { border-color: #1a73e8; outline: none; }
        
        .btn { padding: 10px 20px; border-radius: 5px; cursor: pointer; border: none; font-weight: bold; transition: 0.2s; }
        .btn-save { background: #34a853; color: white; }
        .btn-save:hover { background: #2d8e47; }
        .btn-back { background: #5f6368; color: white; text-decoration: none; }
    </style>
</head>
<body>

<div class="card">
    <h2>Payroll Computation</h2>

    <form method="POST" action="save_to_db.php" id="payrollForm">
        <div class="controls">
            <select name="year_val" class="custom-select" onchange="this.form.action=''; this.form.submit();">
                <option value="2026" <?= $year == '2026' ? 'selected' : '' ?>>2026</option>
                <option value="2025" <?= $year == '2025' ? 'selected' : '' ?>>2025</option>
            </select>
            <select name="month_val" class="custom-select" onchange="this.form.action=''; this.form.submit();">
                <?php
                $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                foreach ($months as $m) {
                    echo "<option value='$m' ".($m == $month ? "selected" : "").">$m</option>";
                }
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
                    <th>Employee Name</th>
                    <th>Rate</th>
                    <th>Days Worked
                        <span class="max-indicator">Limit: <?= $max_weekdays ?> Weekdays</span>
                    </th>
                    <th>SSS (4.5%)</th>
                    <th>Net Salary</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): $id = $row['employeeid']; ?>
                <tr class="employee-row">
                    <td style="text-align: left;">
                        <strong><?= $row['firstname']." ".$row['lastname']; ?></strong>
                        <input type="hidden" name="emp_id[]" value="<?= $id; ?>">
                        <input type="hidden" name="emp_name[]" value="<?= $row['firstname']." ".$row['lastname']; ?>">
                    </td>
                    <td>₱<?= number_format($row['rateperday'], 2); ?>
                        <input type="hidden" id="rate_<?= $id; ?>" value="<?= $row['rateperday']; ?>">
                        <input type="hidden" name="rate_val[]" value="<?= $row['rateperday']; ?>">
                    </td>
                    <td>
                        <input type="number" name="days[]" id="days_<?= $id; ?>" 
                               value="0" step="0.5" min="0" 
                               max="<?= $max_weekdays ?>" 
                               oninput="validateAndCompute(this, <?= $id ?>)">
                    </td>
                    <td><input type="number" name="sss[]" id="sss_<?= $id; ?>" value="0.00" readonly style="background:#f9f9f9; border:none;"></td>
                    <td style="font-weight:bold; color:#2e7d32;">₱<span id="net_display_<?= $id; ?>">0.00</span></td>
                    <input type="hidden" name="net[]" id="net_input_<?= $id; ?>" value="0">
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center;">
            <a href="main.php" class="btn btn-back">Back to Main</a>
            <button type="submit" name="save_btn" class="btn btn-save">Process Payroll & Save</button>
        </div>
    </form>
</div>

<script>
function validateAndCompute(input, id) {
    const maxVal = parseFloat(input.getAttribute('max'));
    let val = parseFloat(input.value) || 0;

    // Strict validation for Weekends
    if (val > maxVal) {
        alert("Invalid Entry! There are only " + maxVal + " working days (Mon-Fri) in this period.");
        input.value = maxVal;
        val = maxVal;
    }

    const rate = parseFloat(document.getElementById('rate_' + id).value) || 0;
    const period = "<?= $period ?>";
    
    const gross = rate * val;
    // SSS Deduction only on the 1st Half
    const sss = (period === "1-15") ? (gross * 0.045) : 0;
    const net = gross - sss;

    document.getElementById('sss_' + id).value = sss.toFixed(2);
    document.getElementById('net_display_' + id).innerText = net.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('net_input_' + id).value = net.toFixed(2);
}
</script>
</body>
</html>