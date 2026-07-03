<?php
session_start();
require_once '../db_config.php';
if (!isset($_SESSION['isLoggedIn'])) { header("Location: login.php"); exit(); }

// Handle Recording New Purchase
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_purchase'])) {
    $product_id = (int)$_POST['prodSelect'];
    $qty = (int)$_POST['purQty'];
    $price = (float)$_POST['purPrice'];
    $date = $_POST['purDate'];

    // 1. Record the purchase
    $stmt = $conn->prepare("INSERT INTO purchases (product_id, qty, price, purchase_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $product_id, $qty, $price, $date);
    $stmt->execute();

    // 2. Update the product master quantity
    $conn->query("UPDATE products SET qty = qty + $qty WHERE id = $product_id");

    header("Location: purchases.php");
    exit();
}

// Fetch Products for dropdown
$products_list = $conn->query("SELECT id, name, qty FROM products ORDER BY name ASC");
// Fetch Purchase History
$history = $conn->query("SELECT pu.*, p.name FROM purchases pu JOIN products p ON pu.product_id = p.id ORDER BY pu.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchases | StockMaster Pro</title>
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

        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            z-index: 1000;
        }

        .nav-link {
            color: #94a3b8;
            padding: 12px 25px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            border-left: 4px solid transparent;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link i { margin-right: 15px; width: 20px; }

        #content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            width: calc(100% - var(--sidebar-width));
        }

        .action-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn-success { background-color: #10b981; border: none; }
        .btn-success:hover { background-color: #059669; }

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
            <li class="nav-item"><a href="products.php" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a></li>
            <li class="nav-item"><a href="purchases.php" class="nav-link active"><i class="fas fa-truck-loading"></i> Purchases</a></li>
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
            <h2 class="fw-bold animate__animated animate__fadeInDown">Stock Purchases</h2>
            <button class="btn btn-success px-4 py-2" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                <i class="fas fa-plus me-2"></i> Record New Purchase
            </button>
        </div>

        <div class="action-card animate__animated animate__fadeIn">
            <h5 class="mb-4">Recent Purchase History</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>S.N.</th>
                            <th>Date</th>
                            <th>Product Name</th>
                            <th>Qty Added</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1; $delay = 0.1; while($row = $history->fetch_assoc()): ?>
                        <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                            <td><?php echo $sn++; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['purchase_date'])); ?></td>
                            <td class="fw-semibold"><?php echo $row['name']; ?></td>
                            <td><span class="text-success fw-bold">+ <?php echo $row['qty']; ?></span></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td>$<?php echo number_format($row['qty'] * $row['price'], 2); ?></td>
                        </tr>
                        <?php $delay += 0.05; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Purchase Modal -->
    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Record Stock Increase</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="purchaseForm" method="POST" action="">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Product</label>
                            <select id="prodSelect" name="prodSelect" class="form-select" required>
                                <option value="">-- Choose Product --</option>
                                <?php while($p = $products_list->fetch_assoc()): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?> (Current: <?php echo $p['qty']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quantity to Add</label>
                                <input type="number" name="purQty" id="purQty" class="form-control" required min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Purchase Price ($)</label>
                                <input type="number" name="purPrice" id="purPrice" class="form-control" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Purchase Date</label>
                            <input type="date" name="purDate" id="purDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="complete_purchase" class="btn btn-success">Complete Purchase</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>