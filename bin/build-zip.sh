#!/usr/bin/env bash
# Package the plugin folder as dist/lotalytics-lottery-results-<version>.zip,
# the file uploaded to wordpress.org (and to SVN on release). Only the plugin
# folder ships: dev/, bin/ and repo files never do.
set -euo pipefail
cd "$(dirname "$0")/.."
SLUG=lotalytics-lottery-results
VERSION=$(sed -n 's/^ \* Version: *//p' "$SLUG/$SLUG.php" | tr -d '[:space:]')
STABLE=$(sed -n 's/^Stable tag: *//p' "$SLUG/readme.txt" | tr -d '[:space:]')
[ "$VERSION" = "$STABLE" ] || { echo "plugin header $VERSION != readme Stable tag $STABLE" >&2; exit 1; }
mkdir -p dist
rm -f "dist/$SLUG-$VERSION.zip"
zip -qr "dist/$SLUG-$VERSION.zip" "$SLUG" -x '*.DS_Store'
echo "dist/$SLUG-$VERSION.zip"
