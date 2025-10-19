<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = trim($_POST['identifier']);
    $new_password = trim($_POST['new_password']);

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password for the user identified by email or phone
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? OR phone = ?");
    $stmt->bind_param("sss", $hashed_password, $identifier, $identifier);

    if ($stmt->execute()) {
        echo "Password updated successfully for user: " . htmlspecialchars($identifier);
    } else {
        echo "Error updating password: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset User Password</title>
</head>
<body>
    <h2>Reset User Password</h2>
    <form method="POST" action="reset_password.php">
        <label for="identifier">Email or Phone:</label><br>
        <input type="text" id="identifier" name="identifier" required><br><br>

        <label for="new_password">New Password:</label><br>
        <input type="password" id="new_password" name="new_password" required><br><br>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
