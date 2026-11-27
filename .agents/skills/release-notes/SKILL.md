---
name: release-notes
description: Build the release note (release-notes/vX.Y.Z.md with screenshots) and the changelog entry in release.md for the next IceHrm release, by comparing develop against the previous release tag. Use when the user is preparing a release or asks for release notes / a changelog.
---

Build two documents for the release the user names (e.g. `36.1.0`):

1. `release-notes/v<version>.md`: the full release note with screenshots.
2. A `## Release note v<version>` entry at the top of `release.md`: the changelog.

Ask for the version and the previous tag if the user didn't give them. The previous tag is
the last release they shipped (`git tag --sort=-v:refname | head`), e.g. `v36.0.2`.

## 1. Check the version bump

`core/config.base.php` must have `VERSION`, `VERSION_NUMBER` (e.g. `360100` for 36.1.0),
`CACHE_VALUE` and `VERSION_DATE` for the new release. `release.sh` refuses to build if
`VERSION_NUMBER` wasn't bumped (`test/version-number-check.php`). If they aren't bumped,
tell the user; don't bump them without asking.

## 2. Find what changed

```bash
git log --oneline <prev-tag>..HEAD
# Changed areas, ignoring built bundles and source maps:
git diff --stat <prev-tag>..HEAD -- . ':!*/dist/*' ':!*.map' \
  | awk '{print $1}' | cut -d/ -f1-3 | sort | uniq -c | sort -rn
git diff --name-status <prev-tag>..HEAD -- core/migrations extensions | grep '^A'
```

Squash-merged PRs hide most of the detail, so read the sources rather than commit titles:

- **Migrations** (`core/migrations/v*.php` added since the tag, plus `extensions/*/*/src/Migrations`):
  the docblock at the top says what each one does. This gives the schema changes, backfills
  and upgrade notes.
- **New extensions**: `extensions/<name>/version.json` and `*/meta.json` (label, menu), then
  the class docblocks in `src/Extension.php`, `src/ApiController.php` and `src/*Service.php`.
  These docblocks describe the feature, its access rules and its endpoints.
- **Seeded content**, e.g. onboarding workflows in `extensions/onboarding/admin/library/*.json`.
- **Core changes**: `core/src/Classes/*Service.php`, `web/shell/src`, `web/admin/src`,
  `web/modules/src`, and deleted email templates in `core/templates/email`.
- **Fixes since the tag** that affect people already running the previous release go under
  Bug Fixes. Bugs found and fixed in features that are new in this release are not bug
  fixes; leave them out.
- **Plan docs** in `docs/*_PLAN.md` explain intent but can be out of date. Confirm in code.

**Verify every specific claim in the code before writing it**: setting names, limits and
defaults (grep the constants), who can see what, and counts (steps, workflows, statuses).
Don't describe something you only inferred from a file name.

## 3. Screenshots

Put them in `release-notes/screenshots/v<major>.<minor>/` as JPGs, named by feature
(`home-employee.jpg`, `give-kudos.jpg`), and reference them as
`screenshots/v<major>.<minor>/<name>.jpg`.

Reuse screenshots already captured in this session if they show the feature cleanly.
Otherwise capture them on the dev stack (`docker compose -f docker-compose-dev.yaml up -d`,
http://localhost:5666/app/ui/, `admin` / `Admin123$`, employee `user1` / `Admin123$`) with
`agent-browser` at a 1440×900 viewport (`agent-browser set viewport 1440 900`, then
`agent-browser screenshot <file>`). See the `login-to-app` skill for logging in.

- Before capturing, replace test data with realistic content: no "QA test …" posts or
  "QA Test Bank" records in release screenshots. Tell the user about any screenshot that
  still shows test data.
- Never show a known bug in a screenshot (e.g. a duplicated person).
- Convert with `sips -s format jpeg -s formatOptions 85 <in.png> --out <out.jpg>`.
- Look at each image before using it.

## 4. Write `release-notes/v<version>.md`

Follow `release-notes/v36.1.0.md` and `release-notes/v36.0.0.md`:

```
# IceHrm - v<major>.<minor>

**Release Date:** <Month Year>
**Version:** <version>

<One paragraph: what this release is about, for an HR admin.>

---

## Table of Contents
...
## <Feature>            one section per feature, bullets as "- **Name** - what it does",
                        each followed by its screenshot(s)
## Other Improvements
## Bug Fixes
## Upgrade Notes        Database Migrations (what changes and what is backfilled),
                        anything that expires or is renamed, New extensions,
                        Requirements, Breaking Changes
```

Write for HR admins, not developers: describe what a person can now do, not class names.
Keep code names only where the admin needs them (a setting name, a menu name).

## 5. Add the changelog entry to `release.md`

Insert `## Release note v<version>` directly under `# Release Notes IceHrmPro`, above the
previous entry, in the same format as the entries below it:

```
## Release note v<version>

Full release notes with screenshots: [release-notes/v<version>.md](release-notes/v<version>.md)

### ☘️ New Features

#### <Feature>
* One line per capability.

### 💼 Improvements
* ...

### 🐛 Bug Fixes
* ...

### 📋 Upgrade Notes
* ...
```

Use `*` bullets (not `-`) and one sentence per bullet. Keep the screenshots out of
`release.md`. `release.md` is excluded from the release zip (`build.xml`), so it is only
read on GitHub.

## 6. Hand back

Don't commit or push unless the user asks. Report: the files created, the screenshots used
(and any showing test data), claims you couldn't verify, and whether the version bump in
`core/config.base.php` is done.
