<?php
session_start();
require_once '../db_config.php';
if (!isset($_SESSION['isLoggedIn'])) { header("Location: login.php"); exit(); }

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM categories WHERE id = $id");
    } elseif (isset($_POST['save_category'])) {
        $name = $conn->real_escape_string($_POST['catName']);
        $id = !empty($_POST['editId']) ? (int)$_POST['editId'] : 0;

        $check = $conn->query("SELECT id FROM categories WHERE name = '$name' AND id != $id");
        if ($check->num_rows > 0) {
            $error = "Category '$name' already exists.";
        } else {
            if ($id > 0) {
                $conn->query("UPDATE categories SET name='$name' WHERE id=$id");
            } else {
                $conn->query("INSERT INTO categories (name) VALUES ('$name')");
            }
            header("Location: categories.php");
            exit();
        }
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories | StockMaster Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --sidebar-width: 260px; --primary-color: #4361ee; --bg-light: #f8f9fc; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: #0f172a; color: #fff; z-index: 1000; }
        .nav-link { color: #94a3b8; padding: 12px 25px; transition: 0.3s; display: flex; margin: 4px 15px; border-radius: 10px; align-items: center; text-decoration: none; }
        .nav-link:hover, .nav-link.active { color: #fff; background: var(--primary-color); }
        .nav-link i { margin-right: 15px; width: 20px; }
        #content { margin-left: var(--sidebar-width); padding: 30px; width: calc(100% - var(--sidebar-width)); }
        .card { border: none; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body>
    <nav id="sidebar">
        <div class="p-4 text-center">
            <h4 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>StockMaster</h4>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li class="nav-item"><a href="products.php" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="categories.php" class="nav-link active"><i class="fas fa-tags"></i> Categories</a></li>
            <li class="nav-item"><a href="purchases.php" class="nav-link"><i class="fas fa-truck-loading"></i> Purchases</a></li>
            <li class="nav-item"><a href="sales.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li class="nav-item"><a href="stock_status.php" class="nav-link"><i class="fas fa-warehouse"></i> Stock Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Reports</a></li>
            <li class="mt-5"><hr class="dropdown-divider bg-secondary mx-3"></li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold animate__animated animate__fadeInDown">Category Management</h2>
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus me-2"></i> Add Category
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card p-4 animate__animated animate__fadeIn">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>S.N.</th>
                            <th>Category Name</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1; $delay = 0.1; while($c = $categories->fetch_assoc()): ?>
                        <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                            <td><?php echo $sn++; ?></td>
                            <td class="fw-semibold"><?php echo $c['name']; ?></td>
                            <td class="text-center">
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                    <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php $delay += 0.05; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="catModalTitle">Add Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="editId" id="editId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category Name</label>
                            <input type="text" name="catName" id="catName" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_category" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
        function editCategory(c) {
            document.getElementById('editId').value = c.id;
            document.getElementById('catName').value = c.name;
            document.getElementById('catModalTitle').innerText = "Edit Category";
            modal.show();
        }
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('editId').value = '';
            document.getElementById('catName').value = '';
            document.getElementById('catModalTitle').innerText = "Add Category";
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>