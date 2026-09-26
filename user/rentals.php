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
               m.phone_number AS owner_phone
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
               m.student_id AS renter_student_id, 
               m.phone_number AS renter_phone
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

  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
    
    <div class="mb-8">
      <h1 class="text-2xl font-extrabold text-navy-900 tracking-tight">Rental Agreements & Handover Hub</h1>
      <p class="text-sm text-slate-500 mt-0.5">Track your rental passes, manage peer requests, and verify campus handover tokens.</p>
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

    <!-- Owner In-Person Handover Token Verification Box -->
    <div class="bg-navy-900 text-white rounded-2xl p-6 mb-8 shadow-lg border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <h3 class="text-base font-bold text-white">Physical Handover Verification</h3>
        </div>
        <p class="text-xs text-slate-300">Are you handing over gear to a student on campus? Enter their 8-character token to confirm handover and activate the rental.</p>
      </div>

      <form method="POST" action="verify_handover.php" class="flex items-center gap-2">
          <input type="text" name="token_input" required placeholder="E.G. TRX-4821" 
                 class="bg-slate-900/60 border border-slate-700 text-white rounded-xl px-4 py-2 text-sm uppercase tracking-wider font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
          <button type="submit" name="verify_token_btn" 
                  class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
              Verify Token
          </button>
      </form>
    </div>

    <!-- Incoming Requests on Your Equipment -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-10 overflow-hidden">
      <div class="p-5 border-b border-slate-200 flex justify-between items-center">
        <div>
          <h3 class="text-base font-extrabold text-navy-900">Incoming Requests on My Equipment</h3>
          <p class="text-xs text-slate-500">Students requesting to rent items you own</p>
        </div>
        <span class="text-xs font-bold text-slate-500"><?php echo count($incoming_requests); ?> Total</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
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
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($incoming_requests)): ?>
              <tr>
                <td colspan="7" class="py-6 text-center text-slate-400 font-medium">
                  No incoming requests on your listings yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($incoming_requests as $req): ?>
                <?php $rid = $req['rental_id'] ?? $req['id']; ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-bold text-navy-900"><?php echo htmlspecialchars($req['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4">
                    <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($req['renter_name'] ?? 'Student'); ?></span>
                    <span class="block text-[10px] text-slate-400"><?php echo htmlspecialchars($req['renter_student_id'] ?? ''); ?></span>
                  </td>
                  <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($req['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($req['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4 font-bold text-navy-900">৳<?php echo number_format($req['total_rent'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4 text-slate-600">৳<?php echo number_format($req['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                      <?php echo ($req['status'] === 'Active') ? 'bg-emerald-100 text-emerald-800' : (($req['status'] === 'Approved') ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'); ?>">
                      <?php echo htmlspecialchars($req['status'] ?? 'Pending'); ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right space-x-1.5">
                    <?php if ($req['status'] === 'Pending'): ?>
                      <a href="rentals.php?action=approve&id=<?php echo $rid; ?>" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                        Approve
                      </a>
                      <a href="rentals.php?action=reject&id=<?php echo $rid; ?>" class="px-2.5 py-1 rounded-lg bg-red-100 hover:bg-red-200 text-red-700 font-bold text-[11px]">
                        Reject
                      </a>
                    <?php elseif ($req['status'] === 'Active'): ?>
                      <a href="rentals.php?action=return&id=<?php echo $rid; ?>" onclick="return confirm('Confirm item returned safely?')" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px]">
                        Mark Returned
                      </a>
                    <?php else: ?>
                      <span class="text-slate-400 text-[11px]">Completed</span>
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
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-200">
        <h3 class="text-base font-extrabold text-navy-900">Equipment I Am Borrowing</h3>
        <p class="text-xs text-slate-500">Your rental agreements and handover tokens</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase">
            <tr>
              <th class="py-3 px-4">Equipment</th>
              <th class="py-3 px-4">Lender</th>
              <th class="py-3 px-4">Dates</th>
              <th class="py-3 px-4">Handover Token</th>
              <th class="py-3 px-4">Deposit</th>
              <th class="py-3 px-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($my_borrowed)): ?>
              <tr>
                <td colspan="6" class="py-6 text-center text-slate-400 font-medium">
                  You have not submitted any rental requests yet.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($my_borrowed as $b): ?>
                <tr class="hover:bg-slate-50/80 transition-colors">
                  <td class="py-3 px-4 font-bold text-navy-900"><?php echo htmlspecialchars($b['title'] ?? 'Equipment'); ?></td>
                  <td class="py-3 px-4 text-slate-700">
                    <span class="font-semibold"><?php echo htmlspecialchars($b['owner_name'] ?? 'Lender'); ?></span>
                    <?php if (!empty($b['owner_phone'])): ?>
                      <span class="block text-[10px] text-slate-400"><?php echo htmlspecialchars($b['owner_phone']); ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($b['start_date'] ?? ''); ?> &rarr; <?php echo htmlspecialchars($b['end_date'] ?? ''); ?></td>
                  <td class="py-3 px-4">
                    <span class="font-mono bg-slate-100 text-slate-800 px-2 py-1 rounded font-bold text-xs"><?php echo htmlspecialchars($b['handover_token'] ?? 'N/A'); ?></span>
                  </td>
                  <td class="py-3 px-4 text-slate-600">৳<?php echo number_format($b['deposit'] ?? 0, 2); ?></td>
                  <td class="py-3 px-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                      <?php echo ($b['status'] === 'Active') ? 'bg-emerald-100 text-emerald-800' : (($b['status'] === 'Approved') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'); ?>">
                      <?php echo htmlspecialchars($b['status'] ?? 'Pending'); ?>
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
