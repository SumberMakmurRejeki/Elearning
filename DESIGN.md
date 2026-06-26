# E-Learning Training Karyawan Design System

## 1. Atmosphere & Identity

This product feels like a calm internal operations console: clean, readable, and efficient rather than flashy. The signature is a white-card dashboard with crisp fog borders, soft elevation, and a restrained blue accent that keeps attention on training data instead of decoration.

## 2. Color

### Palette

| Role | Token | Light | Dark | Usage |
|------|-------|-------|------|-------|
| Surface / page | `--color-cloud` | `#f7f7f7` | `#f7f7f7` | App background |
| Surface / card | `--color-canvas` | `#ffffff` | `#ffffff` | Cards, panels, modals |
| Surface / input | `--color-paper` | `#ffffff` | `#ffffff` | Forms, controls |
| Text / primary | `--color-ink` | `#1a1a1a` | `#1a1a1a` | Headings, body |
| Text / secondary | `--color-charcoal` | `#3d3d3d` | `#3d3d3d` | Descriptions |
| Text / tertiary | `--color-graphite` | `#636363` | `#636363` | Labels, metadata |
| Border / default | `--color-fog` | `#e8e8e8` | `#e8e8e8` | Card and panel borders |
| Border / muted | `--color-steel` | `#c2c2c2` | `#c2c2c2` | Subtle dividers |
| Accent / primary | `--color-primary` | `#024ad8` | `#024ad8` | Primary actions, links, focus |
| Accent / hover | `--color-primary-bright` | `#296ef9` | `#296ef9` | Hover state |
| Accent / deep | `--color-primary-deep` | `#0e3191` | `#0e3191` | Strong emphasis |
| Accent / soft | `--color-primary-soft` | `#c9e0fc` | `#c9e0fc` | Info backgrounds |
| Status / success | `--color-success` | `#15803d` | `#15803d` | Success text and states |
| Status / success soft | `--color-success-soft` | `#dcfce7` | `#dcfce7` | Success badges, alerts |
| Status / warning | `--color-warning` | `#b45309` | `#b45309` | Warning text and states |
| Status / warning soft | `--color-warning-soft` | `#fef3c7` | `#fef3c7` | Warning badges, alerts |
| Status / danger | `--color-danger` | `#b3262b` | `#b3262b` | Errors, destructive states |
| Status / danger soft | `--color-danger-soft` | `#f9d4d2` | `#f9d4d2` | Error badges, alerts |
| Status / info soft | `--color-info-soft` | `#c9e0fc` | `#c9e0fc` | Informational panels |

### Rules

- Keep accent usage functional: actions, focus, and key data emphasis only.
- Use neutral surfaces first; do not add decorative gradients.
- Never introduce an unlisted color without updating this table.

## 3. Typography

### Scale

| Level | Size | Weight | Line height | Usage |
|------|------|--------|-------------|-------|
| H1 | 30px | 600 | 1.2 | Page titles |
| H2 | 24px | 600 | 1.25 | Section headers |
| H3 | 18px | 600 | 1.35 | Card titles |
| Body | 16px | 400 | 1.6 | Primary text |
| Body / small | 14px | 400 | 1.5 | Helper text, filters |
| Caption | 12px | 600 | 1.4 | Labels, eyebrow text |

### Font stack

- Primary: `Inter, ui-sans-serif, system-ui, sans-serif`
- Mono: not used

### Rules

- Keep body copy at 14px or larger.
- Titles can be compact, but must stay readable on tablet and desktop.
- Uppercase labels use wider tracking and smaller sizes.

## 4. Spacing & Layout

### Base unit

All spacing derives from 4px.

| Token | Value | Usage |
|------|-------|-------|
| `--space-1` | 4px | Tight icon-label gaps |
| `--space-2` | 8px | Inline groups |
| `--space-3` | 12px | Compact padding |
| `--space-4` | 16px | Default padding |
| `--space-5` | 20px | Comfortable spacing |
| `--space-6` | 24px | Card padding |
| `--space-8` | 32px | Section gaps |
| `--space-10` | 40px | Major section gaps |
| `--space-12` | 48px | Page rhythm |

### Grid

- Max content width: `1280px` (`max-w-7xl`)
- Layout: sidebar + sticky topbar + content canvas
- Content rhythm: 24px section gaps, 16px card padding on compact elements, 24px padding on primary surfaces
- Breakpoints: Tailwind defaults (`sm`, `md`, `lg`, `xl`, `2xl`)

### Rules

- No magic spacing values outside the 4px scale.
- Dashboard content should feel dense but never cramped.

## 5. Components

### Page Header
- **Structure**: eyebrow, title, description, optional actions
- **Spacing**: 20px internal gaps, 24px top-level section spacing
- **States**: default, action-filled
- **Accessibility**: title hierarchy preserved with one H1 per page

### Card Base / Stat Card
- **Structure**: rounded surface, border, optional shadow
- **Spacing**: 24px padding
- **States**: default, loading, empty, error
- **Usage**: summary cards, chart containers, empty/error states

### Primary Button
- **Structure**: filled blue button
- **States**: default, hover, focus, disabled
- **Usage**: primary actions like apply filter, save, login

### Outline Button
- **Structure**: white button with fog border
- **States**: default, hover, focus, disabled
- **Usage**: secondary actions like reset, logout

### Link Button
- **Structure**: anchor-styled button with primary or outline treatment
- **States**: default, hover, focus
- **Usage**: page header actions and navigation links that should read like buttons

### Form Input
- **Structure**: label + input + help/error text
- **States**: default, focus, error, disabled
- **Usage**: login and dashboard filters

### Form Select
- **Structure**: label + select + help/error text
- **States**: default, focus, error, disabled
- **Usage**: dashboard filters, future admin forms

### Badge
- **Structure**: pill label with semantic color variants
- **Variants**: success, warning, danger, info, neutral, ink
- **Usage**: status markers, counts, role labels

### Empty State
- **Structure**: icon, title, description, optional action
- **States**: default
- **Usage**: no data, no filter results

### Error State
- **Structure**: icon, title, description, optional retry action
- **States**: default
- **Usage**: failed data load, dashboard query errors

### Loading State
- **Structure**: skeleton blocks
- **States**: default
- **Usage**: content fetch placeholders

### Sidebar / Topbar Shell
- **Structure**: sticky topbar, fixed sidebar, overlay on mobile
- **States**: open, closed, active nav item
- **Usage**: authenticated admin/employee areas

## 6. Motion & Interaction

### Timing

| Type | Duration | Usage |
|------|----------|-------|
| Micro | 100-150ms | Button hover, toggles |
| Standard | 200-300ms | Sidebar slide, overlays |
| Emphasis | 400-600ms | Page transitions |

### Rules

- Animate only `transform` and `opacity`.
- Keep hover/focus states visible on every interactive control.
- Respect reduced motion preferences.

## 7. Depth & Surface

### Strategy

Mixed: fog borders define structure, while cards and key surfaces use a very light shadow for separation.

### Rules

- Use `border-fog` for structure and `shadow-sm` only for elevated cards and panels.
- Avoid heavy shadows, glows, and gradients.
- Surfaces should feel calm and utilitarian.
