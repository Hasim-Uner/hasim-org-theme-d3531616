# Codex Project Instructions

This repository contains the WordPress theme/codebase used for the production website.

## Before changing anything

1. Read `docs/AGENTS.md` completely.
2. Read `docs/change-impact.yml`.
3. Read `docs/architecture.yml` for the affected subsystem.
4. Respect all ownership, dependency and `never_touch` rules defined there.

## Safety rules

- Never read, print, edit or commit secrets, credentials, local configuration or `inc/contact-local.php`.
- Do not add deployment credentials, SSH keys, application passwords or API keys to the repository.
- Do not change production deployment configuration unless the user explicitly asks for it.
- Do not make destructive database, migration, rewrite, sitemap, schema or indexing changes without checking the documented impact first.
- Keep changes minimal and scoped to the requested task.
- Preserve backwards compatibility for public `hp_*` APIs unless an explicit migration is part of the task.

## WordPress conventions

- Follow existing project architecture instead of creating parallel systems.
- Prefer existing helpers, modules and conditional asset loading.
- Do not expand `style.css` for feature-specific CSS when an existing conditional stylesheet is appropriate.
- Do not edit generated/minified assets when a source file exists.
- New WordPress meta fields must include appropriate sanitization, REST exposure rules and authorization callbacks where relevant.
- Slug/rewrite changes require a redirect and sitemap/indexing impact check.

## Required validation

For PHP or architecture changes, run the relevant repository checks before considering the task complete:

- PHP lint for changed PHP files.
- `composer validate --no-check-publish`
- `composer install --prefer-dist --no-progress --no-interaction`
- `composer run analyse`
- `php scripts/check-manifest.php`
- `php scripts/generate-wp-docs.php`
- Verify generated architecture docs remain committed and consistent.

If a check cannot be run, state exactly which check was skipped and why.

## Git workflow

- Work on a task branch.
- Do not push directly to `main` unless explicitly instructed.
- Prefer a focused pull request with a concise explanation of the change, risk and validation performed.
- Do not mix unrelated refactors into the requested task.

## Production boundary

GitHub is the source-code workflow. Production hosting/deployment is handled separately. Codex should modify repository code and use the existing Git workflow; it should not attempt to log into the hosting environment or invent new deployment access unless explicitly requested.
