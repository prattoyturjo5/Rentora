<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$error = "";
$success = "";

if (isset($_GET['logged_out'])) {
    $success = "Admin session ended successfully.";
}
if (isset($_GET['password_changed'])) {
    $success = "Admin password updated successfully! Please sign in again.";
}

// If already authenticated as admin, redirect to admin dashboard
if (is_admin()) {
    header("Location: dashboard.php");
    exit();
}

// Handle Admin Sign In
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_signin'])) {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login) || empty($password)) {
        $error = "Please enter both Admin Username/Email and Password.";
    } else {
        try {
            // Find admin in admin table (not member table)
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :login1 OR email = :login2 LIMIT 1");
            $stmt->execute(['login1' => $login, 'login2' => $login]);
            $admin = $stmt->fetch();

            if ($admin) {
                $hash = $admin['password'];
                $isValid = password_verify($password, $hash);

                // Auto-rehash fallback if raw password was seeded in admin table
                if (!$isValid && $password === $hash) {
                    $isValid = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $adminIdCol = isset($admin['admin_id']) ? 'admin_id' : 'id';
                    $upStmt = $pdo->prepare("UPDATE admin SET password = :newHash WHERE $adminIdCol = :id");
                    $upStmt->execute(['newHash' => $newHash, 'id' => $admin[$adminIdCol]]);
                }

                if ($isValid) {
                    $adminId = $admin['admin_id'] ?? $admin['id'] ?? 1;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['user_id'] = $adminId;
                    $_SESSION['name'] = $admin['name'] ?? 'Administrator';
                    $_SESSION['username'] = $admin['username'] ?? $admin['email'] ?? 'admin';

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid administrator password.";
                }
            } else {
                $error = "No administrator record found matching those credentials.";
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Admin Operations Sign In - Rentora';
require_once(__DIR__ . '/../includes/header.php');
?>

  <!-- Top Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <span class="flex items-center gap-1.5 text-amber-400 font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        Restricted University Staff & Proctor Access Only
      </span>
      <a href="../index.php" class="text-blue-400 hover:text-white transition-colors">&larr; Return to Marketplace</a>
    </div>
  </div>

  <div class="max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600 mx-auto mb-3 shadow-inner">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
      </div>
      <h2 class="text-2xl font-extrabold text-navy-900">Admin Console Sign In</h2>
      <p class="text-xs text-slate-500 mt-1">Verified against the dedicated administrator datastore</p>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xl">
      <form action="login.php" method="POST" class="space-y-4">
        
        <div>
          <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Admin Username or Email</label>
          <div class="relative">
            <input type="text" id="login" name="login" required placeholder="admin or admin@univ.ac.bd" class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-1">
            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
            <span class="text-[11px] text-slate-400">Encrypted</span>
          </div>
          <div class="relative">
            <input type="password" id="password" name="password" required placeholder="Enter administrator password" class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
        </div>

        <button type="submit" name="admin_signin" class="w-full py-3 px-4 bg-navy-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-slate-900/30 transition-all flex items-center justify-center gap-2">
          <span>Authenticate as Administrator</span>
          <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <div class="mt-6 pt-5 border-t border-slate-200 text-center">
        <a href="../auth/login.php" class="text-xs text-slate-500 hover:text-primary-600 font-medium">
          Regular student or equipment owner? <span class="font-bold underline">Member Login</span>
        </a>
      </div>
    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
