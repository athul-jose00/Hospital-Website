    <?php
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $role = $_POST["role"];
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        // Determine table based on role
        $table = "";
        if ($role === "admin") {
            $table = "admin_users";
        } elseif ($role === "doctor") {
            $table = "doctor_users";
        } elseif ($role === "patient") {
            $table = "patient_users";
        } else {
            echo "<script>alert('Invalid role selected!'); window.history.back();</script>";
            exit();
        }

        // Get user from database
        $query = "SELECT id, fname, lname, password FROM $table WHERE email = ?";
        if ($role === "doctor") {
            $query = "SELECT id, fname, lname, password, dept_id FROM doctor_users WHERE email = ?";
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check password (no hashing used)
            if ($password === $user["password"]) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["fname"] = $user["fname"];
                $_SESSION["lname"] = $user["lname"];
                $_SESSION["role"] = $role;

                if ($role === "doctor") {
                    $_SESSION["dept_id"] = $user["dept_id"];
                }

                // Redirect based on role
                if ($role === "admin") {
                    header("Location: admin_dashboard.php");
                } elseif ($role === "doctor") {
                    header("Location: doctor_dashboard.php");
                } else {
                    header("Location: patient_dashboard.php");
                }
                exit();
            } else {
                echo "<script>alert('Incorrect password!'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('No user found with this email!'); window.history.back();</script>";
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
        <title>Hospital Login</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: url('https://images.pexels.com/photos/668300/pexels-photo-668300.jpeg') no-repeat center center fixed;
                background-size: cover;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .login-container {
                background: rgba(255, 255, 255, 0.9);
                padding: 20px;
                border-radius: 10px;
                width: 350px;
                text-align: center;
            }
            select, input, button {
                width: 100%;
                padding: 10px;
                margin: 10px 0;
                border-radius: 5px;
                border: 1px solid #ccc;
            }
            button {
                background-color: #007BFF;
                color: white;
                cursor: pointer;
            }
            button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>

    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p><a href="signin.php">Don't have an account? Register Here</a></p>
            <a href="index.html" >Go Back Home</a>
        </form>
    </div>

    </body>
    </html>
