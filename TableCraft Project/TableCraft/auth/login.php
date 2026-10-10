<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
  header('Location: ../dashboard.php');
  exit;
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $oldEmail = $email;

  if ($email === '' || $password === '') {
    $errors[] = 'Please enter both email and password.';
  } else {
    $stmt = mysqli_prepare($conn, 'SELECT id, full_name, email, password, avatar_color FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user_id']      = $user['id'];
      $_SESSION['user_name']    = $user['full_name'];
      $_SESSION['user_email']   = $user['email'];
      $_SESSION['avatar_color'] = $user['avatar_color'];
      header('Location: ../dashboard.php');
      exit;
    } else {
      $errors[] = 'Incorrect email or password. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login · TableCraft</title>
  <script>
    (function() {
      var t = localStorage.getItem('tc_theme') || 'bright';
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
    img {
      height: 55px;
      width: 55px;
    }

    h1 {
      color: #1F50FF;
    }

    label {
      font-size: 35px;
    }
  </style>
</head>

<body>

  <div class="auth-wrap">
    <div class="auth-visual d-none d-lg-flex">
      <a href="../index.php" class="tc-logo mb-5" style="color:#fff">
        <img src="../assets/media/logo.jpeg" alt="TableCraft Logo"> <label> TableCraft </label>
      </a>
      <h1 class="fw-800 mb-3" style="font-size:2.4rem;max-width:460px; color:#fff;">Welcome back to your tables.</h1>
      <p class="mb-4" style="color:rgba(255,255,255,.75);max-width:420px;">Login to continue building student records, shop orders, employee sheets, stock lists and custom tables — styled and exportable in one click.</p>

      <div class="auth-mock-table" style="max-width:380px;">
        <div class="d-flex justify-content-between mb-2">
          <span class="badge-category" style="background:rgba(255,255,255,.15);color:#fff;">Commercial Category</span>
          <i class="bi bi-three-dots text-white-50"></i>
        </div>
        <div class="row-line"></div>
        <div class="row-line short"></div>
        <div class="row-line"></div>
      </div>
    </div>

    <div class="auth-form-side">
      <div class="auth-card">
        <div class="text-center mb-4 d-lg-none">
          <a href="../index.php" class="tc-logo justify-content-center"><span class="tc-logo-mark">TC</span> TableCraft</a>
        </div>

        <h3 class="fw-800 mb-1">Login to TableCraft</h3>
        <p class="text-soft mb-4">Enter your credentials to access your dashboard.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($err) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="alert alert-primary d-flex align-items-start gap-2" style="background:var(--blue-soft);border:none;color:var(--accent);">
          <i class="bi bi-info-circle mt-1"></i>
          <div class="small">Demo login — <strong>demo@tablecraft.com</strong> / <strong>Demo@123</strong><br>(or just register your own free account below)</div>
        </div>

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <div class="input-icon-group">
              <i class="bi bi-envelope"></i>
              <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($oldEmail) ?>" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-icon-group">
              <i class="bi bi-lock"></i>
              <input type="password" name="password" class="form-control" placeholder="Your password" required>
              <button type="button" class="toggle-eye"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <div class="d-flex justify-content-end mb-4">
            <a href="#" class="small text-soft" tabindex="-1">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">Login <i class="bi bi-arrow-right ms-1"></i></button>
        </form>

        <div class="divider-text">new to TableCraft</div>
        <a href="register.php" class="btn btn-soft w-100 py-2">Create an account</a>
      </div>
    </div>
  </div>

  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/main.js"></script>
</body>

</html>