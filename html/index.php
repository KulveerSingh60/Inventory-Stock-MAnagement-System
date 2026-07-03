<?php
session_start();
require_once '../db_config.php';
if (!isset($_SESSION['isLoggedIn'])) {
    header("Location: login.php");
    exit();
}

// Fetch Stats
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_stock = $conn->query("SELECT SUM(qty) as total FROM products")->fetch_assoc()['total'] ?? 0;
$today_sales = $conn->query("SELECT SUM(total_price) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'] ?? 0;
$low_stock = $conn->query("SELECT COUNT(*) as count FROM products WHERE qty < 10")->fetch_assoc()['count'];

// Fetch Sales Trend (Last 7 Days)
$sales_trend_labels = [];
$sales_trend_data = [];
$temp_sales_map = [];
// Initialize the last 7 days with 0 to ensure the chart is full
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $temp_sales_map[$date] = 0;
    $sales_trend_labels[] = date('D', strtotime($date));
}
$trend_query = "SELECT DATE(sale_date) as day, SUM(total_price) as total FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day";
$trend_res = $conn->query($trend_query);
while ($row = $trend_res->fetch_assoc()) {
    $temp_sales_map[$row['day']] = (float) $row['total'];
}
$sales_trend_values = array_values($temp_sales_map);

// Fetch Recent Sales
$recent_sales = $conn->query("SELECT s.*, p.name FROM sales s JOIN products p ON s.product_id = p.id ORDER BY s.sale_date DESC LIMIT 5");
// Fetch Low Stock Alerts
$low_stock_items = $conn->query("SELECT * FROM products WHERE qty < 10 LIMIT 5");

// Fetch Category Distribution for Chart
$cat_labels = [];
$cat_counts = [];
$cat_dist = $conn->query("SELECT category, COUNT(*) as count FROM products GROUP BY category");
while($row = $cat_dist->fetch_assoc()) {
    $cat_labels[] = $row['category'] ?: 'Uncategorized';
    $cat_counts[] = (int)$row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StockMaster Pro</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- aos Animate On Scroll Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4361ee;
            --accent-color: #4895ef;
            --bg-light: #f8f9fc;
            --dark-text: #2b2d42;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #0f172a;
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        .nav-link {
            color: #94a3b8;
            padding: 12px 25px;
            transition: 0.3s;
            display: flex;
            margin: 4px 15px;
            border-radius: 10px;
            align-items: center;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
            background: var(--primary-color);
        }

        .nav-link i {
            margin-right: 15px;
            width: 20px;
        }

        /* Main Content */
        #content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
        }

        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.3s;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .badge-low-stock {
            background-color: #fee2e2;
            color: #dc2626;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            #sidebar {
                margin-left: -260px;
            }

            #content {
                margin-left: 0;
                width: 100%;
            }

            #sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>StockMaster</h4>
        </div>
        <ul class="nav flex-column mt-4">
            <li class="nav-item"><a href="index.php" class="nav-link active"><i class="fas fa-th-large"></i>
                    Dashboard</a></li>
            <li class="nav-item"><a href="products.php" class="nav-link"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="categories.php" class="nav-link"><i class="fas fa-tags"></i> Categories</a></li>
            <li class="nav-item"><a href="purchases.php" class="nav-link"><i class="fas fa-truck-loading"></i>
                    Purchases</a></li>
            <li class="nav-item"><a href="sales.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a>
            </li>
            <li class="nav-item"><a href="stock_status.php" class="nav-link"><i class="fas fa-warehouse"></i> Stock
                    Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-file-invoice-dollar"></i>
                    Reports</a></li>
            <li class="mt-5">
                <hr class="dropdown-divider bg-secondary">
            </li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i>
                    Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <!-- Top Navbar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Dashboard Overview</h2>
            <div class="user-profile d-flex align-items-center">
                <span class="me-3 d-none d-md-block">Welcome,
                    <strong><?php echo $_SESSION['username']; ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4361ee&color=fff"
                    class="rounded-circle" width="40" alt="Profile">
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 animate__animated animate__fadeInUp">
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-0">Total Products</p>
                            <h3 class="fw-bold mb-0"><?php echo number_format($total_products); ?></h3>
                        </div>
                        <div class="icon-box bg-primary text-white">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                    <p class="text-success mt-3 mb-0 small"><i class="fas fa-arrow-up"></i> 12% from last month</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100 shadow-sm border-start border-success border-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-0">Total Stock</p>
                            <h3 class="fw-bold mb-0"><?php echo number_format($total_stock); ?></h3>
                        </div>
                        <div class="icon-box bg-success text-white">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <p class="text-muted mt-3 mb-0 small">Units currently in warehouse</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100 shadow-sm">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-0">Today Sales</p>
                            <h3 class="fw-bold mb-0">$<?php echo number_format($today_sales, 2); ?></h3>
                        </div>
                        <div class="icon-box bg-warning text-white">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <p class="text-success mt-3 mb-0 small"><i class="fas fa-arrow-up"></i> 5% vs Yesterday</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card p-3 h-100 shadow-sm border-start border-danger border-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-0">Low Stock Items</p>
                            <h3 class="fw-bold mb-0"><?php echo $low_stock; ?></h3>
                        </div>
                        <div class="icon-box bg-danger text-white">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <p class="text-danger mt-3 mb-0 small"><i class="fas fa-bell"></i> Action required</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mt-5 g-4">
            <div class="col-lg-8">
                <div class="table-container shadow-sm h-100">
                    <h5 class="fw-bold mb-4">Sales Analytics (Weekly)</h5>
                    <canvas id="salesChart" height="250"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="table-container shadow-sm h-100">
                    <h5 class="fw-bold mb-4">Stock Distribution</h5>
                    <canvas id="stockChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="row mt-5 g-4">
            <div class="col-lg-7">
                <div class="table-container shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Low Stock Alerts</h5>
                        <a href="stock_status.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>In Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $delay = 0.1; while ($row = $low_stock_items->fetch_assoc()): ?>
                                    <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['category']; ?></td>
                                        <td><?php echo str_pad($row['qty'], 2, '0', STR_PAD_LEFT); ?></td>
                                        <td><span
                                                class="badge <?php echo $row['qty'] < 5 ? 'bg-danger' : 'badge-low-stock'; ?> px-3 py-2">
                                                <?php echo $row['qty'] < 5 ? 'Critical' : 'Low Stock'; ?></span></td>
                                    </tr>
                                <?php $delay += 0.05; endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="table-container shadow-sm">
                    <h5 class="fw-bold mb-4">Recent Sales</h5>
                    <div class="list-group list-group-flush">
                        <?php $sn = 1; while ($sale = $recent_sales->fetch_assoc()): ?>
                            <div class="list-group-item px-0 border-0 mb-3 d-flex align-items-center">
                                <div class="icon-box bg-light text-primary me-3">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold">Sale #<?php echo $sn++; ?></h6>
                                    <small class="text-muted"><?php echo date('M d, H:i', strtotime($sale['sale_date'])); ?>
                                        • <?php echo $sale['name']; ?></small>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="fw-bold text-success">$<?php echo number_format($sale['total_price'], 2); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Sales Chart
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($sales_trend_labels); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($sales_trend_values); ?>,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Stock Chart
        const ctxStock = document.getElementById('stockChart').getContext('2d');
        new Chart(ctxStock, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_counts); ?>,
                    backgroundColor: ['#4361ee', '#4cc9f0', '#f72585', '#b5179e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>

</html>