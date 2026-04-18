<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "root", "test_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['save_btn'])) {
    // Capture global values
    $month  = $_POST['month_val'] ?? '';
    $year   = $_POST['year_val'] ?? '';
    $period = $_POST['period_val'] ?? '';

    // Check if emp_id exists to avoid looping over null
    if (isset($_POST['emp_id']) && is_array($_POST['emp_id'])) {
        
        foreach ($_POST['emp_id'] as $key => $id) {
            // Use the null coalescing operator (??) to prevent "Undefined Index" warnings
            $id    = $conn->real_escape_string($id);
            $name  = $conn->real_escape_string($_POST['emp_name'][$key] ?? 'Unknown');
            $rate  = $_POST['rate_val'][$key] ?? 0;
            $days  = $_POST['days'][$key] ?? 0;
            $sss   = $_POST['sss'][$key] ?? 0;
            $ph    = $_POST['philhealth'][$key] ?? 0; // Added PhilHealth
            $pi    = $_POST['pagibig'][$key] ?? 0;    // Added Pag-IBIG
            $net   = $_POST['net'][$key] ?? 0;

            // Only save if the employee actually worked days
            if ($days > 0) {
                // Ensure your tblsalary table has philhealth_deduction and pagibig_deduction columns
                $sql = "INSERT INTO tblsalary 
                        (employee_id, employee_name, month, year, period, days_worked, rate_per_day, sss_deduction, philhealth_deduction, pagibig_deduction, net_salary) 
                        VALUES 
                        ('$id', '$name', '$month', '$year', '$period', '$days', '$rate', '$sss', '$ph', '$pi', '$net')";
                
                if (!$conn->query($sql)) {
                    echo "Error: " . $conn->error;
                }
            }
        }
    }
    
    // Redirect after processing
    header("Location: salary_report.php?month=$month&year=$year&period=$period");
    exit();
}
?>