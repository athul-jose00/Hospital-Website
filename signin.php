<?php
// Start session
session_start();

// Database connection
$host = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "phpmydb"; 

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["lname"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM patient_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Email is already registered. Please use another email or login.'); window.history.back();</script>";
    } else {
        
        

        
        $stmt = $conn->prepare("INSERT INTO patient_users (fname, lname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fname, $lname, $email, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Signup successful! You can now log in.'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Error signing up. Please try again.'); window.history.back();</script>";
        }
    }

    $stmt->close();
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup</title>
    <style>
        
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.pexels.com/photos/668300/pexels-photo-668300.jpeg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h2 {
            color: #004080; 
        }

        input {
            width: 90%;
            padding: 2%;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            margin: 7px auto;
        }

        button:hover {
            background-color: #0066cc;
        }

        .login-link {
            margin-top: 10px;
            display: block;
        }   
    </style>
</head>
<body>
    

    <div class="signup-container">
        <h2>Patient Signup</h2>
        <form action="" method="POST">
            <input type="text" name="fname" placeholder="First Name" required>
            <input type="text" name="lname" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <a class="login-link" href="login.php" style="margin-bottom: 1rem;">Already have an account? Login here</a>
        <a href="index.html" ">Go Back Home</a>
    </div>

</body>
</html>
