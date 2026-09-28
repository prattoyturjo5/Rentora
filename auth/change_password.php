<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

// Must be logged in as a member
if (($_SESSION['role'] ?? '') !== 'member') {
    header("Location: login.php?msg=login_required");
    exit();
}

$error = "";
$success = "";
$member_id = $_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
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
            // Retrieve current member password_hash
            $stmt = $pdo->prepare("SELECT * FROM member WHERE member_id = :id LIMIT 1");
            $stmt->execute(['id' => $member_id]);
            $member = $stmt->fetch();

            if ($member) {
                // Verify current password with password_verify()
                if (password_verify($current_password, $member['password_hash'])) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE member SET password_hash = :new_hash WHERE member_id = :id");
                    $up->execute(['new_hash' => $new_hash, 'id' => $member_id]);

                    $success = "Your password has been changed successfully!";
                } else {
                    $error = "The current password you entered is incorrect.";
                }
            } else {
                $error = "Member account not found.";
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Change Password - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <div class="max-w-md w-full mx-auto px-4 py-12 flex-1">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-accent mx-auto mb-3 shadow-inner">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
      </div>
      <h2 class="text-2xl font-extrabold text-primary">Change Member Password</h2>
      <p class="text-xs text-muted mt-1">Requires your current password to authorize a new one</p>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <div class="card-floating p-6 sm:p-8">
      <form action="change_password.php" method="POST" class="space-y-4">
        
        <div>
          <label for="current_password" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Current Password *</label>
          <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" class="input-subtle w-full px-3 py-2.5 text-xs font-medium">
        </div>

        <div>
          <label for="new_password" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">New Password *</label>
          <input type="password" id="new_password" name="new_password" required placeholder="Create new password" class="input-subtle w-full px-3 py-2.5 text-xs font-medium">
        </div>

        <div>
          <label for="confirm_password" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Confirm New Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password" class="input-subtle w-full px-3 py-2.5 text-xs font-medium">
        </div>

        <button type="submit" name="change_password" class="btn-accent w-full py-3 px-4 font-bold rounded-xl text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2">
          <span>Update Password</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </button>

      </form>

      <div class="mt-6 pt-5 border-t border-subtle text-center">
        <a href="../user/dashboard.php" class="text-xs font-bold text-muted hover:text-accent transition">&larr; Back to Member Dashboard</a>
      </div>
    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
