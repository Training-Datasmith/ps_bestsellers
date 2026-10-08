#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP_IMAGE_544='php@sha256:8fd934196b1eb1cdc32f3301801272b6d1343186d842c244cf3fcb62ac200fc5'
PHP_IMAGE_564='php@sha256:36c3c974e6ee402fdf62d52d3fb918ad97653661e10d9b59dac86b5f1fc52dd3'
# Pinned 5.6.40-cli when plan digests cannot be pulled or run (containerd >= 2.1)
PHP_IMAGE_564_PINNED='php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143'
PHPUNIT_PHAR='/tmp/phpunit-4.8.36.phar'
PHPUNIT_ASC='/tmp/phpunit-4.8.36.phar.asc'
PHPUNIT_SHA='ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e'
PHPUNIT_KEY_FP='D8406D0D82947747293778314AA394086372C20A'

ensure_phpunit() {
  if [[ ! -f "$PHPUNIT_PHAR" ]]; then
    curl -fsSL -o "$PHPUNIT_PHAR" https://phar.phpunit.de/phpunit-4.8.36.phar
    curl -fsSL -o "$PHPUNIT_ASC" https://phar.phpunit.de/phpunit-4.8.36.phar.asc
  fi
  echo "${PHPUNIT_SHA}  ${PHPUNIT_PHAR}" | sha256sum -c -
  gpg --batch --quiet --keyserver hkps://keyserver.ubuntu.com --recv-keys "$PHPUNIT_KEY_FP"
  gpg --batch --verify "$PHPUNIT_ASC" "$PHPUNIT_PHAR"
}

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

select_image() {
  if "${DOCKER[@]}" pull "$PHP_IMAGE_544" >/dev/null 2>&1 \
    && "${DOCKER[@]}" run --rm "$PHP_IMAGE_544" php -v >/dev/null 2>&1; then
    PHP_IMAGE="$PHP_IMAGE_544"
    echo "Using PHP 5.4.45-cli image $PHP_IMAGE"
    return
  fi
  echo "DEVIATION: php:5.4.45-cli unavailable (manifest v1 / pull or start failure); trying php:5.6.40-cli digest from plan" >&2
  if "${DOCKER[@]}" pull "$PHP_IMAGE_564" >/dev/null 2>&1 \
    && "${DOCKER[@]}" run --rm "$PHP_IMAGE_564" php -v >/dev/null 2>&1; then
    PHP_IMAGE="$PHP_IMAGE_564"
    echo "Using PHP 5.6.40-cli image $PHP_IMAGE"
    return
  fi
  echo "DEVIATION: plan php:5.6.40-cli digest failed; using pinned ${PHP_IMAGE_564_PINNED}" >&2
  "${DOCKER[@]}" pull "$PHP_IMAGE_564_PINNED"
  if ! "${DOCKER[@]}" run --rm "$PHP_IMAGE_564_PINNED" php -v >/dev/null 2>&1; then
    echo "ERROR: pinned PHP 5.6 image ${PHP_IMAGE_564_PINNED} could not run" >&2
    exit 1
  fi
  PHP_IMAGE="$PHP_IMAGE_564_PINNED"
}

run_once() {
  local version="$1"
  local bulk="$2"
  local config="$3"
  local shuffle_seed="${4:-}"
  local -a docker_env=(
    -e "PS_VERSION_UNDER_TEST=${version}"
    -e "PS_BESTSELLERS_ASSEMBLE_BULK=${bulk}"
  )
  if [[ -n "$shuffle_seed" ]]; then
    docker_env+=( -e "TEST_SHUFFLE_SEED=${shuffle_seed}" )
  fi
  "${DOCKER[@]}" run --rm \
    -v "$ROOT":/src \
    -v "$PHPUNIT_PHAR":/phpunit.phar:ro \
    -w /src/tests \
    "${docker_env[@]}" \
    "$PHP_IMAGE" \
    php /phpunit.phar -c "$config"
}

ensure_phpunit
select_image

echo "=== default matrix (pass 1) ==="
run_once 1.7.0.0 0 phpunit.xml
run_once 1.7.4.4 0 phpunit.xml
run_once 1.7.5 1 phpunit.xml
run_once 1.7.5.0 1 phpunit.xml
run_once 8.1.0 1 phpunit.xml

echo "=== default matrix (pass 2) ==="
run_once 1.7.0.0 0 phpunit.xml
run_once 1.7.4.4 0 phpunit.xml
run_once 1.7.5 1 phpunit.xml
run_once 1.7.5.0 1 phpunit.xml
run_once 8.1.0 1 phpunit.xml

echo "=== shuffled seed 20261008 (pass 1) ==="
run_once 1.7.5.0 1 phpunit-shuffled.xml 20261008

echo "=== shuffled seed 20261008 (pass 2) ==="
run_once 1.7.5.0 1 phpunit-shuffled.xml 20261008

if [[ "${RUN_OPTIONAL_SHUFFLE_SEED42:-}" == "1" ]]; then
  echo "=== optional shuffled seed 42 ==="
  run_once 1.7.5.0 1 phpunit-shuffled.xml 42
fi
