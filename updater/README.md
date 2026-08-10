# IceHRM Updater

Self-contained web updater. Replaces the IceHRM program files with a newer release,
keeping the deployment's configuration, uploads and any extensions the release does not
ship.

Reach it at `<your-icehrm-url>/updater/` — for a default install that is
`https://example.com/updater/`, alongside `/app/`.

## Why it is independent

The updater replaces `core/`, `web/`, `app/`, `bin/`, `extensions/` and
`extensions-pro/`. It therefore cannot use a single class from any of them — half way
through an update those directories do not exist.

So on **every** invocation it copies `app/config.php` and `core/config.base.php` into
`updater/` and includes those copies instead. Refreshing them each time (rather than
only when missing) matters: a stale `config.base.php` would report the old
`VERSION_NUMBER` forever, making the next update's version check meaningless, and a
rotated database password would break updater sign-in while the application kept
working.

`updater/config.base.php` contains a check that resolves
`__DIR__ . '/../extensions-pro/util/admin/util.php'`. That only works while this
directory sits exactly one level below the IceHRM root. **Do not move `updater/`.**

## What it does

1. **Sign in** — IceHRM administrators only (`Users.user_level = 'Admin'`), in the
   updater's own session (`ICEHRM_UPDATER`). It cannot validate an application session,
   so it never tries: every visit starts logged out.
2. **Preflight** — zip and curl extensions, database reachable, `updater/data/` and the
   IceHRM root writable, enough free disk for the archive + extracted copy + backup.
3. **Download** — Pro installs paste a signed link; the free edition uses the published
   URL. https only, streamed to disk, staged in `updater/data/`.
4. **Validate and extract** — every archive entry is checked for path traversal before
   anything is written, and the expected root directory must be present.
5. **Version check** — `VERSION_NUMBER` from the downloaded `core/config.base.php` must
   be greater than the installed one (compared numerically: it is `'360000'`, not a
   dotted version).
6. **Confirm**, then **install** — see below.
7. **Migrate** — the updater does not touch the database. Schema changes are applied by
   the application when an administrator signs in, so the final screen asks for that
   before checking health.
8. **Health check + cleanup** — on success the backup and staged files are removed. On
   failure, one-click rollback.

## Install strategy: backup, then copy

Each directory being replaced is **renamed** into
`updater/data/backup-<version>-<timestamp>/` — atomic and instant on one filesystem —
and only then is the new one copied in. Nothing is deleted until the health check
passes.

Delete-then-copy would mean a disk-full or permission error part way through leaves a
dead installation with no way back. This way there is always a complete previous version
on disk, and "restore the previous version" is a button rather than a support ticket.

Preserved across an update:

| Path | Why |
|---|---|
| `app/config.php`, `app/config-dev.php` | the deployment's own settings |
| `app/data/`, `app/cache/` | uploads and generated files |
| `extensions/<dir>` not in the release | marketplace and bespoke extensions |
| `updater/config.php`, `updater/data/` | the updater's own state and the backup it is standing on |

`docker/` is **not** copied — `build.xml` excludes it from the release archive, so it is
never present to copy. The root-level `Dockerfile*` and `docker-compose*` files are.

The updater updates itself last.

## Securing `updater/data/`

It holds the downloaded archive and a full extracted copy of the new IceHRM — every PHP
file of it directly invokable over the web before installation. The updater writes an
`.htaccess` (`Require all denied`) into it automatically, which covers Apache.

**nginx ignores `.htaccess`**, so add this to your server block:

```nginx
location ^~ /updater/data {
    deny all;
    return 404;
}
```

The bundled configs in `docker/` already include it.

Consider restricting `/updater/` itself to trusted addresses. It requires an
administrator password, but it is the one endpoint that can replace every file in the
installation.

## Troubleshooting

Every step is logged to `updater/data/update.log`, which survives a failed update and is
the first thing to look at.

If the application does not come back and rollback also fails, the previous files are
still in `updater/data/backup-*/`; move them back by hand, or follow the
[manual upgrade instructions](https://icehrm.com/docs/installation/upgrade).
