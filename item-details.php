<?php
session_start();
require_once('DBconnect.php');

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$sql = "SELECT items.*, categories.name AS category_name, users.name AS owner_name, users.student_id AS owner_student_id, users.phone AS owner_phone 
        FROM items 
        JOIN categories ON items.category_id = categories.category_id 
        JOIN users ON items.owner_id = users.user_id 
        WHERE items.item_id = $item_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

// Default image if empty
$image_url = !empty($item['image_url']) ? $item['image_url'] : 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=600&auto=format&fit=crop&q=80';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($item['title']); ?> - CampusRent Hub</title>
  
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
        <span class="font-medium text-slate-200">Verified Campus Equipment Hub:</span> 
        <span>All deposits are held safely in University Escrow until physical return.</span>
      </div>
      <div class="flex items-center gap-4 text-slate-400">
        <a href="index.php" class="hover:text-white transition-colors flex items-center gap-1">
          &larr; Back to Catalog
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
          <a href="owner-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors">
            Lender Hub
          </a>
          <a href="renter-dashboard.php" class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:text-primary-600 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1.5">
            <span>My Rentals</span>
            <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">Active</span>
          </a>
        </nav>

        <!-- User Controls -->
        <div class="flex items-center gap-3">
          <?php if (isset($_SESSION['user_id'])): ?>
            <div class="flex items-center pl-2 sm:pl-3 border-l border-slate-200 gap-2">
              <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-xs">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
              </div>
              <div class="text-left leading-tight hidden sm:block">
                <div class="text-xs font-bold text-navy-900"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                <div class="text-[11px] font-semibold text-emerald-600"><?php echo htmlspecialchars($_SESSION['student_id']); ?></div>
              </div>
              <a href="logout.php" title="Sign Out" class="text-slate-400 hover:text-red-600 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              </a>
            </div>
          <?php else: ?>
            <a href="signin.php" class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:text-primary-600">Sign In</a>
            <a href="register.php" class="px-3.5 py-1.5 rounded-xl bg-primary-600 text-white text-xs font-bold">Register</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </header>

  <!-- Breadcrumbs -->
  <div class="bg-white border-b border-slate-200 py-2.5 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto flex items-center gap-2 text-xs text-slate-500">
      <a href="index.php" class="hover:text-primary-600">Home</a>
      <span>/</span>
      <span><?php echo htmlspecialchars($item['category_name']); ?></span>
      <span>/</span>
      <span class="font-bold text-navy-900 truncate max-w-xs"><?php echo htmlspecialchars($item['title']); ?></span>
    </div>
  </div>

  <!-- Main Details & Calculator Grid -->
  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Left 7 Columns: Product Gallery, Specs, Rules -->
      <div class="lg:col-span-7 space-y-6">
        
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm p-6">
          
          <!-- Image Showcase -->
          <div class="relative rounded-xl overflow-hidden bg-slate-100 mb-4 h-72 sm:h-96 flex items-center justify-center">
            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-full object-cover">
            
            <div class="absolute top-4 left-4 flex gap-2">
              <span class="badge-status bg-emerald-100 text-emerald-800 border border-emerald-200">
                <?php echo htmlspecialchars($item['item_condition']); ?>
              </span>
              <span class="badge-status bg-navy-900 text-white">
                <?php echo htmlspecialchars($item['category_name']); ?>
              </span>
            </div>

            <div class="absolute bottom-4 left-4 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl shadow border border-slate-200/80 flex items-center gap-2 text-xs font-semibold text-slate-700">
              <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
              <span><?php echo htmlspecialchars($item['campus_spot']); ?></span>
            </div>
          </div>

          <!-- Title & Description -->
          <div class="mt-6">
            <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">
              <?php echo htmlspecialchars($item['category_name']); ?>
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-navy-900 mt-1">
              <?php echo htmlspecialchars($item['title']); ?>
            </h1>
            
            <p class="mt-3 text-sm text-slate-600 leading-relaxed">
              <?php echo nl2br(htmlspecialchars($item['description'])); ?>
            </p>
          </div>

          <!-- Specifications Table -->
          <div class="mt-6 pt-6 border-t border-slate-200">
            <h3 class="text-sm font-bold text-navy-900 uppercase tracking-wider mb-3">
              Technical Specifications & Inclusions
            </h3>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs divide-y divide-slate-200">
              <div class="py-2 flex justify-between gap-4">
                <span class="text-slate-500 font-medium">Equipment Condition</span>
                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($item['item_condition']); ?></span>
              </div>
              <div class="py-2 flex justify-between gap-4">
                <span class="text-slate-500 font-medium">Official Pickup Zone</span>
                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($item['campus_spot']); ?></span>
              </div>
              <div class="py-2 flex justify-between gap-4">
                <span class="text-slate-500 font-medium">Rental Verification</span>
                <span class="font-bold text-emerald-600">6-Digit Secret Token Protected</span>
              </div>
              <div class="py-2 flex justify-between gap-4">
                <span class="text-slate-500 font-medium">Escrow Security</span>
                <span class="font-bold text-emerald-600">100% Refundable Guarantee</span>
              </div>
            </div>
          </div>

          <!-- Rental Guidelines -->
          <div class="mt-6 pt-6 border-t border-slate-200">
            <h3 class="text-sm font-bold text-navy-900 uppercase tracking-wider mb-2">
              Campus Rental Guidelines & Regulations
            </h3>
            <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 text-xs text-blue-900 space-y-2">
              <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><strong>Verification at Handover:</strong> Present your University Student ID Card or Roll confirmation when meeting at the campus spot.</span>
              </div>
              <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><strong>Late Return Penalty:</strong> Overdue returns incur a university fee of ৳50 per 24 hours, deducted automatically from the security deposit.</span>
              </div>
              <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span><strong>100% Escrow Protection:</strong> Security deposit is released back to your bKash wallet immediately once the owner confirms safe return.</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Lender Profile Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Equipment Lender Profile</h3>
          
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-xl shadow-md">
                <?php echo strtoupper(substr($item['owner_name'], 0, 1)); ?>
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="font-bold text-navy-900 text-base"><?php echo htmlspecialchars($item['owner_name']); ?></h4>
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                    Student ID Verified
                  </span>
                </div>
                <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-3">
                  <span>Roll: <?php echo htmlspecialchars($item['owner_student_id']); ?></span>
                  <span>&bull;</span>
                  <span>Phone: <?php echo htmlspecialchars($item['owner_phone']); ?></span>
                </div>
              </div>
            </div>

            <div class="text-right">
              <span class="text-xs font-bold text-emerald-600">✓ On-Campus Verified</span>
              <p class="text-[11px] text-slate-400">P2P Escrow Protected</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Right 5 Columns: Sticky Rental Calculator & Booking Form -->
      <div class="lg:col-span-5">
        <div class="sticky top-24 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
          
          <!-- Pricing Header -->
          <div class="bg-navy-950 p-6 text-white">
            <div class="flex items-baseline justify-between">
              <div>
                <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Daily Rental Rate</span>
                <div class="flex items-baseline gap-1 mt-1">
                  <span class="text-3xl font-extrabold text-white">৳<?php echo number_format($item['daily_rate']); ?></span>
                  <span class="text-slate-400 text-sm font-normal">/ day</span>
                </div>
              </div>
              <div class="text-right">
                <span class="text-[11px] text-slate-400 block">Security Deposit</span>
                <span class="text-lg font-bold text-emerald-400">৳<?php echo number_format($item['security_deposit']); ?></span>
                <span class="text-[10px] text-slate-400 block">(100% Refundable)</span>
              </div>
            </div>
          </div>

          <!-- Rental Booking Form POSTing to submit_request.php -->
          <form id="rental-calculator-form" action="submit_request.php" method="POST" class="p-6 space-y-4">
            
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
            <input type="hidden" id="daily_rate_val" value="<?php echo $item['daily_rate']; ?>">
            <input type="hidden" id="security_deposit_val" value="<?php echo $item['security_deposit']; ?>">

            <!-- Rental Dates -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label for="rental_start_date" class="block text-xs font-bold text-slate-700 mb-1">Pickup Date *</label>
                <input type="date" id="rental_start_date" name="rental_start_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
              </div>
              <div>
                <label for="rental_end_date" class="block text-xs font-bold text-slate-700 mb-1">Return Date *</label>
                <input type="date" id="rental_end_date" name="rental_end_date" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
              </div>
            </div>

            <!-- Campus Meeting Spot Selector -->
            <div>
              <label for="pickup_spot" class="block text-xs font-bold text-slate-700 mb-1">Preferred Pickup Spot *</label>
              <select id="pickup_spot" name="pickup_spot" required class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
                <option value="Central Library Front Gate" <?php if ($item['campus_spot'] === 'Central Library Front Gate') echo 'selected'; ?>>Central Library Front Gate</option>
                <option value="Campus Cafeteria Entrance" <?php if ($item['campus_spot'] === 'Campus Cafeteria Entrance') echo 'selected'; ?>>Campus Cafeteria Entrance</option>
                <option value="Academic Building-1 Gate" <?php if ($item['campus_spot'] === 'Academic Building-1 Gate') echo 'selected'; ?>>Academic Building-1 Gate</option>
                <option value="Engineering Lab Complex (3rd Floor)" <?php if ($item['campus_spot'] === 'Engineering Lab Complex (3rd Floor)') echo 'selected'; ?>>Engineering Lab Complex</option>
                <option value="TSC Ground / Student Union" <?php if ($item['campus_spot'] === 'TSC Ground / Student Union') echo 'selected'; ?>>TSC Ground / Student Union</option>
              </select>
            </div>

            <!-- Meeting Time Slot -->
            <div>
              <label for="pickup_time_slot" class="block text-xs font-bold text-slate-700 mb-1">Meeting Time Window *</label>
              <select id="pickup_time_slot" name="pickup_time_slot" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 font-medium text-slate-800">
                <option value="1:00 PM - 2:00 PM (Lunch Break)">1:00 PM - 2:00 PM (Lunch Break)</option>
                <option value="9:30 AM - 10:30 AM (Morning Slot)">9:30 AM - 10:30 AM (Morning Slot)</option>
                <option value="4:30 PM - 5:30 PM (Post-Class Slot)">4:30 PM - 5:30 PM (Post-Class Slot)</option>
                <option value="Flexible (Coordinate via Mobile)">Flexible (Coordinate via Mobile)</option>
              </select>
            </div>

            <!-- Purpose Note -->
            <div>
              <label for="renter_note" class="block text-xs font-bold text-slate-700 mb-1">Note to Owner / Course Exam Purpose</label>
              <textarea id="renter_note" name="renter_note" rows="2" placeholder="e.g. Needed for Semester Mid-term exam tomorrow." class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 text-slate-800 placeholder-slate-400"></textarea>
            </div>

            <!-- Live Calculation Breakdown -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs space-y-2">
              <div class="flex justify-between text-slate-600">
                <span>Rental Duration:</span>
                <span id="breakdown-days" class="font-bold text-slate-800">1 Day</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Rental Subtotal:</span>
                <span id="breakdown-rent" class="font-bold text-slate-800">৳<?php echo number_format($item['daily_rate']); ?></span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Refundable Escrow Deposit:</span>
                <span id="breakdown-deposit" class="font-bold text-emerald-600">৳<?php echo number_format($item['security_deposit']); ?></span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Platform Escrow Fee:</span>
                <span class="font-bold text-slate-400 line-through">৳50</span>
                <span class="font-bold text-emerald-600">FREE</span>
              </div>
              
              <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
                <div>
                  <span class="text-sm font-bold text-navy-900">Total Payable Now:</span>
                  <p class="text-[10px] text-slate-500">Includes refundable security deposit</p>
                </div>
                <span id="breakdown-total" class="text-xl font-extrabold text-navy-900">
                  ৳<?php echo number_format($item['daily_rate'] + $item['security_deposit']); ?>
                </span>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="submit_request" class="w-full py-3.5 px-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              <span>Submit Request & Authorize Escrow</span>
            </button>

            <div class="text-[11px] text-center text-slate-500">
              🔒 No money is released to lender until you meet on campus and exchange the secret Handover Passcode.
            </div>

          </form>

        </div>
      </div>

    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const today = new Date();
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const dayAfter = new Date(today);
      dayAfter.setDate(dayAfter.getDate() + 3);

      const startInput = document.getElementById('rental_start_date');
      const endInput = document.getElementById('rental_end_date');

      startInput.min = today.toISOString().split('T')[0];
      startInput.value = tomorrow.toISOString().split('T')[0];
      
      endInput.min = tomorrow.toISOString().split('T')[0];
      endInput.value = dayAfter.toISOString().split('T')[0];

      startInput.addEventListener('change', updateCalc);
      endInput.addEventListener('change', updateCalc);

      updateCalc();
    });

    function updateCalc() {
      const startVal = document.getElementById('rental_start_date').value;
      const endVal = document.getElementById('rental_end_date').value;
      const rate = parseFloat(document.getElementById('daily_rate_val').value) || 0;
      const deposit = parseFloat(document.getElementById('security_deposit_val').value) || 0;

      let days = 1;
      if (startVal && endVal) {
        const s = new Date(startVal);
        const e = new Date(endVal);
        const diff = e - s;
        days = Math.max(1, Math.ceil(diff / (1000 * 60 * 60 * 24)));
      }

      const rentSubtotal = days * rate;
      const total = rentSubtotal + deposit;

      document.getElementById('breakdown-days').textContent = `${days} Day${days > 1 ? 's' : ''}`;
      document.getElementById('breakdown-rent').textContent = '৳' + rentSubtotal.toLocaleString();
      document.getElementById('breakdown-deposit').textContent = '৳' + deposit.toLocaleString();
      document.getElementById('breakdown-total').textContent = '৳' + total.toLocaleString();
    }
  </script>
</body>
</html>
