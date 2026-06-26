# update-agents-md-plan - Work Plan

## TL;DR (For humans)
<!-- Fill this LAST, after the detailed plan below is written, so it summarizes the REAL plan. -->
<!-- Plain English for a non-engineer: NO file paths, NO todo numbers, NO wave/agent/tool names. -->

**What you'll get:** A tighter root instruction file that tells future agents exactly how to read the project docs, which commands to trust, and what this repository actually is today. It will preserve the current guardrails while adding the missing repo-specific workflow notes.

**Why this approach:** The existing file already contains the highest-value local rule, so the safest path is to improve it in place. The rest of the guidance will come only from executable repo truth and the few preview/data files that reveal the real project shape.

**What it will NOT do:** It will not change application code or add new agent-policy files.
It will not invent lint, CI, auth, or release workflows that the repo does not actually have.
It will not broaden scope beyond the current MVP foundation.

**Effort:** Quick
**Risk:** Low - documentation-only change, but it must avoid false claims.
**Decisions to sanity-check:** Keep the file compact; describe the app as preview-first rather than feature-complete; call out missing lint/CI wrappers instead of guessing them.

Your next move: Start execution when ready; the worker should update only `AGENTS.md` and verify the result with focused checks. Full execution detail follows below.

---

> TL;DR (machine): Quick, low-risk doc-only update to root AGENTS.md preserving existing doc-routing rules and adding verified commands, topology, and guardrails.

## Scope
### Must have
- Preserve the existing `Docs/00_DOCS_INDEX.md`-first reading rule, selective-doc limits, role-based doc priorities, and MVP scope guard from the current `AGENTS.md`.
- Update root `AGENTS.md` with only verified, repo-specific guidance future agents would otherwise guess wrong.
- Document exact command surface: `composer setup`, `composer dev`, `composer test`, `npm run dev`, `npm run build`, plus the fact that no repo-defined lint/CI/pre-commit wrapper exists.
- Explain that the current app surface is preview-only and custom work is concentrated in Blade components/layouts, Tailwind theme tokens, JS `data-*` hooks, migrations, and seeders.
- Note testing defaults that affect safe verification: `composer test` clears config and uses in-memory SQLite.
### Must NOT have (guardrails, anti-slop, scope boundaries)
- Must NOT edit application code, docs outside `AGENTS.md`, or any workflow/config files.
- Must NOT add unsupported commands such as `npm test`, `npm run lint`, GitHub Actions, Husky, Dusk, or release steps not present in repo config.
- Must NOT describe auth, admin, or employee features as implemented beyond the current preview screens.
- Must NOT remove the current token-saving/doc-routing rules unless replacing them with equivalent verified guidance in the same file.

## Verification strategy
> Zero human intervention - all verification is agent-executed.
- Test decision: tests-after + Laravel/PHPUnit plus Vite build verification for this documentation-only change.
- Evidence: `.omo/evidence/task-1-update-agents-md-plan.txt`, `.omo/evidence/task-2-update-agents-md-plan.txt`, `.omo/evidence/final-update-agents-md-plan.txt`

## Execution strategy
### Parallel execution waves
> Target 5-8 todos per wave. Fewer than 3 (except the final) means you under-split.
- Wave 1: inspect current file, draft the revised compact structure, and apply the single-file edit.
- Wave 2: run focused verification (`composer test`, `npm run build`, and content/diff checks), then perform final plan-compliance review.

### Dependency matrix
| Todo | Depends on | Blocks | Can parallelize with |
| --- | --- | --- | --- |
| 1 | None | 2, 3 | None |
| 2 | 1 | 4 | 3 |
| 3 | 1 | 4 | 2 |
| 4 | 2, 3 | Final verification wave | None |

## Todos
> Implementation + Test = ONE todo. Never separate.
<!-- APPEND TASK BATCHES BELOW THIS LINE WITH edit/apply_patch - never rewrite the headers above. -->
- [ ] 1. Update `AGENTS.md` in place with the verified instruction structure
  What to do / Must NOT do: Rewrite only the root `AGENTS.md` so it stays compact and high-signal. Preserve the current Docs-routing and MVP bullets, then add verified command surface, preview-only topology, custom UI/data locations, and testing/deployment caveats. Must NOT touch any other file or add generic Laravel advice.
  Parallelization: Wave 1 | Blocked by: None | Blocks: 2, 3
  References (executor has NO interview context - be exhaustive): `AGENTS.md:1-14`; `Docs/00_DOCS_INDEX.md:3-7`; `composer.json:35-50`; `package.json:5-8`; `phpunit.xml:21-35`; `bootstrap/app.php:8-21`; `routes/web.php:5-7`; `resources/views/auth/login.blade.php:13-15`; `resources/views/admin/dashboard.blade.php:10-12`; `resources/views/employee/dashboard.blade.php:13-17`; `resources/css/app.css:7-26`; `resources/js/app.js:7-49`; `database/seeders/DatabaseSeeder.php:14-17`; `.opencode/commands/plan-feature.md:7-15`; `.opencode/commands/build-crud.md:7-19`
  Acceptance criteria (agent-executable): `AGENTS.md` exists after edit and includes all of: `Docs/00_DOCS_INDEX.md`, `composer setup`, `composer dev`, `composer test`, `npm run build`, and an explicit preview-only warning grounded in `routes/web.php`; `git diff -- AGENTS.md` shows only the intended single-file doc update.
  QA scenarios (name the exact tool + invocation): Happy: use `read` on `AGENTS.md` and confirm each required phrase/section is present and concise; Failure: use `bash` with `git diff --name-only` and fail if any file other than `AGENTS.md` changed. Evidence `.omo/evidence/task-1-update-agents-md-plan.txt`
  Commit: Y | docs(agents): refresh repo guidance for future sessions

- [ ] 2. Verify the documented backend/test commands still match executable truth
  What to do / Must NOT do: Run the exact verification commands that AGENTS.md references for backend/test behavior. Must NOT broaden into unrelated test suites or change config to make commands pass.
  Parallelization: Wave 2 | Blocked by: 1 | Blocks: 4
  References (executor has NO interview context - be exhaustive): `composer.json:35-50`; `phpunit.xml:21-35`
  Acceptance criteria (agent-executable): `composer test` exits successfully and its output reflects the configured Laravel test run after config clear.
  QA scenarios (name the exact tool + invocation): Happy: `bash` run `composer test` from repo root; Failure: if command fails, inspect output and confirm AGENTS.md does not claim a passing test surface that repo cannot satisfy. Evidence `.omo/evidence/task-2-update-agents-md-plan.txt`
  Commit: N | n/a

- [ ] 3. Verify the documented frontend/build commands still match executable truth
  What to do / Must NOT do: Run the exact frontend build command named in AGENTS.md. Must NOT add missing scripts or silently swap to a different command.
  Parallelization: Wave 2 | Blocked by: 1 | Blocks: 4
  References (executor has NO interview context - be exhaustive): `package.json:5-8`; `vite.config.js:7-24`; `composer.json:35-42`; `Docs/09. Deployment & Ops Doc.md:411-433`
  Acceptance criteria (agent-executable): `npm run build` exits successfully and produces the normal Vite build output for this repo.
  QA scenarios (name the exact tool + invocation): Happy: `bash` run `npm run build`; Failure: if build fails, record the exact error and ensure AGENTS.md does not overstate frontend readiness. Evidence `.omo/evidence/task-3-update-agents-md-plan.txt`
  Commit: N | n/a

- [ ] 4. Audit the final `AGENTS.md` against scope and accuracy
  What to do / Must NOT do: Perform a content audit after command verification. Confirm the file is compact, repo-specific, and free of speculative workflow claims. Must NOT leave stale or duplicate casing variants unresolved; the root file should remain the single edited instruction file.
  Parallelization: Wave 2 | Blocked by: 2, 3 | Blocks: Final verification wave
  References (executor has NO interview context - be exhaustive): `AGENTS.md` final contents; `README.md:1-58`; `routes/web.php:5-7`; `resources/views/auth/login.blade.php:13-15`; `composer.json:34-50`; `package.json:5-8`; `phpunit.xml:21-35`; `Docs/00_DOCS_INDEX.md:3-7`
  Acceptance criteria (agent-executable): Final `AGENTS.md` contains no generic Laravel tutorial text, no invented lint/CI commands, no claims of implemented auth/business routes beyond preview screens, and preserves the original Docs/MVP guardrails.
  QA scenarios (name the exact tool + invocation): Happy: use `read` plus `bash` `git diff -- AGENTS.md` to confirm only verified content remains; Failure: use `bash` `git diff --name-only` and fail if scope escaped beyond `AGENTS.md`. Evidence `.omo/evidence/task-4-update-agents-md-plan.txt`
  Commit: N | n/a

## Final verification wave
> Runs in parallel after ALL todos. ALL must APPROVE. Surface results and wait for the user's explicit okay before declaring complete.
- [ ] F1. Plan compliance audit
  Verify every Must have / Must NOT have item is reflected in the final diff and evidence files.
- [ ] F2. Code quality review
  Verify the rewritten AGENTS file is concise, non-redundant, and grounded only in cited repo truth.
- [ ] F3. Real manual QA
  Re-open final `AGENTS.md` and validate that a new agent could ramp quickly without being misled about app completeness.
- [ ] F4. Scope fidelity
  Verify no file other than `AGENTS.md` changed and no unsupported workflow was introduced.

## Commit strategy
- Single documentation commit after all verification passes: `docs(agents): refresh repo guidance for future sessions`.
- Do not create intermediate commits for verification-only steps.

## Success criteria
- Root `AGENTS.md` is updated in place, remains compact, and preserves the current Docs-routing/MVP rules.
- The file names the exact verified commands future agents should use and clearly states what is missing (no repo-defined lint/CI/pre-commit wrapper).
- The file warns that the app surface is preview-only and points agents to the actual custom work areas (Blade components/layouts, Tailwind tokens, JS hooks, migrations, seeders).
- `composer test` and `npm run build` both pass during verification, or the worker stops and reports the mismatch instead of papering over it.
- No other repository file is modified.
