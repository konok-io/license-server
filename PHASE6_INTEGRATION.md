# MRH License Server — Phase 6 (Control Operations + Analytics Dashboard)

Install path: `C:\xampp\htdocs\saudi-license-server`
Stack: Laravel 12 · PHP 8.3+ · MySQL · Bootstrap 5 · Chart.js 4 · AJAX

## What Phase 6 delivers

A unified enforcement layer and a live analytics dashboard.

### Control / enforcement operations

| Operation             | Route (POST)                                          | Effect |
|-----------------------|-------------------------------------------------------|--------|
| Remote kill switch    | /admin/control/licenses/{license}/kill                | Revokes all activations, flags kill_switch; next verify returns signed KILL |
| Revive (un-kill)      | /admin/control/licenses/{license}/revive              | Clears kill switch; clients must re-activate |
| Suspend license       | /admin/control/licenses/{license}/suspend             | Status→suspended (activations kept); next verify returns DENY |
| Resume license        | /admin/control/licenses/{license}/resume              | Restores service without re-activation |
| Disable customer      | /admin/control/customers/{customer}/disable           | Deactivates customer + kills ALL their licenses (cascade) |
| Enable customer       | /admin/control/customers/{customer}/enable            | Re-enables customer (licenses revived individually) |
| Disable domain        | /admin/control/licenses/{license}/disable-domain      | Revokes activations on that domain + blacklists it |
| Disable installation  | /admin/control/licenses/{license}/disable-installation| Revokes one binding + blacklists the installation_id |

All operations are transactional, revoke the correct activations, refresh the
`activation_count` from source of truth, write an `activation_logs` entry, and
append a hash-chained `audit_logs` record. Enforcement takes effect on the
next client `/api/verify`.

### Analytics dashboard

`/admin/analytics` — loads once; charts, widgets, and reports hydrate via AJAX.

- **Headline metrics** (8 cards): active/suspended/killed/expired licenses,
  live installations, active customers, verifications today, kills today.
  Auto-refresh every 30s.
- **Charts (Chart.js 4)**:
  - Verification trend (line, success vs failed/kill, 7/30/90-day toggle)
  - Activation trend (bar)
  - License status distribution (doughnut)
  - License type distribution (doughnut)
  - Server-type distribution of live installs (doughnut)
- **Widgets**: top customers by license count; licenses expiring within 30 days
  (color-coded by urgency).
- **Reports** (tabbed, date-filterable): Activation Report and Verification
  Report, rendered from the analytics feeds.

## Files added

```
app/Services/
├── LicenseControlService.php        (kill/suspend/disable orchestration)
└── AnalyticsService.php             (stats, chart feeds, reports)

app/Http/Controllers/Admin/
├── ControlController.php            (8 enforcement endpoints)
└── AnalyticsController.php          (dashboard + 6 data endpoints)

app/Http/Requests/Control/
├── ControlActionRequest.php         (reason + optional blacklist)
├── DisableDomainRequest.php
└── DisableInstallationRequest.php

routes/admin-phase6.php              (control + analytics routes)

resources/views/admin/
├── analytics/index.blade.php        (dashboard)
├── licenses/control-modals.blade.php (disable domain/installation modals)
└── partials/sidebar.blade.php       (Analytics link added)
```

## Wiring

### 1. Merge routes
Add the Phase 6 routes into your admin group. Either require the file from
`routes/admin.php` (inside the existing `auth` group), or copy its route
definitions in. Route names resolve as `admin.control.*` and `admin.analytics.*`
under the `admin` prefix/name established in Phase 3.

### 2. Service container
No manual bindings required. `LicenseControlService`, `AnalyticsService`, and
`BlacklistService` are all auto-resolved by Laravel's container.

### 3. Permissions
Phase 6 reuses the Phase 3 permissions — no seeder changes:
`licenses.kill` (kill/revive/disable-domain/disable-installation),
`licenses.update` (suspend/resume), `customers.update` (disable/enable customer),
`licenses.view` (analytics dashboard).

### 4. Optional: control buttons on the license table
Include the control modals in `licenses/index.blade.php`:
```blade
@include('admin.licenses.control-modals')
```
Then add row buttons in the DataTable action renderer:
```js
btns += ` <button class="btn btn-outline-warning btn-disable-domain" data-id="${id}" title="Disable domain"><i class="bi bi-globe2"></i></button>`;
btns += ` <button class="btn btn-outline-warning btn-disable-install" data-id="${id}" title="Disable installation"><i class="bi bi-pc-display"></i></button>`;
```
Expose the table reload as `window.reloadLicenses = () => table.ajax.reload(null,false);`
so the modals refresh the grid after an action.

## Design notes

- **Kill vs suspend vs disable** are deliberately distinct: kill is terminal and
  revokes bindings; suspend is reversible and preserves bindings; disable-domain
  / disable-installation are surgical (one target) and also blacklist to prevent
  re-activation.
- **Disable customer cascades** to kill every non-killed license; re-enabling is
  intentionally NOT a bulk revive, so an operator restores service deliberately.
- **Analytics is strictly read-only** and returns plain arrays shaped for
  Chart.js — grouped/indexed queries keep it cheap at scale.
- **Time-series are zero-filled** across the full window so charts never show
  gaps for days with no events.
