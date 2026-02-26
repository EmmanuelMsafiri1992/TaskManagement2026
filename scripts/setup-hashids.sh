#!/bin/bash
# Setup hashids package manually (workaround for GitHub API rate limits)

VENDOR_DIR="/home/indegnnk/task.emphxs.com/vendor"

echo "Setting up hashids package..."

# Create directory structure
mkdir -p "$VENDOR_DIR/hashids/hashids/src/Math"

# Download files from GitHub
curl -sL https://raw.githubusercontent.com/vinkla/hashids/5.0.2/src/Hashids.php -o "$VENDOR_DIR/hashids/hashids/src/Hashids.php"
curl -sL https://raw.githubusercontent.com/vinkla/hashids/5.0.2/src/HashidsInterface.php -o "$VENDOR_DIR/hashids/hashids/src/HashidsInterface.php"
curl -sL https://raw.githubusercontent.com/vinkla/hashids/5.0.2/src/Math/BCMath.php -o "$VENDOR_DIR/hashids/hashids/src/Math/BCMath.php"
curl -sL https://raw.githubusercontent.com/vinkla/hashids/5.0.2/src/Math/Gmp.php -o "$VENDOR_DIR/hashids/hashids/src/Math/Gmp.php"
curl -sL https://raw.githubusercontent.com/vinkla/hashids/5.0.2/src/Math/MathInterface.php -o "$VENDOR_DIR/hashids/hashids/src/Math/MathInterface.php"

# Set ownership
chown -R indegnnk:indegnnk "$VENDOR_DIR/hashids/"

echo "Hashids package installed successfully!"

# Regenerate autoloader
cd /home/indegnnk/task.emphxs.com
composer dump-autoload

echo "Done! Now run: php artisan jobs:assign-new --hours=24"
