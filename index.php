<?php
session_start();
require_once('DBconnect.php');

// Extract Search & Filter Parameters
$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, trim($_GET['query'])) : '';
$category_id = (isset($_GET['category_id']) && $_GET['category_id'] !== 'ALL' && $_GET['category_id'] !== '') ? (int)$_GET['category_id'] : 0;
$pickup_spot = (isset($_GET['pickup_spot']) && $_GET['pickup_spot'] !== 'ALL') ? mysqli_real_escape_string($conn, trim($_GET['pickup_spot'])) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Fetch Categories for Filter Dropdown and Category Pills
$cat_sql = "SELECT categories.*, COUNT(items.item_id) as item_count 
            FROM categories 
            LEFT JOIN items ON categories.category_id = items.category_id AND items.is_available = 1 
            GROUP BY categories.category_id";
$cat_result = mysqli_query($conn, $cat_sql);
$categories = [];
while ($cat_row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $cat_row;
}

// Construct Query for Equipment Items
$where_clauses = ["items.is_available = 1"];

if (!empty($query)) {
    $where_clauses[] = "(items.title LIKE '%$query%' OR items.description LIKE '%$query%')";
}
if ($category_id > 0) {
    $where_clauses[] = "items.category_id = $category_id";
}
if (!empty($pickup_spot)) {
    $where_clauses[] = "items.campus_spot = '$pickup_spot'";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Sorting
$order_sql = "ORDER BY items.item_id DESC";
if ($sort === 'price_asc') {
    $order_sql = "ORDER BY items.daily_rate ASC";
} elseif ($sort === 'price_desc') {
    $order_sql = "ORDER BY items.daily_rate DESC";
} elseif ($sort === 'deposit_asc') {
    $order_sql = "ORDER BY items.security_deposit ASC";
}

$items_sql = "SELECT items.*, categories.name AS category_name, users.name AS owner_name, users.student_id AS owner_student_id 
              FROM items 
              JOIN categories ON items.category_id = categories.category_id 
              JOIN users ON items.owner_id = users.user_id 
              $where_sql $order_sql";
$items_result = mysqli_query($conn, $items_sql);
$total_items = mysqli_num_rows($items_result);

// Quick platform counters
$count_students_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role != 'Admin'");
$count_students = mysqli_fetch_assoc($count_students_res)['total'];

$count_avail_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM items WHERE is_available = 1");
$count_avail = mysqli_fetch_assoc($count_avail_res)['total'];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampusRent Hub - University Equipment Exchange & Rental Hub</title>
  
  <!-- Tailwind CSS CDN with Custom Theme -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            navy: { 800: '#1e293b', 900: '#0f172a', 950: '#0a0f1d' },
            primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
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
<body class="flex flex-col min-h-screen text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

  <!-- Top Campus Notice Bar -->
  <div class="bg-navy-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
      <div class="flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span class="font-medium text-slate-200">Dhaka Campus Escrow Active:</span> 
        <span>Zero platform fee for verified university student equipment exchanges.</span>
      </div>
      <div class="flex items-center gap-4 text-slate-400">
        <span class="flex items-center gap-1">
          <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
          Pickup Points: Central Library, TSC & Engineering Lab
        </span>
        <span class="hidden sm:inline">|</span>
        <a href="admin.php" class="hover:text-white transition-colors flex items-center gap-1 text-slate-300">
          <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          Admin Console
        </a>
      </div>
    </div>
  </div>

  <!-- Global Header Navigation -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        
        <!-- Brand Logo -->
        <a href="index.php" class="flex items-center gap-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-navy-900 to-primary-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="text-xl font-extrabold tracking-tight text-navy-900">CampusRent</span>
              <span class="text-xl font-bold text-primary-600">Hub</span>
            </div>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Equipment Exchange</p>
          </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
          <a href="index.php" class="px-3.5 py-2 text-sm font-semibold text-primary-600 rounded-lg bg-blue-50/80">
            Browse Equipment
          </a>
          <a href="owner-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>Lender Hub</span>
            <span class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-bold">Earn ৳</span>
          </a>
          <a href="renter-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>My Rentals</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">Active</span>
          </a>
        </nav>

        <!-- Right Side Controls & Profile Switcher -->
        <div class="hidden sm:flex items-center gap-3">
          
          <!-- Campus Spot Selector -->
          <div class="relative">
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700">
              <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
              <span>Dhaka Central Campus</span>
            </button>
          </div>

          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Authenticated User Profile Badge -->
            <div class="flex items-center pl-3 border-l border-slate-200 gap-2.5">
              <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-xs">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
              </div>
              <div class="text-left leading-tight hidden lg:block">
                <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                <div class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1">
                  <span><?php echo htmlspecialchars($_SESSION['student_id']); ?></span>
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </div>
              </div>
              <a href="logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 transition-colors p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              </a>
            </div>

            <a href="owner-dashboard.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-navy-900 hover:bg-navy-800 text-white text-xs font-semibold shadow-sm transition-all hover:shadow-md">
              <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              <span>+ List Item</span>
            </a>
          <?php else: ?>
            <!-- Guest Buttons -->
            <a href="signin.php" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-primary-600 transition-colors">
              Sign In
            </a>
            <a href="register.php" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all">
              Register Student
            </a>
          <?php endif; ?>

        </div>

        <!-- Mobile Menu Button -->
        <div class="flex md:hidden items-center gap-2">
          <a href="renter-dashboard.php" class="p-1.5 text-slate-600 relative">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          </a>
          <button id="mobile-menu-btn" class="p-2 rounded-lg text-slate-600 hover:text-navy-900 hover:bg-slate-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Dropdown Navigation -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-2">
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-xl mb-3">
          <div class="w-9 h-9 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-sm">
            <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
          </div>
          <div>
            <div class="text-sm font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            <div class="text-xs text-emerald-600 font-semibold"><?php echo htmlspecialchars($_SESSION['student_id']); ?> &bull; <?php echo htmlspecialchars($_SESSION['role']); ?></div>
          </div>
        </div>
      <?php endif; ?>
      <a href="index.php" class="block px-3 py-2 text-base font-semibold text-primary-600 bg-blue-50 rounded-lg">Browse Equipment</a>
      <a href="owner-dashboard.php" class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Lender Hub (Earn ৳)</a>
      <a href="renter-dashboard.php" class="block px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Renter Dashboard (Active Tokens)</a>
      <a href="admin.php" class="block px-3 py-2 text-base font-medium text-amber-700 hover:bg-amber-50 rounded-lg">Admin Operations Console</a>
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php" class="block px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded-lg">Sign Out</a>
      <?php else: ?>
        <a href="signin.php" class="block px-3 py-2 text-base font-medium text-primary-600 hover:bg-blue-50 rounded-lg">Sign In</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Hero Section with Multi-Parameter Search -->
  <section class="relative bg-gradient-to-b from-navy-900 via-navy-900 to-slate-900 text-white pt-12 pb-20 overflow-hidden">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 -left-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      
      <!-- Academic Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-blue-200 mb-6 backdrop-blur-sm">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span>Trusted University Equipment Exchange & Escrow Protocol</span>
      </div>

      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white max-w-3xl mx-auto leading-tight">
        Rent Expensive Lab Kits & Gear <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">Directly on Campus</span>
      </h1>
      <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto font-normal">
        Exchange scientific calculators, drafters, cameras, and IoT kits with verified classmates. Safe in-person pickup with 100% escrow deposit protection.
      </p>

      <!-- Search & Filter Form (Procedural PHP GET) -->
      <div class="mt-8 max-w-4xl mx-auto bg-white p-3 sm:p-4 rounded-2xl shadow-2xl border border-slate-200/80 text-left">
        <form id="filter-form" action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          
          <!-- Search Input -->
          <div class="relative">
            <label for="search_query" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Keyword</label>
            <div class="relative">
              <input type="text" id="search_query" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="e.g. Casio, Drafter, Arduino" class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 placeholder-slate-400 font-medium">
              <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
          </div>

          <!-- Category Selector -->
          <div>
            <label for="category_id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Category</label>
            <select id="category_id" name="category_id" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
              <option value="ALL">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php if ($category_id == $cat['category_id']) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['item_count']; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Campus Pickup Spot -->
          <div>
            <label for="pickup_spot" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Campus Spot</label>
            <select id="pickup_spot" name="pickup_spot" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
              <option value="ALL">Any Campus Spot</option>
              <option value="Central Library Front Gate" <?php if ($pickup_spot === 'Central Library Front Gate') echo 'selected'; ?>>Central Library Front</option>
              <option value="Campus Cafeteria Entrance" <?php if ($pickup_spot === 'Campus Cafeteria Entrance') echo 'selected'; ?>>Campus Cafeteria</option>
              <option value="Academic Building-1 Gate" <?php if ($pickup_spot === 'Academic Building-1 Gate') echo 'selected'; ?>>Academic Building-1</option>
              <option value="Engineering Lab Complex (3rd Floor)" <?php if ($pickup_spot === 'Engineering Lab Complex (3rd Floor)') echo 'selected'; ?>>Engineering Lab</option>
              <option value="TSC Ground / Student Union" <?php if ($pickup_spot === 'TSC Ground / Student Union') echo 'selected'; ?>>TSC Ground</option>
            </select>
          </div>

          <!-- Submit Button -->
          <div class="flex items-end">
            <button type="submit" class="w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              <span>Search Gear</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Quick Category Pills -->
      <div class="mt-6 flex flex-wrap justify-center items-center gap-2 text-xs">
        <span class="text-slate-400 font-medium mr-1">Popular:</span>
        <a href="index.php" class="px-3 py-1.5 rounded-full <?php echo ($category_id === 0) ? 'bg-white/30 text-white font-bold' : 'bg-white/10 text-slate-200'; ?> hover:bg-white/20 transition-colors border border-white/15">
          All Gear
        </a>
        <?php foreach ($categories as $cat): ?>
          <a href="index.php?category_id=<?php echo $cat['category_id']; ?>" class="px-3 py-1.5 rounded-full <?php echo ($category_id == $cat['category_id']) ? 'bg-white/30 text-white font-bold' : 'bg-white/10 text-slate-200'; ?> hover:bg-white/20 transition-colors border border-white/15">
            <?php echo htmlspecialchars($cat['name']); ?>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- Dynamic Live Stats Strip -->
  <section class="bg-white border-b border-slate-200 py-3 shadow-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center divide-x divide-slate-100">
        <div>
          <span class="block text-xl font-extrabold text-navy-900"><?php echo $count_students; ?>+</span>
          <span class="text-xs text-slate-500 font-medium">Registered Students</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-primary-600"><?php echo $count_avail; ?>+</span>
          <span class="text-xs text-slate-500 font-medium">Available Instruments</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-emerald-600">৳0 Fee</span>
          <span class="text-xs text-slate-500 font-medium">Peer-to-Peer Escrow</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-amber-600">6 Pickup Zones</span>
          <span class="text-xs text-slate-500 font-medium">Verified Meeting Points</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content Area: Equipment Catalog Grid -->
  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
    
    <!-- Section Header & Filter Controls -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h2 class="text-2xl font-extrabold text-navy-900 tracking-tight">Available Campus Equipment</h2>
        <p class="text-sm text-slate-500 mt-1">Showing <?php echo $total_items; ?> verified equipment items in database</p>
      </div>

      <!-- Sort Form -->
      <form action="index.php" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
        <?php if (!empty($query)): ?><input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>"><?php endif; ?>
        <?php if ($category_id > 0): ?><input type="hidden" name="category_id" value="<?php echo $category_id; ?>"><?php endif; ?>
        <?php if (!empty($pickup_spot)): ?><input type="hidden" name="pickup_spot" value="<?php echo htmlspecialchars($pickup_spot); ?>"><?php endif; ?>
        
        <label for="sort" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Sort By:</label>
        <select id="sort" name="sort" onchange="this.form.submit()" class="px-3 py-1.5 text-xs bg-white border border-slate-200 rounded-lg text-slate-700 font-medium focus:ring-primary-600 focus:border-primary-600">
          <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest First</option>
          <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Rental Rate: Low to High</option>
          <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Rental Rate: High to Low</option>
          <option value="deposit_asc" <?php if ($sort === 'deposit_asc') echo 'selected'; ?>>Deposit: Low to High</option>
        </select>
      </form>
    </div>

    <!-- Equipment Grid -->
    <?php if ($total_items > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php while ($item = mysqli_fetch_assoc($items_result)): 
          // Condition badge styling
          $cond_class = 'bg-emerald-100 text-emerald-800 border-emerald-200';
          if ($item['item_condition'] === 'Good') {
              $cond_class = 'bg-blue-100 text-blue-800 border-blue-200';
          } elseif ($item['item_condition'] === 'Fair') {
              $cond_class = 'bg-amber-100 text-amber-800 border-amber-200';
          }
          $item_image = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80';
        ?>
          <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 card-hover flex flex-col">
            
            <!-- Image Container -->
            <div class="relative h-48 w-full bg-slate-100 overflow-hidden group">
              <img src="<?php echo htmlspecialchars($item_image); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                <span class="badge-status <?php echo $cond_class; ?> shadow-sm backdrop-blur-md">
                  <?php echo htmlspecialchars($item['item_condition']); ?>
                </span>
                <span class="badge-status bg-slate-900/80 text-white backdrop-blur-md">
                  <?php echo htmlspecialchars($item['category_name']); ?>
                </span>
              </div>
              <div class="absolute bottom-3 right-3 bg-white/95 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-navy-900 shadow">
                ৳<?php echo number_format($item['daily_rate']); ?> <span class="text-[10px] text-slate-500 font-normal">/ day</span>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <div class="text-[11px] font-semibold text-primary-600 uppercase tracking-wider mb-1">
                  <?php echo htmlspecialchars($item['category_name']); ?>
                </div>
                <h3 class="font-bold text-navy-900 text-base leading-snug line-clamp-2 hover:text-primary-600 transition-colors">
                  <a href="item-details.php?id=<?php echo $item['item_id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                </h3>

                <!-- Deposit & Pickup Spot -->
                <div class="mt-3 space-y-1.5 text-xs text-slate-500 border-y border-slate-100 py-2.5 my-3">
                  <div class="flex items-center justify-between">
                    <span class="text-slate-500">Security Deposit:</span>
                    <span class="font-bold text-slate-700">৳<?php echo number_format($item['security_deposit']); ?> <span class="text-[10px] font-normal text-emerald-600">(Refundable)</span></span>
                  </div>
                  <div class="flex items-center gap-1.5 text-slate-600 truncate" title="<?php echo htmlspecialchars($item['campus_spot']); ?>">
                    <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                    <span class="truncate"><?php echo htmlspecialchars($item['campus_spot']); ?></span>
                  </div>
                </div>
              </div>

              <!-- Footer: Lender Info & Rent Button -->
              <div>
                <div class="flex items-center justify-between text-xs text-slate-500 mb-3.5">
                  <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                      <?php echo strtoupper(substr($item['owner_name'], 0, 1)); ?>
                    </div>
                    <span class="font-medium text-slate-700 truncate max-w-[120px]"><?php echo htmlspecialchars($item['owner_name']); ?></span>
                  </div>
                  <div class="text-[11px] font-semibold text-primary-600">
                    <?php echo htmlspecialchars($item['owner_student_id']); ?>
                  </div>
                </div>

                <!-- View & Rent Action Button -->
                <a href="item-details.php?id=<?php echo $item['item_id']; ?>" class="w-full py-2.5 px-4 rounded-xl bg-navy-900 hover:bg-primary-600 text-white text-xs font-bold text-center block shadow transition-all hover:shadow-md">
                  View & Rent Equipment
                </a>
              </div>

            </div>

          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <!-- Empty State -->
      <div class="text-center py-16 bg-white rounded-2xl border border-slate-200 p-8 my-6">
        <div class="w-16 h-16 bg-blue-50 text-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-lg font-bold text-navy-900">No equipment found matching criteria</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">Try relaxing your category or pickup spot filter, or search with different keywords.</p>
        <a href="index.php" class="inline-block mt-4 px-4 py-2 bg-primary-600 text-white rounded-xl text-xs font-bold hover:bg-primary-700 transition-colors">
          Reset All Filters
        </a>
      </div>
    <?php endif; ?>

  </main>

  <!-- Global Footer -->
  <footer class="bg-navy-950 text-slate-400 text-sm border-t border-slate-800 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <div class="space-y-4">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold">CR</div>
            <span class="text-lg font-bold text-white">CampusRent Hub</span>
          </div>
          <p class="text-xs text-slate-400 leading-relaxed">
            The dedicated peer-to-peer equipment sharing and rental hub for Bangladeshi university campuses. Built with procedural PHP & MySQL.
          </p>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Quick Navigation</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="index.php" class="hover:text-white transition-colors">Browse All Equipment</a></li>
            <li><a href="owner-dashboard.php" class="hover:text-white transition-colors">Lender Dashboard & Earnings</a></li>
            <li><a href="renter-dashboard.php" class="hover:text-white transition-colors">Active Handover Token Box</a></li>
            <li><a href="admin.php" class="hover:text-white transition-colors">Admin Student Verification</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Official Handover Spots</h4>
          <ul class="space-y-2 text-xs">
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Central Library Front Gate</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Campus Cafeteria Entrance</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Academic Building-1 Gate</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Engineering Lab Complex</li>
          </ul>
        </div>

        <div class="space-y-3">
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Payment & Protection</h4>
          <div class="p-3 bg-slate-900 rounded-xl border border-slate-800 text-xs">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span class="font-bold text-white">100% Escrow Guarantee</span>
            </div>
            <p class="text-[11px] text-slate-400">
              Security deposits are held in platform trust account. Never pay cash outside the official Handover Token protocol.
            </p>
          </div>
        </div>

      </div>

      <div class="mt-10 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-400">
        <div>&copy; <?php echo date('Y'); ?> CampusRent Hub. Procedural PHP & MySQL DBMS Coursework.</div>
        <div class="flex gap-4">
          <a href="admin.php" class="text-amber-400 hover:underline font-semibold">Moderation Console</a>
          <a href="signin.php" class="hover:text-white">Account Login</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
