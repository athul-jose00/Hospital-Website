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

// Ensure user is logged in and is a doctor
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "doctor") {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION["user_id"];

// Handle completing an appointment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["complete_appointment"])) {
    $booking_id = $_POST["booking_id"];
    $update_sql = "UPDATE bookings SET status = 'completed' WHERE id = ? AND doctor_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $booking_id, $doctor_id);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment marked as completed.'); window.location.href='doctor_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating appointment status.');</script>";
    }
}

// Fetch active bookings for the logged-in doctor
$bookings_sql = "SELECT b.id, p.fname, p.lname, b.appointment_date 
                 FROM bookings b
                 JOIN patient_users p ON b.patient_id = p.id
                 WHERE b.doctor_id = ? AND b.status = 'pending'
                 ORDER BY b.appointment_date ASC";
$stmt = $conn->prepare($bookings_sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
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
            width: 60%;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        .welcome {
            font-size: 28px;
            font-weight: bold;
        }

        .table-container {
            margin-top: 30px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #004080;
            color: white;
        }

        .btn {
            background-color: #004080;
            color: white;
            padding: 8px 12px;
            border: none;
            font-size: 14px;
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
        <div>🏥 Doctor Dashboard</div>
        <div>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <p class="welcome">Welcome, Dr. <?php echo htmlspecialchars($_SESSION["fname"]); ?>!</p>
        <p>Here are your active appointments:</p>

        <div class="table-container">
            <?php if ($bookings_result->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>Patient Name</th>
                        <th>Appointment Date</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($booking = $bookings_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['fname'] . " " . $booking['lname']) ?></td>
                            <td><?= htmlspecialchars($booking['appointment_date']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit" name="complete_appointment" class="btn">Complete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No active appointments.</p>
            <?php } ?>
        </div>
    </div>

</body>
</html>

<?php $conn->close(); ?>
