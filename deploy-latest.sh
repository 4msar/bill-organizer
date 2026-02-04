#!/bin/bash

# Bill Organizer - Zero Downtime Deployment Script
# This script downloads the latest release and deploys it without downtime

set -e  # Exit on any error

# Configuration
GITHUB_REPO="4msar/bill-organizer"
APP_NAME="bill-organizer"
CURRENT_FILE_DIR=$(pwd)
INSTALL_DIR="${CURRENT_FILE_DIR}/${APP_NAME}"
BACKUP_DIR="${CURRENT_FILE_DIR}/backups"
TEMP_DIR="${CURRENT_FILE_DIR}/temp"
WEB_USER="www-data"  # Change this to your web server user
GITHUB_TOKEN=""  # Optional: Set if you need authentication for private repos
# Keep specified number of old releases (0 means keep none)
KEEP_RELEASES="2"  # Default to 1 if not set

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Check if running as root or with sudo
check_permissions() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root or with sudo"
        exit 1
    fi
}

# Check required commands
check_dependencies() {
    local deps=("curl" "jq" "unzip" "php" "composer")
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            error "Required dependency '$dep' is not installed"
            exit 1
        fi
    done
}

# Get latest release information
get_latest_release() {
    log "Fetching latest release information..."
    
    local api_url="https://api.github.com/repos/${GITHUB_REPO}/releases/latest"
    local headers=""
    
    if [[ -n "$GITHUB_TOKEN" ]]; then
        headers="-H \"Authorization: Bearer $GITHUB_TOKEN\""
    fi
    
    RELEASE_INFO=$(eval curl -s $headers "$api_url")
    
    if [[ $(echo "$RELEASE_INFO" | jq -r '.message // empty') == "Not Found" ]]; then
        error "Repository not found or no releases available"
        exit 1
    fi
    
    LATEST_VERSION=$(echo "$RELEASE_INFO" | jq -r '.tag_name')
    DOWNLOAD_URL=$(echo "$RELEASE_INFO" | jq -r '.assets[0].browser_download_url')
    ASSET_NAME=$(echo "$RELEASE_INFO" | jq -r '.assets[0].name')
    
    if [[ "$LATEST_VERSION" == "null" || "$DOWNLOAD_URL" == "null" ]]; then
        error "Could not fetch release information"
        exit 1
    fi
    
    success "Latest version: $LATEST_VERSION"
    log "Download URL: $DOWNLOAD_URL"
}

# Check current version
check_current_version() {
    if [[ -f "${INSTALL_DIR}/version.txt" ]]; then
        CURRENT_VERSION=$(cat "${INSTALL_DIR}/version.txt")
        log "Current version: $CURRENT_VERSION"
        
        if [[ "$CURRENT_VERSION" == "$LATEST_VERSION" ]]; then
            warning "Already running the latest version ($LATEST_VERSION)"
            read -p "Do you want to continue anyway? [y/N]: " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                log "Deployment cancelled"
                exit 0
            fi
        fi
    else
        warning "No version file found, proceeding with deployment"
        CURRENT_VERSION="none"
    fi
}

# Download and prepare new version
download_and_prepare() {
    log "Downloading latest release..."
    
    # Clean and create temp directory
    rm -rf "$TEMP_DIR"
    mkdir -p "$TEMP_DIR"
    cd "$TEMP_DIR"
    
    # Download the release
    local headers=""
    if [[ -n "$GITHUB_TOKEN" ]]; then
        headers="-H \"Authorization: Bearer $GITHUB_TOKEN\""
    fi
    
    eval curl -L $headers -o "$ASSET_NAME" "$DOWNLOAD_URL"
    
    if [[ ! -f "$ASSET_NAME" ]]; then
        error "Failed to download release"
        exit 1
    fi
    
    success "Downloaded: $ASSET_NAME"
    
    # Extract the release
    log "Extracting release..."
    unzip -q "$ASSET_NAME"
    
    # Find the extracted directory
    EXTRACTED_DIR=$(find . -maxdepth 1 -type d -name "${APP_NAME}-*" | head -n 1)
    if [[ -z "$EXTRACTED_DIR" ]]; then
        error "Could not find extracted application directory"
        exit 1
    fi
    
    # Set the extracted directory name
    NEW_APP_DIR="${TEMP_DIR}/${EXTRACTED_DIR}"
    success "Extracted to: $NEW_APP_DIR"

    cd "$CURRENT_FILE_DIR"
}

# Preserve important files
preserve_files() {
    log "Preserving important files..."
    echo
    
    # Files to preserve from current installation
    local preserve_files=(
        ".env"
        "storage/app"
        "storage/logs"
        "public/storage"
    )
    
    for file in "${preserve_files[@]}"; do
        log "Checking for file: $file in ${INSTALL_DIR}"
        if [[ -e "${INSTALL_DIR}/${file}" ]]; then
            log "Preserving: $file"

            # Remove the target if it exists to avoid nested directories
            rm -rf "${NEW_APP_DIR}/${file}"
            
            log "Copying ${INSTALL_DIR}/${file} to new application directory: ${NEW_APP_DIR}/${file}"

            cp -rf "${INSTALL_DIR}/${file}" "${NEW_APP_DIR}/${file}" 2>/dev/null || {
                warning "Could not preserve $file"
            }
        else
            warning "File not found: $file in ${INSTALL_DIR}, skipping preservation"
        fi
    done

    echo
    echo
}

# Deploy new version
deploy() {
    log "Deploying new version..."
    
    # Create new release directory
    RELEASE_DIR="${INSTALL_DIR}-${LATEST_VERSION}"

    echo
    echo
    
    # Copy new version to release directory
    cp -r "$NEW_APP_DIR" "$RELEASE_DIR/"
    
    # Set proper permissions
    # check if the web user exists
    if id -u "$WEB_USER" &>/dev/null; then
        chown -R "$WEB_USER:$WEB_USER" "$RELEASE_DIR"
    fi
    chmod -R 755 "$RELEASE_DIR"
    chmod -R 775 "$RELEASE_DIR/storage" "$RELEASE_DIR/bootstrap/cache"
    
    # Run Laravel setup commands
    cd "$RELEASE_DIR"
    
    log "Optimizing Laravel application..."
    
    # Clear caches
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    
    # Cache configurations
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    
    # Run migrations (be careful with this in production)
    read -p "Run database migrations? [y/N]: " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force
    fi
    
    cd "$CURRENT_FILE_DIR"
    
    # Create new symlink
    ln -sfn "$RELEASE_DIR" "${INSTALL_DIR}"

    log "New symlink created: ${INSTALL_DIR}"
    
    # Update version file
    echo "$LATEST_VERSION" > "${INSTALL_DIR}/version.txt"
    
    success "Deployment completed successfully!"
}

# Cleanup old releases
cleanup() {
    log "Cleaning up..."
    
    # Remove temp directory
    rm -rf "$TEMP_DIR"
    
    echo

    log "Removing last old releases..."
    OLD_RELEASES=$(find "$(dirname "$INSTALL_DIR")" -maxdepth 1 -name "${APP_NAME}-v*" -type d | sort -V | head -n 1)

    if [[ -n "$OLD_RELEASES" ]]; then
        for release in $OLD_RELEASES; do

            log "Currently processing release: $release"

            log "Latest version: $APP_NAME-$LATEST_VERSION"

            # Skip the current release
            if [[ $(basename "$release") == "$APP_NAME-$LATEST_VERSION" ]]; then
                log "Skipping current release: $(basename "$release")"
                continue
            fi

            log "Removing old release: $(basename "$release")"
            rm -rf "$release"
        done
    else
        warning "No old releases to clean up"
    fi
    
    success "Cleanup completed"
}

# Rollback function
rollback() {
    error "Deployment failed! Attempting rollback..."
    
    # Find previous release
    PREVIOUS_RELEASE=$(find "$(dirname "$INSTALL_DIR")" -maxdepth 1 -name "${APP_NAME}-v*" -type d | sort -V | tail -n 2 | head -n 1)
    
    if [[ -n "$PREVIOUS_RELEASE" ]]; then
        ln -sfn "$PREVIOUS_RELEASE" "$INSTALL_DIR"
        warning "Rolled back to: $(basename "$PREVIOUS_RELEASE")"
    else
        error "No previous release found for rollback"
    fi
}

# Health check
health_check() {
    log "Performing health check..."
    
    # Check if Laravel can bootstrap
    cd "$INSTALL_DIR"
    if php artisan --version &>/dev/null; then
        success "Application health check passed"
        return 0
    else
        error "Application health check failed"
        return 1
    fi
}

self_update() {
    log "Updating deployment script..."
    
    # Download the latest version of the script
    curl -s -o "$CURRENT_FILE_DIR/deploy-latest.sh" "https://raw.githubusercontent.com/${GITHUB_REPO}/main/deploy-latest.sh"
    
    if [[ $? -ne 0 ]]; then
        error "Failed to update deployment script"
        exit 1
    fi
    
    success "Deployment script updated successfully"
}

# Main deployment process
main() {
    log "Starting Bill Organizer deployment..."
    log "Working directory: $CURRENT_FILE_DIR"
    log "Repository: $GITHUB_REPO"
    log "Install directory: $INSTALL_DIR"

    # print some empty lines for better readability
    echo
    echo
    
    # Pre-deployment checks
    # check_permissions # enable if running as root is required
    check_dependencies
    get_latest_release
    check_current_version
    
    # print some empty lines for better readability
    echo
    echo


    # Deployment process
    download_and_prepare
    preserve_files
    
    # Deploy with error handling
    if deploy && health_check; then
        # ask for confirmation before cleanup
        echo
        read -p "Deployment successful! Do you want to clean up old releases? [y/N]: " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            cleanup
        fi

        success "🎉 Deployment completed successfully!"
        log "New version $LATEST_VERSION is now live"

        echo
        # Self-update the deployment script
        read -p "Update deployment script? [y/N]: " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            self_update
        else
            log "Skipping deployment script update"
        fi
    else
        echo
        rollback
        echo
        error "❌ Deployment failed and has been rolled back"
        exit 1
    fi
}

# Trap errors for cleanup
trap 'rollback; exit 1' ERR

# Run main function
main "$@"