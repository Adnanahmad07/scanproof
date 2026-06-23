# Feature Plan — Public Pricing Page

> **For coding agent: mimo 2.5v**
> Read `context.md` and `design.md` fully before writing any code. This plan is grounded in both.
> Reference layout: https://trello.com/pricing (tier-card grid + billing toggle + FAQ).
> Created: 2026-06-16.

---

## 1. Goal

Add a **public, display-only** pricing page to the ScanProof marketing site. Four tiers in a
responsive card grid, a monthly/annual billing toggle, a feature-comparison feel, and an FAQ —
styled exactly like the existing landing page (`pages/landing.blade.php` + its `x-` components).

### Scope guardrails (READ THIS)
- ✅ **In scope:** a static marketing page (route + Blade views), reusing existing design tokens.
- ❌ **Out of scope:** real billing. `context.md §8` lists *Payments/billing* as out of scope.
  **Do NOT** add Stripe, checkout, subscriptions, a `plans` table, migrations, or any payment
  logic. Prices are hardcoded in the Blade view. CTAs link to `route('register')` only.
- ❌ Do not touch auth, dashboards, or any Phase 2+ feature.
- ✅ Keep with the rules in `context.md §7`: **no file exceeds 300 lines**, reuse existing
  patterns, use the custom Tailwind tokens from `resources/css/app.css`.

---

## 2. Route

Add to `routes/web.php`, immediately after the `home` route (it's a public marketing page, no middleware):

```php
Route::get('/pricing', function () {
    return view('pages.pricing');
})->name('pricing');
```

- Method/URI: `GET /pricing`, name `pricing`, **no middleware** (public, like `/`).
- The billing toggle is **client-side only** (Alpine) — no query params, no controller needed.

---

## 3. Files to create / edit

| Action | File | Purpose |
|---|---|---|
| **Create** | `resources/views/pages/pricing.blade.php` | Page shell — mirrors `pages/landing.blade.php` exactly (same `<head>`, fonts, `@vite`, `x-nav`, `x-footer`). Wraps the new pricing components. |
| **Create** | `resources/views/components/pricing/hero.blade.php` | Page header: display heading + subhead + billing toggle. |
| **Create** | `resources/views/components/pricing/plans.blade.php` | The 4 tier cards grid (the core of the page). |
| **Create** | `resources/views/components/pricing/faq.blade.php` | Accordion FAQ (Alpine), adapted from Trello FAQ. |
| **Edit** | `resources/views/components/nav.blade.php` | Change the `#pricing` anchor (line 16) to `{{ route('pricing') }}`. |
| **Edit** | `resources/views/components/footer.blade.php` | Change the "Pricing" link (line 21) `href="#"` to `{{ route('pricing') }}`. |
| **Edit** | `routes/web.php` | Add the route above. |

> Namespaced components live under `components/pricing/` → reference as `<x-pricing.hero />`,
> `<x-pricing.plans />`, `<x-pricing.faq />`. This matches the existing flat-component convention
> while keeping the new files grouped. Each file stays well under 300 lines.

### `pages/pricing.blade.php` skeleton (copy landing's head verbatim)
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing — ScanProof</title>
    <meta name="description" content="Simple, transparent pricing for facility operations teams of every size.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-surface font-sans text-text-primary antialiased">
    <x-nav />
    <main class="pt-20">           {{-- pt-20 clears the fixed h-20 nav --}}
        <x-pricing.hero />
        <x-pricing.plans />
        <x-pricing.faq />
        <x-cta />                  {{-- reuse the existing CTA band --}}
    </main>
    <x-footer />
</body>
</html>
```

---

## 4. Pricing tiers (adapt Trello → ScanProof)

Trello sells per-user project boards; ScanProof sells **facility operations** (locations, QR codes,
tasks, photo proof, reports — per `context.md §1`). Map Trello's 4-tier shape onto ScanProof units.
Prices are **illustrative placeholders** — fine to keep as-is.

| Tier | Price (annual / monthly) | Tagline | Highlight |
|---|---|---|---|
| **Free** | $0 | "For a single site getting started with QR reporting." | — |
| **Standard** | $4 / $5 per user/mo | "Unlimited locations, tasks, and photo-proof workflows." | — |
| **Premium** | $9 / $11 per user/mo | "Analytics, reports, and admin controls for growing teams." | **Most popular** (badge + primary border) |
| **Enterprise** | Custom | "Org-wide controls, SLAs, and dedicated support." | CTA = "Contact sales" |

### Feature lists (each tier = "everything in previous, plus")
- **Free:** 1 facility · up to 10 locations · QR scan + public issue reporting · 1 supervisor · 3 staff · 30-day history · mobile web.
- **Standard:** Unlimited locations · unlimited tasks · before/after photo proof · task status workflow · email notifications · 1-year history.
- **Premium:** Dashboard analytics & facility health score · PDF report export · WhatsApp notifications · role-based access (all roles) · SLA tracking · priority support.
- **Enterprise:** Unlimited facilities & users · GPS verification · client portal · automated daily reports · SSO/onboarding · dedicated success manager.

> Mirror the tiers/features from `§6 ROADMAP` so the page reflects what ScanProof actually builds
> (Phases 2–8). Don't invent features outside that roadmap.

### Card CTAs (all link to register; no checkout)
- Free → `route('register')`, label "Get started free".
- Standard / Premium → `route('register')`, label "Start free trial".
- Enterprise → `mailto:sales@scanproof.com` (or `route('home')` anchor), label "Contact sales".

---

## 5. Design system mapping (from `design.md` + `app.css`)

Use **only** the existing Tailwind tokens — do not introduce new colors or arbitrary hex values.

| Element | Token / class | Source |
|---|---|---|
| Page bg | `bg-surface` (white) | `app.css` |
| Section rhythm | `py-20 px-4 sm:px-6 lg:px-8`, `max-w-7xl mx-auto` | matches `features`/`cta` |
| Display heading | `text-3xl sm:text-4xl font-bold text-text-primary` | design.md "Display 36/Bold" |
| Subhead / body | `text-lg text-text-secondary` | design.md "Body 16/Regular" |
| Card | `bg-white rounded-2xl border border-gray-100 hover:shadow-lg transition-shadow p-8` | matches `features` cards |
| Card radius | `rounded-2xl` (cards) / `rounded-xl` (buttons) | design.md "rounded-xl 12px" |
| Primary CTA | `bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-xl font-medium` | matches `nav` button |
| Secondary CTA | `bg-white border border-gray-200 hover:border-primary text-text-primary rounded-xl` | derived |
| "Most popular" card | `border-2 border-primary` + badge `bg-primary text-white text-xs rounded-full px-3 py-1` | design.md pills `rounded-full` |
| Feature check icon | `text-success` (emerald) checkmark SVG | design.md Success token |
| Toggle active pill | `bg-primary text-white`; inactive `text-text-secondary` | design.md Action Priority |
| FAQ divider | `border-t border-gray-100` | matches footer |

Typography scale: Display headers 36/bold, section titles 20/semibold, body 16/regular, badges/labels
12–14/medium — exactly as `design.md` specifies. Font is already Inter via Bunny Fonts.

---

## 6. Component specs

### 6.1 `pricing/hero.blade.php`
- `<section class="py-20 ...">` centered.
- H1 display heading: "Simple pricing for every facility".
- Subhead paragraph.
- **Billing toggle** (Alpine — Alpine ships with Livewire 4, and `[x-cloak]` is already wired in
  `app.css`):
  ```blade
  <div x-data="{ annual: true }" class="...">
      <button @click="annual = true"  :class="annual ? 'bg-primary text-white' : 'text-text-secondary'">Annual</button>
      <button @click="annual = false" :class="!annual ? 'bg-primary text-white' : 'text-text-secondary'">Monthly</button>
  </div>
  ```
  Add a small `Save ~20%` pill next to "Annual" (`bg-success/10 text-success rounded-full`).
- The toggle state must reach the cards. Put **one shared `x-data`** on a wrapper that spans both
  hero and plans (e.g. wrap `<x-pricing.hero />` + `<x-pricing.plans />` in a single
  `<div x-data="{ annual: true }">` inside `pricing.blade.php`), OR use `Alpine.store('billing', { annual: true })`.
  **Prefer the shared-wrapper approach** — simplest, no JS file edits.

### 6.2 `pricing/plans.blade.php`
- Grid: `grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto`.
- One card per tier. Each card:
  - Tier name (`text-xl font-semibold`), tagline (`text-text-secondary text-sm`).
  - Price block — show annual vs monthly reactively:
    ```blade
    <span class="text-4xl font-bold" x-text="annual ? '$4' : '$5'"></span>
    <span class="text-text-secondary">/user · mo</span>
    ```
    (Enterprise shows "Custom", no toggle.)
  - CTA button (see §4).
  - Feature list: `<ul class="space-y-3">`, each `<li class="flex gap-3">` with a `text-success`
    check SVG + `text-text-secondary` label.
  - Premium card gets `border-2 border-primary relative` + an absolutely-positioned
    "Most popular" badge.
- Keep this file lean; if it nears 300 lines, extract a `<x-pricing.card />` component that takes
  `:name :tagline :priceAnnual :priceMonthly :features :featured :cta` props and loop a PHP array.
  (Recommended even if short — cleaner and DRY.)

### 6.3 `pricing/faq.blade.php`
- `<section class="py-20 ... bg-surface-low">` (subtle contrast band).
- Heading "Frequently asked questions".
- Alpine accordion: `x-data="{ open: null }"`, each item toggles `open === i`.
- Seed 5–6 Q&As adapted from Trello's FAQ, rewritten for ScanProof:
  - "Is there a free plan?" · "Can I change plans later?" · "What counts as a user?" ·
    "Do you offer non-profit/education discounts?" · "How is my data secured?" ·
    "What happens to my data if I downgrade?"

---

## 7. Acceptance criteria (test cases — follow `context.md` TC-x convention)

Create `tests/Feature/PricingPageTest.php`:

| ID | Test |
|---|---|
| **TC-P.1** | `GET /pricing` returns **200** (public, no auth required). |
| **TC-P.2** | Response contains each tier name: `Free`, `Standard`, `Premium`, `Enterprise`. |
| **TC-P.3** | Response contains the "Most popular" badge text. |
| **TC-P.4** | Each tier's primary CTA links to `route('register')` (assert the register URL appears). |
| **TC-P.5** | The nav and footer "Pricing" links resolve to `route('pricing')` (no leftover `href="#"`/`#pricing`). |
| **TC-P.6** | Full existing suite stays green (no regressions) — `composer test`. |

Example skeleton:
```php
public function test_pricing_page_is_publicly_accessible(): void
{
    $this->get('/pricing')->assertOk()->assertSee('Premium');
}
```

**Feature is DONE only when TC-P.1 → TC-P.6 all pass** (per `context.md` workflow rule).

---

## 8. Build & verify

```bash
npm run build                       # compile Tailwind (new classes must be scanned)
php artisan test --filter=PricingPageTest
composer test                       # confirm no regressions
```
Then open `/pricing`, toggle annual/monthly, and check mobile (single-column), tablet (2-col),
desktop (4-col) breakpoints.

---

## 9. Definition of done checklist

- [ ] `GET /pricing` route added, named `pricing`, public.
- [ ] `pages/pricing.blade.php` + 3 `components/pricing/*` files created, each < 300 lines.
- [ ] Nav (line 16) and footer (line 21) "Pricing" links point to `route('pricing')`.
- [ ] 4 tiers render with correct features, monthly/annual toggle works (Alpine, client-side).
- [ ] Only existing `app.css` design tokens used; visually consistent with the landing page.
- [ ] No billing/payment code, no migrations, no new DB tables.
- [ ] `PricingPageTest` (TC-P.1–P.6) green; full suite green.
