# OpenNetAdmin + PHP 8.2 xajax integration test

This directory runs a **real, unmodified MVC application that bundles an old
(2007-era) copy of xajax**, then swaps in the PHP 8.2-compatible xajax from
this repository, to prove the new build is a working drop-in replacement.

The app is [OpenNetAdmin](https://github.com/opennetadmin/ona) (ONA) v19.0.3,
a PHP/ADOdb network-management web app. ONA ships its own *customised* xajax
under `www/include/xajax/` (with extra `magic_quotes`/`utf8_decode` handling),
plus a set of integration wrappers outside that directory
(`xajax_webwin`, `xajax_drag`, `xajax_suggest`, `include/xajax_setup.inc.php`)
that are left untouched.

## What the image does

1. Installs PHP 8.2 (Apache), MariaDB, and the extensions ONA needs.
2. Downloads ONA at a pinned commit and extracts it to `/opt/ona`.
3. **Replaces** `/opt/ona/www/include/xajax/` with this repository's
   `xajax_core/`, `xajax_js/`, `xajax_plugins/` and `copyright.inc.php`.
4. Adds a small PHP shim (`proxy_shim.php`) so xajax builds the correct public
   `https://` request URI when running behind a TLS-terminating proxy.
5. On first start, installs ONA into MariaDB and starts Apache.

## Build and run

Build context is the repository root (so the image can copy this repo's xajax):

```bash
docker build -f ona-docker/Dockerfile -t ona-php82 .
docker run -d --name ona82 -p 12001:80 ona-php82
```

Then open `http://localhost:12001/ona/` and log in as `admin` / `admin`.

## Automated test

`test_xajax.sh` drives ONA through xajax over HTTP and checks the JSON
responses. Point it at a running instance with `ONA_URL` (defaults to
`http://localhost:12001/ona`):

```bash
ONA_URL=http://localhost:12001/ona bash ona-docker/test_xajax.sh
```

It verifies:

- login via the xajax `window_submit` call returns "Success"
- the authenticated page shows the logged-in user
- the page loads xajax core JS and the webwin plugin JS
- the `tooltips`, `edit_domain` editor and `search_results` windows all return
  valid xajax JSON (`{"xjxobj":[...]}`)

## Verified

On PHP 8.2.35 the swap produced **zero errors from the xajax library** (all
remaining PHP notices/deprecations come from ONA's own code and its bundled
ADOdb). Login, window management, tooltips, editors, search and a full
"add subnet" round-trip (form submit → DB write → rendered result) all work in
a real browser session.
