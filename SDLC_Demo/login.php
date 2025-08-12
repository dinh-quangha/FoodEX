<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asm2";
$connect = mysqli_connect($servername, $username, $password, $dbname);

// Tạo tài khoản admin mặc định nếu chưa tồn tại (chạy 1 lần)
$default_admin_email = 'admin2@fruitshop.com'; // pass: admin123
$check_admin = mysqli_query($connect, "SELECT * FROM users WHERE Email='$default_admin_email'");
if ($check_admin && mysqli_num_rows($check_admin) == 0) {
    $userid = 'ADMIN0003';
    $roleid = 1; // Ví dụ: 1 = admin, 2 = user
    $name = 'Admin Pro';
    $username = 'admin2';
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $phone = '0999999999';
    $address = 'Hanoi, Vietnam';

    mysqli_query($connect, "INSERT INTO users (UserID, RoleID, Name, Email, Phone, Address, Username, Password) 
        VALUES ('$userid', $roleid, '$name', '$default_admin_email', '$phone', '$address', '$username', '$password_hash')");
    
    echo '<div style="position:fixed;top:0;left:0;z-index:9999;background:#d4edda;color:#a6785c;padding:10px;">
            Tài khoản admin mặc định đã được tạo: '.$default_admin_email.' / admin123
          </div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $user = $_POST["login-username"] ?? '';
    $pass = $_POST["login-password"] ?? '';

    // Cho phép đăng nhập bằng username hoặc email
    $sql = "SELECT * FROM users WHERE Username='$user' OR Email='$user'";
    $result = mysqli_query($connect, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($pass, $row['Password'])) {
            $_SESSION['user'] = $row['Username'];
            $_SESSION['role'] = $row['RoleID'];
            if ($row['RoleID'] == 1) {
                echo "<script>alert('Đăng nhập thành công!'); window.location.href = 'admin/index.php';</script>";
            } else {
                echo "<script>alert('Đăng nhập thành công!'); window.location.href = 'index.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Sai mật khẩu!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Không tìm thấy tài khoản!'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - FoodX Delivery</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: linear-gradient(135deg, #d16ba5, #c777b9, #ba83ca, #aa8fd8, #9a9ae1); /* ✅ Đã thay đổi background */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    .login-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      padding: 40px;
      width: 100%;
      max-width: 400px;
      margin: 20px;
    }

    .login-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .login-header h1 {
      color: #00b14f;
      font-size: 28px;
      margin-bottom: 10px;
    }

    .login-header p {
      color: #666;
      font-size: 16px;
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }

    .form-group input {
      padding: 12px 16px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .form-group input:focus {
      border-color: #00b14f;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .login-btn {
      background: #00b14f;
      color: #fff;
      border: none;
      padding: 14px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .login-btn:hover {
      background: #008f3a;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 177, 79, 0.2);
    }

    .register-link {
      text-align: center;
      margin-top: 20px;
      color: #666;
    }

    .register-link a {
      color: #00b14f;
      text-decoration: none;
      font-weight: 600;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    .back-btn {
      position: absolute;
      top: 20px;
      left: 20px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .back-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }
  </style>
</head>

<body>
  <button class="back-btn" onclick="window.location.href='index.php'">← Back to Home</button>

  <div class="login-container">
    <div class="login-header">
      <h1>Welcome Back</h1>
      <p>Login to your FoodX Delivery account</p>
    </div>

    <form class="login-form" id="login-form" action="login.php" method="POST">
      <div class="form-group">
        <label for="username">Username or Email</label>
        <input type="text" id="username" name="login-username" required placeholder="Enter your username or email">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="login-password" required placeholder="Enter your password">
      </div>
      <button type="submit" class="login-btn">Login</button>
    </form>

    <div class="register-link">
      Don't have an account? <a href="register.php">Register here</a>
    </div>
  </div>
</body>

</html>
