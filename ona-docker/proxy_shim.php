<?php
// Reverse-proxy shim: this preview proxy terminates TLS and forwards plain
// HTTP to Apache, so PHP reports SERVER_PORT=80 and HTTPS unset. Correct
// $_SERVER from the forwarded headers before the app runs, so generated
// absolute URLs (e.g. xajax's requestURI) match the public https origin.
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}
