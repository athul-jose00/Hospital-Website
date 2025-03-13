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

// Handle User Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_user"])) {
    $role = $_POST["role"];
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    if ($role === "doctor") {
        $dept_id = $_POST["dept_id"];
        $table = "doctor_users";
        $sql = "INSERT INTO $table (fname, lname, email, password, dept_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $fname, $lname, $email, $password, $dept_id);
    } elseif ($role === "admin") {
        $table = "admin_users";
        $sql = "INSERT INTO $table (fname, lname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $fname, $lname, $email, $password);
    } else {
        $table = "patient_users";
        $sql = "INSERT INTO $table (fname, lname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $fname, $lname, $email, $password);
    }

    if ($stmt->execute()) {
        echo "<script>alert('User Added Successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error adding user.');</script>";
    }
    $stmt->close();
}

// Handle user deletion
if (isset($_GET['delete_user']) && isset($_GET['role']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $role = $_GET['role'];
    
    $table = ($role === "doctor") ? "doctor_users" : (($role === "admin") ? "admin_users" : "patient_users");
    $sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting user.');</script>";
    }
    $stmt->close();
}

// Fetch users
$admin_users = $conn->query("SELECT * FROM admin_users ORDER BY id DESC");
$doctor_users = $conn->query("SELECT * FROM doctor_users ORDER BY id DESC");
$patient_users = $conn->query("SELECT * FROM patient_users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script>
        function confirmDelete(id, role) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `admin_dashboard.php?delete_user=1&id=${id}&role=${role}`;
            }
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.pexels.com/photos/668300/pexels-photo-668300.jpeg') no-repeat center center fixed;
            background-size: cover;
            padding-top: 60px;
            color: #004080;
        }
        .container { text-align: center; }
        .module {
            background: white;
            padding: 20px;
            margin: 20px auto;
            width: 70%;
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th { background-color: #004080; color: white; }
        .icon {
            cursor: pointer;
            margin-left: 10px;
        }
    
        .navbar {
    background: rgba(0, 64, 128, 0.9);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: white;
    position: fixed;  
    top: 0;  
    left: 0;
    width: 98%;  
     
}

.navbar a {
    color: white;
    text-decoration: none;
    margin: 0 15px;
    font-weight: bold;
}

.navbar a:hover {
    text-decoration: underline;
}

.form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }



    </style>
</head>
<body>
<script>
    function toggleForm() {
        var role = document.getElementById("role").value;
        var doctorFields = document.getElementById("doctorFields");
        
        if (role === "doctor") {
            doctorFields.style.display = "block";
        } else {
            doctorFields.style.display = "none";
        }
    }

   
    
    
</script>

<div class="navbar">
    <div>🏥 <b>Admin Dashboard</b></div>
    <div>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
<div class="module">
    <u><h3>Add New User</h3></u>
        <form method="post">
            <div class="form-group">
                <label for="fname">First Name</label>
                <input type="text" name="fname" id="fname" required>
            </div>
            <div class="form-group">
                <label for="lname">Last Name</label>
                <input type="text" name="lname" id="lname" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" onchange="toggleForm()">
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group" id="doctorFields" style="display: none;">
                <label for="dept_id">Department</label>
                <select name="dept_id" id="dept_id">
                    <option value="">Select Department</option>
                    <?php 
                        $departments_sql = "SELECT * FROM departments";
                        $departments_result = $conn->query($departments_sql);
                        while ($dept = $departments_result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($dept['id']) . '">' . htmlspecialchars($dept['name']) . '</option>';
                        }
                    ?>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn">Add User</button>
        </form>
    </div>
    <div class="module">
    <u><h3>Messages</h3></u>
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Received At</th>
            <th>Actions</th>
        </tr>
        <?php 
        $messages = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
        while ($row = $messages->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row["name"]) ?></td>
                <td><?= htmlspecialchars($row["email"]) ?></td>
                <td><?= htmlspecialchars($row["phone"]) ?></td>
                <td><?= nl2br(htmlspecialchars($row["message"])) ?></td>
                <td><?= $row["created_at"] ?></td>
                <td>
                    <span class="icon" onclick="confirmDeleteMessage(<?= $row['id'] ?>)">❌</span>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<script>
    function confirmDeleteMessage(id) {
        if (confirm('Are you sure you want to delete this message?')) {
            window.location.href = `admin_dashboard.php?delete_message=1&id=${id}`;
        }
    }
</script>

<?php 
// Handle message deletion
if (isset($_GET['delete_message']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM contacts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Message deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error deleting message.');</script>";
    }
    $stmt->close();
}
?>

    <div class="module">
        <h3><u>Admin Users</u></h3>
        <table>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $admin_users->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row["fname"] . " " . $row["lname"] ?></td>
                    <td><?= $row["email"] ?></td>
                    <td>
                        <span class="icon" onclick="window.location.href='update_user.php?id=<?= $row['id'] ?>&role=admin'">✏️</span>
                        <span class="icon" onclick="confirmDelete(<?= $row['id'] ?>, 'admin')">❌</span>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    
    <div class="module">
        <u><h3>Doctor Users</h3></u>
        <table>
            <tr><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr>
            <?php while ($row = $doctor_users->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row["fname"] . " " . $row["lname"] ?></td>
                    <td><?= $row["email"] ?></td>
                    <td><?= $row["dept_id"] ?></td>
                    <td>
                        <span class="icon" onclick="window.location.href='update_user.php?id=<?= $row['id'] ?>&role=doctor'">✏️</span>
                        <span class="icon" onclick="confirmDelete(<?= $row['id'] ?>, 'doctor')">❌</span>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    
    <div class="module">
        <u><h3>Patient Users</h3></u>
        <table>
            <tr><th>Name</th><th>Email</th><th>Actions</th></tr>
            <?php while ($row = $patient_users->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row["fname"] . " " . $row["lname"] ?></td>
                    <td><?= $row["email"] ?></td>
                    <td>
                        <span class="icon" onclick="window.location.href='update_user.php?id=<?= $row['id'] ?>&role=patient'">✏️</span>
                        <span class="icon" onclick="confirmDelete(<?= $row['id'] ?>, 'patient')">❌</span>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
<?php $conn->close(); ?>
</body>
</html>
