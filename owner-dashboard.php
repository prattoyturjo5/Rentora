<?php
session_start();
require_once('DBconnect.php');

// If not logged in, default to Demo Owner (Tanvir Ahmed, ID: 2) for smooth testing
if (!isset($_SESSION['user_id'])) {
    $demo_sql = "SELECT * FROM users WHERE user_id = 2";
    $demo_res = mysqli_query($conn, $demo_sql);
    if ($demo_res && mysqli_num_rows($demo_res) > 0) {
        $demo_user = mysqli_fetch_assoc($demo_res);
        $_SESSION['user_id'] = $demo_user['user_id'];
        $_SESSION['student_id'] = $demo_user['student_id'];
        $_SESSION['name'] = $demo_user['name'];
        $_SESSION['role'] = $demo_user['role'];
    } else {
        header("Location: signin.php?msg=login_required");
        exit();
    }
}

$owner_id = (int)$_SESSION['user_id'];

// KPI 1: Total Earnings
$earn_sql = "SELECT SUM(total_rent) AS earnings FROM rental_requests 
             JOIN items ON rental_requests.item_id = items.item_id 
             WHERE items.owner_id = $owner_id AND rental_requests.status IN ('Active', 'Returned')";
$earn_res = mysqli_query($conn, $earn_sql);
$earnings = mysqli_fetch_assoc($earn_res)['earnings'] ?? 0;

// KPI 2: Active Lends
$active_sql = "SELECT COUNT(*) AS active_lends FROM rental_requests 
               JOIN items ON rental_requests.item_id = items.item_id 
               WHERE items.owner_id = $owner_id AND rental_requests.status = 'Active'";
$active_res = mysqli_query($conn, $active_sql);
$active_lends = mysqli_fetch_assoc($active_res)['active_lends'] ?? 0;

// KPI 3: Pending Requests
$pending_sql = "SELECT COUNT(*) AS pending_count FROM rental_requests 
                JOIN items ON rental_requests.item_id = items.item_id 
                WHERE items.owner_id = $owner_id AND rental_requests.status = 'Pending'";
$pending_res = mysqli_query($conn, $pending_sql);
$pending_requests = mysqli_fetch_assoc($pending_res)['pending_count'] ?? 0;

// KPI 4: Security In Escrow
$escrow_sql = "SELECT SUM(deposit) AS escrow_total FROM rental_requests 
               JOIN items ON rental_requests.item_id = items.item_id 
               WHERE items.owner_id = $owner_id AND rental_requests.status IN ('Approved', 'Active')";
$escrow_res = mysqli_query($conn, $escrow_sql);
$escrow_total = mysqli_fetch_assoc($escrow_res)['escrow_total'] ?? 0;

// Fetch Owner's Equipment Listings
$listings_sql = "SELECT items.*, categories.name AS category_name,
                 (SELECT COUNT(*) FROM rental_requests WHERE rental_requests.item_id = items.item_id AND rental_requests.status IN ('Active', 'Returned')) AS total_lends
                 FROM items 
                 JOIN categories ON items.category_id = categories.category_id 
                 WHERE items.owner_id = $owner_id 
                 ORDER BY items.item_id DESC";
$listings_res = mysqli_query($conn, $listings_sql);

// Fetch Incoming Requests for this Owner
$incoming_sql = "SELECT rental_requests.*, items.title, users.name as renter_name, users.student_id as renter_student_id, users.phone as renter_phone 
                 FROM rental_requests 
                 JOIN items ON rental_requests.item_id = items.item_id 
                 JOIN users ON rental_requests.renter_id = users.user_id 
                 WHERE items.owner_id = $owner_id AND rental_requests.status = 'Pending' 
                 ORDER BY rental_requests.request_id DESC";
$incoming_res = mysqli_query($conn, $incoming_sql);
$incoming_count = mysqli_num_rows($incoming_res);

// Fetch Categories for the Add Equipment Modal
$categories_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lender Operations Dashboard - CampusRent Hub</title>
  
  <!-- Tailwind CSS CDN -->
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
        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
        <span class="font-medium text-slate-200">Lender Portal:</span> 
        <span>All rental earnings are transferred directly upon verified safe return.</span>
      </div>
      <div class="flex items-center gap-4 text-slate-400">
        <a href="renter-dashboard.php" class="hover:text-white transition-colors flex items-center gap-1">
          Switch to Renter View &rarr;
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
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Lender Operations</p>
          </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
          <a href="index.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            Browse Equipment
          </a>
          <a href="owner-dashboard.php" class="px-3.5 py-2 text-sm font-semibold text-primary-600 rounded-lg bg-blue-50/80">
            Lender Hub
          </a>
          <a href="renter-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            My Rentals
          </a>
          <a href="admin.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors">
            Admin
          </a>
        </nav>

        <!-- Right Side User Badge & Action -->
        <div class="flex items-center gap-3">
          <button data-modal-target="add-equipment-modal" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Add Equipment</span>
          </button>

          <div class="flex items-center pl-2 sm:pl-3 border-l border-slate-200 gap-2">
            <div class="w-8 h-8 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-xs">
              <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <div class="text-left leading-tight hidden sm:block">
              <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
              <div class="text-[11px] font-semibold text-emerald-600">ID: <?php echo htmlspecialchars($_SESSION['student_id']); ?></div>
            </div>
            <a href="logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 p-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    
    <!-- Notifications & Flash Banners -->
    <?php if (isset($_GET['handover_status']) && $_GET['handover_status'] === 'success'): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">✓</span>
        <div>
          <h4 class="font-bold text-sm">Handover Verified & Authenticated!</h4>
          <p class="text-xs text-emerald-700">Item released to <strong><?php echo htmlspecialchars($_GET['renter'] ?? 'Renter'); ?></strong>. Platform escrow deposit is locked and secure.</p>
        </div>
      </div>
    <?php elseif (isset($_GET['handover_status']) && $_GET['handover_status'] === 'error'): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold">✕</span>
        <div>
          <h4 class="font-bold text-sm">Invalid or Expired Handover Token (<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>)</h4>
          <p class="text-xs text-red-700">Please confirm with the student. Note: Request must be in "Approved" state.</p>
        </div>
      </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'item_added'): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
        🎉 New equipment successfully published to the university catalog!
      </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'request_approved'): ?>
      <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold">
        ✓ Rental request approved! The student can now present their secret handover token to collect the equipment.
      </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'item_returned'): ?>
      <div class="mb-6 p-4 rounded-xl bg-purple-50 border border-purple-200 text-purple-800 text-xs font-semibold">
        ✓ Item marked as Returned safely! Security deposit refunded to student and item is available again.
      </div>
    <?php endif; ?>

    <!-- Welcome Strip -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Owner / Lender Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage your campus equipment listings, incoming requests, and in-person handovers.</p>
      </div>

      <button data-modal-target="add-equipment-modal" class="py-2 px-4 rounded-xl bg-navy-900 hover:bg-navy-800 text-white text-xs font-bold shadow transition-all flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        <span>List New Item</span>
      </button>
    </div>

    <!-- Overview Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
      
      <!-- Total Earnings -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Earnings</span>
          <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">৳</span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-extrabold text-navy-900">৳<?php echo number_format($earnings); ?></div>
          <p class="text-xs text-emerald-600 font-semibold mt-0.5">Direct peer-to-peer payout</p>
        </div>
      </div>

      <!-- Active Lends -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Lends</span>
          <span class="w-9 h-9 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          </span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-extrabold text-navy-900"><?php echo $active_lends; ?> Items</div>
          <p class="text-xs text-slate-500 mt-0.5">Currently with campus peers</p>
        </div>
      </div>

      <!-- Pending Requests -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Requests</span>
          <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-extrabold text-navy-900"><?php echo $pending_requests; ?> Requests</div>
          <p class="text-xs text-amber-600 font-semibold mt-0.5">Awaiting your approval</p>
        </div>
      </div>

      <!-- Security In Escrow -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Security In Escrow</span>
          <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
          </span>
        </div>
        <div class="mt-3">
          <div class="text-2xl font-extrabold text-navy-900">৳<?php echo number_format($escrow_total); ?></div>
          <p class="text-xs text-slate-500 mt-0.5">Platform trust guarantee</p>
        </div>
      </div>

    </div>

    <!-- Interactive Section: In-Person Handover Confirmation Terminal -->
    <section class="bg-gradient-to-tr from-navy-900 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl mb-10 border border-slate-800">
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 font-mono text-xs font-bold uppercase tracking-wider mb-2">
          <span>In-Person Handover Verification Terminal</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-bold">Release Equipment with Renter's Secret Token</h2>
        <p class="text-xs sm:text-sm text-slate-300 mt-1">
          When meeting the student at Central Library, Cafeteria, or Lab, ask them for their secret Renter Passcode (e.g. <span class="font-mono text-blue-300">TRX-8291</span>) to authenticate payment and release the gear.
        </p>
      </div>

      <!-- Token Input Terminal Form POSTing to verify_handover.php -->
      <div class="mt-6 max-w-xl">
        <form action="verify_handover.php" method="POST" class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <input type="text" name="handover_token" required placeholder="Enter Secret Token (e.g. TRX-8291)" class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 font-mono text-base tracking-wider focus:outline-none focus:ring-2 focus:ring-primary-500 uppercase">
          </div>
          <button type="submit" class="py-3 px-6 bg-primary-600 hover:bg-primary-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg transition-all flex items-center justify-center gap-2 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Verify & Release</span>
          </button>
        </form>
      </div>
    </section>

    <!-- Two-Column Grid: Manage Listings & Incoming Requests -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left 8 Columns: Manage Equipment Listings -->
      <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-lg font-bold text-navy-900">My Listed Equipment</h2>
            <p class="text-xs text-slate-500 mt-0.5">Your items available for peer rental across campus departments</p>
          </div>
        </div>

        <!-- Listings Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
              <tr>
                <th class="py-3 px-4">Equipment</th>
                <th class="py-3 px-4">Daily Rate</th>
                <th class="py-3 px-4">Security Deposit</th>
                <th class="py-3 px-4">Total Lends</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <?php if (mysqli_num_rows($listings_res) > 0): ?>
                <?php while ($item = mysqli_fetch_assoc($listings_res)): ?>
                  <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="py-3 px-4">
                      <div class="flex items-center gap-3">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                        <div>
                          <div class="font-bold text-navy-900 text-xs"><?php echo htmlspecialchars($item['title']); ?></div>
                          <div class="text-[10px] text-slate-400"><?php echo htmlspecialchars($item['category_name']); ?> &bull; <?php echo htmlspecialchars($item['campus_spot']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-4 font-bold text-slate-800">৳<?php echo number_format($item['daily_rate']); ?>/day</td>
                    <td class="py-3 px-4 font-bold text-emerald-600">৳<?php echo number_format($item['security_deposit']); ?></td>
                    <td class="py-3 px-4 font-semibold text-slate-700"><?php echo $item['total_lends']; ?> times</td>
                    <td class="py-3 px-4">
                      <?php if ($item['is_available'] == 1): ?>
                        <span class="badge-status bg-emerald-100 text-emerald-800">Available</span>
                      <?php else: ?>
                        <span class="badge-status bg-blue-100 text-blue-800">Rented Out</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 text-right">
                      <div class="flex items-center justify-end gap-2">
                        <a href="delete_item.php?id=<?php echo $item['item_id']; ?>" onclick="return confirm('Are you sure you want to remove this equipment listing?')" class="p-1 text-slate-400 hover:text-red-600 transition-colors" title="Delete">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-8 text-slate-400">You haven't listed any equipment yet. Click "+ Add Equipment" to start earning!</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right 4 Columns: Incoming Rental Requests Queue -->
      <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h2 class="text-base font-bold text-navy-900">Incoming Requests</h2>
            <p class="text-xs text-slate-500">Classmates requesting your equipment</p>
          </div>
          <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-full"><?php echo $incoming_count; ?> New</span>
        </div>

        <div class="space-y-4">
          <?php if ($incoming_count > 0): ?>
            <?php while ($req = mysqli_fetch_assoc($incoming_res)): ?>
              <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white hover:shadow-md transition-all space-y-2.5">
                <div class="flex justify-between items-start">
                  <div>
                    <span class="text-[10px] font-bold text-primary-600 uppercase">Student Request</span>
                    <h4 class="font-bold text-xs text-navy-900"><?php echo htmlspecialchars($req['title']); ?></h4>
                  </div>
                  <span class="text-xs font-extrabold text-emerald-600">৳<?php echo number_format($req['total_rent']); ?></span>
                </div>
                
                <div class="text-[11px] text-slate-600">
                  <span class="font-semibold text-slate-800">Requester:</span> <?php echo htmlspecialchars($req['renter_name']); ?> (<?php echo htmlspecialchars($req['renter_student_id']); ?>)
                </div>

                <div class="text-[11px] text-slate-500 flex items-center gap-1">
                  <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                  <span><?php echo htmlspecialchars($req['pickup_spot']); ?> &bull; <?php echo $req['start_date']; ?> to <?php echo $req['end_date']; ?></span>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200">
                  <a href="handle_request.php?action=approve&id=<?php echo $req['request_id']; ?>" class="py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-center rounded-lg text-xs font-bold shadow-sm transition-colors">
                    Approve Request
                  </a>
                  <a href="handle_request.php?action=reject&id=<?php echo $req['request_id']; ?>" class="py-1.5 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 text-center rounded-lg text-xs font-bold transition-colors">
                    Decline
                  </a>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="text-center py-8 text-slate-400 text-xs">
              No pending rental requests right now.
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

  </main>

  <!-- Add New Equipment Modal (Semantic PHP-ready Form) -->
  <div id="add-equipment-modal" class="modal-container hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="modal-backdrop fixed inset-0 bg-navy-950/80 backdrop-blur-sm"></div>
    
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden z-10 border border-slate-200 max-h-[90vh] flex flex-col">
      
      <!-- Modal Header -->
      <div class="bg-navy-900 p-5 text-white flex justify-between items-center shrink-0">
        <div>
          <h3 class="font-bold text-base">List Equipment for Campus Rental</h3>
          <p class="text-xs text-slate-300">Monetize your idle lab gear and study equipment safely</p>
        </div>
        <button data-modal-close class="text-slate-400 hover:text-white transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <!-- Scrollable Form Body POSTing to add_item.php -->
      <form action="add_item.php" method="POST" class="p-6 space-y-4 overflow-y-auto">
        
        <!-- Item Title -->
        <div>
          <label for="item_title" class="block text-xs font-bold text-slate-700 mb-1">Equipment Title *</label>
          <input type="text" id="item_title" name="item_title" required placeholder="e.g. Casio fx-991EX Calculator or Rotring Drafter Kit" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
        </div>

        <!-- Category & Condition -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="category_id" class="block text-xs font-bold text-slate-700 mb-1">Category *</label>
            <select id="category_id" name="category_id" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
              <?php 
              mysqli_data_seek($categories_res, 0);
              while ($cat = mysqli_fetch_assoc($categories_res)): 
              ?>
                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div>
            <label for="item_condition" class="block text-xs font-bold text-slate-700 mb-1">Item Condition *</label>
            <select id="item_condition" name="item_condition" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
              <option value="Like New">Like New (Mint condition)</option>
              <option value="Good">Good (Lightly used, fully working)</option>
              <option value="Fair">Fair (Cosmetic wear, operational)</option>
            </select>
          </div>
        </div>

        <!-- Campus Pickup Spot -->
        <div>
          <label for="pickup_spot" class="block text-xs font-bold text-slate-700 mb-1">Preferred Campus Handover Spot *</label>
          <select id="pickup_spot" name="pickup_spot" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
            <option value="Central Library Front Gate">Central Library Front Gate</option>
            <option value="Campus Cafeteria Entrance">Campus Cafeteria Entrance</option>
            <option value="Academic Building-1 Gate">Academic Building-1 Gate</option>
            <option value="Engineering Lab Complex (3rd Floor)">Engineering Lab Complex (3rd Floor)</option>
            <option value="TSC Ground / Student Union">TSC Ground / Student Union</option>
          </select>
        </div>

        <!-- Pricing -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label for="daily_rate" class="block text-xs font-bold text-slate-700 mb-1">Daily Rental Rate (৳) *</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-xs font-bold text-slate-500">৳</span>
              <input type="number" id="daily_rate" name="daily_rate" required min="10" max="5000" placeholder="e.g. 70" class="w-full pl-7 pr-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-bold">
            </div>
          </div>

          <div>
            <label for="security_deposit" class="block text-xs font-bold text-slate-700 mb-1">Refundable Security Deposit (৳) *</label>
            <div class="relative">
              <span class="absolute left-3 top-2 text-xs font-bold text-emerald-600">৳</span>
              <input type="number" id="security_deposit" name="security_deposit" required min="50" max="50000" placeholder="e.g. 800" class="w-full pl-7 pr-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-bold">
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label for="item_description" class="block text-xs font-bold text-slate-700 mb-1">Description & Inclusions</label>
          <textarea id="item_description" name="item_description" rows="2" placeholder="Details about inclusions (cables, cover, case) and exam rules." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800"></textarea>
        </div>

        <!-- Image URL -->
        <div>
          <label for="image_url" class="block text-xs font-bold text-slate-700 mb-1">Image URL (Optional)</label>
          <input type="url" id="image_url" name="image_url" placeholder="https://..." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800">
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button type="submit" class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm shadow-md transition-all">
            Publish Equipment to Campus Catalog
          </button>
        </div>

      </form>

    </div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
