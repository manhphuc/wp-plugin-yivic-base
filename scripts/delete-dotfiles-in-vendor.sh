#!/bin/bash

VENDOR_DIR="$(dirname "$0")/../vendor"

# Ensure vendor directory exists
if [ ! -d "$VENDOR_DIR" ]; then
    echo "Vendor directory does not exist: $VENDOR_DIR"
    exit 0  # Exit without error
fi

echo "Scanning and deleting all dot files inside the vendor directory..."

######################
# DELETE ALL DOT FILES IN VENDOR (EXCLUDING .git & .env)
######################
find "$VENDOR_DIR" -type f -name ".*" ! -name ".git" ! -name ".env" -exec rm -f {} + 2>/dev/null

######################
# DELETE ALL DOT DIRECTORIES (EXCLUDING .git)
######################
find "$VENDOR_DIR" -type d -name ".*" ! -name ".git" -exec rm -rf {} + 2>/dev/null

######################
# RUN COMPOSER AUTOLOAD REBUILD
######################
echo "Rebuilding Composer autoload..."
composer dump-autoload

echo "Dot file cleanup completed successfully!"
