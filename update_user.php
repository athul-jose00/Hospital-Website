<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: unauthorized.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpmydb";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
if (isset($_GET['id']) && isset($_GET['role'])) {
    $id = $_GET['id'];
    $role = $_GET['role'];
    
    $table = ($role === "doctor") ? "doctor_users" : (($role === "admin") ? "admin_users" : "patient_users");
    $sql = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Fetch departments from database
$departments_sql = "SELECT * FROM departments";
$departments_result = $conn->query($departments_sql);

// Handle form submission for updating user details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_user"])) {
    $id = $_POST['id'];
    $role = $_POST['role'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : NULL;

    $table = ($role === "doctor") ? "doctor_users" : (($role === "admin") ? "admin_users" : "patient_users");
    
    // Update query - Only include department for doctors
    if ($role === "doctor") {
        $sql = "UPDATE $table SET fname = ?, lname = ?, email = ?, dept_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $fname, $lname, $email, $dept_id, $id);
    } else {
        $sql = "UPDATE $table SET fname = ?, lname = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fname, $lname, $email, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating user.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.pexels.com/photos/668300/pexels-photo-668300.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #004080;
        }
        .container {
            text-align: center;
            margin-top: 50px;
        }
        .module {
            background: white;
            padding: 40px;
            width: 50%;
            margin: auto;
            border-radius: 10px;
        }
        input, select {
            width: 99%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="module">
            <h3>Update User</h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <input type="hidden" name="role" value="<?= $role ?>">
                
                <input type="text" name="fname" placeholder="First Name" value="<?= $user['fname'] ?>" required>
                <input type="text" name="lname" placeholder="Last Name" value="<?= $user['lname'] ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?= $user['email'] ?>" required>

                <?php if ($role === "doctor") { ?>
                    <select name="dept_id">
                        <option value="">Select Department</option>
                        <?php while ($dept = $departments_result->fetch_assoc()) { ?>
                            <option value="<?= $dept['id'] ?>" <?= ($user['dept_id'] == $dept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                <?php } ?>

                <button type="submit" name="update_user" class="btn">Update</button>
            </form>
        </div>
    </div>
    <?php $conn->close(); ?>
</body>
</html>
