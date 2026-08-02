---
name: create-extension
description: Create a new IceHRM extension by running the CLI scaffolding command from the cloud directory
---

Create a new IceHRM extension using the CLI scaffolding tool.

The user should provide:
- **Extension name** — the name of the extension (e.g., `kudos`)
- **Type** — either `admin` or `user`

If the user hasn't specified both values, ask them before proceeding.

Run the following command from the `/` directory:

```bash
php ice create:extension <extension-name> <type>
```

For example, to create an admin extension called "kudos":

```bash
php ice create:extension kudos admin
```

After the command completes:

1. Confirm the extension was created by listing the generated files.
2. Add a build line to `obfuscate-js.sh` for the new extension:
   ```
   gulp ejs --x<extension-name>/<type> -eprod
   ```
   For example, for a `kudos` admin extension, add:
   ```
   gulp ejs --xkudos/admin -eprod
   ```
   Add the line before the `cd extensions/editor/...` block at the end of the file.

When removing an extension, also remove its corresponding `gulp ejs` line from `obfuscate-js.sh`.
