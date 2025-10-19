<?php
session_start();
require_once 'database.php'; // DB connection

// Redirect if user not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get user_id from session

// Get user base info
$user_sql = $conn->prepare("SELECT full_name, email, user_type FROM users WHERE id = ?");
$user_sql->bind_param("i", $user_id);
$user_sql->execute();
$user_result = $user_sql->get_result();

if ($user_result->num_rows === 0) {
    // User not found in database (shouldn't happen if session is valid)
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $user_result->fetch_assoc();

// Only get tutor info if user is actually a tutor
$tutor = null;
if ($user['user_type'] === 'tutor') {
    $tutor_sql = $conn->prepare("SELECT * FROM tutors WHERE user_id = ?");
    $tutor_sql->bind_param("i", $user_id);
    $tutor_sql->execute();
    $tutor_result = $tutor_sql->get_result();
    $tutor = $tutor_result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/profile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-dark text-white">

<nav class="navbar navbar-dark bg-black px-4">
  <span class="navbar-brand">Find a Tutor</span>
  <div class="text-white">
    Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
    <a href="logout.php" class="btn btn-sm btn-outline-light ms-3">Logout</a>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-1 bg-black d-flex flex-column align-items-center py-4">
      <i class="fas fa-user-circle text-white mb-3"></i>
      <i class="fas fa-book text-white mb-3"></i>
    </div>

    <div class="col-md-11 py-4 px-5">
      <h2 class="mb-3">Profile</h2>

      <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-md-4">
          <div class="card bg-secondary text-white p-3 text-center">
            <img src="<?php echo isset($tutor['profile_picture']) ? htmlspecialchars($tutor['profile_picture']) : 'Images/default_avatar.jpg'; ?>" 
                 class="rounded-circle img-fluid w-50 mx-auto mb-3" alt="Profile">
            <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
            <span class="badge bg-info text-dark"><?php echo htmlspecialchars(ucfirst($user['user_type'])); ?></span>
          </div>
        </div>

        <!-- Details -->
        <div class="col-md-8">
          <div class="card bg-secondary text-white p-4">
            <h5 class="mb-3"><?php echo $user['user_type'] === 'tutor' ? 'Tutor' : 'Student'; ?> Information</h5>
            <div class="row">
              <div class="col-sm-6">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <?php if ($user['user_type'] === 'tutor' && isset($tutor)): ?>
                  <p><strong>Phone:</strong> <?php echo htmlspecialchars($tutor['phone_number'] ?? 'Not added'); ?></p>
                  <p><strong>Subject:</strong> <?php echo htmlspecialchars($tutor['subject_specialization'] ?? 'Not added'); ?></p>
                  <p><strong>Level:</strong> <?php echo htmlspecialchars($tutor['teaching_level'] ?? 'Not added'); ?></p>
                <?php endif; ?>
              </div>
              <div class="col-sm-6">
                <?php if ($user['user_type'] === 'tutor' && isset($tutor)): ?>
                  <p><strong>Location:</strong> <?php echo htmlspecialchars($tutor['location'] ?? 'Not added'); ?></p>
                  <p><strong>Availability:</strong> <?php echo htmlspecialchars($tutor['availability'] ?? 'Unavailable'); ?></p>
                  <p><strong>Experience:</strong> <?php echo htmlspecialchars($tutor['experience'] ?? 'Not added'); ?></p>
                <?php endif; ?>
              </div>
            </div>
            <div class="mt-3">
              <a href="edit_profile.php" class="btn btn-outline-light">Edit Profile</a>
            </div>
          </div>
        </div>
      </div>

      <?php if ($user['user_type'] === 'tutor'): ?>
      <div class="card bg-secondary text-white p-3 mt-4">
        <h5>Social Media (Optional)</h5>
        <div class="d-flex gap-3 fs-4">
          <i class="fab fa-youtube"></i>
          <i class="fab fa-instagram"></i>
          <i class="fab fa-facebook"></i>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
