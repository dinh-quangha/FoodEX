<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asm2";
$connect = mysqli_connect($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $address = trim($_POST["address"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $pass = $_POST["password"] ?? '';
    $role = 2; // Luôn là User

    // Validate dữ liệu cơ bản
    if ($name === '' || $email === '' || $phone === '' || $address === '' || $username === '' || $pass === '') {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!'); window.history.back();</script>";
        exit();
    }

    // Kiểm tra email hoặc username đã tồn tại chưa
    $sql = "SELECT * FROM users WHERE Email=? OR Username=?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $email, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Email hoặc Username đã tồn tại!'); window.history.back();</script>";
        exit();
    }

    // Mã hóa mật khẩu
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Thêm user mới với thông tin đầy đủ, dùng prepared statement để tránh SQL injection
    $sql = "INSERT INTO users (RoleID, Name, Email, Phone, Address, Username, Password) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "issssss", $role, $name, $email, $phone, $address, $username, $hash);
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Đăng ký thành công! Vui lòng đăng nhập.'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Đăng ký thất bại!'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - FoodX Delivery</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    .register-container {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      padding: 40px;
      width: 100%;
      max-width: 500px;
      margin: 20px;
    }

    .register-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .register-header h1 {
      color: #00b14f;
      font-size: 28px;
      margin-bottom: 10px;
    }

    .register-header p {
      color: #666;
      font-size: 16px;
    }

    .register-form {
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

    .form-group input,
    .form-group select {
      padding: 12px 16px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #00b14f;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
    }

    .register-btn {
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

    .register-btn:hover {
      background: #008f3a;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 177, 79, 0.2);
    }

    .login-link {
      text-align: center;
      margin-top: 20px;
      color: #666;
    }

    .login-link a {
      color: #00b14f;
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>

<body>
  <div class="register-container">
    <div class="register-header">
      <h1>Create Account</h1>
      <p>Join FoodX Delivery and enjoy delicious food delivered to your door</p>
    </div>
    <form class="register-form" id="register-form" action="register.php" method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required placeholder="Enter your full name">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email">
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
      </div>
      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" required placeholder="Enter your address">
      </div>
      <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required disabled>
          <option value="2" selected>User</option>
        </select>
        <input type="hidden" name="role" value="2">
      </div>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required placeholder="Choose a username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Create a password">
      </div>
      <div class="form-group">
        <label for="confirm-password">Confirm Password</label>
        <input type="password" id="confirm-password" name="confirm-password" required
          placeholder="Confirm your password">
      </div>
      <button type="submit" class="register-btn">Create Account</button>
    </form>
    <div class="login-link">
      Already have an account? <a href="login.php">Login here</a>
    </div>
  </div>
</body>

</html>