# Pinterest Video Downloader

Free Pinterest video downloader - Download Pinterest videos in high quality.

## 🚀 GitHub Pages Deployment

### Important Notes:
- **PHP files won't work** on GitHub Pages (only static HTML/CSS/JS)
- The `api.php` file will be ignored by GitHub Pages
- Only `index.html`, `ads.txt`, and static assets will be published

### Setup Instructions:

1. **Update the canonical URL** in `index.html` (line 15):
   ```html
   <link rel="canonical" href="https://YOUR-USERNAME.github.io/YOUR-REPO-NAME/">
   ```

2. **Create a new repository** on GitHub:
   - Go to https://github.com/new
   - Name it (e.g., `pinterest-downloader`)
   - Make it public

3. **Upload your files**:
   ```bash
   git init
   git add index.html ads.txt
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPO-NAME.git
   git push -u origin main
   ```

4. **Enable GitHub Pages**:
   - Go to repository Settings → Pages
   - Source: Deploy from a branch
   - Branch: `main` / `/ (root)`
   - Save

5. **Wait 1-2 minutes** for deployment

6. **Access your site** at:
   ```
   https://YOUR-USERNAME.github.io/YOUR-REPO-NAME/
   ```

### Files to Upload:
- ✅ `index.html` - Main website
- ✅ `ads.txt` - AdSense verification
- ❌ `api.php` - Won't work on GitHub Pages (skip this)

### For PHP Support:
If you need the PHP backend to work, use hosting services like:
- **Free:** InfinityFree, 000webhost, FreeHosting.com
- **Paid:** Hostinger, Bluehost, SiteGround

## 📝 After Deployment:

1. Update the canonical URL in `index.html` with your actual GitHub Pages URL
2. Update your AdSense `ads.txt` if needed
3. The video extractor will use client-side methods (may have limitations due to CORS)

## ⚠️ Limitations on GitHub Pages:
- Client-side extraction only
- May not work for all Pinterest videos due to CORS restrictions
- For best results, use PHP hosting
