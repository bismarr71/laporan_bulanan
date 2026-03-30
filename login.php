<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Harap isi username dan password!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Laporan Kerja</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  body { display: flex; justify-content: center; align-items: center; height: 100vh; background: #f8fafc; margin: 0; }
  .login-card { background: #fff; padding: 30px 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
  .login-card h2 { text-align: center; margin-top: 0; color: #0f172a; margin-bottom: 20px; }
  .form-group { margin-bottom: 15px; }
  .form-group label { display: block; font-weight: 600; color: #334155; margin-bottom: 5px; }
  .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
  .btn-login { width: 100%; padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; margin-top: 10px; font-size: 1rem; }
  .btn-login:hover { background: #2563eb; }
  .error { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem; text-align: center; }
</style>
</head>
<body>

<div class="login-card">
  <h2>🔒 Login Laporan</h2>
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="Masukkan username" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Masukkan password" required>
    </div>
    <button type="submit" class="btn-login">Masuk</button>
  </form>
</div>

</body>
</html>
