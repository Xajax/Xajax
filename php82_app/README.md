# Xajax PHP 8.2 test app

A small application that exercises the full server-side feature set of this
Xajax library on PHP 8.2. It exists to verify the PHP 8.2 compatibility changes
on this branch.

## Files

| File | Purpose |
| --- | --- |
| `index.php` | The application. Registers every demo and renders the buttons. |
| `xajax_handlers.inc.php` | Handler functions, the callable object, event handlers and processing events. |
| `selftest.php` | Automated checks. Runs in-process or over HTTP. |

## Run it

Serve the repository root with PHP 8.2 and open `php82_app/index.php`:

```sh
php -S 0.0.0.0:8080 -t .
```

Then open `http://localhost:8080/php82_app/index.php`.

The app sits one directory below the library, so it sets `javascript URI` to
`../` and `requestURI` to its own file name. That keeps it working behind any
proxy or port. Debug output opens a popup window, so it is opt-in:

```
http://localhost:8080/php82_app/index.php?debug=1
```

## Self test

In-process (no server needed):

```sh
php php82_app/selftest.php
```

Over HTTP against a running server:

```sh
php php82_app/selftest.php http://localhost:8080/php82_app
```

The suite prints one line per check and a final pass/fail count.

## What is covered

- Function calls with fixed, input and direct values, and parameter types.
- The response command set: assign, append, prepend, replace, clear, remove,
  create, insert, alert, confirmCommands, debug, redirect, call, script, waitFor,
  includeScript, includeCSS, removeScript, removeCSS, setReturnValue and more.
- DOM commands, form value collection, UTF-8 round trips and entity encoding.
- JSON and XML response types, and a custom response class.
- Callable objects, events with one and two handlers, and processing events.
- Error handling through the xajax error handler.
