#!/usr/bin/env bash
set -euo pipefail
PLUGIN=gatekeeper-ai
VERSION=0.1.0
ZIP="${PLUGIN}-${VERSION}.zip"
EXCLUDES=" -x '*.map' -x 'node_modules/*' -x '.git/*' -x '.github/*' -x '.gitignore' -x 'vendor/*' -x 'tests/*' "
cd "$(dirname "$0")/.."
rm -f "$ZIP"
zip -r "$ZIP" . $EXCLUDES
echo "Built $ZIP"
