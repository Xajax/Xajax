# App integration tests

These harnesses prove that this branch's PHP 8.2 build of xajax is a drop-in
replacement for the old 0.5-era xajax shipped inside real third-party
applications. Each one swaps the application's bundled `xajax_core/` and
`xajax_js/` for the ones built from this repository, then drives the app's real
xajax endpoints over HTTP and asserts on the returned command packets.

They complement `php82_app/` (which exercises the server-side feature set in
isolation) by running the library inside unmodified applications.

| App | Upstream | Entry | Checks |
| --- | --- | --- | --- |
| hypertrek | `luigirosa/hypertrek` | `/hypertrek/index.php` | 7 |
| coach_challenges | `nwollmann/coach_challenges` | `/coach/index.php` | 7 |
| DS_Scheduler | `daveseff/DS_Scheduler` | `/ds/index.php` | 13 |

## Running

The harnesses run inside the `xajax-scratch` image, which is `php:8.2-apache`
with the usual extensions (mysqli, pdo_mysql, gd, ...) and the xajax sources
staged at `/src`. Build it from the repository root:

```sh
docker build -t xajax-scratch:latest -f app-tests/Dockerfile .
```

Then run one harness, mounting the app checkout and the scripts:

```sh
docker run --rm \
  -v /path/to/apps:/apps \
  -v "$PWD/app-tests/setup_hypertrek.sh:/setup.sh" \
  -v "$PWD/app-tests/test_hypertrek.sh:/test.sh" \
  xajax-scratch:latest \
  bash -c 'apache2-foreground & sleep 2; bash /setup.sh; bash /test.sh'
```

`setup_*.sh` loads a database, copies the app into the document root, swaps in
the new xajax and applies any app-side PHP 8 fixes. `test_*.sh` performs the
functional checks and prints `TOTAL pass=N fail=M`.

## App-side fixes are not library fixes

Each `setup_*.sh` carries comments marking every change it makes to the
application. Those changes exist only because the applications themselves are
not PHP 8 clean. They are deliberately kept separate from the xajax library:

- **JpGraph version guard** (DS_Scheduler): the bundled JpGraph 2.x refuses to
  run on any PHP major other than 5.
- **Unquoted array keys** (DS_Scheduler): `{$jobs[id]}` and `$jobs[id]` are
  treated as undefined constants by PHP 8; the harness quotes them.
- **Absolute include paths** (coach_challenges): the app hardcodes
  `/model/...` and `DOCUMENT_ROOT`-relative paths, so the harness provides
  symlinks rather than editing the app.

None of these are xajax defects; they are the kinds of breakage any 2007-era
PHP application hits on PHP 8.
