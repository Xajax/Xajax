# AGENTS.md

Repository-specific notes for working on the Xajax PHP library.

## Goal of the current work

Make this (PHP 5.3-era) library run unchanged on PHP 8.2, while remaining a
drop-in replacement for applications written against xajax 0.5. Branch:
`update_php8.2` (fork `blazan`), PR #54 against `Xajax/Xajax`.

## Testing on PHP 8.2

PHP 8.2 is not installed on the host. Run everything through Docker:

```bash
# In-process suite (no web server needed)
docker run --rm -v "$PWD:/repo" -w /repo php:8.2-cli bash -c \
  'docker-php-ext-install -j4 xml >/dev/null 2>&1; php php82_app/selftest.php'
```

- `php82_app/selftest.php` — end-to-end response/XML/JSON checks (exit code 0 = pass).
- `php82_app/verify_*.php` — one script per feature area (changes, compress,
  control, decode, events, legacy, log, plugins, strict, xml). Each exits
  non-zero on failure and prints `passed: N, failed: N`.
- When loading the library from the CLI you must seed `$_SERVER['REQUEST_URI']`,
  `HTTP_HOST`, `SERVER_NAME` and `REQUEST_METHOD` first: the `xajax`
  constructor auto-detects the request URI and calls `exit()` if it cannot.

## Third-party app harnesses (`app-tests/`)

Three real, unmodified old-xajax applications are used as integration test
subjects. Each harness installs the app, swaps in this repo's xajax, and drives
the app's own xajax endpoints over HTTP.

```bash
docker build -f app-tests/Dockerfile -t xajax-php82 .
docker run --rm -v /path/to/app-src:/apps \
  -v "$PWD/app-tests/setup_ds.sh:/setup.sh" \
  -v "$PWD/app-tests/test_ds.sh:/test.sh" \
  xajax-php82 bash -c 'apache2-foreground >/tmp/ap.log 2>&1 & sleep 2; bash /setup.sh; bash /test.sh'
```

Expected: hypertrek 7/7, coach_challenges 7/7, DS_Scheduler 13/13.

Important: several failures in these apps are **app-side PHP 8 bugs**, not xajax
bugs (unquoted array keys, `eregi()`, old JpGraph version guards, `localhost`
PDO socket lookups). `app-tests/fix_ds.php` and the setup scripts document those
fixes so the xajax swap is what is actually being exercised.

## Gotchas fixed for PHP 8.2

- Core includes in `xajax.inc.php` must be `require_once`; `require` inside
  `__wakeup()` redeclares classes when `legacy.inc.php` loads the core first.
- The `xajax` class must not be `final` — xajax 0.5 apps `extends legacyXajax`.
- Deferred script generation: only emit the `<script src=".../deferred/...">`
  tag when the bundle file was actually written; otherwise fall back to inline
  generation, or a read-only `xajax_js/deferred/` silently ships a page with no
  xajax client library.
- `mb_convert_encoding(..., 'HTML-ENTITIES', ...)` was removed; use
  `mb_encode_numericentity`.
- `get_magic_quotes_gpc()`, `utf8_decode()` and `eregi()` are gone.

## Git

- `origin` = `Xajax/Xajax` (upstream, read-only for this token), `fork` =
  `blazan/Xajax` (push here).
- The integration token can push commits to the fork and open PRs, but cannot
  edit the upstream PR description (`Resource not accessible by integration`).
