<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "test_db");

if (isset($_POST['save_btn'])) {
    $month = $_POST['month_val'];
    $year = $_POST['year_val'];
    $period = $_POST['period_val'];

    // Loop through arrays
    foreach ($_POST['emp_id'] as $key => $id) {
        $name = $_POST['emp_name'][$key];
        $rate = $_POST['rate_val'][$key];
        $days = $_POST['days'][$key];
        $sss = $_POST['sss'][$key];
        $net = $_POST['net'][$key];

        // Skip rows with 0 days if you don't want to save empty entries
        if ($days > 0) {
            $sql = "INSERT INTO tblsalary (employee_id, employee_name, month, year, period, days_worked, rate_per_day, sss_deduction, net_salary) 
                    VALUES ('$id', '$name', '$month', '$year', '$period', '$days', '$rate', '$sss', '$net')";
            $conn->query($sql);
        }
    }
    
    // Redirect to salary_report.php to see the final results
    header("Location: salary_report.php?month=$month&year=$year&period=$period");
    exit();
}
?>