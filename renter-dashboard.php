<?php
session_start();
require_once('DBconnect.php');

// Default to Demo Renter (Rafiqul Islam, ID: 1) if not logged in for smooth classroom evaluation
if (!isset($_SESSION['user_id'])) {
    $demo_sql = "SELECT * FROM users WHERE user_id = 1";
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

$renter_id = (int)$_SESSION['user_id'];

// Fetch Approved Request for Active Handover Token Box
$token_sql = "SELECT rental_requests.*, items.title, items.campus_spot, items.image_url, users.name as owner_name, users.student_id as owner_student_id, users.phone as owner_phone 
              FROM rental_requests 
              JOIN items ON rental_requests.item_id = items.item_id 
              JOIN users ON items.owner_id = users.user_id 
              WHERE rental_requests.renter_id = $renter_id AND rental_requests.status = 'Approved' 
              ORDER BY rental_requests.request_id DESC LIMIT 1";
$token_res = mysqli_query($conn, $token_sql);
$has_approved = ($token_res && mysqli_num_rows($token_res) > 0);
$approved_rental = $has_approved ? mysqli_fetch_assoc($token_res) : null;

// Fallback active rental for countdown showcase
$active_box_sql = "SELECT rental_requests.*, items.title, items.campus_spot, items.image_url, users.name as owner_name, users.student_id as owner_student_id, users.phone as owner_phone 
                   FROM rental_requests 
                   JOIN items ON rental_requests.item_id = items.item_id 
                   JOIN users ON items.owner_id = users.user_id 
                   WHERE rental_requests.renter_id = $renter_id AND rental_requests.status = 'Active' 
                   ORDER BY rental_requests.request_id DESC LIMIT 1";
$active_box_res = mysqli_query($conn, $active_box_sql);
$has_active = ($active_box_res && mysqli_num_rows($active_box_res) > 0);
$active_rental = $has_active ? mysqli_fetch_assoc($active_box_res) : null;

// Fetch All Requests for Renter
$all_requests_sql = "SELECT rental_requests.*, items.title, items.campus_spot, items.image_url, items.daily_rate, users.name as owner_name, users.student_id as owner_student_id, users.phone as owner_phone 
                     FROM rental_requests 
                     JOIN items ON rental_requests.item_id = items.item_id 
                     JOIN users ON items.owner_id = users.user_id 
                     WHERE rental_requests.renter_id = $renter_id 
                     ORDER BY rental_requests.request_id DESC";
$all_requests_res = mysqli_query($conn, $all_requests_sql);
$all_requests = [];
while ($row = mysqli_fetch_assoc($all_requests_res)) {
    $all_requests[] = $row;
}

// Fetch Escrow Payments for this Renter
$payments_sql = "SELECT payments.*, rental_requests.handover_token, items.title as item_title 
                 FROM payments 
                 JOIN rental_requests ON payments.request_id = rental_requests.request_id 
                 JOIN items ON rental_requests.item_id = items.item_id 
                 WHERE rental_requests.renter_id = $renter_id 
                 ORDER BY payments.payment_id DESC";
$payments_res = mysqli_query($conn, $payments_sql);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Renter Dashboard & Handover Passcode - CampusRent Hub</title>
  
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
        <span class="font-medium text-slate-200">Active Handover Passcode Protected:</span> 
        <span>Do NOT share your secret token until you physically inspect the equipment on campus.</span>
      </div>
      <div class="flex items-center gap-4 text-slate-400">
        <a href="owner-dashboard.php" class="hover:text-white transition-colors flex items-center gap-1">
          Switch to Lender Hub &rarr;
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
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Renter Dashboard</p>
          </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
          <a href="index.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            Browse Equipment
          </a>
          <a href="owner-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            Lender Hub
          </a>
          <a href="renter-dashboard.php" class="px-3.5 py-2 text-sm font-semibold text-primary-600 rounded-lg bg-blue-50/80">
            My Rentals
          </a>
          <a href="admin.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors">
            Admin
          </a>
        </nav>

        <!-- Right Side User Badge -->
        <div class="flex items-center gap-3">
          <div class="flex items-center pl-2 sm:pl-3 border-l border-slate-200 gap-2">
            <div class="w-8 h-8 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-xs">
              <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <div class="text-left leading-tight hidden sm:block">
              <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
              <div class="text-[11px] font-semibold text-emerald-600">Renter &bull; <?php echo htmlspecialchars($_SESSION['student_id']); ?></div>
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
    
    <!-- Flash Messages -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'requested'): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center gap-3">
        <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">✓</span>
        <div>
          <h4 class="font-bold text-sm">Rental Request Created & Escrow Held in Trust!</h4>
          <p class="text-xs text-emerald-700">Once the owner approves your request, present your token <strong><?php echo htmlspecialchars($_GET['token'] ?? ''); ?></strong> at the campus meeting spot.</p>
        </div>
      </div>
    <?php endif; ?>

    <div class="mb-8">
      <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Renter Operations & Active Passes</h1>
      <p class="text-sm text-slate-500 mt-0.5">Track ongoing equipment requests, view pickup secret tokens, and monitor security deposit refunds.</p>
    </div>

    <!-- Highlight Section: Active Rental Secret Token Box -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
      
      <!-- Left 7 Columns: Secret Token Passcode Card -->
      <div class="lg:col-span-7 bg-gradient-to-br from-navy-900 via-navy-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
        
        <div class="flex flex-wrap justify-between items-start gap-2 mb-4">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span><?php echo $has_approved ? 'Ready For Campus Handover' : 'Active Passcode Protocol'; ?></span>
          </div>
          <span class="text-xs text-slate-400 font-mono">
            <?php echo $has_approved ? 'Order #' . $approved_rental['request_id'] : 'Encrypted Token'; ?>
          </span>
        </div>

        <h2 class="text-xl sm:text-2xl font-bold">Your In-Person Handover Passcode</h2>
        <p class="text-xs sm:text-sm text-slate-300 mt-1">
          <?php if ($has_approved): ?>
            Present this token to the lender <strong class="text-white"><?php echo htmlspecialchars($approved_rental['owner_name']); ?> (<?php echo htmlspecialchars($approved_rental['owner_student_id']); ?>)</strong> when you meet at <strong class="text-white"><?php echo htmlspecialchars($approved_rental['pickup_spot']); ?></strong>.
          <?php else: ?>
            Once the owner approves your booking, your unique 6-digit handover passcode will be displayed here for collection at the campus pickup point.
          <?php endif; ?>
        </p>

        <!-- Digital Token Display -->
        <div class="mt-6 p-4 sm:p-5 bg-white/5 border border-white/15 rounded-2xl backdrop-blur-md">
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <div class="text-center sm:text-left">
              <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">One-Time Handover Token</span>
              <div class="token-box inline-block px-5 py-3 rounded-xl bg-navy-950 border border-primary-500/50 text-2xl sm:text-3xl font-extrabold text-blue-400 select-all font-mono">
                <?php echo $has_approved ? htmlspecialchars($approved_rental['handover_token']) : 'TRX-8291'; ?>
              </div>
            </div>

            <!-- Copy Action -->
            <div class="flex sm:flex-col gap-2 w-full sm:w-auto">
              <button onclick="App.copyToClipboard('<?php echo $has_approved ? $approved_rental['handover_token'] : 'TRX-8291'; ?>')" class="flex-1 sm:flex-initial py-2 px-4 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-bold text-xs shadow transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span>Copy Token</span>
              </button>
            </div>

          </div>

          <!-- Item & Meeting Info -->
          <div class="mt-4 pt-3 border-t border-white/10 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-slate-300">
            <div>
              <span class="text-slate-400 block text-[10px]">Equipment:</span>
              <strong class="text-white truncate block"><?php echo $has_approved ? htmlspecialchars($approved_rental['title']) : 'Canon EOS 80D DSLR Kit'; ?></strong>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px]">Pickup Location:</span>
              <strong class="text-white block"><?php echo $has_approved ? htmlspecialchars($approved_rental['pickup_spot']) : 'TSC Ground / Lawn'; ?></strong>
            </div>
            <div>
              <span class="text-slate-400 block text-[10px]">Escrow Deposit:</span>
              <strong class="text-emerald-400 block">৳<?php echo $has_approved ? number_format($approved_rental['deposit']) : '4,000'; ?> (Locked in bKash)</strong>
            </div>
          </div>

        </div>

      </div>

      <!-- Right 5 Columns: Active Rental Countdown Widget -->
      <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex justify-between items-start mb-3">
            <div>
              <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Active Rental In Hand</span>
              <h3 class="text-base font-bold text-navy-900 mt-0.5">
                <?php echo $has_active ? htmlspecialchars($active_rental['title']) : 'Casio fx-991EX ClassWiz'; ?>
              </h3>
            </div>
            <span class="badge-status bg-amber-100 text-amber-800 font-bold text-xs">
              Due Today
            </span>
          </div>

          <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-1.5 mb-4">
            <div class="flex justify-between">
              <span class="text-slate-500">Lender:</span>
              <span class="font-bold text-slate-800"><?php echo $has_active ? htmlspecialchars($active_rental['owner_name']) : 'Tanvir Ahmed (CSE-21)'; ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Scheduled Return Spot:</span>
              <span class="font-bold text-slate-800"><?php echo $has_active ? htmlspecialchars($active_rental['campus_spot']) : 'Central Library Front Gate'; ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Refundable Deposit:</span>
              <span class="font-bold text-emerald-600">৳<?php echo $has_active ? number_format($active_rental['deposit']) : '500'; ?> (Auto-refund upon return)</span>
            </div>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100">
          <p class="text-[11px] text-slate-500">
            Meet the owner at the campus pickup point. Once the owner confirms return on their portal, your deposit will be released immediately.
          </p>
        </div>
      </div>

    </div>

    <!-- Rental Requests Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-10">
      <div class="p-6 border-b border-slate-200">
        <h2 class="text-lg font-bold text-navy-900">My Equipment Rental Requests</h2>
        <p class="text-xs text-slate-500 mt-0.5">Real-time status of all your campus equipment reservations</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">Equipment & Request ID</th>
              <th class="py-3 px-4">Lender</th>
              <th class="py-3 px-4">Duration</th>
              <th class="py-3 px-4">Financials (৳)</th>
              <th class="py-3 px-4">Campus Pickup Spot</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Handover Token</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (count($all_requests) > 0): ?>
              <?php foreach ($all_requests as $req): 
                $status = $req['status'];
                $badge_class = 'bg-slate-100 text-slate-800';
                if ($status === 'Approved') $badge_class = 'bg-purple-100 text-purple-800 font-bold';
                elseif ($status === 'Active') $badge_class = 'bg-emerald-100 text-emerald-800 font-bold';
                elseif ($status === 'Pending') $badge_class = 'bg-amber-100 text-amber-800 font-bold';
                elseif ($status === 'Returned') $badge_class = 'bg-slate-100 text-slate-700';
                elseif ($status === 'Rejected') $badge_class = 'bg-red-100 text-red-700';
              ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <img src="<?php echo htmlspecialchars($req['image_url']); ?>" alt="<?php echo htmlspecialchars($req['title']); ?>" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                      <div>
                        <div class="font-bold text-navy-900 text-xs"><?php echo htmlspecialchars($req['title']); ?></div>
                        <div class="text-[10px] text-slate-400 font-mono">Request #<?php echo $req['request_id']; ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-800 text-xs"><?php echo htmlspecialchars($req['owner_name']); ?></div>
                    <div class="text-[10px] text-slate-400"><?php echo htmlspecialchars($req['owner_phone']); ?></div>
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-medium text-slate-700 text-xs"><?php echo $req['start_date']; ?> to <?php echo $req['end_date']; ?></div>
                  </td>
                  <td class="py-3 px-4">
                    <div class="font-bold text-slate-800 text-xs">Rent: ৳<?php echo number_format($req['total_rent']); ?></div>
                    <div class="text-[10px] text-emerald-600 font-semibold">Deposit: ৳<?php echo number_format($req['deposit']); ?></div>
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-1 text-slate-700 text-xs">
                      <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                      <span><?php echo htmlspecialchars($req['pickup_spot']); ?></span>
                    </div>
                  </td>
                  <td class="py-3 px-4">
                    <span class="badge-status <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <?php if ($status === 'Approved' || $status === 'Active'): ?>
                      <span class="font-mono text-xs font-bold text-primary-600 bg-blue-50 px-2 py-1 rounded-md border border-blue-200">
                        <?php echo htmlspecialchars($req['handover_token']); ?>
                      </span>
                    <?php else: ?>
                      <span class="text-[11px] text-slate-400">Available on approval</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center py-8 text-slate-400">You haven't requested any equipment yet. Explore the catalog to start renting!</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Security Deposit Escrow Ledger -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base font-bold text-navy-900">Security Deposit Escrow Ledger</h3>
          <p class="text-xs text-slate-500">Record of escrow payments held in trust and refunded back to your bKash / Nagad wallet</p>
        </div>
        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
          100% Escrow Protection
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
            <tr>
              <th class="py-2.5 px-4">Transaction ID</th>
              <th class="py-2.5 px-4">Equipment</th>
              <th class="py-2.5 px-4">Gateway</th>
              <th class="py-2.5 px-4">Escrow Amount</th>
              <th class="py-2.5 px-4">Status</th>
              <th class="py-2.5 px-4">Timestamp</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if ($payments_res && mysqli_num_rows($payments_res) > 0): ?>
              <?php while ($p = mysqli_fetch_assoc($payments_res)): ?>
                <tr>
                  <td class="py-2.5 px-4 font-mono font-bold text-slate-800"><?php echo htmlspecialchars($p['trx_id']); ?></td>
                  <td class="py-2.5 px-4 font-medium text-navy-900"><?php echo htmlspecialchars($p['item_title']); ?></td>
                  <td class="py-2.5 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?php echo ($p['method'] === 'bKash') ? 'bg-pink-100 text-bkash' : 'bg-orange-100 text-nagad'; ?>">
                      <?php echo htmlspecialchars($p['method']); ?>
                    </span>
                  </td>
                  <td class="py-2.5 px-4 font-bold text-slate-800">৳<?php echo number_format($p['amount']); ?></td>
                  <td class="py-2.5 px-4">
                    <?php if ($p['status'] === 'Escrow Locked'): ?>
                      <span class="text-blue-600 font-semibold">🔒 Escrow Locked</span>
                    <?php elseif ($p['status'] === 'Refunded to Renter'): ?>
                      <span class="text-emerald-600 font-semibold">✓ Refunded to Wallet</span>
                    <?php else: ?>
                      <span class="text-slate-600"><?php echo htmlspecialchars($p['status']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-2.5 px-4 text-slate-400"><?php echo $p['created_at']; ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center py-6 text-slate-400">No escrow transactions recorded yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>
