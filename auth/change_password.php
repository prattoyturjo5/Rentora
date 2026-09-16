<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_member('login.php?msg=login_required');

$error = "";
$success = "";
$user_id = $_SESSION['user_id'];

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
            $stmt = $pdo->prepare("SELECT * FROM member WHERE member_id = :id OR id = :id2 LIMIT 1");
            $stmt->execute(['id' => $user_id, 'id2' => $user_id]);
            $member = $stmt->fetch();

            if ($member) {
                $hash = $member['password'];
                $isValid = password_verify($current_password, $hash);
                if (!$isValid && $current_password === $hash) {
                    $isValid = true; // Fallback for raw seed
                }

                if ($isValid) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $col = isset($member['member_id']) ? 'member_id' : 'id';
                    $up = $pdo->prepare("UPDATE member SET password = :new_hash WHERE $col = :id");
                    $up->execute(['new_hash' => $new_hash, 'id' => $member[$col]]);

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
      <h2 class="text-2xl font-extrabold text-navy-900">Change Member Password</h2>
      <p class="text-xs text-slate-500 mt-1">Requires your current password to authorize a new one</p>
    </div>

    <!-- Feedback Alerts -->
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
          <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password *</label>
          <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <div>
          <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password *</label>
          <input type="password" id="new_password" name="new_password" required placeholder="Create new password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <div>
          <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password *</label>
          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <button type="submit" name="change_password" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
          <span>Update Password</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </button>

      </form>

      <div class="mt-6 pt-5 border-t border-slate-200 text-center">
        <a href="../user/dashboard.php" class="text-xs font-bold text-slate-600 hover:text-primary-600">&larr; Back to Member Dashboard</a>
      </div>
    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
