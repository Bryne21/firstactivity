<?php
session_start();
include("testconnection.php"); 

$error = "";

if (isset($_POST["login"])) {
    $username = trim($_POST["fldUsername"]);
    $password = trim($_POST["fldPassword"]);

    $stmt = $conn->prepare("SELECT * FROM tblusers WHERE fldUsername = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $error = "User not found!";
    } else {
        // NOTE: In production, use password_verify() with hashed passwords!
        if ($password === $user['fldPassword']) {
            $_SESSION['fldName'] = $user['fldName'];
            $_SESSION['fldUsername'] = $user['fldUsername'];
            $_SESSION['fldEmail'] = $user['fldEmail'];
            
            header("Location: main.php");
            exit;
        } else {
            $error = "Password Incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #e9ecef; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .login-container { 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 350px; 
        }

        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .input-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; font-size: 0.9rem; }
        
        input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box; 
            transition: border 0.3s;
        }
        input:focus { border-color: #007bff; outline: none; }

        /* Shared button styles */
        .btn {
            width: 100%; 
            padding: 12px; 
            font-size: 1rem; 
            font-weight: bold; 
            border-radius: 5px; 
            cursor: pointer; 
            display: block;
            text-align: center;
            box-sizing: border-box;
            text-decoration: none;
            border: none;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .btn-login {
            background-color: #007bff; 
            color: white; 
        }
        .btn-login:hover { background-color: #0056b3; }

        .btn-signup {
            background-color: #28a745; 
            color: white;
        }
        .btn-signup:hover { background-color: #218838; }

        .error-box { 
            background-color: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 15px; 
            text-align: center; 
            font-size: 0.85rem; 
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Welcome Back</h2>

    <?php if ($error): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="input-group">
            <label>Username</label>
            <input type="text" name="fldUsername" required placeholder="Enter username">
        </div>
        
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="fldPassword" required placeholder="Enter password">
        </div>

        <button type="submit" name="login" class="btn btn-login">Login</button>
        <a href="signupform.php" class="btn btn-signup">Signup</a>
    </form>
</div>

</body>
</html>