# Gilig System

Gilig System is a PHP/MySQL web application built for local use on XAMPP. It includes admin management, user authentication, OCR image-to-text extraction, and profile/dashboard pages.

## Project Structure

- `db.php` - Database connection settings.
- `gilig_db.sql` - Database schema and sample data dump.
- `index.php` - Root entry point that redirects to the OCR page.
- `user/` - User-facing interface and OCR tool.
  - `user/index.php` - Redirects to the OCR page.
  - `user/ocr.php` - Main OCR image upload and text extraction page.
  - `user/dashboard.php` - User dashboard page.
  - `user/profile.php` - User profile page.
  - `user/logout.php` - Log out the current user.
  - `user/uploads/` - Folder for uploaded user files.
- `admin/` - Admin section.
  - `admin/index.php` - Admin login page.
  - `admin/dashboard.php` - Admin dashboard.
  - `admin/profile.php` - Admin profile page.
  - `admin/change_credentials.php` - Admin credential management.
  - `admin/logout.php` - Admin logout.
  - `admin/verify_email.php` - Admin password verification/change page.

## Features

- User login and session handling.
- OCR text extraction using uploaded image files.
- Responsive mobile-friendly OCR page design.
- Admin interface separate from user side.
- Simple local XAMPP deployment.

## Requirements

- XAMPP with Apache and MySQL
- PHP 8.0+ (tested with PHP 8.2)
- Browser with JavaScript enabled

## Setup

1. Copy the project folder into `xampp/htdocs/`.
2. Import `gilig_db.sql` into your MySQL server.
3. Update database settings in `db.php` if needed.
4. Start Apache and MySQL in XAMPP.
5. The OCR page uses OCR.space's public `helloworld` test key by default. Replace it in `user/ocr.php` if you need a private key or higher limits.
6. Open `http://localhost/project/gilig_system/` in your browser.

## Notes

- The root page now redirects directly to the OCR interface.
- `user/index.php` also redirects to `user/ocr.php`.
- The OCR page is styled for a cleaner, water-inspired mobile UI.
- Runtime uploads in `user/uploads/` are ignored by Git except for `default.png`, which is the bundled placeholder avatar used by the sample data.
- If you want to restore the traditional login page, you can add it back in `user/index.php`.

## Developer

- Developed by Ameer

## Troubleshooting

- If the page shows a redirect error, clear your browser cache and cookies.
- Make sure the `user/index.php` and `index.php` files exist in the correct folder.
- Ensure `db.php` contains the correct MySQL credentials for your XAMPP setup.

