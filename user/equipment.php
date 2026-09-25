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

if (isset($_GET['msg']) && $_GET['msg'] === 'item_added') {
    $success = "Equipment listing added successfully!";
}
if (isset($_GET['error'])) {
    $errCode = $_GET['error'];
    if ($errCode === 'missing_fields') {
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
    require_once(__DIR__ . '/add_item.php');
    exit();
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

      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
          <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span class="font-medium"><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form action="add_item.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        
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
            <option value="Hazari Lane">Hazari Lane</option>
            <option value="Wasa">Wasa</option>
            <option value="GEC Campus">GEC Campus</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
            <span>Equipment Image *</span>
            <span class="text-[11px] font-normal text-slate-400">JPG, PNG, WebP up to 5MB</span>
          </label>

          <!-- Upload Container -->
          <div id="equipment-upload-wrapper" class="relative">
            
            <!-- Empty Dropzone State -->
            <div id="upload-dropzone" class="relative group border-2 border-dashed border-slate-300 hover:border-primary-500 hover:bg-slate-50/80 rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 bg-slate-50/50 overflow-hidden">
              <!-- Native file input covers the entire dropzone with opacity-0 -->
              <input type="file" id="equipment_image" name="equipment_image" accept="image/jpeg,image/png,image/webp" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" title="Choose equipment image">
              
              <div class="pointer-events-none">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center mx-auto mb-2.5 group-hover:scale-105 group-hover:bg-blue-100/70 transition-all shadow-sm">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <p class="text-xs font-semibold text-slate-700">
                  <span class="text-primary-600 font-bold group-hover:underline">Click to upload</span> or drag and drop image
                </p>
                <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, or WebP (max. 5MB)</p>
              </div>
            </div>

            <!-- AI Chat-Style Attachment Preview Card (Hidden until file selected) -->
            <div id="upload-preview-card" class="hidden p-3 bg-white border border-slate-200/90 rounded-2xl shadow-sm hover:shadow transition-shadow">
              <div class="flex items-center gap-3.5">
                <!-- Thumbnail Preview (56x56px object-cover) -->
                <div class="relative w-14 h-14 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0 shadow-inner">
                  <img id="image-preview-thumb" src="" alt="Equipment preview" class="w-full h-full object-cover">
                </div>

                <!-- Attachment Details -->
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2">
                    <p id="image-preview-name" class="text-xs font-bold text-navy-900 truncate max-w-[200px] sm:max-w-xs"></p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">
                      Attached
                    </span>
                  </div>
                  <p id="image-preview-meta" class="text-[11px] font-medium text-slate-500 mt-0.5"></p>
                  <button type="button" id="btn-change-image" class="text-[11px] text-primary-600 hover:text-primary-700 font-semibold mt-1 inline-flex items-center gap-1 hover:underline">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span>Change photo</span>
                  </button>
                </div>

                <!-- Remove 'x' Button in corner -->
                <button type="button" id="btn-remove-image" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-400 hover:border hover:border-red-200 flex items-center justify-center transition-all shrink-0 self-start" title="Remove attachment">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Client-side error banner -->
            <p id="upload-error-text" class="hidden text-xs text-red-600 font-medium mt-1.5 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <span id="upload-error-msg">Please upload a valid image file.</span>
            </p>

          </div>
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

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('upload-dropzone');
    const fileInput = document.getElementById('equipment_image');
    const previewCard = document.getElementById('upload-preview-card');
    const previewThumb = document.getElementById('image-preview-thumb');
    const previewName = document.getElementById('image-preview-name');
    const previewMeta = document.getElementById('image-preview-meta');
    const removeBtn = document.getElementById('btn-remove-image');
    const changeBtn = document.getElementById('btn-change-image');
    const errorText = document.getElementById('upload-error-text');
    const errorMsg = document.getElementById('upload-error-msg');

    if (!dropzone || !fileInput) return;

    let currentObjectUrl = null;
    const maxSizeBytes = 5 * 1024 * 1024; // 5MB
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
      dropzone.classList.add('border-red-400', 'bg-red-50/40');
    }

    function clearError() {
      if (errorText) {
        errorText.classList.add('hidden');
      }
      dropzone.classList.remove('border-red-400', 'bg-red-50/40');
    }

    function displayPreview(file) {
      clearError();
      if (currentObjectUrl) {
        URL.revokeObjectURL(currentObjectUrl);
      }
      currentObjectUrl = URL.createObjectURL(file);
      previewThumb.src = currentObjectUrl;
      previewName.textContent = file.name;
      previewMeta.textContent = file.name + ' • ' + formatSize(file.size);

      dropzone.classList.add('hidden');
      previewCard.classList.remove('hidden');
    }

    function handleFile(file) {
      if (!file) return;

      if (!allowedTypes.includes(file.type)) {
        showError('Please upload a valid JPG, PNG, or WebP image file.');
        fileInput.value = '';
        return;
      }

      if (file.size > maxSizeBytes) {
        showError('Image file is too large. Maximum allowed size is 5MB.');
        fileInput.value = '';
        return;
      }

      displayPreview(file);
    }

    // Handle native file input change (file picker)
    fileInput.addEventListener('change', function() {
      if (this.files && this.files.length > 0) {
        handleFile(this.files[0]);
      }
    });

    // Handle drag-and-drop hover states
    ['dragenter', 'dragover'].forEach(function(eventName) {
      dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('border-primary-500', 'bg-blue-50/60', 'ring-2', 'ring-primary-500/20');
      });
    });

    ['dragleave', 'dragend'].forEach(function(eventName) {
      dropzone.addEventListener(eventName, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('border-primary-500', 'bg-blue-50/60', 'ring-2', 'ring-primary-500/20');
      });
    });

    dropzone.addEventListener('drop', function(e) {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.remove('border-primary-500', 'bg-blue-50/60', 'ring-2', 'ring-primary-500/20');

      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const droppedFile = e.dataTransfer.files[0];
        try {
          const dt = new DataTransfer();
          dt.items.add(droppedFile);
          fileInput.files = dt.files;
        } catch (err) {
          // Fallback if DataTransfer not supported
        }
        handleFile(droppedFile);
      }
    });

    // Remove attachment button
    if (removeBtn) {
      removeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.value = '';
        if (currentObjectUrl) {
          URL.revokeObjectURL(currentObjectUrl);
          currentObjectUrl = null;
        }
        previewThumb.src = '';
        previewCard.classList.add('hidden');
        dropzone.classList.remove('hidden');
        clearError();
      });
    }

    // Change photo button
    if (changeBtn) {
      changeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.click();
      });
    }
  });
  </script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
