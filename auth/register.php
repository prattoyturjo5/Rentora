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
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM member WHERE username = :username OR university_email = :email OR student_id = :student_id");
            $stmt->execute([
                'username'   => $username,
                'email'      => $university_email,
                'student_id' => $student_id
            ]);
            if ($stmt->fetchColumn() > 0) {
                $error = "An account with this username, student ID, or university email already exists.";
            } else {
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
require_once(__DIR__ . '/../includes/nav.php');
?>

  <div class="max-w-lg w-full mx-auto px-4 py-12 flex-1 flex flex-col justify-center bg-canvas">
    <div class="text-center mb-6">
      <div class="w-12 h-12 rounded-2xl bg-accent text-white flex items-center justify-center font-bold text-xl shadow-md shadow-accent/20 mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
      </div>
      <h2 class="text-2xl font-extrabold text-primary">Create Member Account</h2>
      <p class="text-xs text-muted mt-1">Join fellow verified campus students to rent, lend, and exchange gear</p>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Registration Card -->
    <div class="bg-surface p-6 sm:p-8 rounded-2xl border border-subtle shadow-float transition-colors">
      <form action="register.php" method="POST" class="space-y-4">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- First Name -->
          <div>
            <label for="first_name" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">First Name *</label>
            <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" placeholder="e.g. Tanvir" class="input-subtle text-xs">
          </div>

          <!-- Last Name -->
          <div>
            <label for="last_name" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Last Name *</label>
            <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" placeholder="e.g. Ahmed" class="input-subtle text-xs">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Student ID -->
          <div>
            <label for="student_id" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Student ID *</label>
            <input type="text" id="student_id" name="student_id" required value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" placeholder="e.g. 024-231-001" maxlength="20" class="input-subtle text-xs">
          </div>

          <!-- Username -->
          <div>
            <label for="username" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Username *</label>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="e.g. tanvir23" maxlength="50" class="input-subtle text-xs">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- University Email -->
          <div>
            <label for="university_email" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Campus Email *</label>
            <input type="email" id="university_email" name="university_email" required value="<?php echo htmlspecialchars($_POST['university_email'] ?? $_POST['email'] ?? ''); ?>" placeholder="name@bscse.puc.ac.bd" class="input-subtle text-xs">
          </div>

          <!-- Mobile Phone -->
          <div>
            <label for="phone_number" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Phone Number</label>
            <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? $_POST['phone'] ?? ''); ?>" placeholder="018XXXXXXXX" class="input-subtle text-xs">
          </div>
        </div>

        <!-- Campus Address -->
        <div>
          <label for="campus_address" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Campus Zone</label>
          <select id="campus_address" name="campus_address" class="input-subtle text-xs">
            <option value="Hazari Lane" <?php echo (($_POST['campus_address'] ?? '') === 'Hazari Lane') ? 'selected' : ''; ?>>Hazari Lane</option>
            <option value="Wasa" <?php echo (($_POST['campus_address'] ?? '') === 'Wasa') ? 'selected' : ''; ?>>Wasa</option>
            <option value="GEC Campus" <?php echo (($_POST['campus_address'] ?? '') === 'GEC Campus') ? 'selected' : ''; ?>>GEC Campus</option>
          </select>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Password *</label>
          <input type="password" id="password" name="password" required placeholder="Create a secure password" class="input-subtle text-xs">
        </div>

        <button type="submit" name="register" class="btn-accent w-full py-3 text-xs uppercase tracking-wider">
          <span>Complete Registration</span>
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <!-- Sign In Footer -->
      <div class="mt-6 pt-5 border-t border-subtle text-center">
        <p class="text-xs text-muted">
          Already registered? 
          <a href="login.php" class="font-bold text-accent hover:underline">Sign In here</a>
        </p>
      </div>

    </div>
  </div>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
