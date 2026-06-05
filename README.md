# Gilig OCR

Gilig OCR is a static OCR demo built for Vercel. Upload an image in the browser and it sends the file directly to OCR.space for text extraction.

## What is included

- `index.html` - The live OCR app.
- `ocr.png` - Project logo and favicon.
- `vercel.json` - Friendly redirects for legacy paths.

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

- The app is fully usable from the root URL on Vercel.
- The UI is optimized for mobile and desktop screens.
- Replace the demo key in `index.html` if you need your own OCR.space limits.
