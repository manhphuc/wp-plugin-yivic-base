#!/bin/bash

VENDOR_DIR="$(dirname "$0")/../vendor"

# Ensure vendor directory exists
if [ ! -d "$VENDOR_DIR" ]; then
    echo "Vendor directory does not exist: $VENDOR_DIR"
    exit 0  # Exit without error
fi

echo "Starting cleanup of unnecessary vendor FOLDERS..."

######################
# DELETE "tests/" DIRECTORIES
######################
echo "Deleting all 'tests/' directories..."
find "$VENDOR_DIR" -type d -name "tests" -exec rm -rf {} + 2>/dev/null

######################
# DELETE "demos/" DIRECTORIES
######################
echo "Deleting all 'demos/' directories..."
find "$VENDOR_DIR" -type d -name "demos" -exec rm -rf {} + 2>/dev/null

######################
# DELETE "development tools" (e.g., node_modules, bower_components)
######################
echo "Deleting development tool directories..."
find "$VENDOR_DIR" -type d \( -name "bower_components" -o -name "node_modules" -o -name "grunt" \) -exec rm -rf {} + 2>/dev/null

######################
# DELETE "Doctrine Deprecations"
######################
echo "Deleting specific vendor folders..."
find "$VENDOR_DIR/doctrine/deprecations/lib/Doctrine/Deprecations/PHPUnit" -type d -exec rm -rf {} + 2>/dev/null

######################
# RUN COMPOSER AUTOLOAD REBUILD
######################
echo "Rebuilding Composer autoload..."
composer dump-autoload

echo "Folder cleanup completed successfully!"