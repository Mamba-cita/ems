# Deploy via SSH (GitHub Actions)

This workflow lets GitHub push code to your server over SSH and run post-deploy commands.

Required GitHub Environment: `production` (recommended — use protected secrets and approvals).

Secrets to add to `production` Environment
- PROD_SSH_HOST — server hostname or IP (e.g., example.com)
- PROD_SSH_USER — SSH username (e.g., deploy)
- PROD_SSH_PRIVATE_KEY — SSH private key (PEM), corresponding public key must be in the server's `~/.ssh/authorized_keys`
- PROD_DEPLOY_PATH — absolute path on the server where the repo should be deployed (e.g., `/home/deploy/app`)
- PROD_SSH_PORT — optional if using a non-standard SSH port (default: 22)
- PROD_HEALTHCHECK_URL — optional URL to verify deployment (e.g., https://example.com/health)

Notes
- The workflow uses `rsync` to transfer files, excluding `.git`, `.env`, `vendor/`, `node_modules/`, and other local artifacts.
- Post-deploy, the action runs `composer install` (or `php composer.phar` if present), runs `php artisan migrate --force`, and caches config/route.
- Keep production secrets in the GitHub Environment (not repo secrets) and protect the environment with required reviewers for manual approval.

Server setup checklist
1. Add the provided public key to `/home/youruser/.ssh/authorized_keys` on the server.
2. Ensure `rsync`, `php`, and `composer` are available on the server, and that the deploy user has permission to write to `PROD_DEPLOY_PATH` and run needed commands.
3. (Optional) Create a maintenance page or health endpoint (`/health`) for the `PROD_HEALTHCHECK_URL`.

Security
- The workflow excludes `.env` and does NOT write any production secrets; keep the server `.env` managed securely on the server.

If you want, I can:
- Use `scp` instead of `rsync` (less efficient but simpler) or add additional steps such as clearing caches, restarting PHP-FPM, or creating a symlinked `current` release dir for zero-downtime deploys.
- Add an approval gate (require a reviewer to approve the `production` environment before deploy runs).

Tell me if you'd like any of those enhancements (zero-downtime, service restarts, or stricter approval requirements).