<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$error = "";

// Redirect if already authenticated
if (is_logged_in()) {
    header("Location: ../user/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($student_id) || empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid university email address.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } else {
        try {
            // Check for duplicate student_id or email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE student_id = :student_id OR email = :email");
            $stmt->execute(['student_id' => $student_id, 'email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "An account with this Student ID or Email already exists.";
            } else {
                // Securely hash password with password_hash()
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare("INSERT INTO member (student_id, name, email, phone, password, status) 
                                             VALUES (:student_id, :name, :email, :phone, :password, 'Verified')");
                $insertStmt->execute([
                    'student_id' => $student_id,
                    'name'       => $name,
                    'email'      => $email,
                    'phone'      => $phone,
                    'password'   => $hashed_password
                ]);

                header("Location: login.php?registered=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

$base_path = '..';
$page_title = 'Member Registration - Rentora';
require_once(__DIR__ . '/../includes/header.php');
?>

  <!-- Top Campus Notice Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <span>🇧🇩 Rentora: Academic DBMS Project - Member Registration</span>
      <a href="../index.php" class="text-blue-400 hover:text-white transition-colors">&larr; Return to Marketplace</a>
    </div>
  </div>

  <div class="max-w-lg w-full mx-auto px-4 py-8 flex-1 flex flex-col justify-center">
    <div class="text-center mb-6">
      <a href="../index.php" class="inline-flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="text-left leading-tight">
          <span class="text-2xl font-extrabold tracking-tight text-navy-900">Rentora</span>
          <span class="text-2xl font-bold text-primary-600">Hub</span>
        </div>
      </a>
      <h2 class="mt-4 text-xl font-extrabold text-navy-900">Create your member account</h2>
      <p class="text-xs text-slate-500 mt-1">Join fellow verified campus students to rent, lend, and exchange gear</p>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Registration Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xl">
      <form action="register.php" method="POST" class="space-y-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Student ID -->
          <div>
            <label for="student_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Student Roll / ID *</label>
            <input type="text" id="student_id" name="student_id" required placeholder="e.g. CSE-23-0182" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Full Name -->
          <div>
            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name *</label>
            <input type="text" id="name" name="name" required placeholder="e.g. Tanvir Ahmed" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- University Email -->
          <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campus Email *</label>
            <input type="email" id="email" name="email" required placeholder="name@univ.ac.bd" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Mobile Phone -->
          <div>
            <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile / bKash *</label>
            <input type="tel" id="phone" name="phone" required placeholder="+88017XXXXXXXX" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Password *</label>
          <input type="password" id="password" name="password" required placeholder="Create a secure password" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          <p class="text-[11px] text-slate-400 mt-1">Passwords are securely hashed using PHP password_hash().</p>
        </div>

        <button type="submit" name="register" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
          <span>Complete Registration</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <!-- Sign In Footer -->
      <div class="mt-6 pt-5 border-t border-slate-200 text-center">
        <p class="text-xs text-slate-600">
          Already registered? 
          <a href="login.php" class="font-bold text-primary-600 hover:underline">Sign In here</a>
        </p>
      </div>

    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
