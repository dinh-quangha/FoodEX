<?php
// Kết nối CSDL
$connect = mysqli_connect("localhost", "root", "", "asm2");
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Thêm nhà hàng
if (isset($_POST['add'])) {
    $name = mysqli_real_escape_string($connect, $_POST['name']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    $imageName = "";
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
    }

    $sql = "INSERT INTO restaurant (Name, Address, Image, Description) 
            VALUES ('$name', '$address', '$imageName', '$description')";
    mysqli_query($connect, $sql);
    header("Location: manage_restaurants.php");
    exit;
}

// Xóa nhà hàng
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($connect, "DELETE FROM restaurant WHERE RestaurantID = $id");
    header("Location: manage_restaurants.php");
    exit;
}

// Sửa nhà hàng
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($connect, $_POST['name']);
    $address = mysqli_real_escape_string($connect, $_POST['address']);
    $description = mysqli_real_escape_string($connect, $_POST['description']);

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
        $updateImg = ", Image='$imageName'";
    } else {
        $updateImg = "";
    }

    $sql = "UPDATE restaurant SET Name='$name', Address='$address', Description='$description' $updateImg 
            WHERE RestaurantID = $id";
    mysqli_query($connect, $sql);
    header("Location: manage_restaurants.php");
    exit;
}

// Lấy danh sách nhà hàng
$result = mysqli_query($connect, "SELECT * FROM restaurant");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý Nhà hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
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
      .stat-sidebar { width: 100%; min-height: unset; }
      .stat-content { padding: 20px 5vw; }
    }
    .orders-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .orders-table th, .orders-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
    .orders-table th { background: #00b14f; color: #fff; }
    .orders-table tr:hover { background: #f6fff8; }
    .update-form select { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }
    .update-form button { background: #00b14f; color: #fff; border: none; padding: 7px 16px; border-radius: 5px; cursor: pointer; font-size: 15px; margin-left: 6px; }
    .update-form button:hover { background: #008f3a; }
    .view-btn { background: #00b14f; color: #fff; border: none; padding: 7px 18px; border-radius: 5px; cursor: pointer; font-size: 15px; }
    .view-btn:hover { background: #008f3a; }
  </style>
</head>
<body>
<div class="stat-main">
    <!-- Sidebar -->
    <aside class="stat-sidebar">
        <img src="../logo-grabfood2.svg" alt="FoodX Delivery" style="display:block;margin:0 auto 10px auto;width:54px;">
        <div class="brand">FoodX Delivery</div>
        <ul class="stat-menu">
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_restaurants.php" class="active">Quản lý nhà hàng</a></li>
            <li><a href="manage_dishes.php">Quản lý món ăn</a></li>
            <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
            <li><a href="manage_users.php">Quản lý người dùng</a></li>
            <li><a href="statistic.php">Thống kê</a></li>
            <li><a href="../logout.php">Đăng xuất</a></li>
        </ul>
    </aside>

    <!-- Nội dung chính -->
    <div class="container">
        <h2 class="mb-4">Quản lý Nhà hàng</h2>

        <!-- Form thêm nhà hàng -->
        <div class="card mb-4">
            <div class="card-header">Thêm Nhà hàng</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Tên nhà hàng</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input type="text" name="address" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Hình ảnh</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <button type="submit" name="add" class="btn btn-success">Thêm mới</button>
                </form>
            </div>
        </div>

        <!-- Danh sách nhà hàng -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Địa chỉ</th>
                    <th>Hình ảnh</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['RestaurantID'] ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Address']) ?></td>
                    <td>
                        <?php if (!empty($row['Image'])): ?>
                            <img src="uploads/<?= $row['Image'] ?>" width="80">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td>
                        <!-- Sửa -->
                        <form method="POST" enctype="multipart/form-data" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $row['RestaurantID'] ?>">
                            <input type="text" name="name" value="<?= htmlspecialchars($row['Name']) ?>" required>
                            <input type="text" name="address" value="<?= htmlspecialchars($row['Address']) ?>" required>
                            <input type="text" name="description" value="<?= htmlspecialchars($row['Description']) ?>">
                            <input type="file" name="image">
                            <button type="submit" name="edit" class="btn btn-warning btn-sm">Sửa</button>
                        </form>
                        <!-- Xóa -->
                        <a href="?delete=<?= $row['RestaurantID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa nhà hàng này?')">Xóa</a>
                        <!-- Quản lý món ăn -->
                        <a href="manage_dishes.php?restaurant_id=<?= $row['RestaurantID'] ?>" class="btn btn-primary btn-sm">Quản lý món ăn</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
