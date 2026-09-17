<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

// Enforce admin guard
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$admin_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_admin_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 4) {
        $error = "New password must be at least 4 characters long.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = :id LIMIT 1");
            $stmt->execute(['id' => $admin_id]);
            $admin = $stmt->fetch();

            if ($admin) {
                $hash = $admin['admin_password_hash'];
                $isValid = password_verify($current_password, $hash);

                // Fallback if plain text was previously seeded
                if (!$isValid && $current_password === $hash) {
                    $isValid = true;
                }

                if ($isValid) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE admin SET admin_password_hash = :new_hash, last_password_change = NOW() WHERE admin_id = :id");
                    $up->execute(['new_hash' => $new_hash, 'id' => $admin_id]);

                    $success = "Administrator password updated successfully!";
                } else {
                    $error = "The current admin password you entered is incorrect.";
                }
            } else {
                $error = "Administrator record not found.";
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Change Admin Password - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <div class="max-w-md w-full mx-auto px-4 py-12 flex-1">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600 mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
      </div>
      <h2 class="text-2xl font-extrabold text-navy-900">Change Admin Password</h2>
      <p class="text-xs text-slate-500 mt-1">Requires the current administrator password</p>
    </div>

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
      <form action="change_password.php" method="POST" class="space-y-4">
        
        <div>
          <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Admin Password *</label>
          <input type="password" id="current_password" name="current_password" required placeholder="Enter current admin password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium text-slate-800">
        </div>

        <div>
          <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Admin Password *</label>
          <input type="password" id="new_password" name="new_password" required placeholder="Create new admin password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium text-slate-800">
        </div>

        <div>
          <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new admin password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-medium text-slate-800">
        </div>

        <button type="submit" name="change_admin_password" class="w-full py-3 px-4 bg-navy-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-slate-900/30 transition-all flex items-center justify-center gap-2">
          <span>Update Admin Password</span>
          <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </button>

      </form>

      <div class="mt-6 pt-5 border-t border-slate-200 text-center">
        <a href="dashboard.php" class="text-xs font-bold text-slate-600 hover:text-amber-600">&larr; Back to Admin Console</a>
      </div>
    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
