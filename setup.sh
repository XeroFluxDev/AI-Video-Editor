#!/bin/bash

# AI Video Editor - Setup Script
# This script automates the installation and configuration process

set -e

echo "====================================="
echo "AI Video Editor - Setup Script"
echo "====================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo -e "${YELLOW}Warning: Running as root. This is not recommended.${NC}"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 1: Check requirements
echo -e "${GREEN}Step 1: Checking requirements...${NC}"

command -v php >/dev/null 2>&1 || { echo -e "${RED}Error: PHP is not installed${NC}" >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo -e "${RED}Error: Composer is not installed${NC}" >&2; exit 1; }
command -v ffmpeg >/dev/null 2>&1 || { echo -e "${RED}Error: FFmpeg is not installed${NC}" >&2; exit 1; }

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✓ PHP version: $PHP_VERSION"

FFMPEG_VERSION=$(ffmpeg -version | head -n1)
echo "✓ FFmpeg: $FFMPEG_VERSION"

COMPOSER_VERSION=$(composer --version --no-ansi | head -n1)
echo "✓ Composer: $COMPOSER_VERSION"

echo ""

# Step 2: Install Composer dependencies
echo -e "${GREEN}Step 2: Installing PHP dependencies...${NC}"
cd app
composer install --no-interaction
echo "✓ Dependencies installed"
cd ..
echo ""

# Step 3: Create environment file
echo -e "${GREEN}Step 3: Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ Created .env file from .env.example"
    echo -e "${YELLOW}Important: Edit .env and add your API keys!${NC}"
else
    echo "✓ .env file already exists"
fi
echo ""

# Step 4: Create storage directories
echo -e "${GREEN}Step 4: Creating storage directories...${NC}"
mkdir -p app/storage/uploads
mkdir -p app/storage/temp
mkdir -p app/storage/exports
mkdir -p app/storage/ai-cache

echo "✓ Created storage/uploads"
echo "✓ Created storage/temp"
echo "✓ Created storage/exports"
echo "✓ Created storage/ai-cache"
echo ""

# Step 5: Set permissions
echo -e "${GREEN}Step 5: Setting permissions...${NC}"
chmod -R 755 app/public
chmod -R 775 app/storage

# Try to set www-data ownership (may fail if not running as root)
if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data app/storage
    echo "✓ Set ownership to www-data"
else
    echo -e "${YELLOW}Note: Run 'sudo chown -R www-data:www-data app/storage' if deploying to web server${NC}"
fi

echo "✓ Set directory permissions"
echo ""

# Step 6: Verify FFmpeg paths
echo -e "${GREEN}Step 6: Verifying FFmpeg configuration...${NC}"
FFMPEG_PATH=$(which ffmpeg)
FFPROBE_PATH=$(which ffprobe)

echo "FFmpeg path: $FFMPEG_PATH"
echo "FFprobe path: $FFPROBE_PATH"

# Update config file with correct paths
sed -i "s|'ffmpeg_path' => '.*'|'ffmpeg_path' => '$FFMPEG_PATH'|g" app/config/ffmpeg.php
sed -i "s|'ffprobe_path' => '.*'|'ffprobe_path' => '$FFPROBE_PATH'|g" app/config/ffmpeg.php

echo "✓ Updated FFmpeg configuration"
echo ""

# Step 7: Test FFmpeg
echo -e "${GREEN}Step 7: Testing FFmpeg...${NC}"
ffmpeg -version >/dev/null 2>&1 && echo "✓ FFmpeg is working" || echo -e "${RED}✗ FFmpeg test failed${NC}"
echo ""

# Step 8: Summary
echo "====================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "====================================="
echo ""
echo "Next steps:"
echo "1. Edit .env and add your API keys:"
echo "   - OPENROUTER_API_KEY"
echo "   - OPENAI_API_KEY"
echo ""
echo "2. Configure your web server:"
echo "   - Point document root to: $(pwd)/app/public"
echo "   - See DEPLOYMENT.md for Nginx/Apache configuration"
echo ""
echo "3. For development, run:"
echo "   cd app/public && php -S localhost:8000"
echo ""
echo "4. Access the application:"
echo "   http://localhost:8000 (development)"
echo "   http://your-domain.com (production)"
echo ""
echo "For deployment instructions, see:"
echo "   - README.md"
echo "   - DEPLOYMENT.md"
echo ""
echo -e "${GREEN}Happy editing!${NC}"
echo ""
