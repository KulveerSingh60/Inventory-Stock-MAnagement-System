<?php
session_start();
require_once '../db_config.php';
require_once '../security.php';
require_login();

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_sale'])) {
    verify_csrf();
    $product_id = (int)$_POST['saleProduct'];
    $qty = (int)$_POST['saleQty'];

    // Check available stock
    $p_check = $conn->query("SELECT price, qty FROM products WHERE id = $product_id")->fetch_assoc();
    
    if ($p_check['qty'] < $qty) {
        $error = "Insufficient Stock! Available: " . $p_check['qty'];
    } else {
        $total_price = $p_check['price'] * $qty;
        
        // 1. Record Sale
        $stmt = $conn->prepare("INSERT INTO sales (product_id, qty, total_price) VALUES (?, ?, ?)");
        $stmt->bind_param("iid", $product_id, $qty, $total_price);
        $stmt->execute();

        // 2. Deduct Stock
        $stmt = $conn->prepare("UPDATE products SET qty = qty - ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $product_id);
        $stmt->execute();

        header("Location: sales.php");
        exit();
    }
}

// Fetch Products for dropdown
$products_list = $conn->query("SELECT * FROM products WHERE qty > 0 ORDER BY name ASC");
// Fetch Sales History
$sales_history = $conn->query("SELECT s.*, p.name FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales | StockMaster Pro</title>
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

        .sales-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .btn-primary { background-color: var(--primary-color); border: none; }
        
        .stock-indicator {
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
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
            <li class="nav-item"><a href="products.php" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a></li>
            <li class="nav-item"><a href="purchases.php" class="nav-link"><i class="fas fa-truck-loading"></i> Purchases</a></li>
            <li class="nav-item"><a href="sales.php" class="nav-link active"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li class="nav-item"><a href="stock_status.php" class="nav-link"><i class="fas fa-warehouse"></i> Stock Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Reports</a></li>
            <li class="mt-5"><hr class="dropdown-divider bg-secondary mx-3"></li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold animate__animated animate__fadeInDown">Sales Operations</h2>
            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#saleModal">
                <i class="fas fa-cart-plus me-2"></i> New Sale
            </button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="sales-card animate__animated animate__fadeIn">
            <h5 class="mb-4">Recent Sales Transactions</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>S.N.</th>
                            <th>Date</th>
                            <th>Product Name</th>
                            <th>Quantity Sold</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1; $delay = 0.1; while($sale = $sales_history->fetch_assoc()): ?>
                        <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                            <td><?php echo $sn++; ?></td>
                            <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                            <td><?php echo $sale['name']; ?></td>
                            <td><?php echo $sale['qty']; ?></td>
                            <td>$<?php echo number_format($sale['total_price'], 2); ?></td>
                            <td><span class="badge bg-success">Completed</span></td>
                        </tr>
                        <?php $delay += 0.05; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- New Sale Modal -->
    <div class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Record New Sale</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="saleForm" method="POST" action="">
                    <?php csrf_field(); ?>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Product</label>
                            <select id="saleProduct" name="saleProduct" class="form-select" required onchange="updateStockPreview()">
                                <option value="">Choose a product...</option>
                                <?php $products_list->data_seek(0); while($p = $products_list->fetch_assoc()): ?>
                                    <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-qty="<?php echo $p['qty']; ?>"><?php echo $p['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                            <small id="stockPreview" class="stock-indicator text-muted"></small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" name="saleQty" id="saleQty" class="form-control" required min="1" oninput="calculateTotal()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Total Amount ($)</label>
                                <input type="text" id="saleTotal" class="form-control bg-light" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="process_sale" class="btn btn-primary">Process Sale</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateStockPreview() {
            const select = document.getElementById('saleProduct');
            const option = select.options[select.selectedIndex];
            const preview = document.getElementById('stockPreview');
            if(select.value) {
                preview.innerHTML = `Available: <strong>${option.dataset.qty}</strong> | Price: $${option.dataset.price}`;
            } else preview.innerHTML = "";
        }
        function calculateTotal() {
            const select = document.getElementById('saleProduct');
            const option = select.options[select.selectedIndex];
            const qty = document.getElementById('saleQty').value;
            if(select.value && qty) document.getElementById('saleTotal').value = (option.dataset.price * qty).toFixed(2);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>