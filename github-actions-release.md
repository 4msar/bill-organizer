# GitHub Actions Release Workflow

This repository includes an automated release workflow that builds and packages the Laravel application for production deployment.

## Features

- **Automated Builds**: Triggers on pushes to `main` branch or manual dispatch
- **Production Ready**: Creates optimized production builds with compiled assets
- **Semantic Versioning**: Automatically increments patch version for releases
- **Clean Artifacts**: Excludes development files and includes only production-ready code
- **Draft Releases**: Creates draft releases for review before publishing

## Workflow Overview

The release workflow (`/.github/workflows/release.yml`) performs these steps:

1. **Environment Setup**
   - Checks out repository with full git history
   - Sets up PHP 8.3 with required extensions
   - Configures Node.js 22 with Yarn caching
   - Caches Composer dependencies for faster builds

2. **Dependency Installation**
   - Installs Composer dependencies (production only)
   - Installs Yarn dependencies with frozen lockfile
   - Generates Laravel application key and Ziggy routes

3. **Build Process**
   - Type checks TypeScript files
   - Compiles frontend assets using Vite
   - Creates optimized production build

4. **Packaging**
   - Creates clean production directory
   - Excludes development files (tests, configs, etc.)
   - Installs production Composer dependencies
   - Generates optimized autoloader
   - Creates ZIP archive

5. **Release Creation**
   - Generates semantic version (auto-increments patch)
   - Creates draft GitHub release
   - Attaches ZIP artifact
   - Uploads build artifacts for 30-day retention

## Triggering the Workflow

### Automatic Trigger
The workflow automatically runs when code is pushed to the `main` branch:

```bash
git push origin main
```

### Manual Trigger
You can manually trigger the workflow from the GitHub Actions tab:

1. Go to your repository's "Actions" tab
2. Select "Release" workflow
3. Click "Run workflow"
4. Choose the branch (typically `main`)
5. Click "Run workflow"

## Customizing the Workflow

### Changing the Version Strategy

By default, the workflow increments the patch version. To use different versioning:

```yaml
# For minor version increment
NEW_MINOR=$((MINOR + 1))
NEW_VERSION="v${MAJOR}.${NEW_MINOR}.0"

# For major version increment  
NEW_MAJOR=$((MAJOR + 1))
NEW_VERSION="v${NEW_MAJOR}.0.0"
```

### Adding Pre-release Support

To create pre-release versions:

```yaml
- name: Generate semantic version
  id: version
  run: |
    # ... existing version logic ...
    
    # For pre-release
    if [[ "${{ github.ref }}" == "refs/heads/develop" ]]; then
      NEW_VERSION="${NEW_VERSION}-beta.${GITHUB_RUN_NUMBER}"
    fi
```

### Excluding Additional Files

Modify the `rsync` command to exclude more files:

```yaml
rsync -av \
  --exclude='custom-config/' \
  --exclude='*.custom' \
  # ... existing excludes ...
```

### Changing PHP/Node Versions

Update the setup steps:

```yaml
- name: Setup PHP 8.4  # Change version here
  uses: shivammathur/setup-php@v2
  with:
    php-version: '8.4'  # Update version

- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'  # Change Node version
```

## Production Deployment

The generated ZIP file contains a production-ready Laravel application:

### Installation Steps
1. Download the ZIP file from the release
2. Extract to your web server directory
3. Copy `.env.example` to `.env` and configure:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Configure database settings in `.env`
5. Run database migrations:
   ```bash
   php artisan migrate --force
   ```
6. Set proper permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```
7. Configure web server to point to `public/` directory

### Requirements
- PHP 8.3 or higher
- MySQL 8.0+ or compatible database
- Web server (Apache/Nginx)
- Proper file permissions

## Troubleshooting

### Build Failures

**Composer issues**: Check if all dependencies are compatible with PHP 8.3
**Yarn issues**: Ensure `yarn.lock` is committed and up-to-date
**TypeScript errors**: Fix type errors in the codebase before building
**Vite build errors**: Check `vite.config.ts` and asset imports

### Release Creation Failures

**Permission issues**: Ensure the repository has proper GitHub Actions permissions
**Tag conflicts**: Check if the generated tag already exists
**Asset upload failures**: Verify the ZIP file was created successfully

### Version Issues

**Wrong version generated**: Check if git tags follow semver format (`v1.2.3`)
**Missing tags**: The workflow creates `v0.0.1` if no tags exist

## Security Considerations

- The workflow uses `GITHUB_TOKEN` with minimal required permissions
- Production builds exclude sensitive development files
- Environment files (`.env`) are never included in releases
- Build artifacts are retained for only 30 days

## Monitoring

The workflow provides detailed logs for each step. Monitor:
- Build duration and success rates
- Artifact sizes
- TypeScript compilation warnings
- Dependency installation issues

For questions or issues with the release workflow, check the GitHub Actions logs or create an issue in the repository.