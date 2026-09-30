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

$user_id = intval($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
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

    $checkEx = $pdo->prepare("SELECT * FROM exchange_agreement WHERE exchange_id = :id LIMIT 1");
    $checkEx->execute(['id' => $exchange_id]);
    $exRow = $checkEx->fetch(PDO::FETCH_ASSOC);

    if (!$exRow) {
        $error = "Exchange agreement #EX-" . str_pad($exchange_id, 4, '0', STR_PAD_LEFT) . " not found.";
    } elseif ($action === 'accept' || $action === 'decline' || $action === 'reject') {
        if ((int)$exRow['lender_b_id'] !== (int)$user_id) {
            $error = "Unauthorized: Only the equipment owner (recipient of this swap proposal) can accept or reject this agreement.";
        } elseif ($member_status !== 'Verified') {
            $error = ($member_status === 'Rejected') ? "Your account was rejected, contact an admin." : "Your account is pending verification. You cannot accept swap agreements until verified.";
        } elseif ($exRow['status'] !== 'Pending' && !empty($exRow['status'])) {
            $error = "This exchange proposal is already marked as " . htmlspecialchars($exRow['status']) . ".";
        } else {
            try {
                if ($action === 'accept') {
                    $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Accepted' WHERE exchange_id = :id AND lender_b_id = :uid");
                    $stmt->execute(['id' => $exchange_id, 'uid' => $user_id]);
                    $success = "Exchange agreement accepted! Coordinate with peer for equipment swap.";
                } else {
                    $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Rejected' WHERE exchange_id = :id AND lender_b_id = :uid");
                    $stmt->execute(['id' => $exchange_id, 'uid' => $user_id]);
                    $success = "Exchange proposal declined.";
                }
            } catch (PDOException $e) {
                $error = "Exchange update error: " . htmlspecialchars($e->getMessage());
            }
        }
    } elseif ($action === 'complete') {
        if ((int)$exRow['lender_a_id'] !== (int)$user_id && (int)$exRow['lender_b_id'] !== (int)$user_id) {
            $error = "Unauthorized: You are not a party in this exchange agreement.";
        } elseif ($exRow['status'] !== 'Accepted') {
            $error = "Only accepted exchanges can be marked as completed.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE exchange_agreement SET status = 'Completed' WHERE exchange_id = :id");
                $stmt->execute(['id' => $exchange_id]);

                // Swap equipment ownership
                $upA = $pdo->prepare("UPDATE equipment SET owner_id = :new_owner WHERE equipment_id = :eq_id");
                $upA->execute(['new_owner' => $exRow['lender_b_id'], 'eq_id' => $exRow['equipment_a_id']]);

                $upB = $pdo->prepare("UPDATE equipment SET owner_id = :new_owner WHERE equipment_id = :eq_id");
                $upB->execute(['new_owner' => $exRow['lender_a_id'], 'eq_id' => $exRow['equipment_b_id']]);

                $success = "Exchange completed! Ownership of both equipment listings has been permanently transferred between members.";
            } catch (PDOException $e) {
                $error = "Exchange completion error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Handle Propose New Exchange
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['propose_exchange'])) {
    if ($member_status !== 'Verified') {
        $error = ($member_status === 'Rejected') ? "Your account was rejected, contact an admin." : "Your account is pending verification. You cannot propose exchanges until verified.";
    } else {
        $equipment_a_id = (int)($_POST['equipment_a_id'] ?? $_POST['offered_equipment_id'] ?? 0);
        $equipment_b_id = (int)($_POST['equipment_b_id'] ?? $_POST['requested_equipment_id'] ?? 0);

        if ($equipment_a_id <= 0 || $equipment_b_id <= 0) {
            $error = "Please select both your offered equipment and the item you wish to swap for.";
        } else {
            try {
                $ownerStmt = $pdo->prepare("SELECT owner_id FROM equipment WHERE equipment_id = :id LIMIT 1");
                $ownerStmt->execute(['id' => $equipment_b_id]);
                $lender_b_id = $ownerStmt->fetchColumn();

                if ($lender_b_id) {
                    $lender_a_id = $user_id;
                    $cash_direction = trim($_POST['cash_direction'] ?? 'none');
                    if (!in_array($cash_direction, ['none', 'offer', 'demand'], true)) {
                        $cash_direction = 'none';
                    }
                    $cash_adjustment = ($cash_direction === 'none') ? 0.00 : max(0.00, floatval($_POST['cash_adjustment'] ?? $_POST['cash_compensation'] ?? 0));
                    if ($cash_adjustment <= 0.00) {
                        $cash_direction = 'none';
                        $cash_adjustment = 0.00;
                    }

                    $ins = $pdo->prepare("
                        INSERT INTO exchange_agreement (lender_a_id, lender_b_id, equipment_a_id, equipment_b_id, cash_adjustment, cash_direction, status)
                        VALUES (:lender_a_id, :lender_b_id, :equipment_a_id, :equipment_b_id, :cash_adjustment, :cash_direction, 'Pending')
                    ");
                    $ins->execute([
                        'lender_a_id'     => $lender_a_id,
                        'lender_b_id'     => $lender_b_id,
                        'equipment_a_id'  => $equipment_a_id,
                        'equipment_b_id'  => $equipment_b_id,
                        'cash_adjustment' => $cash_adjustment,
                        'cash_direction'  => $cash_direction
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

// Fetch member's own equipment
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

// Fetch exchange agreements
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
            ea.cash_adjustment,
            ea.cash_direction,
            eq_a.equipment_name AS gear_a_title,
            eq_a.rental_rate AS gear_a_rate,
            eq_a.security_deposit AS gear_a_val,
            eq_b.equipment_name AS gear_b_title,
            eq_b.rental_rate AS gear_b_rate,
            eq_b.security_deposit AS gear_b_val,
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
        WHERE ea.lender_a_id = ? OR ea.lender_b_id = ?
        ORDER BY ea.exchange_id DESC
    ");
    $exStmt->execute([$user_id, $user_id]);
    $exchanges = $exStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $exchanges = [];
}

$base_path = '..';
$page_title = 'Equipment Exchanges - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full bg-canvas">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <h1 class="text-2xl font-extrabold text-primary tracking-tight">Campus Equipment Exchanges</h1>
        <p class="text-xs text-muted mt-0.5">Permanent peer-to-peer equipment swap agreements.</p>
      </div>

      <a href="#propose-modal" class="btn-accent text-xs">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
        <span>Propose New Swap</span>
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

    <!-- Active Exchanges List -->
    <div class="bg-surface rounded-2xl border border-subtle shadow-float mb-10 overflow-hidden transition-colors">
      <div class="p-5 border-b border-subtle flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-primary">My Exchange Agreements</h3>
          <p class="text-xs text-muted">Track incoming and outgoing swap proposals</p>
        </div>
        <span class="text-xs font-mono font-bold text-muted"><?php echo count($exchanges); ?> Agreements</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Swap ID</th>
              <th class="py-3 px-4">Direction</th>
              <th class="py-3 px-4">Exchange Date</th>
              <th class="py-3 px-4">Offered Gear (Gear A)</th>
              <th class="py-3 px-4">Requested Gear (Gear B)</th>
              <th class="py-3 px-4">Cash Adjustment</th>
              <th class="py-3 px-4">Swap Partner &amp; Contact</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle text-primary">
            <?php if (empty($exchanges)): ?>
              <tr>
                <td colspan="9" class="py-8 text-center text-muted font-medium">
                  No active exchange agreements. Propose a swap below!
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($exchanges as $ex): ?>
                <?php 
                  $exId = (int)$ex['exchange_id'];
                  $is_outgoing = ((int)$ex['lender_a_id'] === (int)$user_id);
                  $is_incoming = ((int)$ex['lender_b_id'] === (int)$user_id);

                  $partner_name = $is_outgoing ? $ex['lender_b_name'] : $ex['lender_a_name'];
                  $partner_phone = $is_outgoing ? $ex['lender_b_phone'] : $ex['lender_a_phone'];
                  $partner_email = $is_outgoing ? $ex['lender_b_email'] : $ex['lender_a_email'];
                  $formatted_date = !empty($ex['exchange_date']) ? date('M d, Y • h:i A', strtotime($ex['exchange_date'])) : 'N/A';

                  $c_dir = $ex['cash_direction'] ?? 'none';
                  $c_amt = floatval($ex['cash_adjustment'] ?? 0);

                  $s = $ex['status'] ?? 'Pending';
                  $badge = match($s) {
                    'Accepted', 'Completed' => 'badge-verified',
                    'Rejected' => 'badge-rejected',
                    default => 'badge-pending'
                  };
                ?>
                <tr class="hover:bg-surface-subtle transition-colors">
                  <td class="py-3 px-4 font-mono font-bold text-accent">
                    #EX-<?php echo str_pad($exId, 4, '0', STR_PAD_LEFT); ?>
                  </td>
                  <td class="py-3 px-4 whitespace-nowrap">
                    <?php if ($is_outgoing): ?>
                      <span class="badge-subtle text-[10px]">
                        Outgoing &rarr;
                      </span>
                    <?php else: ?>
                      <span class="badge-subtle text-[10px] text-accent font-bold">
                        &larr; Incoming
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-muted font-mono whitespace-nowrap">
                    <?php echo htmlspecialchars($formatted_date); ?>
                  </td>
                  <td class="py-3 px-4 font-bold text-primary">
                    <div><?php echo htmlspecialchars($ex['gear_a_title'] ?? 'Offered Item'); ?></div>
                    <div class="text-[10px] font-normal text-muted">By: <?php echo htmlspecialchars($ex['lender_a_name'] ?? 'Initiator'); ?> <?php echo $is_outgoing ? '(You)' : ''; ?></div>
                  </td>
                  <td class="py-3 px-4 font-bold text-accent">
                    <div><?php echo htmlspecialchars($ex['gear_b_title'] ?? 'Requested Item'); ?></div>
                    <div class="text-[10px] font-normal text-muted">Owner: <?php echo htmlspecialchars($ex['lender_b_name'] ?? 'Owner'); ?> <?php echo $is_incoming ? '(You)' : ''; ?></div>
                  </td>
                  <td class="py-3 px-4 whitespace-nowrap">
                    <?php if ($c_dir === 'none' || $c_amt <= 0): ?>
                      <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-subtle text-muted border border-subtle">
                        <svg class="w-3.5 h-3.5 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        <span>Even Swap</span>
                      </div>
                      <div class="text-[10px] text-muted mt-0.5 font-mono">No extra cash (৳0.00)</div>
                    <?php elseif ($c_dir === 'offer'): ?>
                      <?php if ($is_incoming): ?>
                        <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                          <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                          <span>+৳<?php echo number_format($c_amt, 2); ?> to You</span>
                        </div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">Proposer offers you extra cash</div>
                      <?php else: ?>
                        <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                          <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                          <span>-৳<?php echo number_format($c_amt, 2); ?> You Pay</span>
                        </div>
                        <div class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 font-medium">You offered extra cash to owner</div>
                      <?php endif; ?>
                    <?php elseif ($c_dir === 'demand'): ?>
                      <?php if ($is_incoming): ?>
                        <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                          <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                          <span>৳<?php echo number_format($c_amt, 2); ?> Demanded</span>
                        </div>
                        <div class="text-[10px] text-rose-600 dark:text-rose-400 mt-0.5 font-medium">Proposer asks you to pay extra</div>
                      <?php else: ?>
                        <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                          <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                          <span>+৳<?php echo number_format($c_amt, 2); ?> to You</span>
                        </div>
                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5 font-medium">You asked extra cash from owner</div>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-primary">
                    <div class="font-semibold">
                      <?php echo htmlspecialchars($partner_name ?? 'Partner'); ?>
                      <span class="text-[10px] font-normal text-muted">(<?php echo $is_outgoing ? 'Owner' : 'Proposer'; ?>)</span>
                    </div>
                    <?php if (!empty($partner_phone)): ?>
                      <div class="text-[11px] text-accent font-mono"><?php echo htmlspecialchars($partner_phone); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($partner_email)): ?>
                      <div class="text-[10px] text-muted truncate max-w-[140px]"><?php echo htmlspecialchars($partner_email); ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="badge-subtle <?php echo $badge; ?>">
                      <?php echo htmlspecialchars($s); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                    <?php if ($is_incoming && (empty($s) || $s === 'Pending')): ?>
                      <?php
                        $confirm_accept_msg = "Are you sure you want to accept this swap agreement?";
                        if ($c_dir === 'offer' && $c_amt > 0) {
                            $confirm_accept_msg = "Accept this swap? You will receive ৳" . number_format($c_amt, 2) . " extra cash during handover.";
                        } elseif ($c_dir === 'demand' && $c_amt > 0) {
                            $confirm_accept_msg = "Accept this swap? Note: You are required to pay ৳" . number_format($c_amt, 2) . " extra cash during handover.";
                        } else {
                            $confirm_accept_msg = "Accept this swap proposal with no extra money give/take (Even Swap)?";
                        }
                      ?>
                      <a href="exchanges.php?action=accept&id=<?php echo $exId; ?>" 
                         onclick="return confirm('<?php echo htmlspecialchars($confirm_accept_msg, ENT_QUOTES); ?>');"
                         class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 font-bold text-[11px] transition">
                        Accept
                      </a>
                      <a href="exchanges.php?action=reject&id=<?php echo $exId; ?>" 
                         onclick="return confirm('Decline this exchange proposal?');"
                         class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white border border-rose-500/30 font-bold text-[11px] transition">
                        Reject
                      </a>
                    <?php elseif ($s === 'Accepted'): ?>
                      <a href="exchanges.php?action=complete&id=<?php echo $exId; ?>" class="btn-accent px-2.5 py-1 text-[11px]">
                        Mark Completed
                      </a>
                    <?php elseif ($is_outgoing && (empty($s) || $s === 'Pending')): ?>
                      <span class="text-muted text-[11px] font-medium">Waiting for Owner</span>
                    <?php else: ?>
                      <span class="text-muted text-[11px]">-</span>
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
    <div id="propose-modal" class="bg-surface rounded-2xl border border-subtle shadow-float p-6 sm:p-8 max-w-xl mx-auto transition-colors">
      <div class="border-b border-subtle pb-4 mb-6">
        <h2 class="text-lg font-extrabold text-primary">Propose Equipment Swap</h2>
        <p class="text-xs text-muted">Select one of your listed items to offer in exchange for another student's equipment</p>
      </div>

      <?php if ($member_status !== 'Verified'): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs flex items-center gap-2">
          <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <span>
            <?php echo ($member_status === 'Rejected') ? 'Your account was rejected. You cannot propose equipment swaps.' : 'Your account is pending verification. You will be able to propose swaps once approved by an administrator.'; ?>
          </span>
        </div>
      <?php endif; ?>

      <form action="exchanges.php" method="POST" class="space-y-4">
        
        <div>
          <label for="offered_equipment_id" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Your Equipment to Offer *</label>
          <select id="offered_equipment_id" name="equipment_a_id" required class="input-subtle text-xs">
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
          <label for="requested_equipment_id" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Target Equipment You Want *</label>
          <select id="requested_equipment_id" name="equipment_b_id" required class="input-subtle text-xs">
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

        <div>
          <label for="modal_cash_direction" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Cash Adjustment / Compensation</label>
          <select id="modal_cash_direction" name="cash_direction" class="input-subtle text-xs">
            <option value="none">Even Swap / No Cash Adjustment (৳0)</option>
            <option value="offer">I Will Pay Extra Cash to Owner (-৳)</option>
            <option value="demand">I Demand Extra Cash from Owner (+৳)</option>
          </select>
        </div>

        <div id="modal_cash_amount_box" class="hidden">
          <label id="modal_cash_amount_label" for="modal_cash_amount" class="block text-xs font-bold text-muted uppercase tracking-wider mb-1 font-mono">Adjustment Amount (৳) *</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-muted font-bold text-xs">৳</span>
            <input type="number" id="modal_cash_amount" name="cash_adjustment" min="0" step="50" value="0" placeholder="e.g. 500" class="input-subtle text-xs pl-7">
          </div>
          <p id="modal_cash_help" class="text-[11px] text-muted mt-1">Specify additional money to balance equipment value.</p>
        </div>

        <?php if ($member_status === 'Verified'): ?>
          <button type="submit" name="propose_exchange" class="btn-accent w-full py-3 text-xs uppercase tracking-wider">
            <span>Send Swap Proposal</span>
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        <?php else: ?>
          <button type="button" disabled class="w-full py-3 px-4 bg-surface-subtle text-muted font-bold rounded-xl text-xs uppercase tracking-wider cursor-not-allowed border border-subtle">
            <span>Verification Required to Swap</span>
          </button>
        <?php endif; ?>

      </form>
    </div>

  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const dirSel = document.getElementById('modal_cash_direction');
    const amtBox = document.getElementById('modal_cash_amount_box');
    const amtInput = document.getElementById('modal_cash_amount');
    const amtLabel = document.getElementById('modal_cash_amount_label');
    const amtHelp = document.getElementById('modal_cash_help');

    if (dirSel && amtBox && amtInput) {
      function toggleCashInput() {
        if (dirSel.value === 'none') {
          amtBox.classList.add('hidden');
          amtInput.value = '0';
        } else if (dirSel.value === 'offer') {
          amtBox.classList.remove('hidden');
          amtLabel.textContent = 'Extra Money You Will Pay to Owner (৳) *';
          amtHelp.textContent = 'You agree to give this extra cash to the equipment owner during handover.';
        } else if (dirSel.value === 'demand') {
          amtBox.classList.remove('hidden');
          amtLabel.textContent = 'Extra Money You Demand from Owner (৳) *';
          amtHelp.textContent = 'The equipment owner must give you this extra cash during handover.';
        }
      }
      dirSel.addEventListener('change', toggleCashInput);
    }
  });
  </script>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
