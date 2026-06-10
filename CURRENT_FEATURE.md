# Current Feature: Add Supervisor Invitation Flow

## Status: Planning Complete - Ready for Implementation

---

## Overview

Admin invites supervisors via email → Supervisor sets password via secure link → Auto-login redirects to Supervisor Dashboard

---

## Database Changes

| Migration | Purpose |
|-----------|---------|
| `add_role_to_users_table` | Add `role` enum (admin, supervisor, staff, client) to users table |
| `create_supervisor_invitations_table` | Track invitation tokens, emails, expiry, status |

**Supervisor Invitations Table:**
- `id`, `email`, `token` (unique), `invited_by` (admin FK), `expires_at`, `accepted_at`, `status` (pending/accepted/expired)

---

## Backend Components

| Component | Responsibility |
|-----------|----------------|
| `SupervisorInvitationController` | Create invitation, send email, validate token, set password |
| `SupervisorInvitationMail` | Mailable with secure link (signed URL or token-based) |
| `InvitationTokenMiddleware` | Validate token for password-set page |
| `RoleRedirectMiddleware` | Post-login redirect based on role |

---

## Frontend Components (Livewire)

| Component | Page/Location |
|-----------|---------------|
| `Admin.AddSupervisor` | Admin dashboard → "Add Supervisor" modal/form |
| `Supervisor.SetPassword` | Public page: `/supervisor/set-password/{token}` |
| `Supervisor.Dashboard` | Supervisor landing page after login |

---

## Routes

```php
// Admin (auth:admin)
POST   /admin/supervisors/invite          → SupervisorInvitationController@store
GET    /admin/supervisors                 → Admin.SupervisorManagement (index)

// Public (no auth)
GET    /supervisor/set-password/{token}   → Supervisor.SetPassword
POST   /supervisor/set-password/{token}   → SupervisorInvitationController@setPassword

// Post-login redirect handled by middleware
```

---

## Email Flow

1. Admin submits email → `SupervisorInvitationController@store`
2. Generate unique token, save to `supervisor_invitations`
3. Send `SupervisorInvitationMail` with link: `/supervisor/set-password/{token}`
4. Token expires in 24-48 hours

---

## Security Considerations

- Signed URLs or hashed tokens (not plain IDs)
- One-time use tokens (mark `accepted_at` on password set)
- Rate limiting on invitation creation
- Validate email not already registered
- Only `admin` role can access invitation endpoints

---

## Implementation Order

1. **Migrations & Models** - Role enum, Invitation model
2. **Backend** - Controller, Mail, Middleware
3. **Admin UI** - Livewire component for invitation form
4. **Public UI** - Set password page
5. **Auth Integration** - Role-based redirect middleware
6. **Tests** - All 8 test cases

---

## Decisions

- **Token approach:** DB token with hash lookup (simpler for MVP, no URL tampering possible)
- **Email driver:** Laravel Log for local dev, SMTP/Mailgun for production (configurable in .env)
- **Supervisor dashboard:** Placeholder MVP for now
- **Phase alignment:** Build after Phase 1 tests pass
