# Gilig OCR

Gilig OCR is now a static, Vercel-friendly OCR demo. Upload an image in the browser and it sends the file directly to OCR.space for text extraction.

The old PHP and MySQL files were removed because Vercel does not execute PHP. This version serves as a clean static site with a redirect setup for the old `.php` URLs.

## What is included

- `index.html` - The live OCR app.
- `ocr.png` - Project logo and favicon.
- `vercel.json` - Redirects old PHP paths to the new static app.

## How it works

1. Choose an image file or drag it into the upload box.
2. The browser sends the image to OCR.space using the public `helloworld` test key.
3. Copy the extracted text or download it as a `.txt` file.

## Deploying to Vercel

1. Push this repo to GitHub.
2. Import the repository into Vercel.
3. Deploy it as a static site.
4. If you want your own OCR.space key, replace `helloworld` in `index.html`.

## Notes

- Old PHP paths like `index.php` and `user/ocr.php` now redirect to `/`.
- The project no longer depends on PHP, MySQL, or XAMPP.
- The app is fully usable from the root URL on Vercel.
