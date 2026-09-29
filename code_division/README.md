# Rentora - Backend & Database Code Division

Four-member project division based on the project specification sheet.

---

### 1. Prattoy — User / Rental Management
**Coverage:** Equipment management, rental workflow, rental requests, user rental operations, and handover processing.
- `user/add_item.php`
- `user/dashboard.php`
- `user/equipment.php`
- `user/exchanges.php`
- `user/handle_request.php`
- `user/profile.php`
- `user/rentals.php`
- `user/submit_request.php`
- `user/verify_handover.php`

---

### 2. Aiman — Authentication & Account Security
**Coverage:** Registration, login/logout, password change, sessions, and authentication/access protection.
- `auth/change_password.php`
- `auth/login.php`
- `auth/logout.php`
- `auth/register.php`
- `includes/auth_guard.php`

---

### 3. Shreya — Core Marketplace & Exchange
**Coverage:** Marketplace browsing/search/filtering, item details, exchange submission, join/member application, and database connection.
- `index.php`
- `item-details.php`
- `submit_exchange.php`
- `join.php`
- `DBconnect.php`
- `config/db.php`

---

### 4. Samia — Administration (Entire Admin Area)
**Coverage:** All admin functionality stays together: admin login, dashboard, member management, verification, item deletion, dispute resolution, admin password management, and admin-side handover verification.
- `admin/change_password.php`
- `admin/dashboard.php`
- `admin/delete_item.php`
- `admin/login.php`
- `admin/members.php`
- `admin/resolve_dispute.php`
- `admin/verify_handover.php`
- `admin/verify_user.php`

---

### Shared / Excluded Files
- `includes/nav.php`: Shared navigation component with some database-driven checks (shared component; excluded from individual primary feature counts).
- `scratch/test_live_fixes.php`: Test/debug code (excluded from main contribution division).
