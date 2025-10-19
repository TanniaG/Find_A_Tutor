<?php
// Start session and error reporting at the very top
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'database.php';

$error = '';

// Process login only if it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = trim($_POST['password']);

    // Validate identifier (email or phone)
    if (empty($identifier) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        try {
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $identifier_type = 'email';
                $identifier = strtolower($identifier);
                $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            } else {
                $identifier_type = 'phone';
                $stmt = $conn->prepare("SELECT id, password FROM users WHERE phone = ?");
            }

            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashedPassword);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
                    // Successful login - set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_identifier'] = $identifier;
                    $_SESSION['logged_in'] = true;
                    
                    $stmt->close();
                    $conn->close();

                    // Redirect using header() - preferred method
                    if (!headers_sent()) {
                        header("Location: profile.php");
                        exit();
                    } else {
                        // Fallback JavaScript redirect if headers were already sent
                        echo '<script>window.location.href="profile.php";</script>';
                        exit();
                    }
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> 
</head>
<body>
    <div class="container-fluid vh-100">
        <div class="d-flex vh-100">
            <div class="bag-image flex-shrink-0" style="width: 42%;"></div>
            <div class="flex-grow-1 d-flex align-items-center justify-content-center gradient-orange">
                <div class="form-box w-100 mx-3" style="max-width: 400px;">
                    <form action="login.php" method="POST">
                        <h3 class="mb-4 text-center">Login</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="identifier" class="form-label">Email or Phone</label>
                            <input type="text" class="form-control" id="identifier" name="identifier" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
