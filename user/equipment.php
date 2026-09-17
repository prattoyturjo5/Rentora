<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$user_id = $_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0;
$error = "";
$success = "";

// Fetch categories for the Add Equipment form
$categories = [];
try {
    $catStmt = $pdo->query("SELECT category_id, category_name FROM category ORDER BY category_name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Handle Add Equipment Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $equipment_name = trim($_POST['title'] ?? $_POST['equipment_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $rental_rate = floatval($_POST['daily_rate'] ?? $_POST['rental_rate'] ?? 0);
    $security_deposit = floatval($_POST['security_deposit'] ?? 0);
    $campus_spot = trim($_POST['campus_spot'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $condition = trim($_POST['item_condition'] ?? $_POST['condition_status'] ?? 'Good');
    if ($condition === 'Like New') {
        $condition = 'New';
    }
    if (!in_array($condition, ['New', 'Good', 'Fair', 'Poor'])) {
        $condition = 'Good';
    }
    $description = trim($_POST['description'] ?? '');

    if (empty($equipment_name) || $rental_rate <= 0) {
        $error = "Please fill in all required equipment fields (Title and Rental Rate).";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO equipment (owner_id, category_id, equipment_name, description, condition_status, availability_status, rental_rate, security_deposit, campus_spot, image_url)
                VALUES (:owner_id, :category_id, :equipment_name, :description, :condition_status, 'Available', :rental_rate, :security_deposit, :campus_spot, :image_url)
            ");
            $stmt->execute([
                'owner_id'         => $user_id,
                'category_id'      => $category_id,
                'equipment_name'   => $equipment_name,
                'description'      => $description,
                'condition_status' => $condition,
                'rental_rate'      => $rental_rate,
                'security_deposit' => $security_deposit,
                'campus_spot'      => $campus_spot,
                'image_url'        => $image_url
            ]);
            $success = "Equipment listing added successfully!";
        } catch (PDOException $e) {
            $error = "Database notice: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Handle Delete Equipment
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id AND owner_id = :owner_id");
        $delStmt->execute(['id' => $del_id, 'owner_id' => $user_id]);
        $success = "Equipment listing deleted.";
    } catch (PDOException $e) {
        $error = "Could not delete equipment: " . htmlspecialchars($e->getMessage());
    }
}

// Handle Toggle Availability
if (isset($_GET['toggle'])) {
    $tog_id = (int)$_GET['toggle'];
    try {
        $togStmt = $pdo->prepare("
            UPDATE equipment 
            SET availability_status = CASE WHEN availability_status = 'Available' THEN 'Rented' ELSE 'Available' END 
            WHERE equipment_id = :id AND owner_id = :owner_id
        ");
        $togStmt->execute(['id' => $tog_id, 'owner_id' => $user_id]);
        $success = "Equipment availability toggled.";
    } catch (PDOException $e) {
        $error = "Could not update availability.";
    }
}

// Fetch member's equipment
$my_equipment = [];
try {
    $stmt = $pdo->prepare("
        SELECT e.*, e.equipment_name AS title, e.rental_rate AS daily_rate, c.category_name,
               (CASE WHEN e.availability_status = 'Available' THEN 1 ELSE 0 END) AS is_available
        FROM equipment e 
        LEFT JOIN category c ON e.category_id = c.category_id 
        WHERE e.owner_id = :owner_id
        ORDER BY e.equipment_id DESC
    ");
    $stmt->execute(['owner_id' => $user_id]);
    $my_equipment = $stmt->fetchAll();
} catch (Exception $e) {
    $my_equipment = [];
}

$base_path = '..';
$page_title = 'My Equipment Listings - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">My Equipment Listings</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage gear you have listed for rent or exchange on campus.</p>
      </div>

      <a href="#add-item-modal" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition-all flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span>+ Add New Equipment</span>
      </a>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($error)): ?>
      <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <!-- Equipment Grid -->
    <?php if (empty($my_equipment)): ?>
      <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-primary-600 flex items-center justify-center mx-auto mb-4 text-2xl">
          📦
        </div>
        <h3 class="text-lg font-bold text-navy-900">No equipment listed yet</h3>
        <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-6">You haven't listed any equipment yet. Start earning money or exchanging items by listing your calculators, lab gear, or drafter kits.</p>
        <a href="#add-item-modal" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 text-white font-bold text-xs shadow-md shadow-blue-600/30">
          + Add Your First Item
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach ($my_equipment as $eq): ?>
          <?php $eqId = $eq['equipment_id'] ?? $eq['id'] ?? 0; ?>
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
              <div class="h-44 bg-slate-100 relative overflow-hidden">
                <img src="<?php echo htmlspecialchars($eq['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($eq['title'] ?? ''); ?>" class="w-full h-full object-cover">
                <div class="absolute top-3 right-3">
                  <?php if (!empty($eq['is_available'])): ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500 text-white shadow-sm">Available</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-700 text-white shadow-sm">Rented Out</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="p-5">
                <span class="text-[11px] font-bold text-primary-600 uppercase tracking-wider"><?php echo htmlspecialchars($eq['category_name'] ?? 'Equipment'); ?></span>
                <h3 class="text-base font-bold text-navy-900 mt-1 line-clamp-1"><?php echo htmlspecialchars($eq['title'] ?? ''); ?></h3>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?php echo htmlspecialchars($eq['description'] ?? ''); ?></p>
                
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                  <div>
                    <span class="text-slate-400 block text-[10px]">Daily Rate</span>
                    <strong class="text-navy-900 font-extrabold">৳<?php echo number_format($eq['daily_rate'] ?? 0, 2); ?></strong>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">Deposit</span>
                    <strong class="text-slate-700 font-bold">৳<?php echo number_format($eq['security_deposit'] ?? 0, 2); ?></strong>
                  </div>
                  <div>
                    <span class="text-slate-400 block text-[10px]">Spot</span>
                    <span class="text-slate-600 font-medium"><?php echo htmlspecialchars($eq['campus_spot'] ?? 'Campus'); ?></span>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center text-xs">
              <a href="equipment.php?toggle=<?php echo $eqId; ?>" class="text-blue-600 hover:underline font-bold">
                <?php echo !empty($eq['is_available']) ? 'Mark as Rented' : 'Mark as Available'; ?>
              </a>
              <a href="equipment.php?delete=<?php echo $eqId; ?>" onclick="return confirm('Permanently remove this listing?')" class="text-red-600 hover:text-red-800 font-bold">
                Delete
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Add Item Section / Anchor Card -->
    <div id="add-item-modal" class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 sm:p-8 max-w-2xl mx-auto">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h2 class="text-lg font-extrabold text-navy-900">List New Equipment</h2>
        <p class="text-xs text-slate-500">Provide details so campus peers can borrow or swap gear</p>
      </div>

      <form action="equipment.php" method="POST" class="space-y-4">
        
        <div>
          <label for="title" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Equipment Title *</label>
          <input type="text" id="title" name="title" required placeholder="e.g. Casio fx-991EX ClassWiz Calculator" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="category_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Category *</label>
            <select id="category_id" name="category_id" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
              <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c['category_id'] ?? $c['id']; ?>"><?php echo htmlspecialchars($c['category_name'] ?? $c['name'] ?? ''); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="item_condition" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Condition *</label>
            <select id="item_condition" name="item_condition" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
              <option value="New">New</option>
              <option value="Good" selected>Good</option>
              <option value="Fair">Fair</option>
              <option value="Poor">Poor</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="daily_rate" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Daily Rent (৳) *</label>
            <input type="number" step="0.01" id="daily_rate" name="daily_rate" required placeholder="50.00" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>

          <div>
            <label for="security_deposit" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Security Deposit (৳) *</label>
            <input type="number" step="0.01" id="security_deposit" name="security_deposit" required placeholder="500.00" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
          </div>
        </div>

        <div>
          <label for="campus_spot" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Campus Handover Spot *</label>
          <select id="campus_spot" name="campus_spot" class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
            <option value="Central Library Front Gate">Central Library Front Gate</option>
            <option value="TSC Ground / Student Union">TSC Ground / Student Union</option>
            <option value="Academic Building-1 Gate">Academic Building-1 Gate</option>
            <option value="Engineering Lab Complex">Engineering Lab Complex</option>
            <option value="Campus Cafeteria Entrance">Campus Cafeteria Entrance</option>
          </select>
        </div>

        <div>
          <label for="image_url" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Image URL (Optional)</label>
          <input type="url" id="image_url" name="image_url" placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800">
        </div>

        <div>
          <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
          <textarea id="description" name="description" rows="3" placeholder="Condition, included accessories, batteries, allowed exams, etc." class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-600 focus:border-primary-600 font-medium text-slate-800"></textarea>
        </div>

        <button type="submit" name="add_equipment" class="w-full py-3 px-4 bg-navy-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-slate-900/20 transition-all flex items-center justify-center gap-2">
          <span>Publish Equipment Listing</span>
          <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>
    </div>

  </main>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
