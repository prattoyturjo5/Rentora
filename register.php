<?php
session_start();
require_once('DBconnect.php');

$error = "";

if (isset($_POST['register'])) {
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    if (empty($student_id) || empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check for duplicate student_id or email
        $check_sql = "SELECT * FROM users WHERE student_id = '$student_id' OR email = '$email'";
        $check_res = mysqli_query($conn, $check_sql);

        if ($check_res && mysqli_num_rows($check_res) > 0) {
            $error = "An account with this Student ID or Email already exists.";
        } else {
            // Insert user into database
            $insert_sql = "INSERT INTO users (student_id, name, email, password, phone, role, status) 
                           VALUES ('$student_id', '$name', '$email', '$password', '$phone', '$role', 'Verified')";
            
            if (mysqli_query($conn, $insert_sql)) {
                header("Location: signin.php?registered=1");
                exit();
            } else {
                $error = "Registration error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration - CampusRent Hub</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            navy: { 800: '#1e293b', 900: '#0f172a', 950: '#0a0f1d' },
            primary: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex flex-col min-h-screen text-slate-800 antialiased selection:bg-blue-600 selection:text-white justify-between">

  <!-- Top Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <span>🇧🇩 Rentora: Academic DBMS Project - Procedural PHP & MySQL (phpMyAdmin)</span>
      <a href="index.php" class="text-blue-400 hover:text-white transition-colors">&larr; Return to Equipment Catalog</a>
    </div>
  </div>

  <div class="max-w-lg w-full mx-auto px-4 py-8">
    <div class="text-center mb-6">
      <a href="index.php" class="inline-flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="text-left leading-tight">
          <span class="text-2xl font-extrabold tracking-tight text-navy-900">CampusRent</span>
          <span class="text-2xl font-bold text-primary-600">Hub</span>
        </div>
      </a>
      <h2 class="mt-4 text-xl font-extrabold text-navy-900">Create your student account</h2>
      <p class="text-xs text-slate-500 mt-1">Join fellow verified campus students to rent and lend gear</p>
    </div>

    <!-- Error Alert -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo $error; ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Registration Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xl">
      <form action="register.php" method="POST" class="space-y-4">
        
        <!-- Role Switcher -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Your Primary Role</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="cursor-pointer border border-slate-300 rounded-xl p-3 text-center hover:bg-slate-50 transition-all block has-[:checked]:border-primary-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-primary-600">
              <input type="radio" name="role" value="Renter" checked class="hidden">
              <span class="block text-xs font-extrabold text-navy-900">Student Renter</span>
              <span class="block text-[10px] text-slate-500 mt-0.5">Rent equipment from peers</span>
            </label>

            <label class="cursor-pointer border border-slate-300 rounded-xl p-3 text-center hover:bg-slate-50 transition-all block has-[:checked]:border-primary-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-primary-600">
              <input type="radio" name="role" value="Owner" class="hidden">
              <span class="block text-xs font-extrabold text-navy-900">Equipment Owner</span>
              <span class="block text-[10px] text-slate-500 mt-0.5">List & monetize your gear</span>
            </label>
          </div>
        </div>

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
            <input type="email" id="email" name="email" required placeholder="e.g. student@univ.ac.bd" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <!-- Phone Number -->
          <div>
            <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Mobile (+880) *</label>
            <input type="tel" id="phone" name="phone" required placeholder="01712-345678" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Account Password *</label>
          <input type="password" id="password" name="password" required placeholder="Create password (min. 6 characters)" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <button type="submit" name="register" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
          <span>Create Student Account</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <div class="mt-5 text-center text-xs text-slate-500">
        Already registered? 
        <a href="signin.php" class="font-bold text-primary-600 hover:underline">Sign In here</a>
      </div>

    </div>
  </div>

  <footer class="text-center text-xs text-slate-400 py-4 border-t border-slate-200 bg-white">
    &copy; <?php echo date('Y'); ?> Rentora: Campus Equipment Exchange & Rental Hub. Procedural PHP & MySQL.
  </footer>
</body>
</html>
