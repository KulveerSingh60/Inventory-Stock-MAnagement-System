<?php
session_start();
require_once '../db_config.php';
if (!isset($_SESSION['isLoggedIn'])) { header("Location: login.php"); exit(); }

// Handle Post Requests (Add/Edit/Delete)
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: products.php");
        exit();
    } elseif (isset($_POST['save_product'])) {
        $name = trim($_POST['pName']);
        $cat  = trim($_POST['pCategory']);
        $price = (float)$_POST['pPrice'];
        $qty   = (int)$_POST['pQty'];
        $id = !empty($_POST['editIndex']) ? (int)$_POST['editIndex'] : 0;

        // Server-side validation for unique name
        if ($id > 0) {
            $check_stmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND id != ?");
            $check_stmt->bind_param("si", $name, $id);
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
            $check_stmt->bind_param("s", $name);
        }
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();

        if ($check_res && $check_res->num_rows > 0) {
            $error = "Validation Error: A product with the name '$name' already exists.";
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, qty=? WHERE id=?");
                $stmt->bind_param("ssdii", $name, $cat, $price, $qty, $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, category, price, qty) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssdi", $name, $cat, $price, $qty);
            }
            $stmt->execute();
            header("Location: products.php");
            exit();
        }
    }
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $like = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
}

// Fetch categories for modal dropdown
$categories_list = $conn->query("SELECT name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | StockMaster Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4361ee;
            --bg-light: #f8f9fc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

        /* Sidebar same as Dashboard for consistency */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #0f172a;
            color: #fff;
            z-index: 1000;
        }

        .nav-link {
            color: #94a3b8;
            padding: 12px 25px;
            transition: 0.3s;
            display: flex;
            margin: 4px 15px;
            border-radius: 10px;
            align-items: center;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: var(--primary-color);
        }

        .nav-link i { margin-right: 15px; width: 20px; }

        #content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            width: calc(100% - var(--sidebar-width));
        }

        .product-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .btn-primary { background-color: var(--primary-color); border: none; }
        .btn-primary:hover { 
            background-color: #3851d4;
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4);
        }

        .search-box {
            max-width: 300px;
        }

        @media (max-width: 768px) {
            #sidebar { margin-left: -260px; }
            #content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="p-4 text-center">
            <h4 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>StockMaster</h4>
        </div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li class="nav-item"><a href="products.php" class="nav-link active"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a></li>
            <li class="nav-item"><a href="purchases.php" class="nav-link"><i class="fas fa-truck-loading"></i> Purchases</a></li>
            <li class="nav-item"><a href="sales.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li class="nav-item"><a href="stock_status.php" class="nav-link"><i class="fas fa-warehouse"></i> Stock Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Reports</a></li>
            <li class="mt-5"><hr class="dropdown-divider bg-secondary mx-3"></li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold animate__animated animate__fadeInDown">Manage Products</h2>
            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="fas fa-plus me-2"></i> Add Product
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="product-card animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Product List</h5>
                <div class="search-box">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>" onkeyup="if(event.keyCode === 13) window.location.href='?search='+this.value">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>S.N.</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Selling Price</th>
                            <th>Current Stock</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <?php $sn = 1; $delay = 0.1; while($p = $result->fetch_assoc()): ?>
                        <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                            <td><?php echo $sn++; ?></td>
                            <td class="fw-semibold"><?php echo $p['name']; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $p['category']; ?></span></td>
                            <td>$<?php echo number_format($p['price'], 2); ?></td>
                            <td class="<?php echo $p['qty'] < 10 ? 'text-danger fw-bold' : ''; ?>"><?php echo $p['qty']; ?> Units</td>
                            <td class="text-center">
                                <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
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

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Add New Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm" method="POST" action="">
                    <div class="modal-body p-4">
                        <input type="hidden" id="editIndex" name="editIndex">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Product Name</label>
                            <input type="text" id="pName" name="pName" class="form-control" required placeholder="e.g. iPhone 15 Pro">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select id="pCategory" name="pCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php while($cat = $categories_list->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['name']; ?>"><?php echo $cat['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Price ($)</label>
                                <input type="number" id="pPrice" name="pPrice" class="form-control" required min="0" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Initial Quantity</label>
                                <input type="number" id="pQty" name="pQty" class="form-control" required min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_product" class="btn btn-primary" id="saveBtn">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('productModal'));
        function editProduct(p) {
            document.getElementById('editIndex').value = p.id;
            document.getElementById('pName').value = p.name;
            document.getElementById('pCategory').value = p.category;
            document.getElementById('pPrice').value = p.price;
            document.getElementById('pQty').value = p.qty;
            document.getElementById('modalTitle').innerText = "Edit Product";
            modal.show();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>