FROM alpine:3.21

RUN apk update && \
    apk upgrade -a && \
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

RUN curl -#fSL https://dl.static-php.dev/static-php-cli/common/php-8.5.8-cli-linux-$(uname -m).tar.gz | tar -xz -C /usr/local/bin && \
    chmod +x /usr/local/bin/php && \
    curl -#fSL https://dl.static-php.dev/v3/spc-bin/nightly/spc-linux-$(uname -m) -o spc && \
    mv spc /usr/local/bin/spc && \
    chmod +x /usr/local/bin/spc

RUN curl -#fSL https://getcomposer.org/download/latest-stable/composer.phar -o /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    curl -#fSL https://github.com/box-project/box/releases/download/4.7.0/box.phar -o /usr/local/bin/box && \
    chmod +x /usr/local/bin/box

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

RUN --mount=type=cache,target=/app/downloads,sharing=locked \
    if [ -n "$SPC_TARGET" ]; then \
        export SPC_EXTRA_PHP_VARS="--host=$SPC_TARGET"; \
    else \
        unset SPC_TARGET SPC_COMPILER_EXTRA; \
    fi && \
    composer install --no-dev && \
    spc craft && box compile && spc micro:combine bin/sculpin.phar && \
    mv my-app sculpin-linux-${ARCH:-$(uname -m)}
