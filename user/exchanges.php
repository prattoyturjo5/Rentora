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

if (isset($_GET['msg']) && $_GET['msg'] === 'proposal_sent') {
    $success = "Exchange proposal submitted to equipment owner successfully!";
}

// Handle Exchange Status Update (Accept / Decline / Complete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $exchange_id = (int)$_GET['id'];

    if ($action === 'accept' && $member_status !== 'Verified') {
        $error = ($member_status === 'Rejected') ? "Your account was rejected, contact an admin." : "Your account is pending verification. You cannot accept swap agreements until verified.";
    } else {
        try {
            if ($action === 'accept') {
                $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Accepted' WHERE exchange_id = :id");
                $stmt->execute(['id' => $exchange_id]);
                $success = "Exchange agreement accepted! Coordinate with peer for equipment swap.";
            } elseif ($action === 'decline' || $action === 'reject') {
                $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Rejected' WHERE exchange_id = :id");
                $stmt->execute(['id' => $exchange_id]);
                $success = "Exchange proposal declined.";
            } elseif ($action === 'complete') {
                $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Completed' WHERE exchange_id = :id");
                $stmt->execute(['id' => $exchange_id]);
                $success = "Exchange marked as completed!";
            }
        } catch (PDOException $e) {
            $error = "Exchange update error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Handle Propose New Exchange
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['propose_exchange'])) {
    if ($member_status !== 'Verified') {
        $error = ($member_status === 'Rejected') ? "Your account was rejected, contact an admin." : "Your account is pending verification. You cannot propose exchanges until verified.";
    } else {
        $equipment_a_id = (int)($_POST['equipment_a_id'] ?? $_POST['offered_equipment_id'] ?? 0);
        $equipment_b_id = (int)($_POST['equipment_b_id'] ?? $_POST['requested_equipment_id'] ?? 0);

        if ($equipment_a_id <= 0 || $equipment_b_id <= 0) {
            $error = "Please select both your offered equipment and the item you wish to swap for.";
        } else {
            try {
                // Find owner (lender_b_id) of requested equipment
                $ownerStmt = $pdo->prepare("SELECT owner_id FROM equipment WHERE equipment_id = :id LIMIT 1");
                $ownerStmt->execute(['id' => $equipment_b_id]);
                $lender_b_id = $ownerStmt->fetchColumn();

                if ($lender_b_id) {
                    $lender_a_id = $user_id;
                    $ins = $pdo->prepare("
                        INSERT INTO exchange_agreement (lender_a_id, lender_b_id, equipment_a_id, equipment_b_id, status)
                        VALUES (:lender_a_id, :lender_b_id, :equipment_a_id, :equipment_b_id, 'Pending')
                    ");
                    $ins->execute([
                        'lender_a_id'    => $lender_a_id,
                        'lender_b_id'    => $lender_b_id,
                        'equipment_a_id' => $equipment_a_id,
                        'equipment_b_id' => $equipment_b_id
                    ]);
                    $success = "Exchange proposal submitted to owner!";
                } else {
                    $error = "Requested equipment not found.";
                }
            } catch (PDOException $e) {
                $error = "Database notice: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Fetch member's own equipment (for the dropdown)
$my_items = [];
try {
    $myStmt = $pdo->prepare("SELECT equipment_id, equipment_name AS title FROM equipment WHERE owner_id = :uid");
    $myStmt->execute(['uid' => $user_id]);
    $my_items = $myStmt->fetchAll();
} catch (Exception $e) {
    $my_items = [];
}

// Fetch other members' available equipment
$other_items = [];
try {
    $othStmt = $pdo->prepare("
        SELECT e.equipment_id, e.equipment_name AS title, 
               CONCAT(m.first_name, ' ', m.last_name) AS owner_name 
        FROM equipment e 
        JOIN member m ON e.owner_id = m.member_id
        WHERE e.owner_id != :uid AND e.availability_status = 'Available'
    ");
    $othStmt->execute(['uid' => $user_id]);
    $other_items = $othStmt->fetchAll();
} catch (Exception $e) {
    $other_items = [];
}

// Fetch exchange agreements involving this member
$exchanges = [];
try {
    $exStmt = $pdo->prepare("
        SELECT 
            ea.exchange_id,
            ea.exchange_date,
            ea.status,
            ea.lender_a_id,
            ea.lender_b_id,
            ea.equipment_a_id,
            ea.equipment_b_id,
            eq_a.equipment_name AS gear_a_title,
            eq_b.equipment_name AS gear_b_title,
            CONCAT(mem_a.first_name, ' ', mem_a.last_name) AS lender_a_name,
            CONCAT(mem_b.first_name, ' ', mem_b.last_name) AS lender_b_name,
            mem_a.phone_number AS lender_a_phone,
            mem_b.phone_number AS lender_b_phone,
            mem_a.university_email AS lender_a_email,
            mem_b.university_email AS lender_b_email
        FROM exchange_agreement ea
        JOIN equipment eq_a ON ea.equipment_a_id = eq_a.equipment_id
        JOIN equipment eq_b ON ea.equipment_b_id = eq_b.equipment_id
        JOIN member mem_a ON ea.lender_a_id = mem_a.member_id
        JOIN member mem_b ON ea.lender_b_id = mem_b.member_id
        WHERE ea.lender_a_id = :uid1 OR ea.lender_b_id = :uid2
        ORDER BY ea.exchange_id DESC
    ");
    $exStmt->execute(['uid1' => $user_id, 'uid2' => $user_id]);
    $exchanges = $exStmt->fetchAll();
} catch (Exception $e) {
    $exchanges = [];
}

$base_path = '..';
$page_title = 'Equipment Exchanges - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Campus Equipment Exchanges</h1>
        <p class="text-sm text-slate-500 mt-0.5">Peer-to-peer equipment swap agreements without daily monetary rental fees.</p>
      </div>

      <a href="#propose-modal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
        <span>Propose New Swap</span>
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

    <!-- Active Exchanges List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-10 overflow-hidden">
      <div class="p-5 border-b border-slate-200 flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-navy-900">My Exchange Agreements</h3>
          <p class="text-xs text-slate-500">Track incoming and outgoing swap proposals</p>
        </div>
        <span class="text-xs font-bold text-slate-500"><?php echo count($exchanges); ?> Agreements</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Swap ID</th>
              <th class="py-3 px-4">Exchange Date</th>
              <th class="py-3 px-4">Offered Gear (Gear A)</th>
              <th class="py-3 px-4">Requested Gear (Gear B)</th>
              <th class="py-3 px-4">Swap Partner & Contact</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($exchanges)): ?>
              <tr>
                <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                  No active exchange agreements. Propose a swap below!
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($exchanges as $ex): ?>
                <?php 
                  $exId = (int)$ex['exchange_id'];
                  $is_initiator = ($ex['lender_a_id'] == $user_id);
                  $partner_name = $is_initiator ? $ex['lender_b_name'] : $ex['lender_a_name'];
                  $partner_phone = $is_initiator ? $ex['lender_b_phone'] : $ex['lender_a_phone'];
                  $partner_email = $is_initiator ? $ex['lender_b_email'] : $ex['lender_a_email'];
                  $formatted_date = !empty($ex['exchange_date']) ? date('M d, Y • h:i A', strtotime($ex['exchange_date'])) : 'N/A';
                ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4">
                    <span class="font-mono bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-bold text-xs">#EX-<?php echo str_pad($exId, 4, '0', STR_PAD_LEFT); ?></span>
                  </td>
                  <td class="py-3 px-4 text-slate-600 font-medium whitespace-nowrap">
                    <?php echo htmlspecialchars($formatted_date); ?>
                  </td>
                  <td class="py-3 px-4 font-bold text-navy-900">
                    <div><?php echo htmlspecialchars($ex['gear_a_title'] ?? 'Offered Item'); ?></div>
                    <div class="text-[10px] font-normal text-slate-400">By: <?php echo htmlspecialchars($ex['lender_a_name'] ?? 'Initiator'); ?> <?php echo $is_initiator ? '(You)' : ''; ?></div>
                  </td>
                  <td class="py-3 px-4 font-bold text-primary-600">
                    <div><?php echo htmlspecialchars($ex['gear_b_title'] ?? 'Requested Item'); ?></div>
                    <div class="text-[10px] font-normal text-slate-400">Owner: <?php echo htmlspecialchars($ex['lender_b_name'] ?? 'Owner'); ?> <?php echo (!$is_initiator && $ex['lender_b_id'] == $user_id) ? '(You)' : ''; ?></div>
                  </td>
                  <td class="py-3 px-4 text-slate-700">
                    <div class="font-semibold text-slate-800"><?php echo htmlspecialchars($partner_name ?? 'Partner'); ?></div>
                    <?php if (!empty($partner_phone)): ?>
                      <div class="text-[11px] text-primary-600 font-medium"><?php echo htmlspecialchars($partner_phone); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($partner_email)): ?>
                      <div class="text-[10px] text-slate-400 truncate max-w-[140px]"><?php echo htmlspecialchars($partner_email); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                      <?php echo ($ex['status'] === 'Accepted') ? 'bg-emerald-100 text-emerald-800' : (($ex['status'] === 'Completed') ? 'bg-blue-100 text-blue-800' : (($ex['status'] === 'Rejected') ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800')); ?>">
                      <?php echo htmlspecialchars($ex['status'] ?? 'Pending'); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                    <?php if ($ex['status'] === 'Pending' && ($ex['lender_b_id'] == $user_id)): ?>
                      <a href="exchanges.php?action=accept&id=<?php echo $exId; ?>" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                        Accept
                      </a>
                      <a href="exchanges.php?action=decline&id=<?php echo $exId; ?>" class="px-2.5 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 font-bold text-[11px]">
                        Decline
                      </a>
                    <?php elseif ($ex['status'] === 'Accepted'): ?>
                      <a href="exchanges.php?action=complete&id=<?php echo $exId; ?>" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px]">
                        Mark Completed
                      </a>
                    <?php else: ?>
                      <span class="text-slate-400 text-[11px]">-</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Propose New Swap Section -->
    <div id="propose-modal" class="bg-white rounded-2xl border border-slate-200 shadow-xl p-6 sm:p-8 max-w-xl mx-auto">
      <div class="border-b border-slate-200 pb-4 mb-6">
        <h2 class="text-lg font-extrabold text-navy-900">Propose Equipment Swap</h2>
        <p class="text-xs text-slate-500">Select one of your listed items to offer in exchange for another student's equipment</p>
      </div>

      <?php if ($member_status !== 'Verified'): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <span>
            <?php echo ($member_status === 'Rejected') ? 'Your account was rejected. You cannot propose equipment swaps.' : 'Your account is pending verification. You will be able to propose swaps once approved by an administrator.'; ?>
          </span>
        </div>
      <?php endif; ?>

      <form action="exchanges.php" method="POST" class="space-y-4">
        
        <div>
          <label for="offered_equipment_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Equipment to Offer *</label>
          <select id="offered_equipment_id" name="equipment_a_id" required class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 font-medium text-slate-800">
            <?php if (empty($my_items)): ?>
              <option value="">(You have not listed any items yet - please list an item first)</option>
            <?php else: ?>
              <?php foreach ($my_items as $mi): ?>
                <option value="<?php echo $mi['equipment_id'] ?? $mi['id']; ?>">
                  <?php echo htmlspecialchars($mi['title']); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div>
          <label for="requested_equipment_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Equipment You Want *</label>
          <select id="requested_equipment_id" name="equipment_b_id" required class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 font-medium text-slate-800">
            <?php if (empty($other_items)): ?>
              <option value="">(No peer items currently available for exchange)</option>
            <?php else: ?>
              <?php foreach ($other_items as $oi): ?>
                <option value="<?php echo $oi['equipment_id'] ?? $oi['id']; ?>">
                  <?php echo htmlspecialchars($oi['title']); ?> (Owner: <?php echo htmlspecialchars($oi['owner_name']); ?>)
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <?php if ($member_status === 'Verified'): ?>
          <button type="submit" name="propose_exchange" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
            <span>Send Swap Proposal</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        <?php else: ?>
          <button type="button" disabled class="w-full py-3 px-4 bg-slate-200 text-slate-400 font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span>Verification Required to Swap</span>
          </button>
        <?php endif; ?>

      </form>
    </div>

  </main>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
