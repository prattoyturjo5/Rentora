<?php
session_start();
require_once('DBconnect.php');

$error = "";
$success = "";

if (isset($_GET['registered'])) {
    $success = "Registration successful! Please sign in with your student credentials.";
}
if (isset($_GET['logged_out'])) {
    $success = "You have successfully signed out.";
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Admin') {
        header("Location: admin.php");
        exit();
    } elseif ($_SESSION['role'] == 'Owner') {
        header("Location: owner-dashboard.php");
        exit();
    } else {
        header("Location: renter-dashboard.php");
        exit();
    }
}

// Process Sign In
if (isset($_POST['signin'])) {
    $login = mysqli_real_escape_string($conn, trim($_POST['login']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    if (empty($login) || empty($password)) {
        $error = "Please enter both Student ID/Email and Password.";
    } else {
        $sql = "SELECT * FROM users WHERE (email = '$login' OR student_id = '$login') AND password = '$password'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['status'] = $row['status'];

            if ($row['role'] == 'Admin') {
                header("Location: admin.php");
                exit();
            } elseif ($row['role'] == 'Owner') {
                header("Location: owner-dashboard.php");
                exit();
            } else {
                header("Location: renter-dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid Student ID/Email or Password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - CampusRent Hub</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            navy: { 800: '#1e293b', 900: '#0f172a', 950: '#0a0f1d' },
            primary: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
            bkash: '#e2136e',
            nagad: '#f7941d'
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

  <!-- Sign In Container -->
  <div class="max-w-md w-full mx-auto px-4 py-8">
    <div class="text-center mb-8">
      <a href="index.php" class="inline-flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white font-bold shadow-md shadow-blue-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="text-left leading-tight">
          <span class="text-2xl font-extrabold tracking-tight text-navy-900">CampusRent</span>
          <span class="text-2xl font-bold text-primary-600">Hub</span>
        </div>
      </a>
      <h2 class="mt-4 text-xl font-extrabold text-navy-900">Sign in to your university account</h2>
      <p class="text-xs text-slate-500 mt-1">Rent lab equipment or list your own gear on campus</p>
    </div>

    <!-- Notifications -->
    <?php if (!empty($error)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo $error; ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo $success; ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Card -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xl">
      <form action="signin.php" method="POST" class="space-y-4">
        
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
            <span class="text-[11px] text-slate-400">Default: 123456</span>
          </div>
          <div class="relative">
            <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
          </div>
        </div>

        <button type="submit" name="signin" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
          <span>Sign In to Dashboard</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>

      <!-- Quick Demo Login Credentials Buttons for Testing -->
      <div class="mt-6 pt-5 border-t border-slate-200">
        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-2">⚡ Quick Demo Logins</span>
        <div class="grid grid-cols-3 gap-2">
          <button onclick="fillLogin('CSE-22-0145', '123456')" class="py-1.5 px-2 bg-slate-100 hover:bg-blue-50 hover:text-primary-600 text-slate-700 font-semibold rounded-lg text-[10px] transition-colors border border-slate-200">
            Renter (Rafiqul)
          </button>
          <button onclick="fillLogin('CSE-21-0342', '123456')" class="py-1.5 px-2 bg-slate-100 hover:bg-blue-50 hover:text-primary-600 text-slate-700 font-semibold rounded-lg text-[10px] transition-colors border border-slate-200">
            Owner (Tanvir)
          </button>
          <button onclick="fillLogin('admin@univ.ac.bd', 'admin123')" class="py-1.5 px-2 bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-700 font-semibold rounded-lg text-[10px] transition-colors border border-slate-200">
            Admin (Proctor)
          </button>
        </div>
      </div>

      <div class="mt-5 text-center text-xs text-slate-500">
        Don't have an account? 
        <a href="register.php" class="font-bold text-primary-600 hover:underline">Register as Student</a>
      </div>

    </div>
  </div>

  <footer class="text-center text-xs text-slate-400 py-4 border-t border-slate-200 bg-white">
    &copy; <?php echo date('Y'); ?> Rentora: Campus Equipment Exchange & Rental Hub. Procedural PHP & MySQL.
  </footer>

  <script>
    function fillLogin(user, pass) {
      document.getElementById('login').value = user;
      document.getElementById('password').value = pass;
    }
  </script>
</body>
</html>
