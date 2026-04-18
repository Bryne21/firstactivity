<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['save_btn'])) {
    // 1. Capture global values from the top of the form
    $month  = $conn->real_escape_string($_POST['month_val'] ?? '');
    $year   = $conn->real_escape_string($_POST['year_val'] ?? '');
    $period = $conn->real_escape_string($_POST['period_val'] ?? '');

    // 2. Loop through the employee arrays
    if (isset($_POST['emp_id']) && is_array($_POST['emp_id'])) {
        
        foreach ($_POST['emp_id'] as $key => $id) {
            
            // Clean the data for safety
            $id    = $conn->real_escape_string($id);
            // This is the critical part: catching the name and rate
            $name  = $conn->real_escape_string($_POST['emp_name'][$key] ?? 'Unknown');
            $rate  = $_POST['rate_val'][$key] ?? 0;
            $days  = $_POST['days'][$key] ?? 0;
            
            // Catch the 3 main deductions (Kaltas)
            $sss   = $_POST['sss'][$key] ?? 0;
            $ph    = $_POST['philhealth'][$key] ?? 0;
            $pi    = $_POST['pagibig'][$key] ?? 0;
            $net   = $_POST['net'][$key] ?? 0;

            // 3. Only save if the employee actually worked (days > 0)
            if ($days > 0) {
                $sql = "INSERT INTO tblsalary 
                        (employee_id, employee_name, month, year, period, days_worked, rate_per_day, sss_deduction, philhealth_deduction, pagibig_deduction, net_salary) 
                        VALUES 
                        ('$id', '$name', '$month', '$year', '$period', '$days', '$rate', '$sss', '$ph', '$pi', '$net')";
                
                if (!$conn->query($sql)) {
                    // If you see an error here, check if columns exist in tblsalary
                    die("Database Error: " . $conn->error);
                }
            }
        }
    }
    
    // 4. Redirect to the report page with the filters so it shows the data immediately
    header("Location: salary_report.php?month=$month&year=$year&period=$period");
    exit();
}
?>