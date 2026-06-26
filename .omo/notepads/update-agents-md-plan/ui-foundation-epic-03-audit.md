# 2026-06-24 — EPIC-03 UI Foundation Audit

Scope: no source edits. Compared current Laravel UI surface against `Docs/05. UI Spec  Wireframe.md` sections 5.4, 5.5, 5.6, 5.11, 5.12 and `Docs/06. Acceptance Criteria.md` AC-UI-001..018. Roadmap source: `Docs/10. Task Breakdown  Roadmap.md` EPIC-03.

## Files inspected / inventoried

- Layouts: `resources/views/components/layouts/admin.blade.php`, `resources/views/components/layouts/employee.blade.php`, `resources/views/components/layouts/guest.blade.php`; duplicate unused root layouts also exist at `resources/views/layouts/admin.blade.php`, `resources/views/layouts/employee.blade.php`, `resources/views/layouts/guest.blade.php`.
- Navigation: `resources/views/components/sidebar/admin.blade.php`, `resources/views/components/sidebar/employee.blade.php`, `resources/views/components/topbar/admin.blade.php`, `resources/views/components/topbar/employee.blade.php`.
- Buttons: `resources/views/components/button/primary.blade.php`, `outline.blade.php`, `danger.blade.php`, `ink.blade.php`, `icon.blade.php`.
- Forms: `resources/views/components/form/input.blade.php`, `select.blade.php`, `textarea.blade.php`.
- Tables: `resources/views/components/table/table.blade.php`, `header.blade.php`, `row.blade.php`, `empty.blade.php`.
- Cards/badges/modal/states: `resources/views/components/card/base.blade.php`, `card/stat.blade.php`, `badge.blade.php`, `modal/confirm.blade.php`, `alert.blade.php`, `empty-state.blade.php`, `loading-state.blade.php`, `error-state.blade.php`.
- Views/assets/routes: `resources/views/auth/login.blade.php`, `resources/views/admin/dashboard.blade.php`, `resources/views/employee/dashboard.blade.php`, `resources/views/welcome.blade.php`, `resources/css/app.css`, `resources/js/app.js`, `routes/web.php`.

## Already done / exists

- Guest, admin, and employee anonymous layouts exist under `resources/views/components/layouts/*` and current preview pages use `x-layouts.admin`, `x-layouts.employee`, and `x-layouts.guest`.
- Admin and employee sidebar/topbar components exist, with desktop sidebar and mobile toggle hooks. This partially satisfies UI Spec 5.4.1, 5.4.2 and AC-UI-011/013.
- Reusable core controls exist for primary/outline/danger/ink/icon buttons, input/select/textarea, table/header/row/empty, badge, base/stat card, confirm modal, alert, empty/loading/error states. This partially satisfies UI Spec 5.5, 5.6 and AC-UI-001..006, 014..018.
- `resources/css/app.css` defines Tailwind v4 theme tokens/base styles aligned with the clean enterprise foundation.
- `resources/js/app.js` wires sidebar toggle and confirm modal open/close behavior.
- Login UI view exists at `resources/views/auth/login.blade.php` and is wired as preview route `/` in `routes/web.php`.

## Needs work / gaps mapped to EPIC-03 and AC-UI

1. **Page header component missing** — UI Spec 5.4.3 and 5.12 require `resources/views/components/page-header.blade.php`, but only role-specific topbars exist. Edit/add `resources/views/components/page-header.blade.php`; then update admin/employee pages to use it for consistent title, description, breadcrumb, and actions. Impacts AC-UI-011 and AC-UI-016..018.
2. **Toast/success feedback missing** — UI Spec 5.5.8 and AC-UI-007/008 require success notifications. No `resources/views/components/toast.blade.php` or separate success-state component found. Add `resources/views/components/toast.blade.php` and JS/session hooks in `resources/js/app.js` or layout files.
3. **Custom modal is incomplete versus spec** — `resources/views/components/modal/confirm.blade.php` exists, but UI Spec 5.12/5.15 also names delete-specific modal and many action variants. Current implementation uses native `<dialog>`; this is custom UI but should be checked against the “Blade Component + Alpine.js” wording and loading lock requirement. Edit `resources/views/components/modal/confirm.blade.php`; add `resources/views/components/modal/delete.blade.php` or standardize variants; extend `resources/js/app.js` for loading/disabled behavior. Impacts AC-UI-009/010.
4. **Responsive drawer lacks overlay/backdrop and explicit drawer component** — current sidebar toggles `-translate-x-full` in `resources/js/app.js`, but no drawer/backdrop component was found. UI Spec 5.4.2 and 5.11 require mobile drawer behavior. Edit `resources/views/components/sidebar/admin.blade.php`, `resources/views/components/sidebar/employee.blade.php`, role layouts, and `resources/js/app.js` to include overlay, escape/outside close if required, and accessible focus handling. Impacts AC-UI-013.
5. **Employee card variants incomplete** — UI Spec 5.5.5 and 5.12 require `card/training.blade.php`, `card/material.blade.php`, and `card/result.blade.php`; only `card/base.blade.php` and `card/stat.blade.php` were found. Add those card files before EPIC-08 pages depend on them. Impacts AC-UI-017.
6. **Form component set incomplete** — UI Spec 5.12 requires `form/file.blade.php` and `form/error.blade.php`; only input/select/textarea found. Add file input and reusable error component; retrofit `resources/views/auth/login.blade.php` and future form views. Impacts AC-UI-006 and AC-UI-018.
7. **Table component set incomplete** — UI Spec 5.12 requires `table/action.blade.php` and `table/skeleton.blade.php`; only table/header/row/empty found. Add action and skeleton components for icon + tooltip actions and loading rows. Impacts AC-UI-003, AC-UI-016.
8. **Tab/progress/question components missing** — UI Spec 5.12 requires `tabs.blade.php`, `progress-step.blade.php`, and `question-card.blade.php`; none found. Add before detail/training/test UI implementation. Impacts AC-UI-017/018 and future AC-EMP/AC-TEST.
9. **Some existing components appear unused in current views** — `button/ink.blade.php`, `form/select.blade.php`, `form/textarea.blade.php`, and `table/empty.blade.php` exist but no current usages were found. Not necessarily a defect, but EPIC-03 “Done” status should not be treated as fully verified until sample usages or downstream pages exercise them. Impacts AC-UI-014, 016, 018.
10. **Duplicate layout paths can drift** — root `resources/views/layouts/*` duplicate the active anonymous layouts but are unused. Decide whether to delete/archive later or convert all pages to one layout convention. Exact files: `resources/views/layouts/admin.blade.php`, `resources/views/layouts/employee.blade.php`, `resources/views/layouts/guest.blade.php`.
11. **Auth/login route mismatch** — `resources/views/welcome.blade.php` references `route('login')` and `route('register')`, but `routes/web.php` only defines preview routes (`preview.login`, `preview.admin`, `preview.employee`). EPIC-03 only needs guest/login layout, but this is a handoff gap for EPIC-04. Edit `routes/web.php` and/or `resources/views/welcome.blade.php` when auth is implemented.

## Mismatch summary

- Roadmap marks all EPIC-03 tasks Done, but UI Spec 5.12 files are only partially present.
- Layout foundation is present, but topbar is not a full page-header component and mobile drawer is only a class toggle.
- State components cover empty/loading/error; success is only covered indirectly by alert, not toast notification.
- Modal exists, but delete/action-specific modal variants and loading behavior are not yet proven.
- Admin table/card/form foundations exist, but employee-specific card/list components and tab/progress/test components are missing.

## Exact edit targets for a follow-up implementation

- `resources/views/components/page-header.blade.php` — create.
- `resources/views/components/toast.blade.php` — create.
- `resources/views/components/modal/confirm.blade.php` — enhance for variants/loading/accessibility.
- `resources/views/components/modal/delete.blade.php` — create or implement as confirm variant wrapper.
- `resources/views/components/sidebar/admin.blade.php`, `resources/views/components/sidebar/employee.blade.php`, `resources/views/components/layouts/admin.blade.php`, `resources/views/components/layouts/employee.blade.php`, `resources/js/app.js` — improve mobile drawer behavior.
- `resources/views/components/card/training.blade.php`, `resources/views/components/card/material.blade.php`, `resources/views/components/card/result.blade.php` — create.
- `resources/views/components/form/file.blade.php`, `resources/views/components/form/error.blade.php` — create.
- `resources/views/components/table/action.blade.php`, `resources/views/components/table/skeleton.blade.php` — create.
- `resources/views/components/tabs.blade.php`, `resources/views/components/progress-step.blade.php`, `resources/views/components/question-card.blade.php` — create.
- `routes/web.php`, `resources/views/welcome.blade.php` — later EPIC-04 route-name cleanup.
