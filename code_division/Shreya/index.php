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
        WHERE c.category_name != 'Furniture'
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
                         COALESCE(NULLIF(m.student_id, ''), m.username) AS owner_student_id,
                         m.username AS owner_username,
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

  <div class="antimatter-viewport relative w-full overflow-hidden flex-1">

    <!-- 01 HERO — Modern Floating Search Command Deck -->
    <section class="relative bg-canvas border-b border-subtle py-12 sm:py-16 transition-colors">
      <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10">
        
        <!-- Verified Academic Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-surface-subtle border border-subtle text-xs font-semibold text-slate-600 dark:text-muted mb-4 shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="text-primary font-bold">Premier University</span>
          <span>&bull; Equipment Exchange Protocol</span>
        </div>

        <!-- Headline -->
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight max-w-3xl mx-auto text-slate-950 dark:text-primary">
          Your Campus Hub for <br class="hidden sm:inline">
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 dark:from-sky-400 dark:via-cyan-400 dark:to-teal-300">Equipment &amp; Essentials</span>
        </h1>
        
        <p class="mt-3 text-sm sm:text-base text-slate-800 dark:text-muted max-w-2xl mx-auto leading-relaxed font-normal">
          Peer-to-peer university equipment sharing for scientific calculators, drafting systems, oscilloscopes &amp; lab kits with verified student identity and zero platform fees.
        </p>

        <!-- Floating Search Deck -->
        <div class="mt-8 max-w-4xl mx-auto bg-white dark:bg-surface border border-slate-300 dark:border-subtle rounded-2xl text-left p-4 sm:p-5 shadow-float">
          <form id="filter-form" action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            
            <!-- Search Keyword Input -->
            <div class="relative">
              <label for="search_query" class="block text-[11px] font-bold text-slate-700 dark:text-muted uppercase tracking-wider mb-1 font-mono">
                Keyword Search
              </label>
              <div class="relative">
                <input type="text" id="search_query" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="e.g. Casio, Arduino, Rigol..." class="input-subtle pl-9 text-xs">
                <svg class="w-4 h-4 text-muted absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              </div>
            </div>

            <!-- Category Selector -->
            <div>
              <label for="category_id" class="block text-[11px] font-bold text-slate-700 dark:text-muted uppercase tracking-wider mb-1 font-mono">
                Category
              </label>
              <select id="category_id" name="category_id" class="input-subtle text-xs">
                <option value="ALL">All Categories</option>
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
              <label for="pickup_spot" class="block text-[11px] font-bold text-slate-700 dark:text-muted uppercase tracking-wider mb-1 font-mono">
                Campus Zone
              </label>
              <select id="pickup_spot" name="pickup_spot" class="input-subtle text-xs">
                <option value="ALL">All Campus Spots</option>
                <option value="Hazari Lane" <?php if ($pickup_spot === 'Hazari Lane') echo 'selected'; ?>>Hazari Lane</option>
                <option value="Wasa" <?php if ($pickup_spot === 'Wasa') echo 'selected'; ?>>Wasa Campus</option>
                <option value="GEC Campus" <?php if ($pickup_spot === 'GEC Campus') echo 'selected'; ?>>GEC Campus</option>
              </select>
            </div>

            <!-- Submit Button (10% Accent CTA) -->
            <div class="flex items-end">
              <button type="submit" class="btn-accent w-full py-2.5 text-xs">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <span>Filter Equipment</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Discipline Filter Pills -->
        <div class="mt-5 flex flex-wrap justify-center items-center gap-2 text-xs">
          <span class="text-slate-700 dark:text-muted font-mono font-bold text-[11px] uppercase tracking-wider mr-1">Disciplines:</span>
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

    <!-- 02 TELEMETRY METRIC PODS -->
    <section class="bg-surface border-b border-subtle py-4 transition-colors">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center divide-x divide-border-subtle">
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-primary font-black">
              <span class="text-accent">#</span><?php echo $count_students; ?>+
            </div>
            <span class="text-[11px] text-slate-700 dark:text-muted font-mono uppercase tracking-wider font-bold">Registered Scholars</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-primary font-black">
              <span class="text-emerald-500">&bull;</span><?php echo $count_avail; ?>+
            </div>
            <span class="text-[11px] text-slate-700 dark:text-muted font-mono uppercase tracking-wider font-bold">Available Assets</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-emerald-600 dark:text-emerald-400 font-black">
              <span>৳0</span>
            </div>
            <span class="text-[11px] text-slate-700 dark:text-muted font-mono uppercase tracking-wider font-bold">Platform Fee</span>
          </div>
          <div class="reactor-metric-pod">
            <div class="reactor-metric-val text-accent font-black">
              <span>3</span>
            </div>
            <span class="text-[11px] text-slate-700 dark:text-muted font-mono uppercase tracking-wider font-bold">Campus Handover Zones</span>
          </div>
        </div>
      </div>
    </section>

    <!-- 03 FLOATING EQUIPMENT MATRIX -->
    <main class="py-12 sm:py-16 w-full bg-canvas">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Catalog Header & Sorter -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="inline-block text-[10px] font-bold uppercase tracking-widest text-accent bg-surface-subtle border border-subtle px-3 py-0.5 rounded-full">
                Active Catalog
              </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-950 dark:text-primary tracking-tight">Available Campus Equipment</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-700 dark:text-muted">
              <?php echo $total_items; ?> verified assets listed across university sectors
            </p>
          </div>

          <!-- Sort Selector -->
          <form action="index.php" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <?php if (!empty($query)): ?><input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>"><?php endif; ?>
            <?php if ($category_id > 0): ?><input type="hidden" name="category_id" value="<?php echo $category_id; ?>"><?php endif; ?>
            <?php if (!empty($pickup_spot)): ?><input type="hidden" name="pickup_spot" value="<?php echo htmlspecialchars($pickup_spot); ?>"><?php endif; ?>
            
            <label for="sort" class="text-xs font-mono font-bold text-slate-700 dark:text-muted uppercase tracking-wider whitespace-nowrap">Sort:</label>
            <select id="sort" name="sort" onchange="this.form.submit()" class="input-subtle text-xs py-1.5 px-3">
              <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest First</option>
              <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Daily Rate: Low to High</option>
              <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Daily Rate: High to Low</option>
              <option value="deposit_asc" <?php if ($sort === 'deposit_asc') echo 'selected'; ?>>Deposit: Low to High</option>
            </select>
          </form>
        </div>

        <!-- Floating Cards Grid -->
        <?php if ($total_items > 0): ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($items as $index => $item): 
              $cond_raw = trim($item['item_condition'] ?? 'Good');
              $cond_lower = strtolower($cond_raw);
              if ($cond_lower === 'poor') {
                  $cond_badge = 'badge-condition-poor';
              } elseif ($cond_lower === 'fair') {
                  $cond_badge = 'badge-condition-fair';
              } elseif ($cond_lower === 'new' || $cond_lower === 'like new') {
                  $cond_badge = 'badge-condition-new';
              } else {
                  $cond_badge = 'badge-condition-good';
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
              <div class="zero-g-card-wrapper">
                <div class="holo-equipment-card">
                  
                  <!-- Image Container -->
                  <div class="holo-image-container">
                    <img src="<?php echo htmlspecialchars($item_image); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" loading="lazy">
                    
                    <!-- Suspended Badges -->
                    <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
                      <span class="badge-subtle text-[10px] <?php echo $cond_badge; ?>">
                        <?php echo htmlspecialchars($item['item_condition'] ?? 'Good'); ?>
                      </span>
                      <span class="badge-subtle text-[10px] badge-category-glass">
                        <?php echo htmlspecialchars($item['category_name'] ?? 'Equipment'); ?>
                      </span>
                    </div>

                    <!-- Daily Rental Rate Capsule -->
                    <div class="holo-price-capsule">
                      ৳<?php echo number_format($item['daily_rate'] ?? 0); ?> 
                      <span class="text-[10px] text-slate-300 font-normal">/ day</span>
                    </div>
                  </div>

                  <!-- Card Telemetry Body -->
                  <div class="holo-card-body">
                    <div>
                      <div class="text-[10px] font-mono uppercase tracking-wider text-accent font-bold mb-1">
                        // <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
                      </div>

                      <h3 class="holo-card-title line-clamp-2">
                        <a href="item-details.php?id=<?php echo $itemId; ?>">
                          <?php echo htmlspecialchars($item['title'] ?? 'Equipment'); ?>
                        </a>
                      </h3>

                      <!-- Deposit & Location -->
                      <div class="holo-card-divider text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                          <span class="text-muted text-[11px]">Deposit:</span>
                          <span class="font-bold text-primary">
                            ৳<?php echo number_format($item['security_deposit'] ?? 0); ?> 
                            <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">(Refundable)</span>
                          </span>
                        </div>
                        <div class="flex items-center gap-1.5 text-muted truncate" title="<?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?>">
                          <svg class="w-3.5 h-3.5 text-accent shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                          <span class="truncate font-mono text-[11px]"><?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?></span>
                        </div>
                      </div>
                    </div>

                    <!-- Footer: Owner & CTA -->
                    <div class="mt-4">
                      <div class="flex items-center justify-between text-xs mb-3 pt-1 border-t border-subtle">
                        <div class="flex items-center gap-2">
                          <div class="w-5 h-5 rounded-full bg-accent text-white flex items-center justify-center font-bold text-[9px]">
                            <?php echo strtoupper(substr($item['owner_name'] ?? 'M', 0, 1)); ?>
                          </div>
                          <span class="font-medium text-muted truncate max-w-[110px] text-[11px]"><?php echo htmlspecialchars($item['owner_name'] ?? 'Member'); ?></span>
                        </div>
                        <div class="text-[10px] font-mono text-muted">
                          @<?php echo htmlspecialchars(!empty($item['owner_username']) ? $item['owner_username'] : ($item['owner_student_id'] ?? '')); ?>
                        </div>
                      </div>

                      <a href="item-details.php?id=<?php echo $itemId; ?>" class="holo-action-btn">
                        <span>Inspect &amp; Rent &rarr;</span>
                      </a>
                    </div>

                  </div>

                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <!-- Empty State Card -->
          <div class="bg-surface border border-subtle shadow-float rounded-2xl p-10 sm:p-14 text-center max-w-xl mx-auto">
            <div class="w-14 h-14 bg-surface-subtle border border-subtle text-accent rounded-2xl p-3 flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-lg font-bold text-primary mb-1">No Instruments Found</h3>
            <p class="text-xs text-muted mb-5">No equipment matches current search parameters across campus sectors.</p>
            <a href="index.php" class="btn-secondary text-xs">
              Reset Filters
            </a>
          </div>
        <?php endif; ?>

      </div>
    </main>

    <!-- 04 CAMPUS CAPABILITY PILLARS -->
    <section class="py-16 bg-surface-subtle border-b border-subtle transition-colors">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
          <span class="inline-block text-[10px] font-bold uppercase tracking-widest text-accent mb-2 bg-surface border border-subtle px-3 py-1 rounded-full">
            Operations Protocol
          </span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight">How Rentora Empowers Campus Life</h2>
          <p class="mt-2 text-xs sm:text-sm text-muted max-w-xl mx-auto">Verified student identity, zero platform commission, and designated campus handover spots.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card-floating p-6 bg-surface">
            <div class="w-10 h-10 rounded-xl bg-accent/10 text-accent flex items-center justify-center text-xl mb-4">
              ⚡
            </div>
            <h3 class="font-bold text-primary text-base mb-1.5">Frictionless Rental</h3>
            <p class="text-xs text-muted leading-relaxed">Book equipment from peers with instant handover tokens and transparent terms.</p>
          </div>

          <div class="card-floating p-6 bg-surface">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl mb-4">
              💰
            </div>
            <h3 class="font-bold text-primary text-base mb-1.5">Affordable Rates</h3>
            <p class="text-xs text-muted leading-relaxed">Access lab tools at reasonable student prices with fully refundable security deposits.</p>
          </div>

          <div class="card-floating p-6 bg-surface">
            <div class="w-10 h-10 rounded-xl bg-blue-700/10 text-blue-700 dark:bg-sky-500/10 dark:text-sky-500 flex items-center justify-center text-xl mb-4">
              🎓
            </div>
            <h3 class="font-bold text-primary text-base mb-1.5">Campus Community</h3>
            <p class="text-xs text-muted leading-relaxed">Exclusively for Premier University students with convenient handover zones.</p>
          </div>

          <div class="card-floating p-6 bg-surface">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl mb-4">
              🔄
            </div>
            <h3 class="font-bold text-primary text-base mb-1.5">Peer Exchanges</h3>
            <p class="text-xs text-muted leading-relaxed">Direct swap agreements allow sharing tools without any monetary transaction.</p>
          </div>
        </div>

      </div>
    </section>

  </div>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
