# EPIC-03 routing/view wiring inspection — 2026-06-24

Note: `.omo/notepads/update-agents-md-plan/*.md` did not exist when inspected, so this category note was created instead of overwriting another notepad.

## Files inspected

- `routes/web.php`
- `resources/views/auth/login.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/employee/dashboard.blade.php`
- `resources/views/welcome.blade.php`
- `resources/views/components/layouts/admin.blade.php`
- `resources/views/components/layouts/employee.blade.php`
- `resources/views/components/layouts/guest.blade.php`
- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/employee.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/components/sidebar/admin.blade.php`
- `resources/views/components/sidebar/employee.blade.php`
- `resources/views/components/topbar/admin.blade.php`
- `resources/views/components/topbar/employee.blade.php`
- `resources/js/app.js`

## Current registered route-to-view wiring

- `GET|HEAD /` → route name `preview.login` → `resources/views/auth/login.blade.php`
- `GET|HEAD /ui-preview/admin` → route name `preview.admin` → `resources/views/admin/dashboard.blade.php`
- `GET|HEAD /ui-preview/employee` → route name `preview.employee` → `resources/views/employee/dashboard.blade.php`
- Framework routes also exist for `/up` and local storage; no EPIC-03 page wiring there.

## UI foundation currently wired

- `auth/login.blade.php` uses `<x-layouts.guest>` plus reusable `x-card.base`, `x-form.input`, `x-alert`, and `x-button.primary` components.
- `admin/dashboard.blade.php` uses `<x-layouts.admin>` and exercises stat cards, alert, table, badge, buttons, empty/loading/error states, and confirm modal.
- `employee/dashboard.blade.php` uses `<x-layouts.employee>` and exercises stat cards, nested cards, badge, buttons, alert, empty/loading/error states.
- `<x-layouts.admin>` and `<x-layouts.employee>` are consistently composed from matching sidebar/topbar components: `x-sidebar.admin` + `x-topbar.admin`, and `x-sidebar.employee` + `x-topbar.employee`.
- Duplicate non-component layout files exist under `resources/views/layouts/*.blade.php` with the same content, but current Blade usages are all `<x-layouts.*>` component layouts; no `@extends('layouts.*')` usages were found.
- `resources/js/app.js` supports sidebar toggles via `data-toggle-sidebar` and modal open/close via `data-modal-open` / `data-modal-close`.

## Missing/incomplete placeholders and navigation hooks for initial EPIC-03 routing

- Production/auth route names are not wired yet: `routes/web.php` only defines `preview.login`, `preview.admin`, and `preview.employee`; there is no named `login`, no `dashboard`, no role-specific production route group, and no POST login/logout route.
- `resources/views/welcome.blade.php` is still stock Laravel content and references `Route::has('login')`, `route('login')`, `route('register')`, and `url('/dashboard')`, but it is not currently routed because `/` points to `auth.login`. If reused later, it needs replacement or removal from EPIC-03 routing assumptions.
- Admin sidebar menu items in `resources/views/components/sidebar/admin.blade.php` render as `href="#"` only; placeholder named routes/views are needed for dashboard, master data, training, assessment, monitoring/reporting, and profile/password destinations if nav should be clickable in EPIC-03.
- Employee sidebar menu items in `resources/views/components/sidebar/employee.blade.php` also render as `href="#"`; placeholder named routes/views are needed for dashboard, training list/detail/history, profile, and password destinations if nav should be clickable in EPIC-03.
- Topbars are static preview shells: labels/user names are hardcoded and have no logout/profile hooks yet.
- Login form in `resources/views/auth/login.blade.php` has no `method`, `action`, CSRF token, or error binding; it is explicitly marked preview-only in the inline alert.

## Route placeholder recommendation scope

For EPIC-03 routing foundation only, add minimal placeholders before business flows: public login routes, authenticated redirect/dashboard shell, admin dashboard/nav placeholder pages, employee dashboard/nav placeholder pages, and logout/profile/password placeholders as navigation targets. Keep them as layout-backed shells using existing `<x-layouts.admin>` / `<x-layouts.employee>` until real flows are implemented.
