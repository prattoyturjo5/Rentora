<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../DBconnect.php');

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
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Directory | Rentora Admin</title>

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('rentora_theme');
                var theme = savedTheme || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) { }
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    colors: {
                        canvas: 'var(--canvas)',
                        surface: {
                            DEFAULT: 'var(--surface)',
                            elevated: 'var(--surface-elevated)',
                            subtle: 'var(--surface-subtle)',
                        },
                        'border-subtle': 'var(--border-subtle)',
                        primary: 'var(--text-primary)',
                        muted: 'var(--text-muted)',
                        'text-primary': 'var(--text-primary)',
                        'text-muted': 'var(--text-muted)',
                        accent: {
                            DEFAULT: 'var(--accent-primary)',
                            primary: 'var(--accent-primary)',
                            hover: 'var(--accent-hover)',
                            glow: 'var(--accent-glow)',
                        }
                    },
                    boxShadow: {
                        'float': 'var(--shadow-float)',
                        'elevated': 'var(--shadow-elevated)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        mono: ['Space Grotesk', 'ui-monospace', 'monospace'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="bg-canvas text-primary min-h-screen flex flex-col transition-colors">

    <!-- Top Admin Bar -->
    <header class="bg-white dark:bg-[#0B1120] border-b border-slate-200 dark:border-white/10 shadow-sm px-6 py-4 flex items-center justify-between sticky top-0 z-40 transition-colors">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-lg bg-accent flex items-center justify-center text-white font-extrabold shadow-md shadow-accent/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <span class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Rentora Admin</span>
            </a>
            <span
                class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">Operations</span>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold">
            <button type="button" id="admin-theme-btn"
                class="p-1.5 rounded-lg bg-slate-100 dark:bg-white/10 border border-slate-300 dark:border-white/15 text-slate-800 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-white/15 transition"
                title="Toggle Theme">
                🌓
            </button>
            <a href="dashboard.php" class="text-black dark:text-slate-300 hover:text-accent dark:hover:text-white transition">Dashboard</a>
            <a href="members.php" class="text-black dark:text-sky-400 font-extrabold border-b-2 border-black dark:border-sky-400 pb-0.5">Member Directory</a>
            <a href="../index.php" class="text-black dark:text-slate-300 hover:text-accent dark:hover:text-white transition">Marketplace</a>
            <a href="change_password.php" class="text-black dark:text-slate-300 hover:text-accent dark:hover:text-white transition">Security</a>
            <a href="../auth/logout.php"
                class="text-rose-600 dark:text-rose-400 hover:underline font-bold transition ml-2">Sign Out</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8 flex-1 w-full bg-canvas">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-extrabold text-primary tracking-tight">Verified Academic Registry</h1>
                <p class="text-xs text-muted mt-1">Manage Premier University student accounts, approvals, and
                    credentials.</p>
            </div>

            <!-- Metric Badges -->
            <div class="flex items-center gap-3 flex-wrap">
                <div class="bg-surface border border-subtle rounded-xl px-4 py-2 shadow-float">
                    <div class="text-[10px] text-muted font-mono uppercase tracking-wider font-bold">Total Members</div>
                    <div class="text-lg font-black text-primary font-mono"><?php echo $count_all; ?></div>
                </div>
                <div class="bg-surface border border-subtle rounded-xl px-4 py-2 shadow-float">
                    <div class="text-[10px] text-muted font-mono uppercase tracking-wider font-bold">Verified</div>
                    <div class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">
                        <?php echo $count_verified; ?></div>
                </div>
                <div class="bg-surface border border-subtle rounded-xl px-4 py-2 shadow-float">
                    <div class="text-[10px] text-muted font-mono uppercase tracking-wider font-bold">Pending Review
                    </div>
                    <div class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono">
                        <?php echo $count_pending; ?></div>
                </div>
                <div class="bg-surface border border-subtle rounded-xl px-4 py-2 shadow-float">
                    <div class="text-[10px] text-muted font-mono uppercase tracking-wider font-bold">Rejected</div>
                    <div class="text-lg font-black text-rose-600 dark:text-rose-400 font-mono">
                        <?php echo $count_rejected; ?></div>
                </div>
            </div>
        </div>

        <!-- Feedback Alert -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div
                class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span>Member verification status updated successfully.</span>
                </div>
                <a href="members.php" class="text-muted hover:text-primary text-[11px]">&times; Dismiss</a>
            </div>
        <?php endif; ?>

        <!-- Filter & Search Controls -->
        <form method="GET" action="members.php"
            class="bg-surface border border-subtle rounded-2xl p-4 mb-6 shadow-float flex flex-col md:flex-row gap-3 items-center justify-between">
            <div class="w-full md:w-96 relative">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Search by name, student ID, email, or username..." class="input-subtle text-xs">
            </div>
            <div class="w-full md:w-auto flex items-center gap-3">
                <select name="status" class="input-subtle text-xs">
                    <option value="">All Verification States</option>
                    <option value="Pending" <?php echo ($status_filter === 'Pending') ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="Verified" <?php echo ($status_filter === 'Verified') ? 'selected' : ''; ?>>Verified
                    </option>
                    <option value="Rejected" <?php echo ($status_filter === 'Rejected') ? 'selected' : ''; ?>>Rejected
                    </option>
                </select>
                <button type="submit" class="btn-accent py-2 text-xs">Apply</button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="members.php" class="btn-secondary py-2 text-xs">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Members Table -->
        <div class="bg-surface border border-subtle rounded-2xl overflow-hidden shadow-float">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-surface-subtle border-b border-subtle text-muted uppercase font-semibold text-[10px] tracking-wider">
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
                    <tbody class="divide-y divide-border-subtle text-primary">
                        <?php if ($members_res && mysqli_num_rows($members_res) > 0): ?>
                            <?php while ($m = mysqli_fetch_assoc($members_res)): ?>
                                <tr class="hover:bg-surface-subtle transition">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-primary text-sm">
                                            <?php echo htmlspecialchars($m['first_name'] . ' ' . $m['last_name']); ?>
                                        </div>
                                        <div class="text-[11px] text-muted font-mono">
                                            @<?php echo htmlspecialchars($m['username']); ?></div>
                                        <?php if (!empty($m['gender']) || !empty($m['dob'])): ?>
                                            <div class="text-[10px] text-muted mt-0.5">
                                                <?php echo htmlspecialchars(implode(' &bull; ', array_filter([$m['gender'] ?? null, $m['dob'] ?? null]))); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono text-muted">
                                        <?php echo htmlspecialchars($m['student_id'] ?? 'Not set'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-primary"><?php echo htmlspecialchars($m['university_email']); ?></div>
                                        <div class="text-[11px] text-muted font-mono">
                                            <?php echo htmlspecialchars($m['phone_number'] ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-muted">
                                        <?php echo htmlspecialchars($m['campus_address'] ?? 'Not set'); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap font-mono font-semibold text-primary">
                                        ৳<?php echo number_format((float) ($m['account_balance'] ?? 0), 2); ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <?php
                                        $st = $m['status'] ?? 'Pending';
                                        $badge = match ($st) {
                                            'Verified' => 'badge-verified',
                                            'Rejected' => 'badge-rejected',
                                            default => 'badge-pending'
                                        };
                                        ?>
                                        <span class="badge-subtle <?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($st); ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <form method="POST" action="members.php" class="inline-flex items-center gap-1.5">
                                            <input type="hidden" name="member_id" value="<?php echo (int) $m['member_id']; ?>">

                                            <?php if ($st !== 'Verified'): ?>
                                                <button type="submit" name="action" value="Verified"
                                                    class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 rounded-lg text-xs font-bold transition">
                                                    Verify
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($st !== 'Rejected'): ?>
                                                <button type="submit" name="action" value="Rejected"
                                                    class="px-2.5 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white border border-rose-500/30 rounded-lg text-xs font-bold transition">
                                                    Reject
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($st === 'Verified' || $st === 'Rejected'): ?>
                                                <button type="submit" name="action" value="Pending"
                                                    class="px-2.5 py-1 bg-surface-subtle text-muted hover:text-primary border border-subtle rounded-lg text-xs font-medium transition"
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
                                <td colspan="7" class="px-6 py-12 text-center text-muted">
                                    <div class="max-w-xs mx-auto text-center space-y-2">
                                        <svg class="w-8 h-8 text-muted mx-auto" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                        <div class="text-sm font-semibold text-primary">No member records found</div>
                                        <p class="text-xs text-muted">Try adjusting your search terms or filter selection.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="border-t border-subtle bg-surface py-4 px-6 text-center text-xs text-muted">
        Rentora Academic Platform &bull; Operations &amp; Moderation Console
    </footer>

    <script>
        var adminThemeBtn = document.getElementById('admin-theme-btn');
        if (adminThemeBtn) {
            adminThemeBtn.addEventListener('click', function () {
                var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                var newTheme = (currentTheme === 'dark') ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                if (newTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                try {
                    localStorage.setItem('rentora_theme', newTheme);
                } catch (e) { }
            });
        }
    </script>
</body>

</html>