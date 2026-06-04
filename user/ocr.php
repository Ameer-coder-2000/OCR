<?php
session_start();

$result = null;
$error = '';
$extractedText = '';
$fileName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate file upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid image file to upload.";
    } elseif ($_FILES['image']['size'] === 0) {
        $error = "The uploaded file is empty.";
    } else {
        // Process the OCR request
        $api_key = "helloworld"; // OCR.space public test key; swap for a private key if needed
        $tmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        
        // Validate file is an image
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
        $fileType = mime_content_type($tmpPath);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Only image files (JPEG, PNG, GIF, BMP) are allowed.";
        } else {
            // Prepare file for upload
            if (function_exists('curl_file_create')) {
                $cFile = curl_file_create($tmpPath, $fileType, $fileName);
            } else {
                $cFile = '@' . realpath($tmpPath);
            }

            $postFields = [
                'apikey' => $api_key,
                'language' => 'eng',
                'isOverlayRequired' => 'false',
                'OCREngine' => 2,
                'file' => $cFile
            ];

            // Initialize and execute cURL request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.ocr.space/parse/image",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_FAILONERROR => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true
            ]);

            // Add retry logic
            $maxRetries = 2;
            $retryCount = 0;
            
            do {
                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    $retryCount++;
                    sleep(1); // Wait 1 second before retrying
                } else {
                    break;
                }
            } while ($retryCount < $maxRetries);
            
            if (curl_errno($ch)) {
                $error = "Network Error: " . curl_error($ch);
                // Log the error for debugging
                error_log("OCR API Connection Error: " . curl_error($ch));
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode !== 200) {
                    $error = "OCR Service returned HTTP code: " . $httpCode;
                } else {
                    $result = json_decode($response, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $error = "Invalid response from OCR service.";
                    } elseif (isset($result['ErrorMessage']) && !empty($result['ErrorMessage'])) {
                        $error = "OCR Error: " . $result['ErrorMessage'];
                    } elseif (isset($result['ParsedResults'][0]['ParsedText'])) {
                        $extractedText = trim($result['ParsedResults'][0]['ParsedText']);
                    }
                }
            }
            curl_close($ch);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR Text Extraction - Gilig System</title>
    <link rel="icon" type="image/png" sizes="any" href="../ocr.png">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #0e7490;
        --primary-dark: #0284c7;
        --secondary: #475569;
        --light: #f8fafc;
        --dark: #0f172a;
        --danger: #ef4444;
        --success: #16a34a;
        --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        --transition: all 0.25s ease;
        --font-heading: 'Poppins', sans-serif;
        --font-body: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-body);
        background: radial-gradient(circle at top left, rgba(14, 116, 144, 0.16), transparent 18%),
                    radial-gradient(circle at bottom right, rgba(14, 116, 144, 0.1), transparent 16%),
                    linear-gradient(180deg, #eef6ff 0%, #f8fcff 45%, #ffffff 100%);
        color: var(--dark);
        line-height: 1.6;
        min-height: 100vh;
        padding: 12px 10px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .container {
        max-width: 1100px;
        width: 100%;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 18px;
        box-shadow: var(--shadow);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 24px);
        transition: var(--transition);
    }

    .container:hover {
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.1);
    }

    /* Header Styles */
    header {
        background: linear-gradient(135deg, rgba(14, 116, 144, 0.95) 0%, rgba(56, 189, 248, 0.92) 100%);
        color: white;
        padding: 18px 16px;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
        overflow: hidden;
    }

    header::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 20% 25%, rgba(255, 255, 255, 0.22), transparent 16%),
                    radial-gradient(circle at 85% 35%, rgba(255, 255, 255, 0.14), transparent 10%);
        pointer-events: none;
    }

    header h1 {
        position: relative;
        font-family: var(--font-heading);
        font-size: clamp(1.5rem, 2vw, 2rem);
        margin-bottom: 6px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    header h1 .fas {
        font-size: 1.15rem;
    }

    header p {
        position: relative;
        font-size: 0.92rem;
        opacity: 0.92;
        max-width: 760px;
        margin: 0 auto;
        line-height: 1.75;
    }

    /* Navigation */
    .nav-bar {
        background: rgba(255, 255, 255, 0.1);
        padding: 10px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .nav-links {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .nav-link {
        color: white;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Main Content */
    .content {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* Upload Section */
    .upload-section {
        background: var(--light);
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        border: 2px dashed #cbd5e1;
        position: relative;
        transition: var(--transition);
    }

    .upload-section:hover {
        border-color: var(--primary);
        background: #f0f4ff;
    }

    .upload-icon {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 10px;
        animation: bounceIn 0.8s ease-out;
    }

    @keyframes bounceIn {
        0% { transform: scale(0.5); opacity: 0; }
        70% { transform: scale(1.1); opacity: 1; }
        100% { transform: scale(1); }
    }

    .upload-section h2 {
        font-family: var(--font-heading);
        font-size: 1.2rem;
        margin-bottom: 8px;
        color: var(--dark);
    }

    .upload-section p {
        font-size: 0.8rem;
        color: var(--secondary);
        margin-bottom: 15px;
    }

    /* Form Elements */
    .file-input-container {
        position: relative;
        margin: 15px 0;
    }

    .file-input-container input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 10;
    }

    .file-input-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        background: #38bdf8;
        color: white;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 12px 30px rgba(56, 189, 248, 0.18);
        width: 100%;
        justify-content: center;
    }

    .file-input-label:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .file-name {
        margin-top: 10px;
        font-size: 0.8rem;
        color: var(--secondary);
        min-height: 18px;
        word-break: break-all;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 16px;
        background: #0284c7;
        color: white;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(2, 132, 199, 0.16);
    }

    .btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: var(--secondary);
    }

    .btn-secondary:hover {
        background: #475569;
    }

    .btn-success {
        background: var(--success);
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-danger {
        background: var(--danger);
    }

    .btn-danger:hover {
        background: #dc2626;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    .action-buttons .btn {
        width: 100%;
    }

    /* Loading Indicator */
    .loading {
        display: none;
        text-align: center;
        margin: 20px 0;
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid var(--primary);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 10px auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Notifications */
    .notification {
        padding: 12px;
        border-radius: 8px;
        margin: 15px 0;
        text-align: center;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.85rem;
    }

    .error {
        background: #fee2e2;
        color: var(--danger);
        border: 1px solid #fecaca;
    }

    .success {
        background: #dcfce7;
        color: var(--success);
        border: 1px solid #bbf7d0;
    }

    .info {
        background: #e6f7ff;
        color: #007bff;
        border: 1px solid #b3e7ff;
    }

    /* Results Section */
    .results-section {
        display: none;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        box-shadow: var(--shadow);
        padding: 20px;
        border: 1px solid #e2e8f0;
    }

    .results-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .results-header h2 {
        font-family: var(--font-heading);
        font-size: 1.2rem;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .text-count {
        font-size: 0.75rem;
        color: var(--secondary);
        background: var(--light);
        padding: 4px 8px;
        border-radius: 4px;
    }

    .extracted-text {
        background: #fdfdfd;
        padding: 15px;
        border-radius: 8px;
        white-space: pre-wrap;
        word-wrap: break-word;
        max-height: 300px;
        overflow-y: auto;
        line-height: 1.6;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        border: 1px solid #e9ecef;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 15px;
    }

    .text-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .text-actions .btn {
        width: 100%;
        justify-content: center;
    }

    /* Sticky Extract Button for Mobile */
    .sticky-extract-container {
        position: fixed;
        bottom: 15px;
        left: 15px;
        right: 15px;
        z-index: 100;
        display: none;
    }

    .sticky-extract-btn {
        width: 100%;
        padding: 12px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transition: var(--transition);
    }

    .sticky-extract-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    /* Footer */
    footer {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        color: var(--secondary);
        font-size: 0.75rem;
        border-top: 1px solid #e2e8f0;
        background: var(--light);
    }

    /* Desktop Styles */
    @media (min-width: 768px) {
        body {
            padding: 20px;
        }

        .container {
            min-height: calc(100vh - 40px);
        }

        header {
            padding: 20px;
        }

        header h1 {
            font-size: 1.8rem;
        }

        header h1 .fas {
            font-size: 1.5rem;
        }

        header p {
            font-size: 1rem;
        }

        .content {
            padding: 20px;
            gap: 20px;
        }

        .upload-section {
            padding: 30px;
        }

        .upload-icon {
            font-size: 3rem;
        }

        .upload-section h2 {
            font-size: 1.5rem;
        }

        .upload-section p {
            font-size: 0.9rem;
        }

        .action-buttons {
            flex-direction: row;
            justify-content: center;
            gap: 15px;
        }

        .action-buttons .btn {
            width: auto;
            min-width: 150px;
        }

        .results-section {
            padding: 30px;
        }

        .results-header h2 {
            font-size: 1.5rem;
        }

        .extracted-text {
            max-height: 400px;
            font-size: 0.9rem;
        }

        .text-actions {
            flex-direction: row;
            justify-content: center;
            gap: 15px;
        }

        .text-actions .btn {
            width: auto;
            min-width: 140px;
        }

        .sticky-extract-container {
            display: none !important;
        }
    }

    @media (min-width: 992px) {
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }

        .upload-section {
            margin-bottom: 0;
        }

        .results-section {
            margin-top: 0;
            height: fit-content;
        }

        .extracted-text {
            max-height: 500px;
        }
    }
    </style>
    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 100%);
            padding: 18px 12px 24px;
        }

        .container {
            gap: 18px;
        }

        header {
            border-radius: 28px;
            padding: 28px 24px;
            text-align: left;
        }

        header h1 {
            justify-content: flex-start;
            font-size: clamp(1.9rem, 2.6vw, 2.8rem);
        }

        header p {
            text-align: left;
            max-width: 680px;
            color: rgba(255, 255, 255, 0.92);
        }

        .nav-bar {
            padding: 14px 16px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        }

        .nav-link {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }

        .upload-section,
        .results-section {
            border-radius: 28px;
            padding: 26px;
        }

        .file-input-label {
            min-height: 62px;
        }

        .action-buttons,
        .text-actions {
            gap: 14px;
        }

        .results-header {
            gap: 14px;
        }

        .extracted-text {
            min-height: 220px;
            max-height: 460px;
        }

        .sticky-extract-container {
            left: 14px;
            right: 14px;
            bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-image"></i> OCR Text Extraction</h1>
            <p>Extract text from your images with our powerful OCR technology</p>
        </header>

        <nav class="nav-bar">
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profile
                </a>
            </div>
        </nav>

        <div class="content">
            <div class="upload-section">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <h2>Upload Your Image</h2>
                <p>Supported formats: JPG, PNG, GIF, BMP (Max: 5MB)</p>

                <form method="POST" enctype="multipart/form-data" id="ocrForm">
                    <div class="file-input-container">
                        <label for="imageInput" class="file-input-label">
                            <i class="fas fa-folder-open"></i> Choose Image File
                        </label>
                        <input type="file" name="image" id="imageInput" accept="image/*" required>
                    </div>
                    <div class="file-name" id="fileName">
                        <?php echo !empty($fileName) ? 'Selected: ' . htmlspecialchars($fileName) : 'No file selected'; ?>
                    </div>

                    <div class="action-buttons" id="actionButtons">
                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-magnifying-glass"></i> Extract Text
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                </form>
            </div>

            <!-- Loading Indicator -->
            <div class="loading" id="loadingIndicator">
                <div class="spinner"></div>
                <p>Processing your image...</p>
            </div>

            <!-- Notifications -->
            <?php if ($error): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($result) && empty($extractedText) && empty($error)): ?>
                <div class="notification info">
                    <i class="fas fa-info-circle"></i> No text could be detected in the uploaded image.
                </div>
            <?php endif; ?>

            <!-- Results Section -->
            <section class="results-section" id="resultsSection">
                <div class="results-header">
                    <h2><i class="fas fa-file-alt"></i> Extracted Text</h2>
                    <span class="text-count" id="charCount"></span>
                </div>

                <div class="extracted-text" id="extractedText">
                    <?php echo !empty($extractedText) ? htmlspecialchars($extractedText) : ''; ?>
                </div>

                <div class="text-actions">
                    <button onclick="copyToClipboard()" class="btn">
                        <i class="fas fa-copy"></i> Copy to Clipboard
                    </button>
                    <button onclick="downloadText()" class="btn btn-success">
                        <i class="fas fa-download"></i> Download as Text File
                    </button>
                    <button onclick="clearForm()" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Process Another Image
                    </button>
                </div>
            </section>
        </div>

        <!-- Sticky Extract Button for Mobile -->
        <div class="sticky-extract-container" id="stickyExtractBtn">
            <button type="button" class="sticky-extract-btn" onclick="document.getElementById('submitBtn').click()">
                <i class="fas fa-magnifying-glass"></i> Extract Text
            </button>
        </div>

        <footer>
            <p>Gilig Systems &copy; <?php echo date('Y'); ?> - Powered by OCR Space API</p>
        </footer>
    </div>

    <script>
        // Update file name when a file is selected
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const fileName = this.files[0] ? this.files[0].name : 'No file selected';
            document.getElementById('fileName').textContent = 'Selected: ' + fileName;
            
            // Show sticky extract button on mobile after file selection
            if (window.innerWidth <= 767) {
                document.getElementById('stickyExtractBtn').style.display = 'block';
                scrollToElement('stickyExtractBtn');
            } else {
                // Scroll to extract button on desktop
                scrollToElement('actionButtons');
            }
        });
        
        // Show loading indicator when form is submitted
        document.getElementById('ocrForm').addEventListener('submit', function() {
            document.getElementById('loadingIndicator').style.display = 'block';
            // Hide sticky button during processing
            document.getElementById('stickyExtractBtn').style.display = 'none';
        });
        
        // Update character count
        function updateCharCount() {
            const textElement = document.getElementById('extractedText');
            if (textElement) {
                const charCount = textElement.textContent.length;
                document.getElementById('charCount').textContent = charCount + ' characters';
            }
        }
        
        // Show notification
        function showNotification(message, type) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification-temp');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = `notification notification-temp ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            // Insert at the top of content
            const content = document.querySelector('.content');
            content.insertBefore(notification, content.firstChild);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
        }
        
        // Copy text to clipboard
        function copyToClipboard() {
            const textElement = document.getElementById('extractedText');
            if (!textElement) return;
            
            const textToCopy = textElement.textContent;
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    showNotification('Text successfully copied to clipboard!', 'success');
                })
                .catch(err => {
                    console.error('Failed to copy text: ', err);
                    showNotification('Failed to copy text to clipboard. Please try again.', 'error');
                });
        }
        
        // Download text as file
        function downloadText() {
            const textElement = document.getElementById('extractedText');
            if (!textElement) return;
            
            const text = textElement.textContent;
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = 'extracted-text.txt';
            document.body.appendChild(a);
            a.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }, 100);
        }
        
        // Clear form and reset UI
        function clearForm() {
            document.getElementById('ocrForm').reset();
            document.getElementById('fileName').textContent = 'No file selected';
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('stickyExtractBtn').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        // Scroll to element function
        function scrollToElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        // Initialize on page load
        window.onload = function() {
            <?php if (!empty($extractedText)): ?>
                document.getElementById('resultsSection').style.display = 'block';
                updateCharCount();
                // Scroll to results after extraction
                setTimeout(function() {
                    scrollToElement('resultsSection');
                }, 100);
            <?php endif; ?>
            
            // Show sticky extract button on mobile if file is already selected
            if (window.innerWidth <= 767 && document.getElementById('imageInput').files.length > 0) {
                document.getElementById('stickyExtractBtn').style.display = 'block';
            }
            
            // If we have a file selected from previous submission, scroll to appropriate section
            <?php if (!empty($fileName) && empty($extractedText)): ?>
                if (window.innerWidth <= 767) {
                    document.getElementById('stickyExtractBtn').style.display = 'block';
                    scrollToElement('stickyExtractBtn');
                } else {
                    scrollToElement('actionButtons');
                }
            <?php endif; ?>
        };
        
        // Handle window resize to show/hide sticky button
        window.addEventListener('resize', function() {
            if (window.innerWidth > 767) {
                document.getElementById('stickyExtractBtn').style.display = 'none';
            } else if (document.getElementById('imageInput').files.length > 0) {
                document.getElementById('stickyExtractBtn').style.display = 'block';
            }
        });
    </script>
</body>
</html>
