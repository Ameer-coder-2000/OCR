<?php
session_start();
include "../db.php";

// Check if user is logged in
if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$username = $_SESSION['user'];

// Fetch user details by username
$result = $conn->query("SELECT * FROM users WHERE username='$username'");
if($result->num_rows > 0){
    $user = $result->fetch_assoc();
} else {
    // If user not found, logout
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Gilig System</title>
    <link rel="icon" type="image/png" sizes="any" href="../ocr.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --light: #f8fafc;
            --dark: #1e293b;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: #f1f5f9;
            min-height: 100vh;
        }
        
        /* Header Styles */
        header {
            background-color: white;
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            color: var(--primary);
            font-size: 24px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .user-info:hover {
            background-color: #f1f5f9;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .user-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            width: 220px;
            margin-top: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            border-bottom: 1px solid #f1f5f9;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background-color: #f8fafc;
        }
        
        .dropdown-item i {
            width: 20px;
            color: var(--secondary);
        }
        
        .dropdown-item.logout {
            color: var(--danger);
        }
        
        .dropdown-item.logout i {
            color: var(--danger);
        }
        
        /* Main Content */
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-section h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-section p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.8rem;
        }
        
        .tools-icon {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }
        
        .card-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .card-desc {
            color: var(--secondary);
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.7rem 1.5rem;
            background: var(--warning);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-tools {
            background: var(--warning);
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-top: 3rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .footer-links a {
            color: #e2e8f0;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .copyright {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                padding: 0 15px;
            }
            
            .logo-text {
                font-size: 1.2rem;
            }
            
            .user-name {
                display: none;
            }
            
            .welcome-section {
                padding: 1.5rem;
            }
            
            .welcome-section h1 {
                font-size: 1.5rem;
            }
            
            .welcome-section p {
                font-size: 1rem;
            }
            
            .dropdown-menu {
                width: 200px;
                right: -10px;
            }
            
            .footer-links {
                flex-direction: column;
                gap: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0 15px;
            }
            
            .card {
                padding: 1.2rem;
            }
            
            .card-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .card-title {
                font-size: 1.2rem;
            }
            
            .btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-database logo-icon"></i>
                <span class="logo-text">Gilig System</span>
            </div>
            
            <div class="user-menu">
                <div class="user-info" id="user-menu-toggle">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span class="user-name"><?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></span>
                    <i class="fas fa-chevron-down" style="font-size: 12px;"></i>
                </div>
                
                <div class="dropdown-menu" id="dropdown-menu">
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="verify_email.php" class="dropdown-item">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                    <a href="logout.php" class="dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="dashboard-container">
        <section class="welcome-section">
            <h2>Welcome back, <?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?>!</h2>
            <p>What would you like to do today?</p>
        </section>
        
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-icon tools-icon">
                    <i class="fas fa-file-image"></i>
                </div>
                <h3 class="card-title">OCR Tool</h3>
                <p class="card-desc">Extract text from images with our Optical Character Recognition tool</p>
                <a href="ocr.php" class="btn btn-tools">
                    <i class="fas fa-tools"></i> Use OCR Tool
                </a>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Support</a>
                <a href="#">Contact</a>
            </div>
            <p class="copyright">© 2023 Gilig System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Toggle dropdown menu
        document.getElementById('user-menu-toggle').addEventListener('click', function() {
            document.getElementById('dropdown-menu').classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('user-menu-toggle');
            const dropdownMenu = document.getElementById('dropdown-menu');
            
            if (!userMenu.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
