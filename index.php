<?php
session_start();
require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/includes/auth_guard.php');

// Extract Search & Filter Parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$category_id = (isset($_GET['category_id']) && $_GET['category_id'] !== 'ALL' && $_GET['category_id'] !== '') ? (int)$_GET['category_id'] : 0;
$pickup_spot = (isset($_GET['pickup_spot']) && $_GET['pickup_spot'] !== 'ALL') ? trim($_GET['pickup_spot']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// 1. Fetch Categories for Filter Dropdown and Category Pills
$categories = [];
try {
    $catStmt = $pdo->query("
        SELECT c.category_id, c.category_name, COUNT(e.equipment_id) as item_count 
        FROM category c 
        LEFT JOIN equipment e ON c.category_id = e.category_id AND e.availability_status = 'Available'
        GROUP BY c.category_id, c.category_name
        ORDER BY c.category_name ASC
    ");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// 2. Construct Query for Equipment Items
$where_clauses = ["(e.availability_status = 'Available')"];
$params = [];

if (!empty($query)) {
    $where_clauses[] = "(e.equipment_name LIKE :query1 OR e.description LIKE :query2)";
    $params['query1'] = "%$query%";
    $params['query2'] = "%$query%";
}
if ($category_id > 0) {
    $where_clauses[] = "e.category_id = :cat_id";
    $params['cat_id'] = $category_id;
}
if (!empty($pickup_spot) && $pickup_spot !== 'ALL') {
    $where_clauses[] = "e.campus_spot = :spot";
    $params['spot'] = $pickup_spot;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Sorting
$order_sql = "ORDER BY e.equipment_id DESC";
if ($sort === 'price_asc') {
    $order_sql = "ORDER BY e.rental_rate ASC";
} elseif ($sort === 'price_desc') {
    $order_sql = "ORDER BY e.rental_rate DESC";
} elseif ($sort === 'deposit_asc') {
    $order_sql = "ORDER BY e.security_deposit ASC";
}

$items = [];
$total_items = 0;
try {
    $items_sql = "SELECT e.*, e.equipment_name AS title, e.rental_rate AS daily_rate, 
                         e.condition_status AS item_condition, c.category_name, 
                         CONCAT(m.first_name, ' ', m.last_name) AS owner_name, 
                         m.username AS owner_student_id,
                         e.campus_spot
                  FROM equipment e 
                  LEFT JOIN category c ON e.category_id = c.category_id 
                  LEFT JOIN member m ON e.owner_id = m.member_id 
                  $where_sql $order_sql";
    $stmt = $pdo->prepare($items_sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    $total_items = count($items);
} catch (Exception $e) {
    $items = [];
    $total_items = 0;
}

// Platform counters
$count_students = 0;
$count_avail = 0;
try {
    $stRes = $pdo->query("SELECT COUNT(*) FROM member");
    $count_students = $stRes->fetchColumn() ?: 0;

    $avRes = $pdo->query("SELECT COUNT(*) FROM equipment WHERE availability_status = 'Available'");
    $count_avail = $avRes->fetchColumn() ?: 0;
} catch (Exception $e) {
    $count_students = 0;
    $count_avail = 0;
}

$base_path = '.';
$page_title = 'Rentora - Campus Equipment Exchange & Rental Hub';
require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/nav.php');
?>

  <!-- Living Holographic Interface & Spacetime Canvas Container -->
  <div class="antimatter-viewport relative w-full overflow-hidden">

    <!-- ═══════════════════════════════════════════════════════════════
         01 HERO — Dense, High-Impact Gravitational Singularity Core
         (Purged of rogue anomalies & recalibrated vertical Y-axis)
    ════════════════════════════════════════════════════════════════ -->
    <section class="relative bg-gradient-to-br from-navy-950 via-navy-900 to-slate-900 text-white overflow-hidden py-10 sm:py-12 border-b border-blue-900/50">
      
      <!-- Subtle Microdot Coordinate Texture (Zero Drag) -->
      <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(rgba(255,255,255,.08) 1px,transparent 1px);background-size:24px 24px;"></div>

      <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
        
        <!-- Glassmorphic Verified Protocol Badge -->
        <div class="ab-fade ab-fade-d1 inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-blue-200 mb-4 backdrop-blur-md shadow-md">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          <span>Premier University Equipment Protocol &bull; Verified Academic Registry</span>
        </div>

        <!-- Radiant Gradient Headline -->
        <h1 class="ab-fade ab-fade-d2 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight max-w-3xl mx-auto">
          Precision Campus Lab Equipment <br class="hidden sm:inline">
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-emerald-300 to-teal-300">
            Institutional Exchange Monolith
          </span>
        </h1>
        
        <p class="ab-fade ab-fade-d3 mt-3 text-sm sm:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed font-normal">
          Frictionless university equipment exchange for scientific calculators, drafting systems, oscilloscopes &amp; engineering kits. Verified student identity, zero platform fee, and refundable deposit protocol.
        </p>

        <!-- Glassmorphic Quantum Search Command Deck (Dense & High-Impact) -->
        <div class="ab-fade ab-fade-d4 mt-6 max-w-4xl mx-auto glass-search-deck text-left p-4 sm:p-5">
          <form id="filter-form" action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Search Keyword Input -->
            <div class="relative">
              <label for="search_query" class="block text-[11px] font-bold text-blue-200 uppercase tracking-widest mb-1 font-mono">
                // Asset Search
              </label>
              <div class="relative">
                <input type="text" id="search_query" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="e.g. Casio, Arduino, Rigol..." class="glass-input w-full pl-9 pr-3 py-2 text-sm placeholder-slate-400 font-medium">
                <svg class="w-4 h-4 text-blue-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              </div>
            </div>

            <!-- Category Selector -->
            <div>
              <label for="category_id" class="block text-[11px] font-bold text-blue-200 uppercase tracking-widest mb-1 font-mono">
                // Discipline Category
              </label>
              <select id="category_id" name="category_id" class="glass-input w-full px-3 py-2 text-sm font-medium">
                <option value="ALL">All Disciplines</option>
                <?php foreach ($categories as $cat): ?>
                  <?php $cid = $cat['category_id'] ?? $cat['id']; ?>
                  <option value="<?php echo $cid; ?>" <?php if ($category_id == $cid) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['category_name'] ?? $cat['name'] ?? ''); ?> (<?php echo $cat['item_count'] ?? 0; ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Campus Pickup Spot -->
            <div>
              <label for="pickup_spot" class="block text-[11px] font-bold text-blue-200 uppercase tracking-widest mb-1 font-mono">
                // Campus Sector
              </label>
              <select id="pickup_spot" name="pickup_spot" class="glass-input w-full px-3 py-2 text-sm font-medium">
                <option value="ALL">All Campus Sectors</option>
                <option value="Hazari Lane" <?php if ($pickup_spot === 'Hazari Lane') echo 'selected'; ?>>Hazari Lane Campus</option>
                <option value="Wasa" <?php if ($pickup_spot === 'Wasa') echo 'selected'; ?>>Wasa Campus Hub</option>
                <option value="GEC Campus" <?php if ($pickup_spot === 'GEC Campus') echo 'selected'; ?>>GEC Campus Point</option>
              </select>
            </div>

            <!-- Terminal Velocity Submit Button -->
            <div class="flex items-end">
              <button type="submit" class="quantum-submit-btn w-full py-2 px-4 text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span>Execute Query</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Glassmorphic Category Filter Pills (Tightly Aligned) -->
        <div class="ab-fade ab-fade-d5 mt-5 flex flex-wrap justify-center items-center gap-2 text-xs">
          <span class="text-blue-300 font-mono font-bold mr-1">// DISCIPLINES:</span>
          <a href="index.php" class="quantum-pill <?php echo ($category_id === 0) ? 'quantum-pill-active' : ''; ?>">
            All Equipment
          </a>
          <?php foreach ($categories as $cat): ?>
            <?php $cid = $cat['category_id'] ?? $cat['id']; ?>
            <a href="index.php?category_id=<?php echo $cid; ?>" class="quantum-pill <?php echo ($category_id == $cid) ? 'quantum-pill-active' : ''; ?>">
              <?php echo htmlspecialchars($cat['category_name'] ?? $cat['name'] ?? ''); ?>
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         02 DEEP NAVY TELEMETRY RIBBON (#0A0F1D) — Live Counters
    ════════════════════════════════════════════════════════════════ -->
    <section class="relative text-white py-4 border-b border-blue-900/40 overflow-hidden" style="background: #0A0F1D;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center divide-x divide-slate-800">
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-white">
              <span class="text-blue-400">#</span><?php echo $count_students; ?>+
            </div>
            <span class="text-[11px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Verified Scholars</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-cyan-300">
              <span class="text-emerald-400">&bull;</span><?php echo $count_avail; ?>+
            </div>
            <span class="text-[11px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Available Assets</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-emerald-400">
              <span>৳0</span>
            </div>
            <span class="text-[11px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Institutional Fee</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-amber-400">
              <span>3</span>
            </div>
            <span class="text-[11px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Campus Handover Zones</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         03 ZERO-GRAVITY EQUIPMENT MATRIX & REFINED CARD EQUILIBRIUM (#F4F8FF)
         (Exact Color Gradient and Surface DNA from About Page Section 02)
    ════════════════════════════════════════════════════════════════ -->
    <main class="flex-1 zero-g-catalog-container py-14 sm:py-20 w-full border-b border-blue-100/60" style="background: #F4F8FF;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Catalog Header & Sorter with About Page Styling -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 bg-blue-100/80 px-3.5 py-1 rounded-full font-semibold">
                Verified Campus Registry
              </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">Institutional Equipment Registry</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-600 font-mono">
              Fluid quantum equilibrium &bull; <?php echo $total_items; ?> verified assets registered across campus sectors
            </p>
          </div>

          <!-- Sort Selector -->
          <form action="index.php" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
            <?php if (!empty($query)): ?><input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>"><?php endif; ?>
            <?php if ($category_id > 0): ?><input type="hidden" name="category_id" value="<?php echo $category_id; ?>"><?php endif; ?>
            <?php if (!empty($pickup_spot)): ?><input type="hidden" name="pickup_spot" value="<?php echo htmlspecialchars($pickup_spot); ?>"><?php endif; ?>
            
            <label for="sort" class="text-xs font-mono font-bold text-slate-500 whitespace-nowrap">// ORDER:</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="bg-white border border-[#D1DFEE] text-navy-900 rounded-xl text-xs py-2 px-3 shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 font-medium">
              <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest Registry Ingress</option>
              <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Daily Rate: Low to High</option>
              <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Daily Rate: High to Low</option>
              <option value="deposit_asc" <?php if ($sort === 'deposit_asc') echo 'selected'; ?>>Deposit: Low to High</option>
            </select>
          </form>
        </div>

        <!-- 3D Equipment Cards Field Hovering in Perfect Equilibrium -->
        <?php if ($total_items > 0): ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-7">
            <?php foreach ($items as $index => $item): 
              $cond_class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
              if (($item['item_condition'] ?? '') === 'Good') {
                  $cond_class = 'bg-blue-50 text-blue-700 border-blue-200';
              } elseif (($item['item_condition'] ?? '') === 'Fair') {
                  $cond_class = 'bg-amber-50 text-amber-700 border-amber-200';
              }
              $raw_img = $item['image_url'] ?? '';
              if (!empty($raw_img)) {
                  if (strpos($raw_img, 'http://') === 0 || strpos($raw_img, 'https://') === 0) {
                      $item_image = $raw_img;
                  } else {
                      $item_image = $base_path . '/' . ltrim($raw_img, '/');
                  }
              } else {
                  $item_image = 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80';
              }
              $itemId = $item['equipment_id'] ?? $item['id'] ?? 1;
            ?>
              <!-- Zero-Gravity 3D Card Parent Wrapper -->
              <div class="zero-g-card-wrapper" data-item-id="<?php echo $itemId; ?>">
                
                <!-- The Equipment Card Element (About Page Card DNA + Quantum Equilibrium) -->
                <div class="holo-equipment-card h-full">
                  
                  <!-- Dynamic Specular Sheen Layer -->
                  <div class="holo-sheen-layer"></div>

                  <!-- Cybernetic Corner HUD Reticles -->
                  <div class="hud-corner-tl"></div>
                  <div class="hud-corner-br"></div>

                  <!-- Equipment Holographic Image Container -->
                  <div class="holo-image-container">
                    <img src="<?php echo htmlspecialchars($item_image); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" loading="lazy">
                    
                    <!-- Cyan Scanning Laser Line -->
                    <div class="holo-scanner-beam"></div>

                    <!-- Suspended Badges -->
                    <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
                      <span class="holo-badge-3d text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full border <?php echo $cond_class; ?> shadow-sm">
                        <?php echo htmlspecialchars($item['item_condition'] ?? 'Good'); ?>
                      </span>
                      <span class="holo-badge-3d text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-white/95 text-slate-700 border border-slate-200 shadow-sm">
                        <?php echo htmlspecialchars($item['category_name'] ?? 'Equipment'); ?>
                      </span>
                    </div>

                    <!-- Daily Rental Rate Capsule -->
                    <div class="holo-price-capsule">
                      ৳<?php echo number_format($item['daily_rate'] ?? 0); ?> 
                      <span class="text-[10px] text-slate-500 font-normal">/ day</span>
                    </div>
                  </div>

                  <!-- Card Telemetry Body -->
                  <div class="holo-card-body">
                    <div>
                      <div class="text-[10px] font-mono uppercase tracking-widest text-primary-600 font-bold mb-1">
                        // <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
                      </div>

                      <h3 class="holo-card-title line-clamp-2">
                        <a href="item-details.php?id=<?php echo $itemId; ?>" class="hover:text-primary-600 transition-colors">
                          <?php echo htmlspecialchars($item['title'] ?? 'Equipment'); ?>
                        </a>
                      </h3>

                      <!-- Deposit & Campus Coordinate Divider -->
                      <div class="holo-card-divider text-xs text-slate-600 space-y-1.5">
                        <div class="flex items-center justify-between">
                          <span class="text-slate-500 font-mono text-[11px]">Deposit:</span>
                          <span class="font-bold text-slate-900">
                            ৳<?php echo number_format($item['security_deposit'] ?? 0); ?> 
                            <span class="text-[10px] font-semibold text-emerald-600">(Refundable)</span>
                          </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-slate-600 truncate" title="<?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?>">
                          <svg class="w-3.5 h-3.5 text-primary-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                          <span class="truncate font-mono text-[11px]"><?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?></span>
                        </div>
                      </div>
                    </div>

                    <!-- Footer: Lender Node & Action Button -->
                    <div>
                      <div class="flex items-center justify-between text-xs mb-3.5 pt-1">
                        <div class="flex items-center gap-2">
                          <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-primary-600 to-navy-900 text-white flex items-center justify-center font-bold text-[10px] shadow-sm">
                            <?php echo strtoupper(substr($item['owner_name'] ?? 'M', 0, 1)); ?>
                          </div>
                          <span class="font-medium text-slate-700 truncate max-w-[110px] text-[11px]"><?php echo htmlspecialchars($item['owner_name'] ?? 'Member'); ?></span>
                        </div>
                        <div class="text-[10px] font-mono text-primary-700 font-semibold">
                          <?php echo htmlspecialchars($item['owner_student_id'] ?? ''); ?>
                        </div>
                      </div>

                      <!-- Frictionless Terminal Velocity Action Button -->
                      <a href="item-details.php?id=<?php echo $itemId; ?>" class="holo-action-btn">
                        <span>Inspect &amp; Rent Instrument &rarr;</span>
                      </a>
                    </div>

                  </div>

                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <!-- Glassmorphic Empty State -->
          <div class="bg-white border border-[#D1DFEE] shadow-xl rounded-2xl p-10 sm:p-14 text-center max-w-xl mx-auto">
            <div class="w-16 h-16 bg-blue-50 border border-blue-200 text-primary-600 rounded-2xl p-4 flex items-center justify-center mx-auto mb-5 shadow-sm">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-navy-900 mb-2">No Instruments Found</h3>
            <p class="text-sm text-slate-600 font-mono mb-6">No instruments match current filter parameters across this campus sector.</p>
            <a href="index.php" class="quantum-submit-btn inline-block px-6 py-2.5 text-xs font-bold font-mono">
              Reset Query Filters
            </a>
          </div>
        <?php endif; ?>

      </div>
    </main>

    <!-- ═══════════════════════════════════════════════════════════════
         04 CAMPUS CAPABILITY PILLARS (#EBF2FA) — From About Page DNA
    ════════════════════════════════════════════════════════════════ -->
    <section class="py-16 sm:py-20 border-b border-blue-100/60" style="background: #EBF2FA;">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12 ab-fade">
          <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full font-semibold">Campus Operations</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">How Rentora Empowers Campus Life</h2>
          <p class="mt-2 text-sm text-slate-600 max-w-xl mx-auto">Verified student identity, zero platform fee, and safe designated campus handover zones.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="ab-why-card ab-fade ab-fade-d1">
            <div class="bg-blue-100 text-blue-700 w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm">
              ⚡
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-2">Easy to Rent</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Find equipment listed by fellow students without unnecessary market hassle.</p>
          </div>

          <div class="ab-why-card ab-fade ab-fade-d2">
            <div class="bg-emerald-100 text-emerald-700 w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm">
              💰
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-2">Affordable</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Access specialized project gear at student rates without buying outright.</p>
          </div>

          <div class="ab-why-card ab-fade ab-fade-d3">
            <div class="bg-indigo-100 text-indigo-700 w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm">
              🎓
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-2">Campus Focused</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Built around Premier University life with safe campus handover spots.</p>
          </div>

          <div class="ab-why-card ab-fade ab-fade-d4">
            <div class="bg-amber-100 text-amber-700 w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm">
              🔄
            </div>
            <h3 class="font-bold text-navy-900 text-lg mb-2">Share &amp; Exchange</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Give unused equipment a second life by exchanging directly with peers.</p>
          </div>
        </div>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         05 MADE FOR CAMPUS LIFE — Rich Soft Blue Gradient Banner
         (Exact Visual DNA from About Page Section 07)
    ════════════════════════════════════════════════════════════════ -->
    <section class="py-16 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);">

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center mb-10 ab-fade">
          <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-200 mb-2 bg-white/10 px-3.5 py-1 rounded-full backdrop-blur-md">Campus Ecosystem</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Made for Campus Life</h2>
          <p class="mt-2 text-blue-100 max-w-xl mx-auto text-base font-medium">
            Useful equipment should be accessible when you need it across all university departments.
          </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <?php
          $campus_tiles = [
            ['💻', 'Laptops & Computing', 'Workstations for coding & CAD'],
            ['🧮', 'Scientific Calculators', 'Standard & graphic calculators'],
            ['📷', 'Cameras & Video', 'DSLRs & gear for media projects'],
            ['🔬', 'Lab Equipment', 'Microscopes & test instruments'],
          ];
          foreach ($campus_tiles as $i => [$ico, $name, $sub]): ?>
            <div class="ab-gear-tile ab-fade ab-fade-d<?php echo ($i%4)+1; ?>">
              <div class="text-2xl mb-1.5"><?php echo $ico; ?></div>
              <h4 class="font-bold text-white text-sm mb-0.5"><?php echo htmlspecialchars($name); ?></h4>
              <p class="text-xs text-blue-100/80 leading-snug"><?php echo htmlspecialchars($sub); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  </div><!-- /.antimatter-viewport -->

  <!-- Scroll-triggered Fade-up Animation Script from About Page -->
  <script>
  (function(){
    'use strict';
    if(!window.IntersectionObserver) return;
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
      });
    },{threshold:0.08});
    document.querySelectorAll('.ab-fade').forEach(function(el){ io.observe(el); });
  })();
  </script>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
