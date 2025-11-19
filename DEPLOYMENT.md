# Deployment Instructions

## Workflow: Build Locally, Deploy to Production

Since you cannot run `npm run build` on the production server, follow this workflow:

### On Your Local Machine (Before Pushing)

1. **Make your code changes**

2. **Build assets for production:**
```bash
npm run build
```

3. **Add the built assets to git:**
```bash
git add public/build
git add .gitignore
git commit -m "Build assets for production"
git push
```

### On Production Server (After Pulling)

1. **Pull the latest code:**
```bash
git pull origin main  # or your branch name
```

2. **Clear Laravel caches (recommended):**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

3. **That's it!** The built assets are already in the repository.

## Important Notes

- **Always run `npm run build` locally** before committing when you change JavaScript/Vue components
- **Always commit `/public/build`** directory - it's now tracked in git
- The `/public/build` directory is **NOT** in `.gitignore` anymore, so it will be committed with your code
- If you forget to build before pushing, your production site will use old assets until you push a new build

