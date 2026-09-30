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
$member_status = get_member_status($pdo, $user_id);
$error = "";
$success = "";

$member_phone = '';
$member_email = $_SESSION['email'] ?? '';
try {
    $mStmt = $pdo->prepare("SELECT phone_number, university_email FROM member WHERE member_id = :id LIMIT 1");
    $mStmt->execute(['id' => $user_id]);
    $mRow = $mStmt->fetch();
    if ($mRow) {
        $member_phone = $mRow['phone_number'] ?? '';
        if (!empty($mRow['university_email'])) {
            $member_email = $mRow['university_email'];
        }
    }
} catch (Exception $e) {}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'item_added') {
        $success = "Equipment listing added successfully!";
    } elseif ($_GET['msg'] === 'deleted') {
        $success = "Equipment listing deleted successfully.";
    } elseif ($_GET['msg'] === 'archived') {
        $success = "Equipment is linked to existing rental records and has been archived (marked Unavailable) to protect relational integrity.";
    }
}
if (isset($_GET['error'])) {
    $errCode = $_GET['error'];
    if ($errCode === 'account_pending') {
        $error = "Your account is pending verification. Equipment listing is disabled until verified by an administrator.";
    } elseif ($errCode === 'account_rejected') {
        $error = "Your account was rejected. Please contact an admin for assistance.";
    } elseif ($errCode === 'missing_fields') {
        $error = "Please fill in all required equipment fields (Title, Rental Rate, and Equipment Image).";
    } elseif ($errCode === 'missing_image') {
        $error = "An equipment image is required. Please upload a photo (JPG, PNG, or WebP).";
    } elseif ($errCode === 'invalid_image') {
        $error = "Invalid image file format. Only JPG, PNG, and WebP images are allowed.";
    } elseif ($errCode === 'file_too_large') {
        $error = "Image file is too large. Maximum allowed size is 5MB.";
    } elseif ($errCode === 'upload_failed') {
        $error = "Failed to upload the equipment image. Please try again.";
    } elseif ($errCode === 'db_error') {
        $error = "Database error while adding equipment listing.";
    } else {
        $error = htmlspecialchars($errCode);
    }
}

// Fetch categories for the Add Equipment form
$categories = [];
try {
    $catStmt = $pdo->query("SELECT category_id, category_name FROM category ORDER BY category_name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Handle Direct Add Equipment Submission (delegates to add_item.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_equipment']) || isset($_POST['title']) || isset($_POST['equipment_name']))) {
    if ($member_status !== 'Verified') {
        $err = ($member_status === 'Rejected') ? 'account_rejected' : 'account_pending';
        header("Location: equipment.php?error=" . $err);
        exit();
    }
    require_once(__DIR__ . '/add_item.php');
    exit();
}

// Handle Delete Equipment
if (isset($_GET['delete'])) {
    $equipment_id = (int)$_GET['delete'];
    $owner_id = (int)$user_id;

    try {
        $checkStmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = :id AND owner_id = :owner_id LIMIT 1");
        $checkStmt->execute(['id' => $equipment_id, 'owner_id' => $owner_id]);
        $ownedItem = $checkStmt->fetch();

        if ($ownedItem) {
            $checkRentals = $pdo->prepare("SELECT COUNT(*) AS total FROM rental_agreement WHERE equipment_id = :id");
            $checkRentals->execute(['id' => $equipment_id]);
            $rentalCount = (int)($checkRentals->fetch()['total'] ?? 0);

            $checkExchanges = $pdo->prepare("SELECT COUNT(*) AS total FROM exchange_agreement WHERE equipment_a_id = :id1 OR equipment_b_id = :id2");
            $checkExchanges->execute(['id1' => $equipment_id, 'id2' => $equipment_id]);
            $exchangeCount = (int)($checkExchanges->fetch()['total'] ?? 0);

            if ($rentalCount > 0 || $exchangeCount > 0) {
                $archiveStmt = $pdo->prepare("UPDATE equipment SET availability_status = 'Unavailable' WHERE equipment_id = :id AND owner_id = :owner_id");
                $archiveStmt->execute(['id' => $equipment_id, 'owner_id' => $owner_id]);
                header("Location: equipment.php?msg=archived");
                exit();
            } else {
                $delStmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id AND owner_id = :owner_id");
                $delStmt->execute(['id' => $equipment_id, 'owner_id' => $owner_id]);
                header("Location: equipment.php?msg=deleted");
                exit();
            }
        } else {
            $error = "Equipment listing not found or you do not have permission to delete it.";
        }
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

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full bg-canvas">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-extrabold text-primary tracking-tight">My Equipment Listings</h1>
        <p class="text-xs text-muted mt-0.5">Manage gear you have listed for rent or exchange on campus.</p>
      </div>

      <a href="#add-item-modal" class="btn-accent text-xs">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span>Add New Equipment</span>
      </a>
    </div>

    <!-- Alert Notices -->
    <?php if (!empty($error)): ?>
      <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span><?php echo htmlspecialchars($success); ?></span>
      </div>
    <?php endif; ?>

    <!-- Equipment Grid -->
    <?php if (empty($my_equipment)): ?>
      <div class="bg-surface rounded-2xl p-12 text-center border border-subtle shadow-float">
        <div class="w-14 h-14 rounded-2xl bg-surface-subtle text-accent flex items-center justify-center mx-auto mb-4 text-2xl">
          📦
        </div>
        <h3 class="text-lg font-bold text-primary">No equipment listed yet</h3>
        <p class="text-xs text-muted max-w-md mx-auto mt-1 mb-6">You haven't listed any equipment yet. Start earning money or exchanging items by listing your calculators, lab gear, or drafter kits.</p>
        <a href="#add-item-modal" class="btn-accent text-xs">
          + Add Your First Item
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach ($my_equipment as $eq): ?>
          <?php $eqId = $eq['equipment_id'] ?? $eq['id'] ?? 0; ?>
          <div class="bg-surface rounded-2xl border border-subtle shadow-float overflow-hidden flex flex-col justify-between transition-colors">
            <div>
              <div class="h-44 bg-surface-subtle relative overflow-hidden">
                <?php 
                  $eq_img = $eq['image_url'] ?? '';
                  if (!empty($eq_img)) {
                      $img_src = (strpos($eq_img, 'http://') === 0 || strpos($eq_img, 'https://') === 0) ? $eq_img : $base_path . '/' . ltrim($eq_img, '/');
                  } else {
                      $img_src = 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80';
                  }
                ?>
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($eq['title'] ?? ''); ?>" class="w-full h-full object-cover">
                <div class="absolute top-3 right-3">
                  <?php if (!empty($eq['is_available'])): ?>
                    <span class="badge-subtle badge-verified text-[10px]">Available</span>
                  <?php else: ?>
                    <span class="badge-subtle text-[10px]">Rented Out</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="p-5">
                <span class="text-[11px] font-bold text-accent uppercase tracking-wider font-mono"><?php echo htmlspecialchars($eq['category_name'] ?? 'Equipment'); ?></span>
                <h3 class="text-base font-bold text-primary mt-1 line-clamp-1"><?php echo htmlspecialchars($eq['title'] ?? ''); ?></h3>
                <p class="text-xs text-muted mt-1 line-clamp-2"><?php echo htmlspecialchars($eq['description'] ?? ''); ?></p>
                
                <div class="mt-4 pt-3 border-t border-subtle flex justify-between items-center text-xs">
                  <div>
                    <span class="text-muted block text-[10px]">Daily Rate</span>
                    <strong class="text-primary font-mono font-extrabold">৳<?php echo number_format($eq['daily_rate'] ?? 0, 2); ?></strong>
                  </div>
                  <div>
                    <span class="text-muted block text-[10px]">Deposit</span>
                    <strong class="text-primary font-mono font-bold">৳<?php echo number_format($eq['security_deposit'] ?? 0, 2); ?></strong>
                  </div>
                  <div>
                    <span class="text-muted block text-[10px]">Spot</span>
                    <span class="text-primary font-medium"><?php echo htmlspecialchars($eq['campus_spot'] ?? 'Campus'); ?></span>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-4 bg-surface-subtle border-t border-subtle flex justify-between items-center text-xs">
              <a href="equipment.php?toggle=<?php echo $eqId; ?>" class="text-accent hover:underline font-bold">
                <?php echo !empty($eq['is_available']) ? 'Mark as Rented' : 'Mark as Available'; ?>
              </a>
              <a href="equipment.php?delete=<?php echo $eqId; ?>" onclick="return confirm('Permanently remove this listing?')" class="text-rose-600 dark:text-rose-400 hover:underline font-bold">
                Delete
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Add Item Section / Anchor Card -->
    <div id="add-item-modal" class="bg-surface rounded-2xl border border-subtle shadow-float p-6 sm:p-8 max-w-2xl mx-auto transition-colors">
      <div class="border-b border-subtle pb-4 mb-6">
        <h2 class="text-lg font-extrabold text-primary">List New Equipment</h2>
        <p class="text-xs text-muted">Provide details so campus peers can borrow or swap gear</p>
      </div>

      <?php if ($member_status !== 'Verified'): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <span>
            <?php echo ($member_status === 'Rejected') ? 'Your account was rejected. You cannot publish equipment listings.' : 'Your account is pending verification. You will be able to publish listings once approved by an administrator.'; ?>
          </span>
        </div>
      <?php endif; ?>

      <form action="add_item.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        
        <div>
          <label for="title" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Equipment Title *</label>
          <input type="text" id="title" name="title" required placeholder="e.g. Casio fx-991EX ClassWiz Calculator" class="input-subtle text-xs">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="category_id" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Category *</label>
            <select id="category_id" name="category_id" class="input-subtle text-xs">
              <?php foreach ($categories as $c): ?>
                <option value="<?php echo $c['category_id'] ?? $c['id']; ?>"><?php echo htmlspecialchars($c['category_name'] ?? $c['name'] ?? ''); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="item_condition" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Condition *</label>
            <select id="item_condition" name="item_condition" class="input-subtle text-xs">
              <option value="New">New</option>
              <option value="Good" selected>Good</option>
              <option value="Fair">Fair</option>
              <option value="Poor">Poor</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="daily_rate" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Daily Rent (৳) *</label>
            <input type="number" step="0.01" id="daily_rate" name="daily_rate" required placeholder="50.00" class="input-subtle text-xs">
          </div>

          <div>
            <label for="security_deposit" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Security Deposit (৳) *</label>
            <input type="number" step="0.01" id="security_deposit" name="security_deposit" required placeholder="500.00" class="input-subtle text-xs">
          </div>
        </div>

        <div>
          <label for="campus_spot" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Campus Handover Spot *</label>
          <select id="campus_spot" name="campus_spot" class="input-subtle text-xs">
            <option value="Hazari Lane">Hazari Lane</option>
            <option value="Wasa">Wasa</option>
            <option value="GEC Campus">GEC Campus</option>
          </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="lender_phone" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Contact Phone *</label>
            <input type="tel" id="lender_phone" name="lender_phone" required placeholder="018XXXXXXXX" value="<?php echo htmlspecialchars($member_phone); ?>" class="input-subtle text-xs">
          </div>
          <div>
            <label for="lender_email" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Contact Email *</label>
            <input type="email" id="lender_email" name="lender_email" required value="<?php echo htmlspecialchars($member_email); ?>" class="input-subtle text-xs">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-muted uppercase tracking-wider mb-1.5 flex items-center justify-between font-mono">
            <span>Equipment Image *</span>
            <span class="text-[11px] font-normal text-muted">JPG, PNG, WebP up to 5MB</span>
          </label>

          <!-- Upload Container -->
          <div id="equipment-upload-wrapper" class="relative">
            <div id="upload-dropzone" class="relative group border-2 border-dashed border-subtle hover:border-accent rounded-2xl p-6 text-center cursor-pointer transition-all bg-surface-subtle overflow-hidden">
              <input type="file" id="equipment_image" name="equipment_image" accept="image/jpeg,image/png,image/webp" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" title="Choose equipment image">
              <input type="hidden" id="dropped_web_image_base64" name="dropped_web_image_base64" value="">
              
              <!-- Default State -->
              <div id="dropzone-default" class="pointer-events-none transition-opacity duration-200">
                <div class="w-10 h-10 rounded-xl bg-surface text-accent flex items-center justify-center mx-auto mb-2 shadow-sm transition-transform group-hover:scale-110">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <p class="text-xs font-semibold text-primary">
                  <span class="text-accent group-hover:underline">Click to upload</span> or drag image here
                </p>
                <p class="text-[11px] text-muted mt-0.5">Drag from PC or drop any web image &bull; PNG, JPG, WebP (max 5MB)</p>
              </div>

              <!-- Drag Hover Active Overlay -->
              <div id="dropzone-dragover" class="hidden pointer-events-none absolute inset-0 bg-accent/10 backdrop-blur-[2px] flex flex-col items-center justify-center border-2 border-accent rounded-2xl z-20 transition-all">
                <div class="w-10 h-10 rounded-xl bg-accent text-white flex items-center justify-center mb-2 shadow-md animate-bounce">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <p class="text-xs font-bold text-accent">Drop Image Here</p>
                <p class="text-[10px] text-muted">Supports PC files and Web images</p>
              </div>

              <!-- Downloading / Processing Overlay -->
              <div id="dropzone-loading" class="hidden pointer-events-none absolute inset-0 bg-surface/90 backdrop-blur-[2px] flex flex-col items-center justify-center rounded-2xl z-20 transition-all">
                <div class="w-8 h-8 border-2 border-accent border-t-transparent rounded-full animate-spin mb-2"></div>
                <p id="dropzone-loading-text" class="text-xs font-bold text-primary">Fetching web image...</p>
                <p class="text-[10px] text-muted">Downloading and validating image</p>
              </div>
            </div>

            <div id="upload-preview-card" class="hidden p-3 bg-surface border border-subtle rounded-2xl shadow-sm">
              <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 rounded-xl overflow-hidden bg-surface-subtle border border-subtle shrink-0">
                  <img id="image-preview-thumb" src="" alt="Equipment preview" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-1.5">
                    <p id="image-preview-name" class="text-xs font-bold text-primary truncate"></p>
                    <span id="image-preview-source-badge" class="hidden text-[9px] font-mono px-1.5 py-0.5 rounded bg-accent/10 text-accent font-semibold">Web Image</span>
                  </div>
                  <p id="image-preview-meta" class="text-[11px] text-muted"></p>
                  <button type="button" id="btn-change-image" class="text-[11px] text-accent font-semibold hover:underline">Change photo</button>
                </div>
                <button type="button" id="btn-remove-image" class="p-1.5 rounded-lg bg-surface-subtle hover:bg-rose-500/10 text-muted hover:text-rose-500 transition-colors" title="Remove">
                  &times;
                </button>
              </div>
            </div>

            <p id="upload-error-text" class="hidden text-xs text-rose-600 dark:text-rose-400 font-medium mt-1.5 flex items-center gap-1.5">
              <span id="upload-error-msg">Please upload a valid image file.</span>
            </p>
          </div>
        </div>

        <div>
          <label for="description" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Description</label>
          <textarea id="description" name="description" rows="3" placeholder="Condition, included accessories, batteries, etc." class="input-subtle text-xs"></textarea>
        </div>

        <?php if ($member_status === 'Verified'): ?>
          <button type="submit" name="add_equipment" class="btn-accent w-full py-3 text-xs uppercase tracking-wider">
            <span>Publish Equipment Listing</span>
          </button>
        <?php else: ?>
          <button type="button" disabled class="w-full py-3 px-4 bg-surface-subtle text-muted font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed border border-subtle">
            <span>Verification Required to Publish</span>
          </button>
        <?php endif; ?>

      </form>
    </div>

  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('equipment_image');
    const hiddenBase64 = document.getElementById('dropped_web_image_base64');
    const dragOverlay = document.getElementById('dropzone-dragover');
    const loadingOverlay = document.getElementById('dropzone-loading');
    const loadingText = document.getElementById('dropzone-loading-text');
    const previewCard = document.getElementById('upload-preview-card');
    const previewThumb = document.getElementById('image-preview-thumb');
    const previewName = document.getElementById('image-preview-name');
    const previewMeta = document.getElementById('image-preview-meta');
    const sourceBadge = document.getElementById('image-preview-source-badge');
    const removeBtn = document.getElementById('btn-remove-image');
    const changeBtn = document.getElementById('btn-change-image');
    const errorText = document.getElementById('upload-error-text');
    const errorMsg = document.getElementById('upload-error-msg');

    if (!dropzone || !fileInput) return;

    let currentObjectUrl = null;
    const maxSizeBytes = 5 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    function formatSize(bytes) {
      if (!bytes || bytes === 0) return '0 B';
      const k = 1024;
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function showError(msg) {
      if (errorText && errorMsg) {
        errorMsg.textContent = msg;
        errorText.classList.remove('hidden');
      }
    }

    function clearError() {
      if (errorText) {
        errorText.classList.add('hidden');
      }
    }

    function showLoading(msg) {
      clearError();
      if (loadingText) loadingText.textContent = msg || 'Fetching web image...';
      if (loadingOverlay) loadingOverlay.classList.remove('hidden');
    }

    function hideLoading() {
      if (loadingOverlay) loadingOverlay.classList.add('hidden');
    }

    function displayPreview(file, isWeb = false) {
      clearError();
      hideLoading();
      if (currentObjectUrl) {
        URL.revokeObjectURL(currentObjectUrl);
      }
      currentObjectUrl = URL.createObjectURL(file);
      previewThumb.src = currentObjectUrl;
      previewName.textContent = file.name;
      previewMeta.textContent = formatSize(file.size);

      if (sourceBadge) {
        if (isWeb) sourceBadge.classList.remove('hidden');
        else sourceBadge.classList.add('hidden');
      }

      dropzone.classList.add('hidden');
      previewCard.classList.remove('hidden');
    }

    function handleFile(file, isWeb = false) {
      if (!file) return;

      if (!allowedTypes.includes(file.type)) {
        showError('Please upload a valid JPG, PNG, or WebP image file.');
        resetInput();
        return;
      }

      if (file.size > maxSizeBytes) {
        showError('Image file is too large. Maximum allowed size is 5MB.');
        resetInput();
        return;
      }

      // Populate base64 fallback in case needed
      const reader = new FileReader();
      reader.onload = function(e) {
        if (hiddenBase64) hiddenBase64.value = e.target.result;
      };
      reader.readAsDataURL(file);

      displayPreview(file, isWeb);
    }

    function resetInput() {
      fileInput.value = '';
      if (hiddenBase64) hiddenBase64.value = '';
      if (currentObjectUrl) {
        URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = null;
      }
      previewThumb.src = '';
      previewCard.classList.add('hidden');
      dropzone.classList.remove('hidden');
      hideLoading();
    }

    // Helper: Convert any image Blob (e.g. GIF, AVIF, BMP) to JPEG via Canvas
    function convertBlobToJpeg(blob) {
      return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(blob);
        img.onload = function() {
          URL.revokeObjectURL(url);
          try {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth || img.width || 400;
            canvas.height = img.naturalHeight || img.height || 400;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            canvas.toBlob(function(jpegBlob) {
              resolve(jpegBlob || blob);
            }, 'image/jpeg', 0.92);
          } catch (_) {
            resolve(blob);
          }
        };
        img.onerror = function() {
          URL.revokeObjectURL(url);
          resolve(blob);
        };
        img.src = url;
      });
    }

    // Smart extractor for dragged web image URL
    function extractImageUrl(dt) {
      if (!dt) return null;
      const candidates = [];

      // 1. Check text/html for <img> tag or Google images redirect
      const html = dt.getData('text/html');
      if (html) {
        try {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const imgs = doc.querySelectorAll('img');
          for (const img of imgs) {
            if (img.src) candidates.push(img.src);
            if (img.dataset && img.dataset.src) candidates.push(img.dataset.src);
          }
          const links = doc.querySelectorAll('a');
          for (const a of links) {
            if (a.href) {
              const gMatch = a.href.match(/[?&]imgurl=([^&]+)/i);
              if (gMatch) candidates.unshift(decodeURIComponent(gMatch[1]));
              else if (/\.(jpe?g|png|webp|gif)($|\?)/i.test(a.href)) candidates.push(a.href);
            }
          }
        } catch (_) {
          const match = html.match(/<img[^>]+src=["']([^"']+)["']/i);
          if (match) candidates.push(match[1]);
        }
      }

      // 2. Check text/uri-list
      const uriList = dt.getData('text/uri-list');
      if (uriList) {
        const lines = uriList.split(/\r?\n/).map(l => l.trim()).filter(l => l && !l.startsWith('#'));
        for (const line of lines) {
          const gMatch = line.match(/[?&]imgurl=([^&]+)/i);
          if (gMatch) candidates.unshift(decodeURIComponent(gMatch[1]));
          else candidates.push(line);
        }
      }

      // 3. Check text/plain
      const text = dt.getData('text/plain');
      if (text) {
        const trimmed = text.trim();
        if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:image/')) {
          const gMatch = trimmed.match(/[?&]imgurl=([^&]+)/i);
          if (gMatch) candidates.unshift(decodeURIComponent(gMatch[1]));
          else candidates.push(trimmed);
        }
      }

      for (const url of candidates) {
        if (url && (url.startsWith('data:image/') || url.startsWith('http://') || url.startsWith('https://') || url.startsWith('blob:'))) {
          return url;
        }
      }
      return null;
    }

    async function handleWebImageUrl(imageUrl) {
      showLoading('Fetching web image...');
      try {
        let blob = null;

        if (imageUrl.startsWith('data:image/')) {
          const res = await fetch(imageUrl);
          blob = await res.blob();
        } else if (imageUrl.startsWith('blob:')) {
          const res = await fetch(imageUrl);
          blob = await res.blob();
        } else {
          // Attempt direct fetch first (if CORS allowed by host)
          try {
            const resp = await fetch(imageUrl, { mode: 'cors' });
            if (resp.ok) {
              const ct = resp.headers.get('content-type') || '';
              if (ct.startsWith('image/')) {
                blob = await resp.blob();
              }
            }
          } catch (_) {
            // Direct fetch blocked by CORS, proceed to local proxy
          }

          if (!blob) {
            // Fetch via local proxy endpoint
            const proxyUrl = '../api/fetch_web_image.php?url=' + encodeURIComponent(imageUrl);
            const resp = await fetch(proxyUrl);
            if (!resp.ok) {
              let errText = 'Failed to fetch image from web';
              try {
                const errJson = await resp.json();
                if (errJson && errJson.error) errText = errJson.error;
              } catch (_) {}
              throw new Error(errText);
            }
            blob = await resp.blob();
          }
        }

        if (!blob) {
          throw new Error('Could not retrieve image data from the provided URL.');
        }

        if (blob.size > maxSizeBytes) {
          throw new Error('Image file is too large. Maximum allowed size is 5MB.');
        }

        // Convert non-standard image types to JPEG if needed
        if (!allowedTypes.includes(blob.type)) {
          showLoading('Optimizing image format...');
          blob = await convertBlobToJpeg(blob);
        }

        // Determine appropriate filename
        let fileName = 'web_image.jpg';
        try {
          const u = new URL(imageUrl);
          const p = u.pathname.split('/').pop();
          if (p && /\.(jpe?g|png|webp)$/i.test(p)) {
            fileName = decodeURIComponent(p);
          } else {
            const ext = blob.type === 'image/png' ? 'png' : (blob.type === 'image/webp' ? 'webp' : 'jpg');
            fileName = 'web_image_' + Math.floor(Date.now() / 1000) + '.' + ext;
          }
        } catch (_) {
          const ext = blob.type === 'image/png' ? 'png' : (blob.type === 'image/webp' ? 'webp' : 'jpg');
          fileName = 'web_image_' + Math.floor(Date.now() / 1000) + '.' + ext;
        }

        const file = new File([blob], fileName, { type: blob.type });

        // Update file input via DataTransfer
        try {
          const dt = new DataTransfer();
          dt.items.add(file);
          fileInput.files = dt.files;
        } catch (_) {
          // If browser restricts DataTransfer assignment, fallback input is handled
        }

        handleFile(file, true);
      } catch (err) {
        hideLoading();
        showError(err.message || 'Failed to load web image. Please try downloading it first or use another image.');
      }
    }

    // Attach Drag and Drop handlers
    let dragCounter = 0;

    function onDragEnter(e) {
      e.preventDefault();
      e.stopPropagation();
      dragCounter++;
      if (dragOverlay) dragOverlay.classList.remove('hidden');
      dropzone.classList.add('border-accent', 'bg-surface');
    }

    function onDragOver(e) {
      e.preventDefault();
      e.stopPropagation();
      e.dataTransfer.dropEffect = 'copy';
      if (dragOverlay) dragOverlay.classList.remove('hidden');
      dropzone.classList.add('border-accent', 'bg-surface');
    }

    function onDragLeave(e) {
      e.preventDefault();
      e.stopPropagation();
      dragCounter--;
      if (dragCounter <= 0) {
        dragCounter = 0;
        if (dragOverlay) dragOverlay.classList.add('hidden');
        dropzone.classList.remove('border-accent', 'bg-surface');
      }
    }

    async function onDrop(e) {
      e.preventDefault();
      e.stopPropagation();
      dragCounter = 0;
      if (dragOverlay) dragOverlay.classList.add('hidden');
      dropzone.classList.remove('border-accent', 'bg-surface');

      const dt = e.dataTransfer;
      if (!dt) return;

      // 1. Check for local PC file first
      if (dt.files && dt.files.length > 0) {
        fileInput.files = dt.files;
        handleFile(dt.files[0], false);
        return;
      }

      // 2. Check for web image URL
      const imageUrl = extractImageUrl(dt);
      if (imageUrl) {
        await handleWebImageUrl(imageUrl);
        return;
      }

      showError('No valid image file or web image detected in the drop.');
    }

    ['dragenter'].forEach(evt => {
      dropzone.addEventListener(evt, onDragEnter);
      fileInput.addEventListener(evt, onDragEnter);
    });

    ['dragover'].forEach(evt => {
      dropzone.addEventListener(evt, onDragOver);
      fileInput.addEventListener(evt, onDragOver);
    });

    ['dragleave', 'dragend'].forEach(evt => {
      dropzone.addEventListener(evt, onDragLeave);
      fileInput.addEventListener(evt, onDragLeave);
    });

    dropzone.addEventListener('drop', onDrop);
    fileInput.addEventListener('drop', onDrop);

    // Standard file selection via click
    fileInput.addEventListener('change', function() {
      if (this.files && this.files.length > 0) {
        handleFile(this.files[0], false);
      }
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        resetInput();
        clearError();
      });
    }

    if (changeBtn) {
      changeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.click();
      });
    }
  });
  </script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
