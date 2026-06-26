---
slug: update-agents-md-plan
status: planned
intent: clear
pending-action: wait for user to start execution
approach: Update the existing root AGENTS.md in place, preserving the current Docs-routing and MVP guardrails while adding only verified command, topology, and workflow notes future agents would otherwise guess wrong.
---

# Draft: update-agents-md-plan

## Components (topology ledger)
<!-- Lock the SHAPE before depth. One row per top-level component that can succeed or fail independently. -->
<!-- id | outcome (one line) | status: active|deferred | evidence path -->
- docs-routing | preserve Docs/00_DOCS_INDEX-first rule and selective-doc reading caps | active | AGENTS.md:1-14; Docs/00_DOCS_INDEX.md:3-7
- command-surface | document exact setup/dev/build/test commands and explicitly note missing lint/CI wrappers | active | composer.json:34-69; package.json:5-15; phpunit.xml:20-35
- repo-topology | describe preview-only routes and where custom work actually lives | active | bootstrap/app.php:8-21; routes/web.php:5-7; resources/views/auth/login.blade.php:13-15; resources/css/app.css:7-26; resources/js/app.js:7-49
- data-and-testing | document migrations/seeders as source of truth and testing defaults | active | database/seeders/DatabaseSeeder.php:12-18; database/seeders/SampleDataSeeder.php:15-152; phpunit.xml:21-35

## Open assumptions (announced defaults)
<!-- Record any default you adopt instead of asking, so the user can veto it at the gate. -->
<!-- assumption | adopted default | rationale | reversible? -->
- language | keep AGENTS.md primarily in concise English while retaining the existing Indonesian-specific doc-routing wording only if needed for precision | aligns with user request for compact agent-facing instructions and existing repo usage | reversible
- verification depth | require focused verification (`composer test` and `npm run build`) instead of broader manual app QA for this doc-only change | executable source of truth exists; change is documentation-only | reversible

## Findings (cited - path:lines)
- Current AGENTS.md is minimal and only covers token-saving/doc-selection/MVP guardrails; it lacks command surface and repo topology. (`AGENTS.md:1-14`)
- The project README is stock Laravel and should not be treated as repo-specific guidance. (`README.md:1-58`)
- `composer setup` is the only verified bootstrap flow and includes `.env` copy, key generation, migration, npm install with `--ignore-scripts`, and frontend build. (`composer.json:35-42`)
- `composer dev` launches four concurrent processes: server, queue listener, log tail, and Vite dev server. (`composer.json:43-46`)
- `composer test` clears config before running the Laravel test suite. (`composer.json:47-50`)
- Tests run against in-memory SQLite with sync queue/array cache-session settings. (`phpunit.xml:21-35`)
- The HTTP surface is still preview-only; routes point directly to Blade views for login/admin/employee previews. (`routes/web.php:5-7`)
- The login view explicitly says auth logic is not built yet. (`resources/views/auth/login.blade.php:13-15`)
- Custom UI conventions live in Blade components/layouts, Tailwind theme tokens, and JS `data-*` hooks for sidebar/modal behavior. (`resources/views/admin/dashboard.blade.php:10-12`; `resources/css/app.css:7-26`; `resources/js/app.js:7-49`)
- Database setup is driven by migrations plus two seeders: admin seed and sample training-domain data. (`database/seeders/DatabaseSeeder.php:14-17`; `database/seeders/AdminUserSeeder.php:12-20`; `database/seeders/SampleDataSeeder.php:15-152`)
- No repo-local CI workflows, pre-commit config, Copilot instructions, CLAUDE.md, or Cursor rules were found. (`Test-Path` checks in repo root; composer/package files also define no such wrappers.)

## Decisions (with rationale)
- Edit the existing root `AGENTS.md` in place rather than creating a separate instruction file, because the repo already routes internal commands to that file. (`.opencode/commands/plan-feature.md:7-15`; `.opencode/commands/build-crud.md:7-19`)
- Keep the final AGENTS content short and repo-specific; omit generic Laravel advice and any unverified team workflow.
- Treat `composer.json`, `package.json`, `phpunit.xml`, and current route/view files as source of truth over prose docs when there is overlap.
- Include one explicit warning that this codebase is a UI/data foundation with preview screens, not a completed auth/business-flow app, to stop future agents from coding against nonexistent endpoints.

## Scope IN
- Root `AGENTS.md` content refresh.
- Preserve verified existing guidance from the current AGENTS file.
- Add verified commands, topology notes, testing defaults, and operational/documentation gotchas that change agent behavior.

## Scope OUT (Must NOT have)
- No product-code edits.
- No creation of CI, Husky, pre-commit, CLAUDE, Copilot, or Cursor instruction files.
- No speculative policies, release workflow, or unsupported lint commands.
- No expansion beyond MVP or implication that auth/business features already exist.

## Open questions
- None. Approval received; plan is decision-complete.

## Approval gate
status: approved
<!-- When exploration is exhausted and unknowns are answered, set status: awaiting-approval. -->
<!-- That durable record is the loop guard: on a later turn read it and resume at the gate instead of re-running exploration. -->
