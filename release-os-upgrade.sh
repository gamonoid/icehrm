#!/bin/bash

set -e

# Parse arguments
SKIP_OBFUSCATE=false
for arg in "$@"; do
    case $arg in
        --skip-obfuscate|-s)
            SKIP_OBFUSCATE=true
            shift
            ;;
    esac
done

echo "=== Starting IceHrm OS Upgrade Release Build ==="

if [ "$SKIP_OBFUSCATE" = false ]; then
    echo "Running JavaScript obfuscation..."
    ./obfuscate-js.sh

    echo "Running PHP obfuscation..."
    ./obfuscate-php.sh
else
    echo "Skipping obfuscation (--skip-obfuscate flag set)"
fi

# Step 1: Prepare releases directory
echo "Preparing releases directory..."
rm -rf releases/extensions/icehrm
rm releases/extensions/zips/icehrm.zip
mkdir -p releases/extensions/icehrm
mkdir -p releases/zips

DEST="releases/extensions/icehrm"

# Step 2: Copy /app folder (skip config*, data, cache, install)
echo "Copying /app folder..."
mkdir -p "$DEST/app"
for item in app/*; do
    basename=$(basename "$item")
    # Skip files starting with 'config'
    if [[ "$basename" == config* ]]; then
        echo "  Skipping $basename"
        continue
    fi
    # Skip data, cache, install directories
    if [[ "$basename" == "data" || "$basename" == "cache" || "$basename" == "install" ]]; then
        echo "  Skipping $basename"
        continue
    fi
    cp -r "$item" "$DEST/app/"
done

# Step 3: Copy /core folder
echo "Copying /core folder..."
cp -r core "$DEST/"

# Step 4: Copy /docs folder
echo "Copying /docs folder..."
cp -r docs "$DEST/"

# Step 5: Copy /release-notes folder
echo "Copying /release-notes folder..."
cp -r release-notes "$DEST/"

# Step 6: Copy specific extensions (editor and marketplace)
echo "Copying extensions (editor, marketplace)..."
mkdir -p "$DEST/extensions"
cp -r extensions/editor "$DEST/extensions/"
# Copy marketplace extension but exclude admin/src (PHP source) and admin/web/js (JS source)
rsync -a --exclude='admin/src' --exclude='admin/web/js' extensions/marketplace/ "$DEST/extensions/marketplace/"

# Step 7: Copy /web folder (excluding admin, modules, themecss, themejs, node_modules)
echo "Copying /web folder..."
rsync -a --exclude='admin' --exclude='modules' --exclude='themecss' --exclude='themejs' --exclude='node_modules' web/ "$DEST/web/"

# Step 8: Copy individual files
echo "Copying docker and config files..."
cp docker-compose.yaml "$DEST/"
cp docker-compose-prod.yaml "$DEST/"
cp docker-compose-testing.yaml "$DEST/"
cp Dockerfile "$DEST/"
cp Dockerfile-prod "$DEST/"
cp Dockerfile-testing "$DEST/"
cp Dockerfile-worker "$DEST/"
cp readme.md "$DEST/"
cp release.md "$DEST/"
cp RoboFile.php "$DEST/"

# Step 9: Extract version from core/config.base.php and create version.json
echo "Creating version.json..."
VERSION=$(grep -o "define('VERSION', '[^']*')" core/config.base.php | sed "s/define('VERSION', '//;s/')//")
# Remove .PRO suffix if present
VERSION=$(echo "$VERSION" | sed 's/\.PRO$//')

# Get current date
RELEASE_DATE=$(date +%Y-%m-%d)

# Create version.json
cat > "$DEST/version.json" << EOF
{
  "version": "$VERSION",
  "releaseDate": "$RELEASE_DATE"
}
EOF

echo "  Version: $VERSION"
echo "  Release Date: $RELEASE_DATE"

# Step 10: Create zip file
echo "Creating icehrm.zip..."
cd releases/extensions
zip -rq "../zips/icehrm.zip" icehrm
cd ../..

echo "=== IceHrm OS Upgrade Release Build Complete ==="
echo "Release folder: releases/extensions/icehrm/"
echo "Zip file: releases/zips/icehrm.zip"
echo "Version: $VERSION"
