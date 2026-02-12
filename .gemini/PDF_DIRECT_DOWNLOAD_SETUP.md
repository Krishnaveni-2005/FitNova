# Direct PDF Download - Installation Instructions

## Current Status
The download button currently opens a print dialog. To enable **direct PDF download**, you need to install a PDF library.

## Easiest Solution: Install TCPDF Manually

### Step 1: Download TCPDF
1. Go to: https://github.com/tecnickcom/TCPDF/releases
2. Download the latest release (usually a .zip file)
3. Extract the ZIP file

### Step 2: Install in Your Project
1. Copy the extracted `tcpdf` folder to: `c:\xamppp\htdocs\fitnova\`
2. The structure should be:
   ```
   c:\xamppp\htdocs\fitnova\
   ├── tcpdf/
   │   ├── tcpdf.php
   │   ├── config/
   │   ├── fonts/
   │   └── ...
   ├── generate_receipt_pdf.php
   └── ... (other files)
   ```

### Step 3: Test
1. Click the "Download" button on the receipt page
2. PDF should download directly without print dialog!

---

## Alternative: Use Current Print-to-PDF Method

The current implementation works perfectly fine:
- Click "Download"
- Print dialog opens
- Select "Save as PDF"
- Choose location and save

This method:
✅ No installation required
✅ Works immediately
✅ Professional output
✅ User has control over quality

---

## Why Direct Download Needs a Library?

PHP cannot create PDFs natively. You need one of these libraries:
- **TCPDF** (Free, no Composer needed)
- **mPDF** (Free, no Composer needed)
- **DomPDF** (Requires Composer)
- **FPDF** (Simple, limited features)

I've already set up the code to work with TCPDF - you just need to download and place the library in your project folder!

---

**Recommendation**: Stick with the current print-to-PDF method unless you specifically need automated PDF downloads.
