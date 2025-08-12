<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../index.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asm2";
$connect = mysqli_connect($servername, $username, $password, $dbname);

// Lấy UserID từ Username trong session
$user_query = mysqli_query($connect, "SELECT UserID FROM users WHERE Username='" . mysqli_real_escape_string($connect, $_SESSION['user']) . "'");
$user_row = mysqli_fetch_assoc($user_query);
$user_id = $user_row['UserID'];

// Xử lý thêm món
if (isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);
    $price = floatval($_POST['price']);
    $oldPrice = isset($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $restaurantID = isset($_POST['restaurant_id']) ? intval($_POST['restaurant_id']) : null;
    $isFeatured = isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0;

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = __DIR__ . '/uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $img_name;
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allow_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($img_ext, $allow_ext)) {
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image = 'admin/uploads/' . $img_name;
        }
    }
    if ($name && $price && $image && $restaurantID !== null) {
        $stmt = mysqli_prepare($connect, "INSERT INTO dish (UserID, Name, Description, Price, OldPrice, Image, RestaurantID, IsFeatured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issddsii", $user_id, $name, $desc, $price, $oldPrice, $image, $restaurantID, $isFeatured);
        mysqli_stmt_execute($stmt);
    }
}

// Xử lý xóa món
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($connect, "DELETE FROM dish WHERE DishID = $id");
}

// Xử lý sửa món
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $desc = trim($_POST['desc']);
    $price = floatval($_POST['price']);
    $oldPrice = isset($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $restaurantID = isset($_POST['restaurant_id']) ? intval($_POST['restaurant_id']) : null;
    $isFeatured = isset($_POST['is_featured']) ? intval($_POST['is_featured']) : 0;

    $image = $_POST['old_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = __DIR__ . '/uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $img_name;
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allow_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($img_ext, $allow_ext)) {
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image = 'admin/uploads/' . $img_name;
        }
    }
    if ($name && $price && $image && $restaurantID !== null) {
        $stmt = mysqli_prepare($connect, "UPDATE dish SET Name=?, Description=?, Price=?, OldPrice=?, Image=?, RestaurantID=?, IsFeatured=? WHERE DishID=?");
        mysqli_stmt_bind_param($stmt, "ssddsiii", $name, $desc, $price, $oldPrice, $image, $restaurantID, $isFeatured, $id);
        mysqli_stmt_execute($stmt);
    }
}

// Lấy danh sách món ăn kèm tên nhà hàng (cần join bảng restaurant)
$dishes = [];
$sql = "SELECT d.*, r.Name AS RestaurantName FROM dish d LEFT JOIN restaurant r ON d.RestaurantID = r.RestaurantID ORDER BY d.DishID DESC";
$result = mysqli_query($connect, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $dishes[] = $row;
}

// Lấy thông tin món cần sửa nếu có
$edit_dish = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $res = mysqli_query($connect, "SELECT * FROM dish WHERE DishID = $eid");
    $edit_dish = mysqli_fetch_assoc($res);
}

// Lấy danh sách nhà hàng để admin chọn trong form
$restaurants = [];
$res_rest = mysqli_query($connect, "SELECT RestaurantID, Name FROM restaurant ORDER BY Name ASC");
while ($row = mysqli_fetch_assoc($res_rest)) {
    $restaurants[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Quản lý món ăn - Admin</title>
  <link rel="stylesheet" href="../style.css" />
<style>
    body { background: #f7f7f7; }
    .stat-main { display: flex; }
    .stat-sidebar {
      width: 230px;
      background: linear-gradient(160deg, #4e54c8 0%, #00c3ff 50%, #00ff99 100%);
      min-height: 100vh;
      color: #fff;
      padding-top: 30px;
      border-top-right-radius: 24px;
      border-bottom-right-radius: 24px;
      box-shadow: 4px 0 24px 0 rgba(0,0,0,0.12);
      position: relative;
      z-index: 2;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-sidebar .brand {
      text-align: center;
      margin-bottom: 28px;
      font-weight: bold;
      font-size: 26px;
      color: #39ff14;
      letter-spacing: 2px;
      text-shadow: 0 0 8px #39ff14, 0 0 2px #fff;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-menu { list-style: none; padding: 0; margin: 0; }
    .stat-menu li { margin: 18px 0; }
    .stat-menu a {
      color: #fff;
      text-decoration: none;
      font-size: 19px;
      padding: 13px 32px;
      display: block;
      border-radius: 12px 0 0 12px;
      border-left: 5px solid transparent;
      transition: background 0.18s, border-color 0.18s, color 0.18s, box-shadow 0.18s;
      font-weight: 500;
      box-shadow: none;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-menu a.active, .stat-menu a:hover {
      background: rgba(255,255,255,0.18);
      border-left: 5px solid #ffe082;
      color: #ffe082;
      box-shadow: 0 2px 16px 0 rgba(0,255,153,0.12);
      font-weight: bold;
      text-shadow: 0 0 6px #fff, 0 0 2px #ffe082;
    }
    .stat-content { flex: 1; padding: 40px 50px; }
    @media (max-width: 900px) {
      .stat-main { flex-direction: column; }
      .stat-sidebar { width: 100%; min-height: unset; border-radius: 0 0 24px 24px; }
      .stat-content { padding: 20px 5vw; }
    }
    .admin-form {
      max-width: 520px;
      margin: 40px auto 32px auto;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 6px 32px 0 rgba(0, 177, 79, 0.10);
      padding: 38px 36px 28px 36px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .admin-form h3 {
      color: #00b14f;
      margin-bottom: 22px;
      font-size: 1.5rem;
      font-weight: bold;
      letter-spacing: 1px;
    }
    .admin-form input, .admin-form textarea {
      width: 100%;
      padding: 14px 16px;
      margin-bottom: 16px;
      border-radius: 8px;
      border: 1.5px solid #e0e0e0;
      font-size: 1.1rem;
      transition: border 0.2s, box-shadow 0.2s;
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f8f8f8;
    }
    .admin-form input:focus, .admin-form textarea:focus {
      border-color: #00b14f;
      outline: none;
      box-shadow: 0 0 0 2px #00b14f22;
      background: #fff;
    }
    .admin-form button {
      background: linear-gradient(90deg, #00b14f 0%, #00ff99 100%);
      color: #fff;
      border: none;
      padding: 14px 38px;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 8px;
      transition: background 0.18s, box-shadow 0.18s, color 0.18s;
      box-shadow: 0 2px 12px 0 #00b14f22;
    }
    .admin-form button:hover {
      background: linear-gradient(90deg, #00ff99 0%, #00b14f 100%);
      color: #ffe082;
    }
    .admin-form a {
      color: #00b14f;
      font-weight: 500;
      margin-left: 18px;
      text-decoration: underline;
      font-size: 1rem;
    }
    .dish-table {
      width: 98%;
      margin: 0 auto 40px auto;
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px 0 rgba(0,177,79,0.08);
      overflow: hidden;
    }
    .dish-table th {
      background: linear-gradient(90deg, #00b14f 0%, #00ff99 100%);
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
      padding: 16px 0;
      border-bottom: 2px solid #e0e0e0;
    }
    .dish-table td {
      padding: 14px 0;
      border-bottom: 1px solid #f0f0f0;
      text-align: center;
      font-size: 1.05rem;
      color: #222;
      background: #fff;
    }
    .dish-img {
      width: 64px;
      height: 48px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 8px #00b14f22;
    }
    .action-btn {
      background: #00b14f;
      color: #fff;
      border: none;
      padding: 8px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      margin: 0 2px;
      transition: background 0.18s, color 0.18s, box-shadow 0.18s;
      box-shadow: 0 2px 8px #00b14f22;
    }
    .action-btn.delete {
      background: #ff424e;
    }
    .action-btn:hover {
      background: #008f3a;
      color: #ffe082;
    }
    .action-btn.delete:hover {
      background: #ff1744;
      color: #fff;
    }
    @media (max-width: 900px) {
      .admin-form { padding: 18px 4vw; }
      .dish-table th, .dish-table td { font-size: 0.95rem; padding: 8px 0; }
      .dish-img { width: 44px; height: 32px; }
    }
  </style>
</head>
<body>
<div class="stat-main">
  <aside class="stat-sidebar">
    <img src="../logo-grabfood2.svg" alt="FoodX Delivery" style="display:block;margin:0 auto 10px auto;width:54px;" />
    <div class="brand">FoodX Delivery</div>
    <ul class="stat-menu">
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="manage_restaurants.php">Quản lý nhà hàng</a></li>
      <li><a href="manage_dishes.php" class="active">Quản lý món ăn</a></li>
      <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
      <li><a href="manage_users.php">Quản lý người dùng</a></li>
      <li><a href="statistic.php">Thống kê</a></li>
      <li><a href="../logout.php">Đăng xuất</a></li>
    </ul>
  </aside>
  <section class="stat-content">
    <div class="admin-form">
      <h3><?= $edit_dish ? 'Sửa món ăn' : 'Thêm món ăn mới'; ?></h3>
      <form method="post" enctype="multipart/form-data">
        <?php if ($edit_dish): ?>
          <input type="hidden" name="id" value="<?= $edit_dish['DishID']; ?>" />
        <?php endif; ?>

        <input
          type="text"
          name="name"
          required
          placeholder="Tên món"
          value="<?= htmlspecialchars($edit_dish['Name'] ?? '') ?>"
        />

        <textarea
          name="desc"
          placeholder="Mô tả món ăn"
          ><?= htmlspecialchars($edit_dish['Description'] ?? '') ?></textarea
        >

        <input
          type="number"
          name="price"
          required
          min="0"
          step="0.01"
          placeholder="Giá"
          value="<?= $edit_dish['Price'] ?? '' ?>"
        />

        <input
          type="number"
          name="old_price"
          min="0"
          step="0.01"
          placeholder="Giá cũ (nếu có)"
          value="<?= isset($edit_dish['OldPrice']) ? $edit_dish['OldPrice'] : '' ?>"
        />

        <select name="restaurant_id" required>
          <option value="">-- Chọn nhà hàng --</option>
          <?php foreach ($restaurants as $rest): ?>
            <option
              value="<?= $rest['RestaurantID'] ?>"
              <?= (isset($edit_dish['RestaurantID']) && $edit_dish['RestaurantID'] == $rest['RestaurantID']) ? 'selected' : '' ?>
            >
              <?= htmlspecialchars($rest['Name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label style="margin: 12px 0;">
          <input
            type="checkbox"
            name="is_featured"
            value="1"
            <?= (isset($edit_dish['IsFeatured']) && $edit_dish['IsFeatured']) ? 'checked' : '' ?>
          />
          Món nổi bật
        </label>

        <input type="hidden" name="old_image" value="<?= htmlspecialchars($edit_dish['Image'] ?? '') ?>" />
        <input type="file" name="image" accept="image/*" <?= $edit_dish ? '' : 'required' ?> />
        <?php if ($edit_dish && !empty($edit_dish['Image'])): ?>
          <div style="margin-bottom:10px;">
            <img
              src="../<?= htmlspecialchars($edit_dish['Image']) ?>"
              style="max-width:100px; max-height:60px; border-radius:6px;"
              alt="Ảnh món"
            />
          </div>
        <?php endif; ?>

        <button type="submit" name="<?= $edit_dish ? 'edit' : 'add' ?>">
          <?= $edit_dish ? 'Cập nhật' : 'Thêm mới' ?>
        </button>
        <?php if ($edit_dish): ?>
          <a href="manage_dishes.php" style="margin-left:18px;">Hủy</a>
        <?php endif; ?>
      </form>
    </div>

    <table class="dish-table">
      <tr>
        <th>ID</th>
        <th>Ảnh</th>
        <th>Tên món</th>
        <th>Mô tả</th>
        <th>Nhà hàng</th>
        <th>Giá</th>
        <th>Nổi bật</th>
        <th>Hành động</th>
      </tr>
      <?php foreach ($dishes as $dish): ?>
        <tr>
          <td><?= $dish['DishID'] ?></td>
          <td><img src="../<?= htmlspecialchars($dish['Image']) ?>" class="dish-img" alt="Ảnh món" /></td>
          <td><?= htmlspecialchars($dish['Name']) ?></td>
          <td><?= htmlspecialchars($dish['Description']) ?></td>
          <td><?= htmlspecialchars($dish['RestaurantName']) ?></td>
          <td>
            <?= number_format($dish['Price'], 0, ',', '.') ?>đ
            <?php if (!empty($dish['OldPrice']) && $dish['OldPrice'] > $dish['Price']): ?>
              <br />
              <span style="text-decoration: line-through; color: #888; font-size: 0.9rem;">
                <?= number_format($dish['OldPrice'], 0, ',', '.') ?>đ
              </span>
            <?php endif; ?>
          </td>
          <td><?= $dish['IsFeatured'] ? '✓' : '' ?></td>
          <td>
            <a href="manage_dishes.php?edit=<?= $dish['DishID'] ?>" class="action-btn">Sửa</a>
            <a
              href="manage_dishes.php?delete=<?= $dish['DishID'] ?>"
              class="action-btn delete"
              onclick="return confirm('Xóa món này?');"
              >Xóa</a
            >
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </section>
</div>
</body>
</html>
