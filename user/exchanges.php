<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_member('../auth/login.php');

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle Exchange Status Update (Accept / Decline / Complete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $exchange_id = (int)$_GET['id'];

    try {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Accepted' WHERE exchange_id = :id OR id = :id2");
            $stmt->execute(['id' => $exchange_id, 'id2' => $exchange_id]);
            $success = "Exchange agreement accepted! Coordinate with peer for equipment swap.";
        } elseif ($action === 'decline') {
            $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Declined' WHERE exchange_id = :id OR id = :id2");
            $stmt->execute(['id' => $exchange_id, 'id2' => $exchange_id]);
            $success = "Exchange proposal declined.";
        } elseif ($action === 'complete') {
            $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Completed' WHERE exchange_id = :id OR id = :id2");
            $stmt->execute(['id' => $exchange_id, 'id2' => $exchange_id]);
            $success = "Exchange marked as completed!";
        }
    } catch (PDOException $e) {
        $error = "Exchange update error: " . htmlspecialchars($e->getMessage());
    }
}

// Handle Propose New Exchange
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['propose_exchange'])) {
    $offered_id = (int)($_POST['offered_equipment_id'] ?? 0);
    $requested_id = (int)($_POST['requested_equipment_id'] ?? 0);

    if ($offered_id <= 0 || $requested_id <= 0) {
        $error = "Please select both your offered equipment and the item you wish to swap for.";
    } else {
        try {
            // Find owner of requested equipment
            $ownerStmt = $pdo->prepare("SELECT member_id FROM equipment WHERE equipment_id = :id OR id = :id2 LIMIT 1");
            $ownerStmt->execute(['id' => $requested_id, 'id2' => $requested_id]);
            $owner_id = $ownerStmt->fetchColumn();

            if ($owner_id) {
                $ins = $pdo->prepare("
                    INSERT INTO exchange_agreement (requester_id, owner_id, offered_equipment_id, requested_equipment_id, status)
                    VALUES (:requester_id, :owner_id, :offered, :requested, 'Pending')
                ");
                $ins->execute([
                    'requester_id' => $user_id,
                    'owner_id'     => $owner_id,
                    'offered'      => $offered_id,
                    'requested'    => $requested_id
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

// Fetch member's own equipment (for the dropdown)
$my_items = [];
try {
    $myStmt = $pdo->prepare("SELECT * FROM equipment WHERE member_id = :uid1 OR member_id = :uid2");
    $myStmt->execute(['uid1' => $user_id, 'uid2' => $user_id]);
    $my_items = $myStmt->fetchAll();
} catch (Exception $e) {
    $my_items = [];
}

// Fetch other members' available equipment
$other_items = [];
try {
    $othStmt = $pdo->prepare("
        SELECT e.*, m.name as owner_name 
        FROM equipment e 
        JOIN member m ON e.member_id = m.member_id OR e.member_id = m.id
        WHERE (e.member_id != :uid1 AND e.member_id != :uid2) AND e.is_available = 1
    ");
    $othStmt->execute(['uid1' => $user_id, 'uid2' => $user_id]);
    $other_items = $othStmt->fetchAll();
} catch (Exception $e) {
    $other_items = [];
}

// Fetch exchange agreements involving this member
$exchanges = [];
try {
    $exStmt = $pdo->prepare("
        SELECT ex.*, 
               e_off.title as offered_title, 
               e_req.title as requested_title,
               m_req.name as requester_name,
               m_own.name as owner_name
        FROM exchange_agreement ex
        LEFT JOIN equipment e_off ON ex.offered_equipment_id = e_off.equipment_id OR ex.offered_equipment_id = e_off.id
        LEFT JOIN equipment e_req ON ex.requested_equipment_id = e_req.equipment_id OR ex.requested_equipment_id = e_req.id
        LEFT JOIN member m_req ON ex.requester_id = m_req.member_id OR ex.requester_id = m_req.id
        LEFT JOIN member m_own ON ex.owner_id = m_own.member_id OR ex.owner_id = m_own.id
        WHERE ex.requester_id = :uid1 OR ex.owner_id = :uid2
        ORDER BY 1 DESC
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
              <th class="py-3 px-4">Offered Item</th>
              <th class="py-3 px-4">Requested Item</th>
              <th class="py-3 px-4">Requester</th>
              <th class="py-3 px-4">Target Owner</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($exchanges)): ?>
              <tr>
                <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                  No active exchange agreements. Propose a swap below!
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($exchanges as $ex): ?>
                <?php $exId = $ex['exchange_id'] ?? $ex['id']; ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-bold text-navy-900"><?php echo htmlspecialchars($ex['offered_title'] ?? 'Offered Item'); ?></td>
                  <td class="py-3 px-4 font-bold text-primary-600"><?php echo htmlspecialchars($ex['requested_title'] ?? 'Requested Item'); ?></td>
                  <td class="py-3 px-4 text-slate-700"><?php echo htmlspecialchars($ex['requester_name'] ?? 'Member'); ?></td>
                  <td class="py-3 px-4 text-slate-700"><?php echo htmlspecialchars($ex['owner_name'] ?? 'Owner'); ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                      <?php echo ($ex['status'] === 'Accepted') ? 'bg-emerald-100 text-emerald-800' : (($ex['status'] === 'Completed') ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'); ?>">
                      <?php echo htmlspecialchars($ex['status'] ?? 'Pending'); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5">
                    <?php if ($ex['status'] === 'Pending' && $ex['owner_id'] == $user_id): ?>
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

      <form action="exchanges.php" method="POST" class="space-y-4">
        
        <div>
          <label for="offered_equipment_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Your Equipment to Offer *</label>
          <select id="offered_equipment_id" name="offered_equipment_id" required class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 font-medium text-slate-800">
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
          <select id="requested_equipment_id" name="requested_equipment_id" required class="w-full px-3 py-2.5 text-xs bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 font-medium text-slate-800">
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

        <button type="submit" name="propose_exchange" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2">
          <span>Send Swap Proposal</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>

      </form>
    </div>

  </main>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
