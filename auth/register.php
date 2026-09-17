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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $full_name = trim($_POST['name'] ?? '');
    
    // Support either separate first/last name or combined name
    if (empty($first_name) && !empty($full_name)) {
        $parts = explode(' ', $full_name, 2);
        $first_name = $parts[0];
        $last_name = $parts[1] ?? $parts[0];
    }
    
    $student_id = trim($_POST['student_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $university_email = trim($_POST['university_email'] ?? $_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? $_POST['phone'] ?? '');
    $campus_address = trim($_POST['campus_address'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($student_id) || empty($username) || empty($university_email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($university_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid university email address.";
    } elseif (strlen($password) < 4) {
        $error = "Password must be at least 4 characters long.";
    } else {
        try {
            // Validate that username, university_email, and student_id aren't already taken before inserting
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE username = :username OR university_email = :email OR student_id = :student_id");
            $stmt->execute([
                'username'   => $username,
                'email'      => $university_email,
                'student_id' => $student_id
            ]);
            if ($stmt->fetchColumn() > 0) {
                $error = "An account with this username, student ID, or university email already exists.";
            } else {
                // Securely hash password with password_hash()
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insertStmt = $pdo->prepare("
                    INSERT INTO member (first_name, last_name, student_id, dob, gender, university_email, phone_number, campus_address, account_balance, username, password_hash) 
                    VALUES (:first_name, :last_name, :student_id, NULL, NULL, :university_email, :phone_number, :campus_address, 0.00, :username, :password_hash)
                ");
                $insertStmt->execute([
                    'first_name'       => $first_name,
                    'last_name'        => $last_name,
                    'student_id'       => $student_id,
                    'university_email' => $university_email,
                    'phone_number'     => !empty($phone_number) ? $phone_number : null,
                    'campus_address'   => !empty($campus_address) ? $campus_address : null,
                    'username'         => $username,
                    'password_hash'    => $hashed_password
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
          <!-- First Name -->
          <div>
            <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">First Name *</label>
            <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" placeholder="e.g. Tanvir" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Last Name -->
          <div>
            <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" placeholder="e.g. Ahmed" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Student ID -->
          <div>
            <label for="student_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Student ID *</label>
            <input type="text" id="student_id" name="student_id" required value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" placeholder="e.g. 024-231-001" maxlength="20" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Username -->
          <div>
            <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Username *</label>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="e.g. tanvir23" maxlength="50" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- University Email -->
          <div>
            <label for="university_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campus Email *</label>
            <input type="email" id="university_email" name="university_email" required value="<?php echo htmlspecialchars($_POST['university_email'] ?? $_POST['email'] ?? ''); ?>" placeholder="name@univ.ac.bd" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Mobile Phone -->
          <div>
            <label for="phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? $_POST['phone'] ?? ''); ?>" placeholder="+88017XXXXXXXX" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <!-- Campus Address -->
        <div>
          <label for="campus_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campus Address</label>
          <select id="campus_address" name="campus_address" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
            <option value="Hazari Lane" <?php echo (($_POST['campus_address'] ?? '') === 'Hazari Lane') ? 'selected' : ''; ?>>Hazari Lane</option>
            <option value="Wasa" <?php echo (($_POST['campus_address'] ?? '') === 'Wasa') ? 'selected' : ''; ?>>Wasa</option>
            <option value="GEC Campus" <?php echo (($_POST['campus_address'] ?? '') === 'GEC Campus') ? 'selected' : ''; ?>>GEC Campus</option>
          </select>
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
