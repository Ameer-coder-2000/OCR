# Gilig System

Gilig System is a PHP/MySQL web application for OCR text extraction on XAMPP. It lets users upload images and automatically extract text from them, combining image processing and OCR technology to turn printed or handwritten content into editable, searchable text.

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

## Features

- User login and session handling.
- OCR text extraction using uploaded image files.
- Responsive mobile-friendly OCR page design.
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
- Runtime uploads in `user/uploads/` are ignored by Git except for `default.png`, which is the bundled placeholder avatar used by the sample data.
- If you want to restore a traditional login page or admin area, you can add those pages back in later.
- The project is intended for local XAMPP use.

## Developer

- Developed by Ameer

## Troubleshooting

- If the page shows a redirect error, clear your browser cache and cookies.
- Make sure the `user/index.php` and `index.php` files exist in the correct folder.
- Ensure `db.php` contains the correct MySQL credentials for your XAMPP setup.
