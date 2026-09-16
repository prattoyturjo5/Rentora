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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    $login = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login) || empty($password)) {
        $error = "Please provide your Student ID or Email and Password.";
    } else {
        try {
            // Find member by email or student_id
            $stmt = $pdo->prepare("SELECT * FROM member WHERE email = :login1 OR student_id = :login2 LIMIT 1");
            $stmt->execute(['login1' => $login, 'login2' => $login]);
            $member = $stmt->fetch();

            if ($member) {
                $hash = $member['password'];
                $isValid = password_verify($password, $hash);

                // Safe fallback if raw plain text was seeded initially
                if (!$isValid && $password === $hash) {
                    $isValid = true;
                    // Auto-rehash to secure hash
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $memberIdCol = isset($member['member_id']) ? 'member_id' : 'id';
                    $upStmt = $pdo->prepare("UPDATE member SET password = :newHash WHERE $memberIdCol = :id");
                    $upStmt->execute(['newHash' => $newHash, 'id' => $member[$memberIdCol]]);
                }

                if ($isValid) {
                    $memberId = $member['member_id'] ?? $member['id'] ?? 0;
                    $_SESSION['role'] = 'member';
                    $_SESSION['user_id'] = $memberId;
                    $_SESSION['name'] = $member['name'] ?? 'Member';
                    $_SESSION['email'] = $member['email'] ?? '';
                    $_SESSION['student_id'] = $member['student_id'] ?? '';
                    $_SESSION['phone'] = $member['phone'] ?? '';

                    header("Location: ../user/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid credentials. Please verify your Student ID/Email and password.";
                }
            } else {
                $error = "No member account found with those credentials.";
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Member Sign In - Rentora';
require_once(__DIR__ . '/../includes/header.php');
?>

  <!-- Top Campus Notice Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <span>🇧🇩 Rentora: Academic DBMS Project - Member Authentication</span>
      <a href="../index.php" class="text-blue-400 hover:text-white transition-colors">&larr; Return to Marketplace</a>
    </div>
  </div>

  <!-- Sign In Container -->
  <div class="max-w-md w-full mx-auto px-4 py-8 flex-1 flex flex-col justify-center">
    <div class="text-center mb-8">
      <a href="../index.php" class="inline-flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="text-left leading-tight">
          <span class="text-2xl font-extrabold tracking-tight text-navy-900">Rentora</span>
          <span class="text-2xl font-bold text-primary-600">Hub</span>
        </div>
      </a>
      <h2 class="mt-4 text-xl font-extrabold text-navy-900">Member Sign In</h2>
      <p class="text-xs text-slate-500 mt-1">Access your campus equipment rentals and listings</p>
    </div>

    <!-- Notifications -->
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

    <!-- Main Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xl">
      <form action="login.php" method="POST" class="space-y-4">
        
        <div>
          <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Student ID or Email</label>
          <div class="relative">
            <input type="text" id="login" name="login" required placeholder="e.g. CSE-22-0145 or student@univ.ac.bd" class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          </div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-1">
            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
            <span class="text-[11px] text-slate-400">Secured via password_verify()</span>
          </div>
          <div class="relative">
            <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
        </div>

        <button type="submit" name="signin" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
          <span>Sign In as Member</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <!-- Member Links -->
      <div class="mt-6 pt-5 border-t border-slate-200 text-center space-y-2">
        <p class="text-xs text-slate-600">
          Don't have a member account? 
          <a href="register.php" class="font-bold text-primary-600 hover:underline">Register Student Account</a>
        </p>
        <p class="text-xs text-slate-500">
          Need Admin Access? 
          <a href="../admin/login.php" class="font-bold text-amber-600 hover:underline">Admin Login &rarr;</a>
        </p>
      </div>

    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
