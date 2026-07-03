<?php
session_start();
require_once '../db_config.php';
if (!isset($_SESSION['isLoggedIn'])) { header("Location: login.php"); exit(); }

$products_res = $conn->query("SELECT * FROM products");
$products_data = [];
while($row = $products_res->fetch_assoc()) {
    $products_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Status | StockMaster Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4361ee;
            --bg-light: #f8f9fc;
            --danger-soft: #fee2e2;
            --warning-soft: #fef3c7;
            --success-soft: #dcfce7;
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

        .status-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .stock-bar { height: 8px; border-radius: 4px; }
        
        .indicator-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .bg-low { background-color: #ef4444; }
        .bg-medium { background-color: #f59e0b; }
        .bg-high { background-color: #10b981; }

        @media print {
            #sidebar, .nav-tabs, .btn-print { display: none; }
            #content { margin-left: 0; width: 100%; padding: 0; }
            .status-card { box-shadow: none; border: 1px solid #eee; }
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
            <li class="nav-item"><a href="sales.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a></li>
            <li class="nav-item"><a href="stock_status.php" class="nav-link active"><i class="fas fa-warehouse"></i> Stock Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Reports</a></li>
            <li class="mt-5"><hr class="dropdown-divider bg-secondary mx-3"></li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold animate__animated animate__fadeInDown mb-1">Stock Status</h2>
                <p class="text-muted">Real-time inventory levels and health check.</p>
            </div>
            <button class="btn btn-outline-dark btn-print" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Audit
            </button>
        </div>

        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-4 border-0 animate__animated animate__fadeIn">
            <li class="nav-item">
                <a class="nav-link active rounded-pill px-4 me-2" href="#" onclick="filterStock('all', this)">All Items</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 me-2 border text-danger" href="#" onclick="filterStock('low', this)">Low Stock</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 border text-success" href="#" onclick="filterStock('healthy', this)">In Stock</a>
            </li>
        </ul>

        <div class="status-card animate__animated animate__fadeIn">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Product Details</th>
                            <th>Stock Health</th>
                            <th class="text-center">Current Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                        <!-- Data Injected by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Load products from PHP/Database
        const products = <?php echo json_encode($products_data); ?>;

        function renderStockTable(filterType = 'all') {
            const tableBody = document.getElementById('stockTableBody');
            tableBody.innerHTML = '';

            const filtered = products.filter(p => {
                if (filterType === 'low') return parseInt(p.qty) < 10;
                if (filterType === 'healthy') return parseInt(p.qty) >= 10;
                return true;
            });

            if (filtered.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">No products found matching this criteria.</td></tr>`;
                return;
            }

            filtered.forEach(p => {
                const qty = parseInt(p.qty);
                let statusBadge = '';
                let barClass = '';
                let barWidth = Math.min((qty / 50) * 100, 100); // Scale relative to 50 units

                if (qty <= 0) {
                    statusBadge = '<span class="badge bg-danger">Out of Stock</span>';
                    barClass = 'bg-danger';
                } else if (qty < 10) {
                    statusBadge = '<span class="badge bg-warning text-dark">Low Stock</span>';
                    barClass = 'bg-warning';
                } else {
                    statusBadge = '<span class="badge bg-success">Healthy</span>';
                    barClass = 'bg-success';
                }

                tableBody.innerHTML += `
                    <tr>
                        <td style="width: 35%;">
                            <div class="fw-bold text-dark">${p.name}</div>
                            <small class="text-muted">${p.category} | ID: #${p.id}</small>
                        </td>
                        <td style="width: 30%;">
                            <div class="progress stock-bar">
                                <div class="progress-bar ${barClass}" style="width: ${barWidth}%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fs-5 fw-bold">${qty}</span> <small class="text-muted">Units</small>
                        </td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
        }

        function filterStock(type, el) {
            // UI Toggle
            document.querySelectorAll('.nav-tabs .nav-link').forEach(link => link.classList.remove('active'));
            el.classList.add('active');
            
            renderStockTable(type);
        }

        // Initialize
        window.onload = () => renderStockTable();
    </script>
</body>
</html>