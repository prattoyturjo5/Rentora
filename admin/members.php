<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../DBconnect.php');

// Restrict access to authenticated administrators only
if (!isset($_SESSION['admin_id']) && ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle member status mutations (Verify / Reject / Reset)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['member_id'])) {
    $target_member_id = intval($_POST['member_id']);
    $action = $_POST['action'];

    if (in_array($action, ['Verified', 'Rejected', 'Pending']) && $target_member_id > 0) {
        $update_sql = "UPDATE member SET status = '$action' WHERE member_id = '$target_member_id'";
        mysqli_query($conn, $update_sql);
    }
    header("Location: members.php?msg=updated");
    exit();
}

// Search and filter handling
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, trim($_GET['status'])) : '';

$where_clauses = [];
if (!empty($search)) {
    $where_clauses[] = "(first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR student_id LIKE '%$search%' OR university_email LIKE '%$search%' OR username LIKE '%$search%')";
}
if (!empty($status_filter) && in_array($status_filter, ['Pending', 'Verified', 'Rejected'])) {
    $where_clauses[] = "status = '$status_filter'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$query = "SELECT * FROM member $where_sql ORDER BY member_id DESC";
$members_res = mysqli_query($conn, $query);

// Summary counts for metric cards
$count_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM member"))['c'] ?? 0;
$count_verified = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM member WHERE status = 'Verified'"))['c'] ?? 0;
$count_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM member WHERE status = 'Pending'"))['c'] ?? 0;
$count_rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM member WHERE status = 'Rejected'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Directory | Rentora Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">

    <!-- Top Admin Bar -->
    <header class="border-b border-slate-800 bg-slate-900/60 backdrop-blur sticky top-0 z-50 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white font-extrabold shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <span class="text-lg font-bold text-white tracking-tight">Rentora Admin</span>
            </a>
            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Operations</span>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <a href="dashboard.php" class="text-slate-400 hover:text-white transition">Dashboard</a>
            <a href="members.php" class="text-white font-semibold border-b-2 border-blue-500 pb-0.5">Member Directory</a>
            <a href="../index.php" class="text-slate-400 hover:text-white transition">Public Marketplace</a>
            <a href="change_password.php" class="text-slate-400 hover:text-white transition">Security</a>
            <a href="../auth/logout.php" class="text-rose-400 hover:text-rose-300 font-medium transition ml-4">Sign Out</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 flex-1 w-full">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Verified Academic Registry</h1>
                <p class="text-xs text-slate-400 mt-1">Manage Premier University student accounts, approvals, and credentials.</p>
            </div>

            <!-- Metric Badges -->
            <div class="flex items-center gap-3 flex-wrap">
                <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Total Members</div>
                    <div class="text-lg font-black text-white"><?php echo $count_all; ?></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Verified</div>
                    <div class="text-lg font-black text-emerald-400"><?php echo $count_verified; ?></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Pending Review</div>
                    <div class="text-lg font-black text-amber-400"><?php echo $count_pending; ?></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2">
                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Rejected</div>
                    <div class="text-lg font-black text-rose-400"><?php echo $count_rejected; ?></div>
                </div>
            </div>
        </div>

        <!-- Feedback Alert -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Member verification status updated successfully.</span>
                </div>
                <a href="members.php" class="text-slate-400 hover:text-white text-[11px]">&times; Dismiss</a>
            </div>
        <?php endif; ?>

        <!-- Filter & Search Controls -->
        <form method="GET" action="members.php" class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 mb-6 flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-96 relative">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name, student ID, email, or username..." 
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-full md:w-auto flex items-center gap-3">
                <select name="status" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Verification States</option>
                    <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Verified" <?php echo ($status_filter === 'Verified') ? 'selected' : ''; ?>>Verified</option>
                    <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition">Apply</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="members.php" class="px-3 py-2.5 text-xs text-slate-400 hover:text-white transition">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Members Table -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 border-b border-slate-800 text-slate-400 uppercase font-semibold text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Student / Member</th>
                            <th class="px-4 py-3.5">Student ID</th>
                            <th class="px-4 py-3.5">Contact Details</th>
                            <th class="px-4 py-3.5">Campus Spot</th>
                            <th class="px-4 py-3.5">Balance</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300">
                        <?php if ($members_res && mysqli_num_rows($members_res) > 0): ?>
                            <?php while ($m = mysqli_fetch_assoc($members_res)): ?>
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-white text-sm">
                                            <?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-mono">@<?php echo htmlspecialchars($m['username']); ?></div>
                                        <?php if (!empty($m['gender']) || !empty($m['dob'])): ?>
                                            <div class="text-[10px] text-slate-500 mt-0.5">
                                                <?php echo htmlspecialchars(implode(' &bull; ', array_filter([$m['gender'] ?? null, $m['dob'] ?? null]))); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono text-slate-300">
                                        <?php echo htmlspecialchars($m['student_id'] ?? 'Not set'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-slate-300"><?php echo htmlspecialchars($m['university_email']); ?></div>
                                        <div class="text-[11px] text-slate-500 font-mono"><?php echo htmlspecialchars($m['phone_number'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-400">
                                        <?php echo htmlspecialchars($m['campus_address'] ?? 'Not set'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono font-semibold text-slate-200">
                                        ৳<?php echo number_format((float)($m['account_balance'] ?? 0), 2); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <?php
                                        $st = $m['status'] ?? 'Pending';
                                        if ($st === 'Verified'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                Verified
                                            </span>
                                        <?php elseif ($st === 'Rejected'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                Rejected
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                Pending Review
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="members.php" class="inline-flex items-center gap-1.5">
                                            <input type="hidden" name="member_id" value="<?php echo (int)$m['member_id']; ?>">
                                            
                                            <?php if ($st !== 'Verified'): ?>
                                                <button type="submit" name="action" value="Verified" 
                                                        class="px-2.5 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 rounded-lg text-xs font-bold transition">
                                                    Verify
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($st !== 'Rejected'): ?>
                                                <button type="submit" name="action" value="Rejected" 
                                                        class="px-2.5 py-1 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/30 rounded-lg text-xs font-bold transition">
                                                    Reject
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($st === 'Verified' || $st === 'Rejected'): ?>
                                                <button type="submit" name="action" value="Pending" 
                                                        class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 rounded-lg text-xs font-medium transition"
                                                        title="Reset to Pending status">
                                                    Reset
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto text-center space-y-2">
                                        <svg class="w-8 h-8 text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <div class="text-sm font-semibold text-slate-300">No member records found</div>
                                        <p class="text-xs text-slate-500">Try adjusting your search terms or filter selection.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-900 bg-slate-950 py-4 px-6 text-center text-xs text-slate-600">
        Rentora Academic Platform &bull; Operations &amp; Moderation Console
    </footer>
</body>
</html>
