<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $conn = mysqli_connect("localhost", "root", "9932", "devcollab");
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  } else {
    session_start();
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $command = "insert into users (username,email, password) values ('$username', '$email', '$password')";
    $result = mysqli_query($conn, $command) or die(mysqli_error($conn));
    $query = "SELECT id, username FROM users WHERE username = '$username' AND password = '$password' LIMIT 1";
    $res = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($res)) {
      $_SESSION['id'] = $row['id'];
      $_SESSION['username'] = $row['username'];
    }
    header("Location: dashboard.php");
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Register - DevCollab</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
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
      0% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0% 50%;
      }
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
          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email address</label>
              <input type="email" class="form-control" id="email" name="email" required>
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
  <!-- Bootstrap JS (optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>