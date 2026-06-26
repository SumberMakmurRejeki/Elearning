status: awaiting-approval
pending_action: write .omo/plans/update-agents-md.md
intent: clear
request: Create or update AGENTS.md for this repository with compact, verified repo-specific guidance.

components:
  - id: docs-routing
    outcome: Preserve and tighten the existing Docs/00_DOCS_INDEX-first reading rule and MVP scope guard.
    status: verified
    evidence:
      - AGENTS.md:1-14
      - Docs/00_DOCS_INDEX.md:3-7
  - id: command-surface
    outcome: Document exact setup/dev/build/test commands and note missing lint/CI wrappers.
    status: verified
    evidence:
      - composer.json:34-69
      - package.json:5-15
      - phpunit.xml:7-35
      - Docs/09. Deployment & Ops Doc.md:338-405
  - id: repo-topology
    outcome: Summarize the real implementation surface so future agents do not assume a finished app.
    status: verified
    evidence:
      - bootstrap/app.php:8-21
      - routes/web.php:3-7
      - resources/views/auth/login.blade.php:1-20
      - resources/views/admin/dashboard.blade.php:1-62
      - resources/views/employee/dashboard.blade.php:1-50
      - resources/css/app.css:1-32
      - resources/js/app.js:1-49
      - database/seeders/DatabaseSeeder.php:12-18
      - database/seeders/SampleDataSeeder.php:15-152
      - database/migrations/*.php

findings:
  - README.md is still the stock Laravel README and should not drive AGENTS guidance.
  - The current AGENTS file only contains token-saving/doc-routing rules; it misses actual run/test/build commands and project topology.
  - composer setup is the only verified bootstrap sequence: install deps, copy .env, key:generate, migrate --force, npm install --ignore-scripts, npm run build.
  - composer dev starts four concurrent processes: artisan serve, queue:listen, pail, and vite.
  - composer test clears config first, then runs artisan test; tests use sqlite :memory: via phpunit.xml.
  - No repo-local CI workflow, pre-commit config, CLAUDE.md, Copilot instructions, or cursor rules were found.
  - routes/web.php exposes only preview routes (/ , /ui-preview/admin, /ui-preview/employee); auth/business logic is not implemented yet.
  - Custom work is concentrated in Blade components/layout previews, Tailwind theme tokens, JS data-* hooks, training-domain migrations, and seeders.

approach:
  - Update AGENTS.md in place as a compact instruction file.
  - Preserve the existing Docs reading rules and MVP guardrails.
  - Add only verified, high-signal items: exact commands, missing command caveats, preview-only app status, where custom UI/schema work lives, and deployment/testing gotchas.
  - Omit generic Laravel advice and speculative workflow rules.
