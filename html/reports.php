<?php
session_start();
require_once '../db_config.php';
require_once '../security.php';
require_login();

$products_res = $conn->query("SELECT * FROM products");
$products_data = [];
while($row = $products_res->fetch_assoc()) {
    $products_data[] = $row;
}

$sales_res = $conn->query("SELECT s.*, p.name as productName FROM sales s JOIN products p ON s.product_id = p.id");
$sales_data = [];
while($row = $sales_res->fetch_assoc()) {
    $row['total'] = $row['total_price'];
    $row['date'] = date('m/d/Y', strtotime($row['sale_date']));
    $sales_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | StockMaster Pro</title>
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

        .report-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            height: 100%;
        }

        .summary-box {
            border-radius: 12px;
            padding: 20px;
            color: white;
        }

        @media print {
            #sidebar, .btn-print { display: none; }
            #content { margin-left: 0; width: 100%; padding: 0; }
            .report-card { box-shadow: none; border: 1px solid #ddd; }
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
            <li class="nav-item"><a href="stock_status.php" class="nav-link"><i class="fas fa-warehouse"></i> Stock Check</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link active"><i class="fas fa-file-invoice-dollar"></i> Reports</a></li>
            <li class="mt-5"><hr class="dropdown-divider bg-secondary mx-3"></li>
            <li class="nav-item"><a href="login.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold animate__animated animate__fadeInDown">System Reports</h2>
            <button class="btn btn-dark btn-print px-4 py-2" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>

        <!-- Financial Summary -->
        <div class="row g-4 mb-5 animate__animated animate__fadeInUp">
            <div class="col-md-4">
                <div class="summary-box bg-primary shadow-sm">
                    <p class="mb-1 opacity-75">Total Sales Revenue</p>
                    <h2 id="totalRevenue" class="fw-bold mb-0">$0.00</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box bg-success shadow-sm">
                    <p class="mb-1 opacity-75">Estimated Total Profit</p>
                    <h2 id="totalProfit" class="fw-bold mb-0">$0.00</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-box bg-info shadow-sm">
                    <p class="mb-1 opacity-75">Current Inventory Value</p>
                    <h2 id="inventoryValue" class="fw-bold mb-0">$0.00</h2>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sales Report Table -->
            <div class="col-lg-8">
                <div class="report-card animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Detailed Sales Report</h5>
                        <span class="text-muted small">Updated: <span id="reportDate"></span></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <!-- Data from Sales Storage -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="col-lg-4">
                <div class="report-card animate__animated animate__fadeIn">
                    <h5 class="fw-bold mb-4">Inventory Breakdown</h5>
                    <div id="categoryStats">
                        <!-- Stats Injected by JS -->
                    </div>
                    <hr>
                    <div class="mt-4">
                        <h6 class="fw-bold text-muted small text-uppercase">Top Performing Category</h6>
                        <h4 id="topCategory" class="fw-bold text-primary">--</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load data from PHP/Database
        const products = <?php echo json_encode($products_data); ?>;
        const sales = <?php echo json_encode($sales_data); ?>;

        function generateReport() {
            const tableBody = document.getElementById('reportTableBody');
            const catStats = document.getElementById('categoryStats');
            let revenue = 0;
            let inventoryVal = 0;

            // Update Header Date
            document.getElementById('reportDate').innerText = new Date().toLocaleDateString();

            // 1. Calculate Revenue & Render Sales Table
            tableBody.innerHTML = '';
            sales.forEach(s => {
                revenue += parseFloat(s.total);
                // Attempt to find unit price from current products if not saved in sale
                const unitPrice = (s.total / s.qty).toFixed(2);
                
                tableBody.innerHTML += `
                    <tr>
                        <td>${s.date}</td>
                        <td class="fw-semibold">${s.productName}</td>
                        <td>${s.qty}</td>
                        <td>$${unitPrice}</td>
                        <td class="fw-bold">$${parseFloat(s.total).toFixed(2)}</td>
                    </tr>
                `;
            });

            // 2. Calculate Inventory Value
            products.forEach(p => {
                inventoryVal += (p.price * p.qty);
            });

            // 3. Category Breakdown
            const categories = {};
            products.forEach(p => {
                categories[p.category] = (categories[p.category] || 0) + parseInt(p.qty);
            });

            catStats.innerHTML = '';
            let maxQty = 0;
            let topCat = "None";

            for (const [cat, qty] of Object.entries(categories)) {
                if(qty > maxQty) { maxQty = qty; topCat = cat; }
                catStats.innerHTML += `
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>${cat}</span>
                            <span class="fw-bold">${qty} Units</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: ${Math.min(qty, 100)}%"></div>
                        </div>
                    </div>
                `;
            }

            // Update Summary UI
            document.getElementById('totalRevenue').innerText = `$${revenue.toLocaleString()}`;
            document.getElementById('inventoryValue').innerText = `$${inventoryVal.toLocaleString()}`;
            document.getElementById('totalProfit').innerText = `$${(revenue * 0.25).toLocaleString()}`; // Mock 25% margin
            document.getElementById('topCategory').innerText = topCat;

            if(sales.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No sales data found.</td></tr>';
            }
        }

        // Run report generation on load
        window.onload = generateReport;
    </script>
</body>
</html>