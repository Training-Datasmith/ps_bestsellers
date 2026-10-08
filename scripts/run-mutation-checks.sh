#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
MODULE="$ROOT/ps_bestsellers.php"
BACKUP="$ROOT/ps_bestsellers.php.mutation-bak"
PHP_IMAGE='php:5.6.40-cli'
PHPUNIT_PHAR='/tmp/phpunit-4.8.36.phar'
DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

run_test() {
  local version="$1"
  local bulk="$2"
  local filter="$3"
  "${DOCKER[@]}" run --rm \
    -v "$ROOT":/src \
    -v "$PHPUNIT_PHAR":/phpunit.phar:ro \
    -w /src/tests \
    -e PS_VERSION_UNDER_TEST="$version" \
    -e PS_BESTSELLERS_ASSEMBLE_BULK="$bulk" \
    "$PHP_IMAGE" \
    php /phpunit.phar -c phpunit.xml --filter "$filter" >/dev/null
}

expect_fail() {
  local name="$1"
  local mutation_cmd="$2"
  local version="$3"
  local bulk="$4"
  local filter="$5"
  cp "$MODULE" "$BACKUP"
  eval "$mutation_cmd"
  set +e
  run_test "$version" "$bulk" "$filter"
  local code=$?
  set -e
  restore
  if [[ "$code" -eq 0 ]]; then
    echo "MUTATION NOT DETECTED: $name" >&2
    exit 1
  fi
  echo "mutation detected: $name"
}

restore() {
  mv "$BACKUP" "$MODULE"
}

expect_fail install-chain \
  "sed -i 's/&& Configuration::updateValue/|| Configuration::updateValue/' \"$MODULE\"" \
  1.7.5.0 1 'PsBestSellersInstallTest::testInstallStopsWhenConfigUpdateFails'

expect_fail catalog-mode \
  "sed -i 's/Configuration::get('\''PS_CATALOG_MODE'\'')/false/' \"$MODULE\"" \
  1.7.5.0 1 'PsBestSellersWidgetTest::testCatalogModeSkipsSearch'

expect_fail int-cast \
  "sed -i 's/(int) Configuration::get('\''PS_BLOCK_BESTSELLERS_TO_DISPLAY'\'')/Configuration::get('\''PS_BLOCK_BESTSELLERS_TO_DISPLAY'\'')/' \"$MODULE\"" \
  1.7.5.0 1 'PsBestSellersQueryTest::testQueryUsesConfiguredPageSizeAndSalesSort'

expect_fail presenter-cutoff \
  "sed -i \"s/version_compare(_PS_VERSION_, '1.7.5', '>=')/version_compare(_PS_VERSION_, '9.9', '>=')/\" \"$MODULE\"" \
  1.7.5.0 1 'PsBestSellersQueryTest::testPresenterClassFollowsPinnedVersionMap'

expect_fail bulk-branch \
  "python3 -c \"from pathlib import Path; p=Path('$MODULE'); t=p.read_text(); t=t.replace(\\\"\\\$assembleInBulk = method_exists(\\\$assembler, 'assembleProducts');\\\", '\\\$assembleInBulk = false;'); p.write_text(t)\"" \
  1.7.5.0 1 'PsBestSellersQueryTest::testPresenterReceivesAssembledRowsAndContextLanguage'

expect_fail cache-hit \
  "sed -i 's/if (!\$this->isCached/if (false \&\& !\$this->isCached/' \"$MODULE\"" \
  1.7.5.0 1 'PsBestSellersWidgetTest::testCacheHitSkipsRebuild'

echo "All mutation checks failed as expected when broken."
