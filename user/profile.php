<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'member') {
    header("Location: ../auth/login.php?msg=login_required");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');
require_once(__DIR__ . '/../includes/avatar_presets.php');

$user_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
$success_msg = "";
$error_msg = "";

// Hardwire data pipeline directly to server's root uploads/avatars sector using dynamic __DIR__ matrix
$avatar_dir = dirname(__DIR__) . '/uploads/avatars';
if (!is_dir($avatar_dir)) {
    @mkdir($avatar_dir, 0777, true);
}
@chmod($avatar_dir, 0777);

// Auto-discover avatar from local file system root sector (zero SQL mutations)
if ($user_id > 0) {
    $avatar_matches = glob($avatar_dir . '/avatar_' . $user_id . '.*');
    if (!empty($avatar_matches)) {
        $_SESSION['avatar'] = 'uploads/avatars/' . basename($avatar_matches[0]);
        if (empty($_SESSION['avatar_v'])) {
            $_SESSION['avatar_v'] = filemtime($avatar_matches[0]);
        }
    }
}

// Handle Form Submissions via Native PHP Core Architecture (Zero AJAX / Zero Virtual Memory Buffers)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Direct Native Multipart Binary Ingestion
    if ($action === 'update_avatar') {
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp   = $_FILES['avatar_file']['tmp_name'];
            $file_name  = $_FILES['avatar_file']['name'];
            $file_size  = $_FILES['avatar_file']['size'];
            $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if ($file_ext === 'jpeg') $file_ext = 'jpg';

            $allowed_exts = ['jpg', 'png', 'webp', 'gif', 'svg'];

            if (!in_array($file_ext, $allowed_exts)) {
                $error_msg = "Invalid image format. Allowed: PNG, JPG, WEBP, GIF, SVG.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error_msg = "Image exceeds 5MB limit.";
            } else {
                // Purge older avatar files for this user
                foreach (glob($avatar_dir . '/avatar_' . $user_id . '.*') as $old_file) {
                    @unlink($old_file);
                }

                $target_filename = 'avatar_' . $user_id . '.' . $file_ext;
                $target_path     = $avatar_dir . '/' . $target_filename;

                // Native hardwire binary write directly to uploads/avatars/ root sector
                $written = @move_uploaded_file($file_tmp, $target_path);
                if (!$written) {
                    $binary = @file_get_contents($file_tmp);
                    if ($binary !== false) {
                        $written = (@file_put_contents($target_path, $binary, LOCK_EX) !== false);
                    }
                }
                if (!$written) {
                    $written = @copy($file_tmp, $target_path);
                }

                if ($written) {
                    @chmod($target_path, 0666);
                    $cache_token = time();
                    $_SESSION['avatar']   = 'uploads/avatars/' . $target_filename;
                    $_SESSION['avatar_v'] = $cache_token;
                    unset($_SESSION['avatar_preset']);
                    $success_msg = "Avatar binary hardwired directly to uploads/avatars/" . $target_filename . " with zero SQL mutations.";
                } else {
                    $error_msg = "Filesystem write failed on path: " . $target_path;
                }
            }
        } elseif (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $err_code = $_FILES['avatar_file']['error'];
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'Image exceeds upload_max_filesize in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'Image exceeds form MAX_FILE_SIZE.',
                UPLOAD_ERR_PARTIAL    => 'Image was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp upload folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'File upload stopped by PHP extension.'
            ];
            $error_msg = $upload_errors[$err_code] ?? ('Upload failed with error code ' . $err_code);
        }
    }

    // Directive 3: SVG Binary Transmuter Execution
    if ($action === 'transmute_preset' || isset($_POST['preset_archetype'])) {
        $preset_key = trim($_POST['preset_archetype'] ?? $_POST['preset_key'] ?? '');
        $res = transmute_svg_preset($preset_key, $user_id, $avatar_dir);
        if ($res['success']) {
            $cache_token = time();
            $_SESSION['avatar']        = $res['rel_path'];
            $_SESSION['avatar_v']      = $cache_token;
            $_SESSION['avatar_preset'] = $res['name'];
            $success_msg = "SVG Binary Transmuter compiled physical archetype [" . htmlspecialchars($res['name']) . "] directly to " . $res['rel_path'] . " with zero schema mutations.";
        } else {
            $error_msg = $res['error'] ?? "Failed to compile SVG preset archetype.";
        }
    }

    // Action 1: Modify Name & Identity
    if ($action === 'update_identity') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $campus_address = trim($_POST['campus_address'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Other');

        if (empty($first_name) || empty($last_name)) {
            $error_msg = "First Name and Last Name are required.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE member 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        phone_number = :phone_number, 
                        campus_address = :campus_address, 
                        gender = :gender 
                    WHERE member_id = :id
                ");
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone_number' => $phone_number,
                    'campus_address' => $campus_address,
                    'gender' => in_array($gender, ['Male', 'Female', 'Other']) ? $gender : 'Other',
                    'id' => $user_id
                ]);

                // Immediately synchronize session identity
                $_SESSION['name'] = trim($first_name . ' ' . $last_name);
                $success_msg = "Identity Matrix successfully synchronized. Display name updated to " . htmlspecialchars($_SESSION['name']) . ".";
            } catch (Exception $e) {
                $error_msg = "Failed to update identity matrix: " . $e->getMessage();
            }
        }
    }
}

// Fetch fresh member record
try {
    $stmt = $pdo->prepare("SELECT * FROM member WHERE member_id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $member = $stmt->fetch();
} catch (Exception $e) {
    $member = false;
}

if (!$member) {
    header("Location: ../auth/logout.php");
    exit();
}

$first_name = $member['first_name'] ?? '';
$last_name = $member['last_name'] ?? '';
$full_name = trim($first_name . ' ' . $last_name);
$username = $member['username'] ?? '';
$student_id = $member['student_id'] ?? '';
$email = $member['university_email'] ?? '';
$phone = $member['phone_number'] ?? '';
$address = $member['campus_address'] ?? '';
$gender = $member['gender'] ?? 'Other';
$status = $member['status'] ?? 'Pending';
$balance = number_format((float)($member['account_balance'] ?? 0), 2);
$created_at = $member['created_at'] ?? '';

$base_path = '..';
$page_title = 'Profile Singularity - ' . htmlspecialchars($full_name) . ' | Rentora Hub';
$active_nav = 'dashboard';
$dark_sector = true;
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

<main class="flex-1 relative pb-16">
  <!-- Quantum Dark-Matter Banner (Locked to Night Blue #151B54 — Zero Gradient Drag) -->
  <div class="relative bg-[#151B54] text-white py-10 px-4 sm:px-6 lg:px-8 border-b border-white/10 overflow-hidden">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
      <div class="flex items-center gap-4">
        <!-- Interactive Singularity Avatar -->
        <div class="relative group" id="banner-avatar-container">
          <?php
            $has_av = !empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../' . $_SESSION['avatar']);
            $av_src = $has_av ? ('../' . $_SESSION['avatar'] . '?v=' . ($_SESSION['avatar_v'] ?? '1')) : null;
          ?>
          <img id="banner-avatar-img" src="<?php echo htmlspecialchars($av_src ?? ''); ?>" alt="Avatar"
               class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-xl ring-2 ring-white/20 <?php echo $has_av ? '' : 'hidden'; ?>">
          <div id="banner-avatar-fallback" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#151B54] text-white flex items-center justify-center font-black text-2xl shadow-xl ring-2 ring-white/30 border border-white/20 <?php echo $has_av ? 'hidden' : ''; ?>">
            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
          </div>
          <?php if ($status === 'Verified'): ?>
            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-full bg-emerald-500 text-white font-bold text-[10px] ring-2 ring-[#151B54] shadow-sm flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
              Verified
            </span>
          <?php elseif ($status === 'Rejected'): ?>
            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-full bg-rose-500 text-white font-bold text-[10px] ring-2 ring-[#151B54] shadow-sm">
              Rejected
            </span>
          <?php else: ?>
            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-full bg-amber-500 text-white font-bold text-[10px] ring-2 ring-[#151B54] shadow-sm">
              Pending
            </span>
          <?php endif; ?>
        </div>

        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white"><?php echo htmlspecialchars($full_name); ?></h1>
          </div>
          <p class="text-xs sm:text-sm text-slate-300 font-mono mt-0.5">@<?php echo htmlspecialchars($username); ?> &bull; ID: <?php echo htmlspecialchars($student_id ?: 'Not linked'); ?></p>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-white/10 text-white border border-white/10">
              <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              <?php echo htmlspecialchars($email); ?>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-white/10 text-white border border-white/10">
              <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
              <?php echo htmlspecialchars($address ?: 'Hazari Lane, PUC'); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Balance & Nav Singularity Shortcuts -->
      <div class="flex items-center gap-3">
        <a href="<?php echo $base_path; ?>/user/dashboard.php" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold border border-white/15 transition-all">
          &larr; Return to Dashboard
        </a>
        <a href="<?php echo $base_path; ?>/auth/change_password.php" class="px-4 py-2 rounded-xl bg-[#151B54] hover:bg-[#1E2570] text-white text-xs font-semibold border border-white/30 shadow-lg shadow-[#151B54]/40 transition-all">
          Security Settings &rarr;
        </a>
      </div>
    </div>
  </div>

  <!-- Messages Alert -->
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <?php if (!empty($success_msg)): ?>
      <div class="p-4 rounded-xl bg-[#151B54] border border-emerald-400/40 text-emerald-300 text-sm font-medium flex items-center gap-3 shadow-lg mb-6">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span><?php echo $success_msg; ?></span>
      </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
      <div class="p-4 rounded-xl bg-[#151B54] border border-rose-400/40 text-rose-300 text-sm font-medium flex items-center gap-3 shadow-lg mb-6">
        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span><?php echo htmlspecialchars($error_msg); ?></span>
      </div>
    <?php endif; ?>

    <!-- Main Grid Content: Responsive 3-Tier Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Left 2 Cols: Form Sections -->
      <div class="lg:col-span-2 space-y-8">
        
        <!-- Section 1: Name Modifications & Identity Matrix -->
        <section id="identity" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-slate-900">Name Modifications &amp; Identity Matrix</h2>
                <p class="text-xs text-slate-500">Update your official display name, contact phone, and campus location</p>
              </div>
            </div>
            <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
              Live Profile
            </span>
          </div>

          <form action="" method="POST" class="p-6 space-y-5">
            <input type="hidden" name="action" value="update_identity">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name *</label>
                <input type="text" id="first_name" name="first_name" required value="<?php echo htmlspecialchars($first_name); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm text-slate-900 transition-all font-medium">
              </div>
              <div>
                <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name *</label>
                <input type="text" id="last_name" name="last_name" required value="<?php echo htmlspecialchars($last_name); ?>" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm text-slate-900 transition-all font-medium">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="phone_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone); ?>" placeholder="01XXXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm text-slate-900 transition-all font-mono">
              </div>
              <div>
                <label for="gender" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gender Identification</label>
                <select id="gender" name="gender" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm text-slate-900 transition-all font-medium">
                  <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                  <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                  <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other / Prefer not to say</option>
                </select>
              </div>
            </div>

            <div>
              <label for="campus_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Campus Spot / Delivery Address</label>
              <input type="text" id="campus_address" name="campus_address" value="<?php echo htmlspecialchars($address); ?>" placeholder="e.g. Hazari Lane Academic Building, Room 402" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 text-sm text-slate-900 transition-all font-medium">
              <p class="text-[11px] text-slate-400 mt-1">Used as your default exchange &amp; handover meeting reference point.</p>
            </div>

            <div class="pt-2 flex items-center justify-between">
              <p class="text-xs text-slate-400 italic">Database Schema Preservation Protocol active: zero table migrations required.</p>
              <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition-all hover:scale-[1.02]">
                Save Identity Changes
              </button>
            </div>
          </form>
        </section>

        <!-- Section 2: Avatar Uploads & Hologram Studio (Strict Photonic Chromatic Lock) -->
        <section id="avatar" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden scroll-mt-24">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 flex items-center justify-center text-white">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-white">Avatar Uploads &amp; Hologram Studio</h2>
                <p class="text-xs text-slate-300">Zero-latency binary pipeline hardwired to server root (<code class="font-mono text-white bg-white/10 px-1 py-0.5 rounded">uploads/avatars/</code>)</p>
              </div>
            </div>
            <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-white/10 text-white border border-white/20 flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              Hardwired Pipeline
            </span>
          </div>

          <div class="p-6 space-y-6">
            <!-- Direct Stream File Upload Dropzone -->
            <form id="avatar-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="action" value="update_avatar">

              <label class="block text-xs font-bold text-white uppercase tracking-wider">Holographic Projection &amp; Binary Stream Portal</label>
              
              <div class="flex flex-col sm:flex-row items-center gap-5">
                <!-- Studio Hologram Frame -->
                <div class="relative group shrink-0">
                  <div class="w-24 h-24 rounded-2xl bg-[#0A0E2E] border-2 border-white/30 p-1 shadow-xl flex items-center justify-center overflow-hidden">
                    <img id="studio-avatar-img" src="<?php echo htmlspecialchars($av_src ?? ''); ?>" alt="Avatar Preview"
                         class="w-full h-full rounded-xl object-cover <?php echo $has_av ? '' : 'hidden'; ?>">
                    <div id="studio-avatar-fallback" class="w-full h-full rounded-xl bg-[#151B54] text-white flex items-center justify-center font-black text-2xl border border-white/20 <?php echo $has_av ? 'hidden' : ''; ?>">
                      <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                    </div>
                  </div>
                  <span class="absolute -bottom-1.5 -right-1.5 px-1.5 py-0.5 rounded-full bg-[#151B54] text-white font-mono text-[9px] font-bold tracking-tight border border-white/30 shadow">
                    STREAM
                  </span>
                </div>

                <!-- Hardwired Dropzone Pipeline -->
                <div id="avatar-dropzone" class="flex-1 w-full border-2 border-dashed border-white/30 hover:border-white/80 bg-[#0A0E2E]/60 hover:bg-[#0A0E2E]/90 rounded-2xl p-5 text-center transition-all cursor-pointer group">
                  <input type="file" id="avatar-file-input" name="avatar_file" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" class="sr-only">
                  
                  <div class="flex flex-col items-center justify-center space-y-1.5 pointer-events-none">
                    <div class="w-10 h-10 rounded-xl bg-white/10 text-white flex items-center justify-center group-hover:scale-110 transition-transform">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <p class="text-xs font-bold text-white">
                      <span class="text-[#EFF3FF] underline underline-offset-2">Click to transmit binary</span> or drag &amp; drop image stream
                    </p>
                    <p class="text-[11px] text-slate-300 font-mono">Accepts PNG, JPG, WEBP, SVG &bull; Max 5MB &bull; Latency: 0ms</p>
                  </div>
                </div>
              </div>

              <!-- Stream Live Feedback Bar -->
              <div id="stream-status-bar" class="p-3 rounded-xl bg-[#0A0E2E]/80 border border-white/15 text-xs font-mono flex items-center justify-between text-white">
                <div class="flex items-center gap-2">
                  <span id="stream-pulse" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                  <span id="stream-status-text" class="text-slate-200">Hardwired pipeline ready: /Rentora/uploads/avatars/</span>
                </div>
                <span id="stream-meta" class="text-[11px] text-[#EFF3FF] font-bold">Direct I/O Mode</span>
              </div>
            </form>

            <!-- Standardized Preset Avatar Module (Directive 2 & 3) -->
            <form id="preset-transmute-form" action="" method="POST" class="space-y-4 pt-4 border-t border-white/10">
              <input type="hidden" name="action" value="transmute_preset">
              
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>Standardized Preset Avatar Archetypes</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30">Casual Grid</span>
                  </h3>
                  <p class="text-[11px] text-slate-300 font-mono mt-0.5">Selecting an archetype triggers the SVG Binary Transmuter to compile a physical vector file.</p>
                </div>
                <span class="self-start sm:self-auto text-[10px] font-mono text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full font-bold flex items-center gap-1.5 shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                  Physical SVG Engine
                </span>
              </div>

              <!-- High-Velocity Responsive Grid of Classic Archetypes -->
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <?php
                $presets = get_casual_avatar_presets();
                $current_preset_name = $_SESSION['avatar_preset'] ?? '';
                foreach ($presets as $pkey => $p):
                  $is_active_preset = ($current_preset_name === $p['name']);
                ?>
                  <button type="submit"
                          name="preset_archetype"
                          value="<?php echo htmlspecialchars($pkey); ?>"
                          data-preset-key="<?php echo htmlspecialchars($pkey); ?>"
                          data-preset-name="<?php echo htmlspecialchars($p['name']); ?>"
                          class="preset-archetype-btn flex flex-col items-center p-3.5 rounded-xl border <?php echo $is_active_preset ? 'border-sky-400 bg-[#12184A] ring-2 ring-sky-400/50' : 'border-white/15 bg-[#0A0E2E]/80 hover:bg-[#0A0E2E] hover:border-white/50'; ?> cursor-pointer hover:scale-105 active:scale-95 transition-all text-center group relative overflow-hidden focus:outline-none focus:ring-2 focus:ring-sky-400">
                    
                    <?php if ($is_active_preset): ?>
                      <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-sky-400 ring-2 ring-[#0A0E2E]" title="Active Archetype"></span>
                    <?php endif; ?>

                    <div class="w-14 h-14 rounded-full overflow-hidden p-0.5 bg-white/5 border border-white/20 mb-2 shadow-inner group-hover:border-white/60 group-hover:scale-110 transition-all flex items-center justify-center">
                      <?php echo $p['svg']; ?>
                    </div>
                    
                    <span class="text-xs font-bold text-white tracking-wide"><?php echo htmlspecialchars($p['name']); ?></span>
                    <span class="text-[10px] text-slate-300 font-mono mt-0.5"><?php echo htmlspecialchars($p['tagline']); ?></span>
                    
                    <span class="mt-2.5 px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-white/10 text-sky-300 border border-white/15 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                      Transmute SVG
                    </span>
                  </button>
                <?php endforeach; ?>
              </div>
            </form>

            <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-300">
              <span class="italic">Direct local I/O write &bull; Writes physical vector directly to uploads/avatars/</span>
              <span class="font-mono text-emerald-400 font-bold">Zero Database Schema Mutation</span>
            </div>
          </div>
        </section>

      </div>

      <!-- Right Col: Profile Singularity Card & Security Status -->
      <div class="space-y-6">
        
        <!-- Academic Verification Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider font-mono">Academic Beacon</span>
            <?php if ($status === 'Verified'): ?>
              <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Active Member
              </span>
            <?php elseif ($status === 'Rejected'): ?>
              <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                Action Required
              </span>
            <?php else: ?>
              <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Pending Approval
              </span>
            <?php endif; ?>
          </div>

          <div class="space-y-3 text-xs">
            <div class="flex justify-between py-1.5 border-b border-slate-100">
              <span class="text-slate-500">Student ID</span>
              <span class="font-mono font-bold text-slate-900"><?php echo htmlspecialchars($student_id ?: 'Unverified'); ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-slate-100">
              <span class="text-slate-500">Campus Email</span>
              <span class="font-mono font-medium text-slate-900 truncate max-w-[160px]"><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-slate-100">
              <span class="text-slate-500">Account Balance</span>
              <span class="font-mono font-bold text-emerald-600">৳<?php echo $balance; ?> BDT</span>
            </div>
            <div class="flex justify-between py-1.5">
              <span class="text-slate-500">Member Since</span>
              <span class="font-mono text-slate-600"><?php echo $created_at ? date('M Y', strtotime($created_at)) : '2026'; ?></span>
            </div>
          </div>
        </div>

        <!-- Security Gateway Card (Solid Night Blue #151B54 — No Gradients) -->
        <div id="security" class="bg-[#151B54] rounded-2xl p-6 text-white border border-white/15 shadow-xl scroll-mt-24">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-white/10 border border-white/20 flex items-center justify-center text-white">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Encryption &amp; Security</h3>
              <p class="text-[11px] text-slate-300 font-mono">Quantum hash password protocol</p>
            </div>
          </div>
          <p class="text-xs text-slate-200 leading-relaxed mb-4">
            Protect your equipment listings and campus exchange agreements by maintaining a high-entropy password.
          </p>
          <a href="<?php echo $base_path; ?>/auth/change_password.php" class="block w-full text-center py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition-all">
            Update Security Credentials &rarr;
          </a>
        </div>

      </div>

    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const fileInput      = document.getElementById('avatar-file-input');
  const dropzone       = document.getElementById('avatar-dropzone');
  const avatarForm     = document.getElementById('avatar-form');
  const studioImg      = document.getElementById('studio-avatar-img');
  const studioFallback = document.getElementById('studio-avatar-fallback');
  const bannerImg      = document.getElementById('banner-avatar-img');
  const bannerFallback = document.getElementById('banner-avatar-fallback');
  const statusText     = document.getElementById('stream-status-text');
  const statusPulse    = document.getElementById('stream-pulse');
  const metaText       = document.getElementById('stream-meta');

  if (!fileInput || !dropzone || !avatarForm) return;

  dropzone.addEventListener('click', function(e) {
    if (e.target !== fileInput) {
      fileInput.click();
    }
  });

  ['dragenter', 'dragover'].forEach(name => {
    dropzone.addEventListener(name, function(e) {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.add('border-white', 'bg-[#12184A]');
    });
  });

  ['dragleave', 'drop'].forEach(name => {
    dropzone.addEventListener(name, function(e) {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.remove('border-white', 'bg-[#12184A]');
    });
  });

  dropzone.addEventListener('drop', function(e) {
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      tunnelBinary(files[0]);
    }
  });

  fileInput.addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
      tunnelBinary(e.target.files[0]);
    }
  });

  /**
   * Directive 2: Zero-Gravity Binary Injection Pipeline
   * 1. Instant local DOM photonic preview (zero latency).
   * 2. Quantum-tunnel binary payload directly into native PHP core architecture via multipart form POST.
   * 3. Eradicates all asynchronous AJAX streams, fetch(), and virtual memory buffers.
   */
  function tunnelBinary(file) {
    if (!file.type.startsWith('image/')) {
      alert('Selected payload is not a valid image format.');
      return;
    }

    // Instant local DOM preview before transmission
    const localUrl = URL.createObjectURL(file);
    if (studioImg) {
      studioImg.src = localUrl;
      studioImg.classList.remove('hidden');
    }
    if (studioFallback) studioFallback.classList.add('hidden');
    if (bannerImg) {
      bannerImg.src = localUrl;
      bannerImg.classList.remove('hidden');
    }
    if (bannerFallback) bannerFallback.classList.add('hidden');

    // Update status indicators
    if (statusText) statusText.innerHTML = '<span class="text-emerald-300 font-bold">⚡ Binary injection initiated — transmitting to PHP core...</span>';
    if (statusPulse) statusPulse.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
    if (metaText) metaText.textContent = 'Hardwiring Stream...';

    // If file was dropped, inject into fileInput for native POST
    if (file !== fileInput.files[0]) {
      const dt = new DataTransfer();
      dt.items.add(file);
      fileInput.files = dt.files;
    }

    // The microsecond a binary image crosses the event horizon (onchange/drop),
    // natively submit the HTML form directly to PHP core reactor:
    avatarForm.submit();
  }

  // Directive 3: Preset Archetype Selection & SVG Transmutation Handler
  const presetButtons = document.querySelectorAll('.preset-archetype-btn');
  presetButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const presetName = this.getAttribute('data-preset-name');
      const svgElement = this.querySelector('svg');

      if (svgElement) {
        // Instant 0ms local vector preview before transmission
        const svgXml = new XMLSerializer().serializeToString(svgElement);
        const svgDataUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgXml);

        if (studioImg) {
          studioImg.src = svgDataUrl;
          studioImg.classList.remove('hidden');
        }
        if (studioFallback) studioFallback.classList.add('hidden');
        if (bannerImg) {
          bannerImg.src = svgDataUrl;
          bannerImg.classList.remove('hidden');
        }
        if (bannerFallback) bannerFallback.classList.add('hidden');

        if (statusText) statusText.innerHTML = '<span class="text-emerald-300 font-bold">⚡ SVG Binary Transmuter active — compiling ' + presetName + ' to disk...</span>';
        if (statusPulse) statusPulse.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-ping';
        if (metaText) metaText.textContent = 'Transmuting SVG...';
      }
    });
  });
});
</script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
