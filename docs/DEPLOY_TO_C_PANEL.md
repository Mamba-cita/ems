# Deploying to cPanel from GitHub (Deploy HEAD Commit)

This project includes a GitHub Actions workflow that can trigger cPanel's "Deploy HEAD Commit" for a cPanel-managed Git repository.

Overview
- Workflow: `.github/workflows/deploy-to-cpanel.yml`
- Trigger: `push` to `main` or `workflow_dispatch` (manual run in Actions UI)
- Uses cPanel UAPI `Git::deploy_head` to request a HEAD deployment of the configured repository on the cPanel account.

Required GitHub Secrets (recommended scope: Environment `production` secrets)
- `CPANEL_HOST` — your cPanel host (e.g., `example.com`)
- `CPANEL_USER` — your cPanel username (account that manages the repository)
- `CPANEL_API_TOKEN` — an API token created in cPanel (see below)
- `CPANEL_REPOSITORY_PATH` — the repository path as cPanel knows it, e.g. `/home/your_cpanel_user/repositories/ems` or the repository identifier shown in cPanel's Git Version Control UI

Create an API token in cPanel
1. Log into cPanel as the account that owns the repository.
2. Go to **Security** → **Manage API Tokens** (or search for "API Tokens").
3. Create a new API token (give it a descriptive name like `github-deploy-token`).
4. Copy the token — you will not be able to view it again. Save it to GitHub Secrets as `CPANEL_API_TOKEN`.

Repository path
- In cPanel's *Git Version Control* area you'll see the repository and a path or name used by cPanel. Use that exact path (or the repository clone URL) as the `CPANEL_REPOSITORY_PATH` secret.

Security and approvals
- The workflow references a GitHub **Environment** named `production` so you can require manual approvals or reviewers before the `deploy` job runs. Configure the `production` Environment in your repository settings → Environments.
- The workflow does not write any production `.env` values. Keep production secrets in GitHub Environments.

How it works
- The Action sends a GET request to: `https://$CPANEL_HOST:2083/execute/Git/deploy_head?repository=$CPANEL_REPOSITORY_PATH` with an `Authorization: cpanel user:APITOKEN` header.
- cPanel will attempt to perform its normal "Deploy" / "Pull" operation for the repository and will log activity in cPanel's Git Version Control UI.

Troubleshooting
- If you see a 403 or authentication error, double-check the token and the username.
- If the response indicates the repository is not found, ensure `CPANEL_REPOSITORY_PATH` matches the path in cPanel (you can get this from the repo details page).

If you want, I can:
- Add a small step that writes a short `deploy.log` file to the repo or to your server via SSH after successful deploy verification.
- Add richer parsing of the UAPI JSON response (using `jq`) and fail the Action on explicit error codes.

Tell me if you'd like me to wire in a post-deploy check (eg: call your health endpoint and fail the workflow if it returns non-200).