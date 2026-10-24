FROM alpine:3.21 AS staticphpbuilder

# Composer version
ARG SPC_COMPOSER=latest-stable

# PHP Version (intended to be >8.6, but keeping at 8.5.x for now)
ARG SPC_PHP_VERSION=8.5.8

# StaticPHP 3.0-alpha1 (nightly)
ARG SPC_VERSION=v3
ARG SPC_BUILD=nightly

# Box Project Releases: https://github.com/box-project/box/releases
ARG SPC_BOX_RELEASE=4.7.0

RUN <<-EOF
    apk update
    apk upgrade -a

    apk add --no-cache \
        autoconf \
        automake \
        gettext \
        bash \
        binutils \
        bison \
        build-base \
        cmake \
        curl \
        file \
        flex \
        g++ \
        gcc \
        git \
        jq \
        libgcc \
        libtool \
        libstdc++ \
        linux-headers \
        m4 \
        make \
        pkgconfig \
        re2c \
        wget \
        xz \
        gettext-dev \
        binutils-gold \
        llvm19
EOF

RUN <<-EOF
    # Fetch pre-built static-php-cli PHP version for Linux
    curl -#fSL "https://dl.static-php.dev/static-php-cli/common/php-${SPC_PHP_VERSION}-cli-linux-$(uname -m).tar.gz" | tar -xz -C /usr/local/bin
    chmod +x /usr/local/bin/php

    # Fetch pre-built static-php-cli binary
    curl -#fSL https://dl.static-php.dev/${SPC_VERSION}/spc-bin/${SPC_BUILD}/spc-linux-$(uname -m) -o spc
    mv spc /usr/local/bin/spc
    chmod +x /usr/local/bin/spc

    # fetch specified composer
    curl -#fSL https://getcomposer.org/download/${SPC_COMPOSER}/composer.phar -o /usr/local/bin/composer
    chmod +x /usr/local/bin/composer

    # fetch specified version of Box
    curl -#fSL "https://github.com/box-project/box/releases/download/${SPC_BOX_RELEASE}/box.phar" -o /usr/local/bin/box
    chmod +x /usr/local/bin/box
EOF

# GNU objcopy/strip aren't multi-arch
RUN ln -sf /usr/bin/llvm-objcopy /usr/local/bin/objcopy && \
    ln -sf /usr/bin/llvm-strip /usr/local/bin/strip

WORKDIR /app
RUN spc doctor --auto-fix -vvv
ARG SPC_TARGET=""
ARG ARCH
ARG SPC_COMPILER_EXTRA=""

COPY ./composer.* /app/
COPY ./craft.yml /app/craft.yml
COPY ./box.json.dist /app/box.json
COPY ./bin /app/bin
COPY ./src /app/src

RUN <<-EOF
    --mount=type=cache,target=/app/downloads,sharing=locked

    if [ -n "$SPC_TARGET" ]; then
        export SPC_EXTRA_PHP_VARS="--host=$SPC_TARGET";
    else
        unset SPC_TARGET SPC_COMPILER_EXTRA;
    fi

    rm -rf /app/tests /app/src/Sculpin/Tests
    composer install --no-dev -a
    spc craft
    box compile
    spc micro:combine bin/sculpin.phar
    mv my-app sculpin-linux-${ARCH:-$(uname -m)}
EOF
