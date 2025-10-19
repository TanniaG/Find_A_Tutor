<?php
include 'database.php'; 

// Check if data is received
$json = file_get_contents("php://input");
if (empty($json)) {
    die("No data received");
}

$data = json_decode($json);
if ($data === null) {
    die("Invalid JSON data");
}

// Validate required fields
if (!isset($data->name, $data->email, $data->firebase_uid)) {
    die("Missing required fields");
}

$full_name = $data->name;
$email = $data->email;
$firebase_uid = $data->firebase_uid;

// Check if user already exists
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, firebase_uid) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $full_name, $email, $firebase_uid);
    if ($stmt->execute()) {
        echo "New user created";
    } else {
        echo "Error: " . $stmt->error;
    }
} else {
    echo "User already exists";
}
?>
