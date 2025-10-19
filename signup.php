<?php
session_start();
require_once 'database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars(trim($_POST["name"]));
    $email = strtolower(trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $user_type = $_POST["user_type"];
    
    // Validation
    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $error = "Invalid name format";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        $conn->begin_transaction();
        
        try {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, password_hash($password, PASSWORD_DEFAULT), $user_type);
            $stmt->execute();
            
            if ($user_type === 'tutor') {
                $user_id = $stmt->insert_id;
                
                // Insert tutor with prepared statement
                $tutor_stmt = $conn->prepare("
                    INSERT INTO tutors (user_id, email, full_name, profile_picture, subject_specialization)
                    VALUES (?, ?, ?, 'Images/default_avatar.jpg', 'Not specified')
                ");
                $tutor_stmt->bind_param("iss", $user_id, $email, $name);
                $tutor_stmt->execute();
                $tutor_stmt->close();
            }
            
            $stmt->close();
            $conn->commit();
            $_SESSION['signup_success'] = true;
            header("Location: login.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Registration error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Italianno&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="CSS/style.css" rel="stylesheet"> 

</head>

<body style="background-image: url('Images/tutor3.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; height: 100vh;">

 <div class="bg-cover d-flex justify-content-center align-items-center">
    <div class="form-box col-md-2 col-lg-4">

      <h2 class="text-center mb-4">Create an Account</h2>

      <form action="signup.php" method="POST">

        <div class="mb-3 input-icon-wrapper">
          <i class="fa-regular fa-circle-user"></i>
          <input type="text" name="name" class="form-control" placeholder = "Full Name" id ="name" required>
            
        </div>

        <div class="mb-3 input-icon-wrapper">

          <i class="fas fa-envelope"></i> 
          <input type="email" name="email" class="form-control" placeholder = "Email" id = "email" required>

        </div>

        <div class="mb-3 input-icon-wrapper">

          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" class="form-control pe-5" id ="password" placeholder = "Password" required>
          <i class="fa fa-eye position-absolute eye" id="togglePassword"> </i>

        </div>

        <div class="mb-3">

          <select name="user_type" class="form-select" id ="user_type" required>
            <option value="student">Student</option>
            <option value="tutor">Tutor</option>
          </select>

        </div>

        <button type="submit" name="register" class="btn btn-primary bg-weirdbrown w-100 fs-5">Sign Up</button>

      </form>

      <div class="text-center mt-3">
          <p>OR</p>

          <button type="button" id="googleSignIn" class="btn btn-danger w-100 fs-5 bg-ash">Sign up with Google</button>

          <p class="mt-3">Already have an account? <a href="login.php">Login</a></p>

        </div>

    </div>
  </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="module" src="js/firebase.js"></script>

<script>
  const toggle = document.getElementById('togglePassword');
  const password = document.getElementById('password');

  toggle.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('fa-eye-slash');
    this.classList.toggle('fa-eye');
  });
</script>

</body>

</html>