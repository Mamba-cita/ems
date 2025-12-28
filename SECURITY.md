# Security & Hardening Checklist 🔐

This document lists actionable steps to make the application and deployment more secure.

## Repository & CI
- Enable GitHub **Branch Protection** on `main` and require status checks (CI and `security/scan`).
- Use GitHub **Environments** for production with required reviewers and secrets.
- Add and enforce **2FA** for all collaborators.
- Add a secret scanning job (`.github/workflows/security-scan.yml`) to detect accidental secrets in PRs. (Added in repo.)

## Secrets
- Never commit `.env` or secrets. Rotate any secrets that were committed historically.
- Use GitHub Environment secrets (`production`) for deploy credentials and API keys.
- Use a secrets manager (Vault, AWS Secrets Manager, etc.) for production-level secret rotation.

## Server & App
- Set Document Root to `public/` in cPanel for the domain.
- Ensure `storage` and other app internals are not web-accessible.
- Keep PHP, Composer, and system packages updated.
- Use HTTPS (HSTS header already added) and obtain a valid TLS certificate.

## Web Hardening
- Deny access to sensitive files (`.env`, `.git`, backups, composer files). (Already added.)
- Add strict security headers (X-Frame-Options, CSP, HSTS) — implemented in `public/.htaccess`.
- Disable directory listings (implemented in root `.htaccess`).

## Monitoring & Incident Response
- Enable GitHub Secret Scanning (if on GitHub Advanced Security) and configure alerts.
- Add basic health & uptime monitoring for the production site.
- Prepare a rotation plan for exposed secrets and a contact plan for your host.

If you'd like, I can: add branch protection rules guidance, wire a managed secret rotation script, or set up a zero-downtime deploy strategy.