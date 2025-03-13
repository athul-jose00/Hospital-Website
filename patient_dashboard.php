<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpmydb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "patient") {
    header("Location: login.php");
    exit();
}

// Fetch Departments
$departments_sql = "SELECT * FROM departments";
$departments_result = $conn->query($departments_sql);

// Fetch Doctors (If "Get Doctors" is clicked)
$doctors = [];
$selected_dept = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["get_doctors"])) {
    $selected_dept = $_POST["department"];
    $doctors_sql = "SELECT id, fname, lname FROM doctor_users WHERE dept_id = ?";
    $stmt = $conn->prepare($doctors_sql);
    $stmt->bind_param("i", $selected_dept);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    $stmt->close();
}

// Handle Booking Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["book_appointment"])) {
    $patient_id = $_SESSION["user_id"];
    $doctor_id = $_POST["doctor_id"];
    $appointment_date = $_POST["appointment_date"];

    // Check if the doctor is already booked at this date
    $check_sql = "SELECT * FROM bookings WHERE doctor_id = ? AND appointment_date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $doctor_id, $appointment_date);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('This doctor is already booked on the selected date.');</script>";
    } else {
        $insert_sql = "INSERT INTO bookings (patient_id, doctor_id, appointment_date) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iis", $patient_id, $doctor_id, $appointment_date);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Appointment booked successfully!'); window.location.href='patient_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error booking appointment.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://images.pexels.com/photos/7551677/pexels-photo-7551677.jpeg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #004080;
        }

        .navbar {
            background: rgba(0, 64, 128, 0.9);
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
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

        .container {
            text-align: center;
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.9);
            margin: 50px auto;
            width: 80%;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .form-group {
            margin: 15px 0;
            text-align: left;
        }

        select, input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #004080;
            color: white;
            padding: 10px 20px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
            display: inline-block;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #0066cc;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div>🏥 <b>Patient Dashboard</b></div>
        <div>
            
            <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2 class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION["fname"]); ?>!</h2>
        <p>Your health is our priority. Explore our services below.</p>

        <div class="services">
            <h2>Our Services</h2>
            <p>✔ Online Consultations</p>
            <p>✔ Prescription Management</p>
            <p>✔ Health Records</p>
        </div>

    <div class="container">
        <h2>Book an Appointment</h2>
        
        <!-- Step 1: Select Department and Get Doctors -->
        <form method="post">
            <div class="form-group">
                <label for="department">Select Department:</label>
                <select name="department" required>
                    <option value="">Select Department</option>
                    <?php while ($dept = $departments_result->fetch_assoc()) { ?>
                        <option value="<?= htmlspecialchars($dept['id']) ?>" <?= ($selected_dept == $dept['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="get_doctors" class="btn">Get Doctors</button>
        </form>

        <!-- Step 2: Select Doctor & Book Appointment -->
        <?php if (!empty($doctors)) { ?>
            <form method="post">
                <div class="form-group">
                    <label for="doctor_id">Select Doctor:</label>
                    <select name="doctor_id" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doc) { ?>
                            <option value="<?= $doc['id'] ?>"><?= $doc['fname'] . " " . $doc['lname'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Select Date:</label>
                    <input type="datetime-local" name="appointment_date" required>
                </div>

                <button type="submit" name="book_appointment" class="btn">Book Now</button>
            </form>
        <?php } ?>

    </div>

</body>
</html>

<?php $conn->close(); ?>
