#!/bin/sh
set -e
if [ "${SCULPIN_STATIC_BUILDER:-}" != "1" ]; then
    echo "bin/static-build.sh must run inside the static-builder container," >&2
    echo "use bin/docker-static-build.sh instead" >&2
    exit 1
fi

OUT="sculpin-${SPC_TARGET:-$(uname -m)-linux-musl}"

rm -rf tests src/Sculpin/Tests

composer install --no-dev -a

mkdir -p downloads
spc craft
box compile
# strip micro.sfx BEFORE combining: micro appends the the phar after the ELF section
strip --strip-debug "${BUILD_ROOT_PATH:-$PWD/buildroot}/bin/micro.sfx"
spc micro:combine bin/sculpin.phar
mv my-app "$OUT"
