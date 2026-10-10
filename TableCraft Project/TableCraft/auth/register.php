<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
  header('Location: ../dashboard.php');
  exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';
  $confirm   = $_POST['confirm_password'] ?? '';
  $old       = ['full_name' => $full_name, 'email' => $email];

  if ($full_name === '' || strlen($full_name) < 2) $errors[] = 'Please enter your full name.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
  if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long.';
  if ($password !== $confirm) $errors[] = 'Passwords do not match.';

  if (empty($errors)) {
    $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
      $errors[] = 'An account with this email already exists. Please login instead.';
    }
    mysqli_stmt_close($stmt);
  }

  if (empty($errors)) {
    $palette = ['#1F50FF', '#080E53', '#4B74FF', '#17B26A', '#F5A524'];
    $avatarColor = $palette[array_rand($palette)];
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = mysqli_prepare($conn, 'INSERT INTO users (full_name, email, password, avatar_color) VALUES (?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'ssss', $full_name, $email, $hash, $avatarColor);

    if (mysqli_stmt_execute($stmt)) {
      $_SESSION['user_id']      = mysqli_insert_id($conn);
      $_SESSION['user_name']    = $full_name;
      $_SESSION['user_email']   = $email;
      $_SESSION['avatar_color'] = $avatarColor;
      header('Location: ../dashboard.php');
      exit;
    } else {
      $errors[] = 'Something went wrong while creating your account. Please try again.';
    }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register · TableCraft</title>
  <script>
    (function() {
      var t = localStorage.getItem('tc_theme') || 'bright';
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>

  <div class="auth-wrap">
    <div class="auth-visual d-none d-lg-flex">
      <a href="../index.php" class="tc-logo mb-5" style="color:#fff">
        <img src="../assets/media/logo.jpeg" alt="TableCraft Logo"> <label> TableCraft </label>
      </a>
      <h1 class="fw-800 mb-3" style="font-size:2.4rem;max-width:460px; color:#fff;">Type your data. Get a beautiful table instantly.</h1>
      <p class="mb-4" style="color:rgba(255,255,255,.75);max-width:420px;">Join TableCraft to build, style, and export structured tables for students, shops, employees, libraries, stock and more — no spreadsheet skills required.</p>

      <div class="auth-mock-table" style="max-width:380px;">
        <div class="d-flex justify-content-between mb-2">
          <span class="badge-category" style="background:rgba(255,255,255,.15);color:#fff;">Student Record</span>
          <i class="bi bi-three-dots text-white-50"></i>
        </div>
        <div class="row-line"></div>
        <div class="row-line short"></div>
        <div class="row-line"></div>
        <div class="row-line short"></div>
      </div>
    </div>

    <div class="auth-form-side">
      <div class="auth-card">
        <div class="text-center mb-4 d-lg-none">
          <a href="../index.php" class="tc-logo justify-content-center"><span class="tc-logo-mark">TC</span> TableCraft</a>
        </div>

        <h3 class="fw-800 mb-1">Create your account</h3>
        <p class="text-soft mb-4">Start turning raw data into structured tables in seconds.</p>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger" role="alert">
            <?php foreach ($errors as $err): ?><div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($err) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <div class="input-icon-group">
              <i class="bi bi-person"></i>
              <input type="text" name="full_name" class="form-control" placeholder="Thumar Paras" value="<?= htmlspecialchars($old['full_name']) ?>" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <div class="input-icon-group">
              <i class="bi bi-envelope"></i>
              <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($old['email']) ?>" required>
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Password</label>
            <div class="input-icon-group">
              <i class="bi bi-lock"></i>
              <input type="password" id="reg_password" name="password" class="form-control" placeholder="At least 6 characters" required>
              <button type="button" class="toggle-eye"><i class="bi bi-eye"></i></button>
            </div>
          </div>
          <div class="mb-3">
            <div style="height:4px;border-radius:4px;background:var(--border);overflow:hidden;">
              <div id="pw_strength_bar" style="height:100%;width:0;transition:width .2s ease;"></div>
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <div class="input-icon-group">
              <i class="bi bi-lock-fill"></i>
              <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
              <button type="button" class="toggle-eye"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">Create account <i class="bi bi-arrow-right ms-1"></i></button>
        </form>

        <div class="divider-text">already registered</div>
        <a href="login.php" class="btn btn-soft w-100 py-2">Login instead</a>
      </div>
    </div>
  </div>

  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/main.js"></script>
</body>

</html>