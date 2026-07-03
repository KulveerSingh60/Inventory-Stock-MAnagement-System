<?php
session_start();
require_once '../db_config.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StockMaster Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --primary-color: #4361ee;
            --bg-gradient: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-card {
            background: white;
            padding: 50px 40px;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }

        .form-control {
            padding: 14px 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-color);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #3851d4;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .contact-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }

        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .contact-btn:hover {
            background: #3851d4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            color: white;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }
        .circle-1 { width: 300px; height: 300px; top: -100px; right: -100px; }
        .circle-2 { width: 200px; height: 200px; bottom: -50px; left: -50px; }
    </style>
</head>
<body>

    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="login-card animate__animated animate__zoomIn">
        <div class="brand-logo">
            <i class="fas fa-boxes"></i>
        </div>
        <h3 class="text-center fw-bold mb-1">Welcome Back</h3>
        <p class="text-center text-muted mb-4">Inventory Management System</p>

        <form id="loginForm" method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Enter your username" required>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label class="form-label fw-semibold">Password</label>
                    <a href="#" class="text-decoration-none small text-primary" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger mb-3 animate__animated animate__shakeX" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="login" class="btn btn-primary btn-login w-100 mb-3">
                Sign In
            </button>

            <div class="contact-info">
                <div class="mb-2">
                    <i class="fas fa-envelope text-primary me-1"></i>
                    <span class="text-muted">Need login credentials?</span>
                </div>
                <button type="button" class="contact-btn" onclick="openOutlook()">
                    <i class="fas fa-paper-plane"></i> Contact Admin
                </button>
            </div>
        </form>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Enter your username</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" id="forgotUsername" class="form-control" placeholder="Username">
                    </div>
                    <div id="forgotAlert" class="alert alert-danger d-none" role="alert">
                        Username not found.
                    </div>
                    <div id="forgotSuccess" class="alert alert-success d-none" role="alert">
                        Your password is: <strong id="foundPassword"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="retrievePassword()">Retrieve Password</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openOutlook() {
            window.location.href = 'mailto:kingksd999@gmail.com?subject=Login Credentials Request&body=Hello Admin...';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
