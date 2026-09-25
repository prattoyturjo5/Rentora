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
    $where_clauses[] = "(m.campus_address = :spot OR e.campus_spot = :spot)";
    $params['spot'] = $pickup_spot;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Sorting
$order_sql = "ORDER BY e.equipment_id DESC";
if ($sort === 'price_asc') {
    $order_sql = "ORDER BY e.rental_rate ASC";
} elseif ($sort === 'price_desc') {
    $order_sql = "ORDER BY e.rental_rate DESC";
}

$items = [];
$total_items = 0;
try {
    $items_sql = "SELECT e.*, e.equipment_name AS title, e.rental_rate AS daily_rate, 
                         e.condition_status AS item_condition, c.category_name, 
                         CONCAT(m.first_name, ' ', m.last_name) AS owner_name, 
                         m.username AS owner_student_id,
                         m.campus_address AS campus_spot
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

  <!-- Hero Section with Multi-Parameter Search -->
  <section class="relative bg-gradient-to-b from-navy-900 via-navy-900 to-slate-900 text-white pt-12 pb-20 overflow-hidden">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 -left-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      
      <!-- Academic Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-blue-200 mb-6 backdrop-blur-sm">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span>Trusted University Equipment Exchange & Handover Protocol</span>
      </div>

      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white max-w-3xl mx-auto leading-tight">
        Rent & Exchange Lab Kits & Gear <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">Directly on Campus</span>
      </h1>
      <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto font-normal">
        Exchange scientific calculators, drafters, cameras, and IoT kits with verified classmates. Safe in-person handover with a refundable security deposit.
      </p>

      <!-- Search & Filter Form -->
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
                <?php $cid = $cat['category_id'] ?? $cat['id']; ?>
                <option value="<?php echo $cid; ?>" <?php if ($category_id == $cid) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($cat['category_name'] ?? $cat['name'] ?? ''); ?> (<?php echo $cat['item_count'] ?? 0; ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Campus Pickup Spot -->
          <div>
            <label for="pickup_spot" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Campus Spot</label>
            <select id="pickup_spot" name="pickup_spot" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 text-slate-800 font-medium">
              <option value="ALL">Any Campus Spot</option>
              <option value="Hazari Lane" <?php if ($pickup_spot === 'Hazari Lane') echo 'selected'; ?>>Hazari Lane</option>
              <option value="Wasa" <?php if ($pickup_spot === 'Wasa') echo 'selected'; ?>>Wasa</option>
              <option value="GEC Campus" <?php if ($pickup_spot === 'GEC Campus') echo 'selected'; ?>>GEC Campus</option>
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
          <?php $cid = $cat['category_id'] ?? $cat['id']; ?>
          <a href="index.php?category_id=<?php echo $cid; ?>" class="px-3 py-1.5 rounded-full <?php echo ($category_id == $cid) ? 'bg-white/30 text-white font-bold' : 'bg-white/10 text-slate-200'; ?> hover:bg-white/20 transition-colors border border-white/15">
            <?php echo htmlspecialchars($cat['category_name'] ?? $cat['name'] ?? ''); ?>
          </a>
        <?php endforeach; ?>
      </div>

    </div>
  </section>

  <!-- Live Stats Strip -->
  <section class="bg-white border-b border-slate-200 py-3 shadow-inner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center divide-x divide-slate-100">
        <div>
          <span class="block text-xl font-extrabold text-navy-900"><?php echo $count_students; ?>+</span>
          <span class="text-xs text-slate-500 font-medium">Verified Members</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-primary-600"><?php echo $count_avail; ?>+</span>
          <span class="text-xs text-slate-500 font-medium">Available Instruments</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-emerald-600">৳0 Fee</span>
          <span class="text-xs text-slate-500 font-medium">Cash-Only, No Platform Fees</span>
        </div>
        <div>
          <span class="block text-xl font-extrabold text-amber-600">3 Pickup Zones</span>
          <span class="text-xs text-slate-500 font-medium">Official Handover Spots</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content Area: Equipment Catalog Grid -->
  <main class="flex-1 bg-slate-50 py-10 sm:py-12 w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Section Header & Filter Controls -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Available Campus Equipment</h2>
          <p class="text-sm text-slate-500 font-medium mt-1">Showing <?php echo $total_items; ?> verified equipment items in database</p>
        </div>

        <!-- Sort Form -->
        <form action="index.php" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
          <?php if (!empty($query)): ?><input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>"><?php endif; ?>
          <?php if ($category_id > 0): ?><input type="hidden" name="category_id" value="<?php echo $category_id; ?>"><?php endif; ?>
          <?php if (!empty($pickup_spot)): ?><input type="hidden" name="pickup_spot" value="<?php echo htmlspecialchars($pickup_spot); ?>"><?php endif; ?>
          
          <label for="sort" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Sort By:</label>
          <select id="sort" name="sort" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 rounded-lg px-3 py-1.5 shadow-sm text-sm focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 font-medium">
            <option value="newest" <?php if ($sort === 'newest') echo 'selected'; ?>>Newest First</option>
            <option value="price_asc" <?php if ($sort === 'price_asc') echo 'selected'; ?>>Rental Rate: Low to High</option>
            <option value="price_desc" <?php if ($sort === 'price_desc') echo 'selected'; ?>>Rental Rate: High to Low</option>
            <option value="deposit_asc" <?php if ($sort === 'deposit_asc') echo 'selected'; ?>>Deposit: Low to High</option>
          </select>
        </form>
      </div>

      <!-- Equipment Container / Card Grid -->
      <?php if ($total_items > 0): ?>
        <div class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-6 sm:p-8 lg:p-10">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($items as $item): 
              $cond_class = 'bg-emerald-100 text-emerald-800 border-emerald-200';
              if (($item['item_condition'] ?? '') === 'Good') {
                  $cond_class = 'bg-blue-100 text-blue-800 border-blue-200';
              } elseif (($item['item_condition'] ?? '') === 'Fair') {
                  $cond_class = 'bg-amber-100 text-amber-800 border-amber-200';
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
              <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 card-hover flex flex-col">
                
                <!-- Image Container -->
                <div class="relative h-48 w-full bg-slate-100 overflow-hidden group">
                  <img src="<?php echo htmlspecialchars($item_image); ?>" alt="<?php echo htmlspecialchars($item['title'] ?? ''); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                  <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full border <?php echo $cond_class; ?> shadow-sm backdrop-blur-md">
                      <?php echo htmlspecialchars($item['item_condition'] ?? 'Good'); ?>
                    </span>
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-slate-900/80 text-white backdrop-blur-md">
                      <?php echo htmlspecialchars($item['category_name'] ?? 'Equipment'); ?>
                    </span>
                  </div>
                  <div class="absolute bottom-3 right-3 bg-white/95 backdrop-blur-md px-2.5 py-1 rounded-lg text-xs font-bold text-navy-900 shadow">
                    ৳<?php echo number_format($item['daily_rate'] ?? 0); ?> <span class="text-[10px] text-slate-500 font-normal">/ day</span>
                  </div>
                </div>

                <!-- Card Body -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                  <div>
                    <div class="text-[11px] font-semibold text-primary-600 uppercase tracking-wider mb-1">
                      <?php echo htmlspecialchars($item['category_name'] ?? 'General'); ?>
                    </div>
                    <h3 class="font-bold text-navy-900 text-base leading-snug line-clamp-2 hover:text-primary-600 transition-colors">
                      <a href="item-details.php?id=<?php echo $itemId; ?>"><?php echo htmlspecialchars($item['title'] ?? 'Equipment'); ?></a>
                    </h3>

                    <!-- Deposit & Pickup Spot -->
                    <div class="mt-3 space-y-1.5 text-xs text-slate-500 border-y border-slate-100 py-2.5 my-3">
                      <div class="flex items-center justify-between">
                        <span class="text-slate-500">Deposit:</span>
                        <span class="font-bold text-slate-700">৳<?php echo number_format($item['security_deposit'] ?? 0); ?> <span class="text-[10px] font-normal text-emerald-600">(Refundable)</span></span>
                      </div>
                      <div class="flex items-center gap-1.5 text-slate-600 truncate" title="<?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?>">
                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                        <span class="truncate"><?php echo htmlspecialchars($item['campus_spot'] ?? 'Campus'); ?></span>
                      </div>
                    </div>
                  </div>

                  <!-- Footer: Lender Info & Rent Button -->
                  <div>
                    <div class="flex items-center justify-between text-xs text-slate-500 mb-3.5">
                      <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px]">
                          <?php echo strtoupper(substr($item['owner_name'] ?? 'M', 0, 1)); ?>
                        </div>
                        <span class="font-medium text-slate-700 truncate max-w-[120px]"><?php echo htmlspecialchars($item['owner_name'] ?? 'Member'); ?></span>
                      </div>
                      <div class="text-[11px] font-semibold text-primary-600">
                        <?php echo htmlspecialchars($item['owner_student_id'] ?? ''); ?>
                      </div>
                    </div>

                    <!-- View & Rent Action Button -->
                    <a href="item-details.php?id=<?php echo $itemId; ?>" class="w-full py-2.5 px-4 rounded-xl bg-navy-900 hover:bg-primary-600 text-white text-xs font-bold text-center block shadow transition-all hover:shadow-md">
                      View & Rent Equipment
                    </a>
                  </div>

                </div>

              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <!-- Empty State Container -->
        <div class="bg-white border border-slate-200/80 shadow-sm rounded-2xl p-8 sm:p-12 text-center">
          <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl p-4 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <h3 class="text-lg font-bold text-slate-900">No equipment found matching criteria</h3>
          <p class="text-sm text-slate-500 font-medium max-w-md mx-auto mt-2">Try relaxing your category or pickup spot filter, or search with different keywords.</p>
          <a href="index.php" class="inline-block mt-6 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-sm shadow-sm transition">
            Reset All Filters
          </a>
        </div>
      <?php endif; ?>

    </div>
  </main>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
