<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$error = "";
$success = "";

if (isset($_GET['registered'])) {
    $success = "Registration successful! You can now sign in with your credentials.";
}
if (isset($_GET['logged_out'])) {
    $success = "You have successfully signed out.";
}
if (isset($_GET['password_changed'])) {
    $success = "Password changed successfully! Please sign in with your new password.";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'login_required') {
    $error = "Please sign in to access that page.";
}

// Redirect if already authenticated
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        header("Location: ../user/dashboard.php");
        exit();
    }
}

// Handle Sign In submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['signin']) || isset($_POST['username']))) {
    $username = trim($_POST['username'] ?? $_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please provide both username and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM member WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $member = $stmt->fetch();

            if ($member && password_verify($password, $member['password_hash'])) {
                $_SESSION['role'] = 'member';
                $_SESSION['member_id'] = (int)$member['member_id'];
                $_SESSION['user_id'] = (int)$member['member_id'];
                $_SESSION['username'] = $member['username'];
                $_SESSION['name'] = trim($member['first_name'] . ' ' . $member['last_name']);
                $_SESSION['email'] = $member['university_email'] ?? '';
                $_SESSION['student_id'] = $member['student_id'] ?? '';
                $_SESSION['status'] = $member['status'] ?? 'Pending';

                header("Location: ../user/dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Member Sign In - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <!-- Sign In Container -->
  <div class="max-w-md w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center bg-canvas">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-2xl bg-accent text-white flex items-center justify-center font-bold text-xl shadow-md shadow-accent/20 mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
      </div>
      <h2 class="text-2xl font-extrabold text-primary">Member Sign In</h2>
      <p class="text-xs text-muted mt-1">Access your campus equipment rentals and listings</p>
    </div>

    <!-- Notifications -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="bg-surface p-6 sm:p-8 rounded-2xl border border-subtle shadow-float transition-colors">
      <form action="login.php" method="POST" class="space-y-4">
        
        <div>
          <label for="username" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Username</label>
          <div class="relative">
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? $_POST['login'] ?? ''); ?>" placeholder="e.g. tanvir23" class="input-subtle pl-9 text-xs">
            <svg class="w-4 h-4 text-muted absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-1">
            <label for="password" class="block text-xs font-bold text-muted uppercase tracking-wider font-mono">Password</label>
          </div>
          <div class="relative">
            <input type="password" id="password" name="password" required placeholder="Enter your password" class="input-subtle pl-9 text-xs">
            <svg class="w-4 h-4 text-muted absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
        </div>

        <button type="submit" name="signin" class="btn-accent w-full py-3 text-xs uppercase tracking-wider">
          <span>Sign In as Member</span>
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <!-- Member Links -->
      <div class="mt-6 pt-5 border-t border-subtle text-center space-y-2">
        <p class="text-xs text-muted">
          Don't have a member account? 
          <a href="register.php" class="font-bold text-accent hover:underline">Register Student Account</a>
        </p>
        <p class="text-xs text-muted">
          Need Admin Access? 
          <a href="../admin/login.php" class="font-bold text-amber-600 dark:text-amber-400 hover:underline">Admin Login &rarr;</a>
        </p>
      </div>

    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
