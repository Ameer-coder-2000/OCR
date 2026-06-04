<?php
include "../db.php";
session_start();

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

// Handle profile update
if(isset($_POST['update'])){
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $profile_pic = $user['profile_pic']; // existing pic

    // Handle new profile picture upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $profile_pic = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "uploads/" . $profile_pic);
    }

    $conn->query("UPDATE users SET fullname='$fullname', phone='$phone', profile_pic='$profile_pic' WHERE username='$username'");
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Profile | Gilig System</title>
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
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        /* Main Content */
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .profile-title {
            font-size: 2rem;
            color: var(--dark);
        }
        
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .profile-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .profile-card-body {
            padding: 2rem;
        }
        
        .profile-picture-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            margin-bottom: 1rem;
        }
        
        .profile-picture-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            margin: 0 auto 1rem;
            border: 4px solid var(--primary);
        }
        
        .file-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .file-upload-label {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-upload-label:hover {
            background: var(--primary-dark);
        }
        
        .file-input {
            display: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .input-with-icon input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        
        .input-with-icon input[readonly] {
            background-color: #f8fafc;
            color: var(--secondary);
            cursor: not-allowed;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.9rem 1.8rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-back {
            background: var(--secondary);
        }
        
        .btn-update {
            background: var(--success);
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
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
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                <div class="user-info">
                    <?php if(!empty($user['profile_pic'])): ?>
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span class="user-name"><?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></span>
                </div>
            </div>
        </div>
    </header>
    
    <main class="profile-container">
        <div class="profile-header">
            <h1 class="profile-title">Your Profile</h1>
        </div>
        
        <div class="profile-card">
            <div class="profile-card-header">
                <h2>Personal Information</h2>
                <p>Update your profile details</p>
            </div>
            
            <div class="profile-card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-picture-section">
                        <?php if(!empty($user['profile_pic'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="profile-picture">
                        <?php else: ?>
                            <div class="profile-picture-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="file-upload">
                            <label for="profile-pic-upload" class="file-upload-label">
                                <i class="fas fa-camera"></i> Change Photo
                            </label>
                            <input type="file" id="profile-pic-upload" name="profile_pic" class="file-input" accept="image/*">
                            <span id="file-name" style="font-size: 0.9rem; color: var(--secondary);"></span>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user-tag"></i>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fullname">Full Name</label>
                            <div class="input-with-icon">
                                <i class="fas fa-signature"></i>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" placeholder="Enter your full name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number">
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="dashboard.php" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        
                        <button type="submit" name="update" class="btn btn-update">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
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
        // Show selected file name
        document.getElementById('profile-pic-upload').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        });
        
        // Image preview before upload
        document.getElementById('profile-pic-upload').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                const profilePicture = document.querySelector('.profile-picture') || 
                                      document.querySelector('.profile-picture-placeholder');
                
                reader.onload = function(e) {
                    // Replace placeholder with img if needed
                    if (profilePicture.classList.contains('profile-picture-placeholder')) {
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = "Profile Picture";
                        newImg.className = "profile-picture";
                        profilePicture.parentNode.replaceChild(newImg, profilePicture);
                    } else {
                        profilePicture.src = e.target.result;
                    }
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>