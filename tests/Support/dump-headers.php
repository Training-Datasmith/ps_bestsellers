<?php

if ($argc < 2) {
    fwrite(STDERR, "Usage: dump-headers.php <path-to-index.php>\n");
    exit(2);
}

$target = $argv[1];
register_shutdown_function(function () {
    echo json_encode(headers_list());
});

ob_start();
include $target;
ob_end_clean();
