<?php

if (getenv('PS_VERSION_UNDER_TEST') === false || getenv('PS_VERSION_UNDER_TEST') === '') {
    fwrite(STDERR, "PS_VERSION_UNDER_TEST is required\n");
    exit(1);
}

if (getenv('PS_BESTSELLERS_ASSEMBLE_BULK') === false || getenv('PS_BESTSELLERS_ASSEMBLE_BULK') === '') {
    fwrite(STDERR, "PS_BESTSELLERS_ASSEMBLE_BULK is required (0 or 1)\n");
    exit(1);
}

define('_PS_VERSION_', getenv('PS_VERSION_UNDER_TEST'));

$testDir = __DIR__;
$root = dirname($testDir);
require_once $testDir . '/stubs/prestashop.php';
require_once $root . '/ps_bestsellers.php';
require_once $testDir . '/Support/Fixture.php';
require_once $testDir . '/Support/Ps_BestSellersClearSpy.php';
