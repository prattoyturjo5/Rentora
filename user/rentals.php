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

if (isset($_GET['verify_status'])) {
    if ($_GET['verify_status'] === 'success') {
        $success = "Token verified successfully! The rental is now Active, and both parties are verified.";
    } elseif ($_GET['verify_status'] === 'invalid') {
        $error = "Invalid or expired handover token. Please confirm the code with the renter.";
    }
}

// Handle Owner Request Actions (Approve / Reject / Mark Returned)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $agreement_id = (int)$_GET['id'];

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Active' WHERE rental_id = :id");
            $stmt->execute(['id' => $agreement_id]);
            
            $upEq = $pdo->prepare("UPDATE equipment SET availability_status = 'Rented' WHERE equipment_id = (SELECT equipment_id FROM rental_agreement WHERE rental_id = :id LIMIT 1)");
            $upEq->execute(['id' => $agreement_id]);
            $success = "Rental request approved! Rental is now active.";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Cancelled' WHERE rental_id = :id");
            $stmt->execute(['id' => $agreement_id]);
            $success = "Rental request rejected.";
        } elseif ($action === 'return') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Completed', actual_end_date = CURRENT_DATE WHERE rental_id = :id");
            $stmt->execute(['id' => $agreement_id]);

            // Release item back to available
            $upEq = $pdo->prepare("UPDATE equipment SET availability_status = 'Available' WHERE equipment_id = (SELECT equipment_id FROM rental_agreement WHERE rental_id = :id LIMIT 1)");
            $upEq->execute(['id' => $agreement_id]);
            $success = "Equipment marked as returned. Deposit released!";
        }
    } catch (PDOException $e) {
        $error = "Action error: " . htmlspecialchars($e->getMessage());
    }
}

// 1. Fetch My Borrowed Rentals (where member is renter)
$my_borrowed = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.*, r.expected_end_date AS end_date, r.deposit_amount AS deposit, r.total_cost AS total_rent,
               e.equipment_name AS title, e.campus_spot, e.rental_rate AS daily_rate, 
               CONCAT(m.first_name, ' ', m.last_name) AS owner_name, 
               m.phone_number AS owner_phone,
               m.university_email AS owner_email
        FROM rental_agreement r
        LEFT JOIN equipment e ON r.equipment_id = e.equipment_id
        LEFT JOIN member m ON e.owner_id = m.member_id
        WHERE r.renter_id = :uid
        ORDER BY r.rental_id DESC
    ");
    $stmt->execute(['uid' => $user_id]);
    $my_borrowed = $stmt->fetchAll();
} catch (Exception $e) {
    $my_borrowed = [];
}

// 2. Fetch Incoming Requests on My Equipment (where member is owner)
$incoming_requests = [];
try {
    $inStmt = $pdo->prepare("
        SELECT r.*, r.expected_end_date AS end_date, r.deposit_amount AS deposit, r.total_cost AS total_rent,
               e.equipment_name AS title, 
               CONCAT(m.first_name, ' ', m.last_name) AS renter_name, 
               m.username AS renter_student_id, 
               m.phone_number AS renter_phone,
               m.university_email AS renter_email
        FROM rental_agreement r
        JOIN equipment e ON r.equipment_id = e.equipment_id
        LEFT JOIN member m ON r.renter_id = m.member_id
        WHERE e.owner_id = :uid
        ORDER BY r.rental_id DESC
    ");
    $inStmt->execute(['uid' => $user_id]);
    $incoming_requests = $inStmt->fetchAll();
} catch (Exception $e) {
    $incoming_requests = [];
}

$base_path = '..';
$page_title = 'Rental Agreements & Handover - Rentora';
require_once(__DIR__ . '/../includes/header.php');
require_once(__DIR__ . '/../includes/nav.php');
?>

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full bg-canvas">
    
    <div class="mb-8">
      <h1 class="text-2xl font-extrabold text-primary tracking-tight">Rental Agreements &amp; Handover Hub</h1>
      <p class="text-xs text-muted mt-0.5">Track your rental passes, manage peer requests, and verify campus handover tokens.</p>
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

    <!-- Owner In-Person Handover Token Verification Box -->
    <div class="bg-surface rounded-2xl p-6 mb-8 shadow-float border border-subtle flex flex-col md:flex-row items-center justify-between gap-6 transition-colors">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <h3 class="text-base font-bold text-primary">Physical Handover Verification</h3>
        </div>
        <p class="text-xs text-muted">Are you handing over gear to a student on campus? Enter their 8-character token to confirm handover and activate the rental.</p>
      </div>

      <form method="POST" action="verify_handover.php" class="flex items-center gap-2 w-full md:w-auto">
          <input type="text" name="token_input" required placeholder="E.G. TRX-4821" 
                 class="input-subtle text-xs uppercase tracking-wider font-mono">
          <button type="submit" name="verify_token_btn" 
                  class="btn-accent text-xs whitespace-nowrap">
              Verify Token
          </button>
      </form>
    </div>

    <!-- Incoming Requests on Your Equipment -->
    <div class="bg-surface rounded-2xl border border-subtle shadow-float mb-10 overflow-hidden transition-colors">
      <div class="p-5 border-b border-subtle flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-primary">Incoming Requests on My Equipment</h3>
          <p class="text-xs text-muted">Students requesting to rent items you own</p>
        </div>
        <span class="text-xs font-mono font-bold text-muted"><?php echo count($incoming_requests); ?> Total</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Renter</th>
              <th class="py-3 px-4">Duration</th>
              <th class="py-3 px-4">Rent Total</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle text-primary">
            <?php if (empty($incoming_requests)): ?>
              <tr>
                <td colspan="7" class="py-6 text-center text-muted font-medium">
                  No incoming requests on your listings yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($incoming_requests as $req): ?>
                <?php $rid = $req['rental_id'] ?? $req['id']; ?>
                <tr class="hover:bg-surface-subtle transition-colors">
                  <td class="py-3 px-4 font-bold text-primary"><?php echo htmlspecialchars($req['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4">
                    <span class="font-semibold text-primary"><?php echo htmlspecialchars($req['renter_name'] ?? 'Student'); ?></span>
                    <?php if (!empty($req['renter_phone'])): ?>
                      <span class="block text-[11px] text-accent font-medium font-mono"><?php echo htmlspecialchars($req['renter_phone']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($req['renter_email'])): ?>
                      <span class="block text-[10px] text-muted truncate max-w-[150px]"><?php echo htmlspecialchars($req['renter_email']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-muted font-mono"><?php echo htmlspecialchars($req['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($req['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4 font-bold text-primary font-mono">৳<?php echo number_format($req['total_rent'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-muted font-mono">৳<?php echo number_format($req['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <?php
                      $st = $req['status'] ?? 'Pending';
                      $badge = match($st) {
                        'Active' => 'badge-verified',
                        'Cancelled', 'Rejected' => 'badge-rejected',
                        default => 'badge-pending'
                      };
                    ?>
                    <span class="badge-subtle <?php echo $badge; ?>">
                      <?php echo htmlspecialchars($st); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                    <?php if ($req['status'] === 'Pending'): ?>
                      <a href="rentals.php?action=approve&id=<?php echo $rid; ?>" class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 font-bold text-[11px] transition">
                        Approve
                      </a>
                      <a href="rentals.php?action=reject&id=<?php echo $rid; ?>" class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white border border-rose-500/30 font-bold text-[11px] transition">
                        Reject
                      </a>
                    <?php elseif ($req['status'] === 'Active'): ?>
                      <a href="rentals.php?action=return&id=<?php echo $rid; ?>" onclick="return confirm('Confirm item returned safely?')" class="px-2.5 py-1 rounded-lg bg-accent/10 text-accent hover:bg-accent hover:text-white border border-accent/30 font-bold text-[11px] transition">
                        Mark Returned
                      </a>
                    <?php else: ?>
                      <span class="text-muted text-[11px]">Completed</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- My Borrowed Equipment Rentals -->
    <div class="bg-surface rounded-2xl border border-subtle shadow-float overflow-hidden transition-colors">
      <div class="p-5 border-b border-subtle">
        <h3 class="text-base font-extrabold text-primary">Equipment I Am Borrowing</h3>
        <p class="text-xs text-muted">Your rental agreements and handover tokens</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-surface-subtle border-b border-subtle text-muted font-bold uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Lender Contact</th>
              <th class="py-3 px-4">Dates</th>
              <th class="py-3 px-4">Handover Token</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border-subtle text-primary">
            <?php if (empty($my_borrowed)): ?>
              <tr>
                <td colspan="6" class="py-6 text-center text-muted font-medium">
                  You have not submitted any rental requests yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($my_borrowed as $b): ?>
                <tr class="hover:bg-surface-subtle transition-colors">
                  <td class="py-3 px-4 font-bold text-primary"><?php echo htmlspecialchars($b['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4 text-muted">
                    <span class="font-semibold text-primary"><?php echo htmlspecialchars($b['owner_name'] ?? 'Lender'); ?></span>
                    <?php if (!empty($b['owner_phone'])): ?>
                      <span class="block text-[11px] text-accent font-medium font-mono"><?php echo htmlspecialchars($b['owner_phone']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($b['owner_email'])): ?>
                      <span class="block text-[10px] text-muted truncate max-w-[150px]"><?php echo htmlspecialchars($b['owner_email']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-muted font-mono"><?php echo htmlspecialchars($b['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($b['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4">
                    <span class="font-mono bg-surface-subtle text-accent border border-subtle px-2 py-1 rounded font-bold text-xs"><?php echo htmlspecialchars($b['handover_token'] ?? 'N/A'); ?></span>
                  </td>
                  <td class="py-3 px-4 text-muted font-mono">৳<?php echo number_format($b['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <?php
                      $st = $b['status'] ?? 'Pending';
                      $badge = match($st) {
                        'Active' => 'badge-verified',
                        'Cancelled', 'Rejected' => 'badge-rejected',
                        default => 'badge-pending'
                      };
                    ?>
                    <span class="badge-subtle <?php echo $badge; ?>">
                      <?php echo htmlspecialchars($st); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

<?php require_once(__DIR__ . '/../includes/footer.php'); ?>
