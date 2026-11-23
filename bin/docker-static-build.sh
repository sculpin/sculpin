#!/bin/sh
# Usage: bin/docker-static-build.sh [zig target triple] (default: $(uname -m)-linux-musl)
set -e
cd "$(dirname "$0")/.."

ARCH="$(uname -m)"
TARGET="${1:-${ARCH}-linux-musl}"

docker buildx build --load --network=host -t "sculpin-builder:$ARCH" -f Dockerfile_staticbuild .
docker rm -f "sculpin-build-$TARGET" >/dev/null 2>&1 || true

# persist spc/pkg caches across runs
docker create --name "sculpin-build-$TARGET" \
  -e SPC_TARGET="$TARGET" -e GITHUB_TOKEN \
  -v "$PWD/logs:/app/log" \
  -v "sculpin-spc-downloads:/app/downloads" \
  -v "sculpin-spc-source-$TARGET:/app/source" \
  -v "sculpin-spc-buildroot-$TARGET:/app/buildroot" \
  -v "sculpin-spc-pkgroot:/app/pkgroot/$ARCH-linux" \
  -v "sculpin-spc-composer:/root/.cache/composer" \
  -v "sculpin-spc-vendor:/app/vendor" \
  "sculpin-builder:$ARCH" >/dev/null

docker start -a "sculpin-build-$TARGET"

mkdir -p build
docker cp "sculpin-build-$TARGET:/app/sculpin-$TARGET" build/
docker rm "sculpin-build-$TARGET" >/dev/null
