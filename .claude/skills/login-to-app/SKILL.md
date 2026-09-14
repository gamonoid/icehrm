---
name: login-to-app
description: Log into the IceHRM app using agent-browser by navigating to the login page and entering credentials
---

Use `agent-browser` to log into the IceHRM application.

First, check that `agent-browser` is available by running `which agent-browser`. If it's not installed, ask the user to install it:
```
npm install -g agent-browser && agent-browser install
```

Once available, run the following steps using `agent-browser` CLI commands:

1. **Close any existing session and navigate to the login page:**
   ```bash
   agent-browser close --all && agent-browser open "http://localhost:9080/app/"
   ```

2. **Take a snapshot to identify form elements:**
   ```bash
   agent-browser snapshot -i
   ```

3. **Fill in credentials and submit** using the refs from the snapshot:
   ```bash
   agent-browser fill <username-ref> "admin" && agent-browser fill <password-ref> "Admin123$" && agent-browser click <login-button-ref>
   ```
   Replace `<username-ref>`, `<password-ref>`, and `<login-button-ref>` with the actual `@eN` refs from the snapshot output.

4. **Verify login success:**
   ```bash
   agent-browser screenshot
   ```
   Check the screenshot to confirm the dashboard has loaded.

If any step fails, take a screenshot (`agent-browser screenshot`) to diagnose the issue and report back what went wrong.
