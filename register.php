<?php
session_start();

$conn = mysqli_connect("localhost", "root", "9932", "devcollab");
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $email = trim($_POST['email'] ?? '');

  // Basic validation
  if (!$username || !$password || !$email) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address.";
  } else {
    // Check for duplicate username or email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $error = "Username or email already exists.";
    } else {
      // Hash the password
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

      // Insert user
      $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $username, $email, $hashedPassword);
      if ($stmt->execute()) {
        // Get user id
        $user_id = $stmt->insert_id;
        $_SESSION['id'] = $user_id;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php");
        exit;
      } else {
        $error = "Registration failed. Please try again.";
      }
    }
    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - DevCollab</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: gradientBG 8s ease-in-out infinite;
    background-size: 200% 200%;
  }
  @keyframes gradientBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  .card {
    border-radius: 1rem;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    background: rgba(255, 255, 255, 0.95);
  }
  .form-control:focus {
    border-color: #2a5298;
    box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, .25);
  }
  .btn-primary {
    background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
    border: none;
  }
  .btn-primary:hover {
    background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
  }
  .logo {
    font-size: 2rem;
    font-weight: bold;
    color: #2a5298;
    letter-spacing: 2px;
    margin-bottom: 1rem;
  }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item">
      <a class="nav-link active" aria-current="page" href="#">Home</a>
      </li>
      <li class="nav-item">
      <a class="nav-link" href="#">Features</a>
      </li>
      <li class="nav-item">
      <a class="nav-link" href="#">Pricing</a>
      </li>
      <li class="nav-item">
      <a class="nav-link disabled" aria-disabled="true">Disabled</a>
      </li>
    </ul>
    </div>
  </div>
  </nav>
  <div class="container">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-6 col-lg-5">
    <div class="card p-4">
      <div class="text-center logo mb-3">DevCollab</div>
      <h2 class="text-center mb-4">Register</h2>
      <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" autocomplete="off">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Register</button>
      </div>
      </form>
      <div class="text-center mt-3">
      <small>Already have an account? <a href="login.php" class="text-primary">Login</a></small>
      </div>
    </div>
    </div>
  </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>