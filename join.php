<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/includes/auth_guard.php');

$base_path = '.';
$page_title = 'Join the Development Monolith — Rentora Core Engineering';
$active_nav = 'join';

// Telemetry Extraction for Logged-in Members
$is_logged_in_member = (!empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'member');
$prefill = [
    'applicant_name'   => '',
    'university_email' => '',
    'student_id'       => '',
    'department'       => 'Computer Science & Engineering',
    'phone_number'     => '',
];

if ($is_logged_in_member) {
    try {
        $stmt = $pdo->prepare("SELECT first_name, last_name, university_email, student_id, phone_number FROM member WHERE member_id = ?");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $member_data = $stmt->fetch();
        if ($member_data) {
            $prefill['applicant_name']   = trim(($member_data['first_name'] ?? '') . ' ' . ($member_data['last_name'] ?? ''));
            $prefill['university_email'] = $member_data['university_email'] ?? '';
            $prefill['student_id']       = $member_data['student_id'] ?? '';
            $prefill['phone_number']     = $member_data['phone_number'] ?? '';
        }
    } catch (Exception $e) {
        // Fallback silently if member read fails
    }
}

// Handle Form Submission
$error_msg = '';
$success_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    $applicant_name       = trim($_POST['applicant_name'] ?? '');
    $university_email     = trim($_POST['university_email'] ?? '');
    $student_id           = trim($_POST['student_id'] ?? '');
    $department           = trim($_POST['department'] ?? 'Computer Science & Engineering');
    $phone_number         = trim($_POST['phone_number'] ?? '');
    $role_applied         = trim($_POST['role_applied'] ?? '');
    $portfolio_link       = trim($_POST['portfolio_link'] ?? '');
    $technical_skills     = trim($_POST['technical_skills'] ?? '');
    $statement_of_purpose = trim($_POST['statement_of_purpose'] ?? '');
    $member_id            = $is_logged_in_member ? (int)$_SESSION['user_id'] : null;

    if (empty($applicant_name) || empty($university_email) || empty($student_id) || empty($role_applied) || empty($statement_of_purpose)) {
        $error_msg = "Mandatory telemetry fields missing. Please provide your Name, University Email, Student ID, Target Role, and Statement of Purpose.";
    } elseif (!filter_var($university_email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid university communication node. Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO team_applications 
                (applicant_name, university_email, student_id, department, phone_number, role_applied, portfolio_link, technical_skills, statement_of_purpose, status, member_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
            ");
            $stmt->execute([
                $applicant_name,
                $university_email,
                $student_id,
                $department,
                $phone_number,
                $role_applied,
                $portfolio_link,
                $technical_skills,
                $statement_of_purpose,
                $member_id
            ]);

            $app_id = (int)$pdo->lastInsertId();
            $success_data = [
                'id'         => $app_id,
                'ref_code'   => 'RENTORA-DEV-' . str_pad((string)$app_id, 4, '0', STR_PAD_LEFT),
                'name'       => $applicant_name,
                'role'       => $role_applied,
                'email'      => $university_email,
                'timestamp'  => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $error_msg = "Direct schema mutation rejected by MySQL reactor: " . $e->getMessage();
        }
    }
}

require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/nav.php');
?>

<main class="flex-1 flex flex-col w-full bg-canvas">

  <!-- ═══════════════════════════════════════════════════════════════
       1. HERO VACUUM — Semantic Surface Elevated Monolith
  ════════════════════════════════════════════════════════════════ -->
  <section class="join-hero-vacuum bg-surface-elevated text-primary pt-14 pb-16 sm:pt-20 sm:pb-24 border-b border-subtle relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      
      <div class="flex flex-wrap items-center gap-3 mb-5">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-mono font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          RECRUITMENT MATRIX ACTIVE
        </span>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-surface-subtle text-primary border border-subtle text-xs font-mono">
          <span class="text-muted">Sector:</span>
          <span class="font-bold">team_applications</span>
        </span>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-xs font-mono">
          <span>Premier University CSE</span>
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-8">
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-primary leading-tight">
            Join the Rentora <br class="hidden sm:inline">
            <span class="text-accent">Development Team</span>
          </h1>
          <p class="mt-5 text-base sm:text-lg text-muted max-w-3xl leading-relaxed">
            Rentora Hub is engineered by Premier University BSc in Computer Science &amp; Engineering students to eradicate equipment scarcity across campus. We are opening our development pipeline to passionate campus engineers, architects, designers, and systems builders.
          </p>
          <div class="mt-8 flex flex-wrap gap-4 text-xs font-mono text-muted">
            <div class="flex items-center gap-2 bg-surface-subtle border border-subtle px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <span>4 Founding Architects</span>
            </div>
            <div class="flex items-center gap-2 bg-surface-subtle border border-subtle px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
              <span>Direct MySQL Schema Storage</span>
            </div>
            <div class="flex items-center gap-2 bg-surface-subtle border border-subtle px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
              <span>Zero Platform Fee Infrastructure</span>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4 flex justify-center lg:justify-end">
          <div class="w-full max-w-sm bg-surface border border-subtle rounded-2xl p-6 shadow-float">
            <div class="flex items-center gap-3 pb-4 border-b border-subtle">
              <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center text-accent font-bold font-mono">
                &lt;/&gt;
              </div>
              <div>
                <h3 class="text-sm font-bold text-primary">Engineering Cohort</h3>
                <p class="text-xs text-muted font-mono">Hazari Lane &amp; GEC Hub</p>
              </div>
            </div>
            <div class="mt-4 space-y-2.5 text-xs">
              <div class="flex justify-between text-muted">
                <span>Current Status:</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Applications Open</span>
              </div>
              <div class="flex justify-between text-muted">
                <span>Core Stack:</span>
                <span class="font-mono text-primary">PHP 8.2 &bull; MySQL &bull; Tailwind</span>
              </div>
              <div class="flex justify-between text-muted">
                <span>Target Cohort:</span>
                <span class="text-accent font-semibold">PU Students / CSE Dept</span>
              </div>
              <div class="flex justify-between text-muted">
                <span>Review Cycle:</span>
                <span class="text-primary">Continuous Telemetry</span>
              </div>
            </div>
            <div class="mt-6 pt-4 border-t border-subtle">
              <a href="#apply-terminal" class="btn-accent block w-full py-2.5 px-4 text-center rounded-xl text-white font-bold text-xs shadow-float">
                Jump to Application Terminal &darr;
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       2. FOUNDING MEMBERS — The Core Team
  ════════════════════════════════════════════════════════════════ -->
  <section class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    
    <div class="text-center max-w-3xl mx-auto mb-12">
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-accent/10 text-accent text-xs font-mono font-bold uppercase tracking-wider mb-3 border border-accent/20">
        Core Team
      </div>
      <h2 class="text-2xl sm:text-4xl font-extrabold text-primary tracking-tight">
        The Founding Development Team
      </h2>
      <p class="mt-3 text-sm sm:text-base text-muted leading-relaxed">
        Meet the four Premier University CSE students who built Rentora — connecting peers across campus for equipment sharing and verified rentals.
      </p>
    </div>

    <!-- Identity Nodes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

      <!-- Node 1: Aiman Hussain -->
      <div class="join-founder-card rounded-2xl p-6 border border-subtle bg-surface shadow-float flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/aiman_hussain.jpg" alt="Aiman Hussain" class="w-16 h-16 rounded-2xl object-cover object-center border-2 border-accent/40 shadow-sm ring-2 ring-accent/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-surface" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-[10px] font-mono font-extrabold tracking-wide">
              LEAD ARCHITECT
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-primary tracking-tight">Aiman Hussain</h3>
          <p class="text-xs font-semibold text-accent mb-2">Systems Architect &amp; Core Backend Lead</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-muted mb-4 bg-surface-subtle px-2 py-0.5 rounded border border-subtle">
            <span>ID:</span>
            <span class="font-bold text-primary">0222420005101197</span>
          </div>

          <p class="text-xs text-muted leading-relaxed mb-4">
            Architected the foundational system structure, database connection handling, and backend routing. Designed session management, security safeguards, and core application workflows.
          </p>
        </div>

        <div class="pt-4 border-t border-subtle space-y-2">
          <div class="text-[11px] font-mono text-muted">
            <span>Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">PHP</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">MySQL</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">System Architecture</span>
          </div>
        </div>
      </div>

      <!-- Node 2: Prattoy Barua Turja -->
      <div class="join-founder-card rounded-2xl p-6 border border-subtle bg-surface shadow-float flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/prattoy_turja.jpg" alt="Prattoy Barua Turja" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-accent/40 shadow-sm ring-2 ring-accent/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-surface" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-[10px] font-mono font-extrabold tracking-wide">
              FULL-STACK
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-primary tracking-tight">Prattoy Barua Turja</h3>
          <p class="text-xs font-semibold text-accent mb-2">Full-Stack Developer &amp; Database Lead</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-muted mb-4 bg-surface-subtle px-2 py-0.5 rounded border border-subtle">
            <span>ID:</span>
            <span class="font-bold text-primary">0222420005101171</span>
          </div>

          <p class="text-xs text-muted leading-relaxed mb-4">
            Developed the rental and equipment exchange workflows, relational database schemas, and item listing management. Built the handover verification tokens and dashboard features.
          </p>
        </div>

        <div class="pt-4 border-t border-subtle space-y-2">
          <div class="text-[11px] font-mono text-muted">
            <span>Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">PHP &amp; SQL</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">Database Design</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">REST APIs</span>
          </div>
        </div>
      </div>

      <!-- Node 3: Shreya Chakraborty -->
      <div class="join-founder-card rounded-2xl p-6 border border-subtle bg-surface shadow-float flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/shreya_chakraborty.jpg" alt="Shreya Chakraborty" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-accent/40 shadow-sm ring-2 ring-accent/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-surface" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-[10px] font-mono font-extrabold tracking-wide">
              UI/UX DESIGNER
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-primary tracking-tight">Shreya Chakraborty</h3>
          <p class="text-xs font-semibold text-accent mb-2">UI/UX Designer &amp; Frontend Systems</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-muted mb-4 bg-surface-subtle px-2 py-0.5 rounded border border-subtle">
            <span>ID:</span>
            <span class="font-bold text-primary">0222420005101183</span>
          </div>

          <p class="text-xs text-muted leading-relaxed mb-4">
            Designed the visual interface, responsive page layouts, and intuitive user workflows. Crafted the color palette, design consistency across components, and dark mode theme.
          </p>
        </div>

        <div class="pt-4 border-t border-subtle space-y-2">
          <div class="text-[11px] font-mono text-muted">
            <span>Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">UI/UX Design</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">Figma</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">Tailwind CSS</span>
          </div>
        </div>
      </div>

      <!-- Node 4: Samia Akter -->
      <div class="join-founder-card rounded-2xl p-6 border border-subtle bg-surface shadow-float flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/samia_akter.png" alt="Samia Akter" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-accent/40 shadow-sm ring-2 ring-accent/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-surface" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-[10px] font-mono font-extrabold tracking-wide">
              OPERATIONS &amp; QA
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-primary tracking-tight">Samia Akter</h3>
          <p class="text-xs font-semibold text-accent mb-2">Platform Operations &amp; Quality Lead</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-muted mb-4 bg-surface-subtle px-2 py-0.5 rounded border border-subtle">
            <span>ID:</span>
            <span class="font-bold text-primary">0222420005101172</span>
          </div>

          <p class="text-xs text-muted leading-relaxed mb-4">
            Coordinated campus handover safety guidelines, student verification standards, and deposit return workflows. Led platform testing and user experience quality checks.
          </p>
        </div>

        <div class="pt-4 border-t border-subtle space-y-2">
          <div class="text-[11px] font-mono text-muted">
            <span>Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">QA Testing</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">Campus Safety</span>
            <span class="bg-surface-subtle text-primary border border-subtle px-2 py-0.5 rounded">Operations</span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       3. RECRUITMENT VECTORS — Open Engineering Specializations
  ════════════════════════════════════════════════════════════════ -->
  <section class="py-12 bg-surface-subtle border-y border-subtle">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
          <span class="text-xs font-mono font-bold text-accent uppercase tracking-widest">Active Specializations</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight mt-1">
            Where Can You Inject Your Capabilities?
          </h2>
        </div>
        <p class="text-xs sm:text-sm text-muted max-w-md">
          Every role interfaces directly with live campus infrastructure. We value clean code, strong architectural intuition, and team-first execution.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-primary">
        
        <div class="p-5 rounded-2xl bg-surface border border-subtle shadow-float">
          <div class="w-8 h-8 rounded-lg bg-accent/10 border border-accent/20 text-accent flex items-center justify-center font-mono font-bold text-sm mb-3">01</div>
          <h3 class="text-base font-bold text-primary">Systems &amp; Backend Engineering</h3>
          <p class="text-xs text-muted mt-2 leading-relaxed">
            Expand relational MySQL pipelines, PDO connection pooling, role-based authorization vectors, and automated token invalidation timers.
          </p>
          <div class="mt-3 text-[11px] font-mono text-accent font-semibold">Stack: PHP 8.2, MySQL, Apache, Git</div>
        </div>

        <div class="p-5 rounded-2xl bg-surface border border-subtle shadow-float">
          <div class="w-8 h-8 rounded-lg bg-accent/10 border border-accent/20 text-accent flex items-center justify-center font-mono font-bold text-sm mb-3">02</div>
          <h3 class="text-base font-bold text-primary">Frontend UI/UX Systems</h3>
          <p class="text-xs text-muted mt-2 leading-relaxed">
            Maintain the trinary dark-matter aesthetic, zero-latency state machines, responsive typography, and frictionless SVG visual pipelines.
          </p>
          <div class="mt-3 text-[11px] font-mono text-accent font-semibold">Stack: Vanilla CSS, Tailwind, JS DOM, SVG</div>
        </div>

        <div class="p-5 rounded-2xl bg-surface border border-subtle shadow-float">
          <div class="w-8 h-8 rounded-lg bg-accent/10 border border-accent/20 text-accent flex items-center justify-center font-mono font-bold text-sm mb-3">03</div>
          <h3 class="text-base font-bold text-primary">Security, QA &amp; Campus Logistics</h3>
          <p class="text-xs text-muted mt-2 leading-relaxed">
            Execute manual and automated test matrices, verify deposit reconciliation logic, and coordinate physical handover points at campus landmarks.
          </p>
          <div class="mt-3 text-[11px] font-mono text-accent font-semibold">Stack: DBMS Auditing, Manual QA, Operations</div>
        </div>

      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       4. APPLICATION TERMINAL — Semantic Form Container
  ════════════════════════════════════════════════════════════════ -->
  <section id="apply-terminal" class="py-16 sm:py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    
    <div class="join-terminal-container rounded-3xl p-6 sm:p-10 bg-surface border border-subtle shadow-elevated">
      
      <!-- Terminal Header -->
      <div class="border-b border-subtle pb-6 mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-accent/10 text-accent border border-accent/20 text-xs font-mono font-bold tracking-wider">
            TERMINAL INPUT NODE
          </span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-primary tracking-tight mt-2">
            Submit Your Engineering Telemetry
          </h2>
          <p class="text-xs sm:text-sm text-muted mt-1">
            Data is written directly to the <code class="font-mono bg-surface-subtle px-1.5 py-0.5 rounded border border-subtle font-bold text-primary">team_applications</code> table in MySQL.
          </p>
        </div>

        <?php if ($is_logged_in_member): ?>
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-xs font-medium">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Linked to Student Account #<?php echo (int)$_SESSION['user_id']; ?></span>
          </div>
        <?php else: ?>
          <div class="text-xs font-mono text-muted bg-surface-subtle px-3 py-1.5 rounded-xl border border-subtle">
            Guest Protocol (Open Submission)
          </div>
        <?php endif; ?>
      </div>

      <!-- Success Notification Monolith -->
      <?php if ($success_data): ?>
        <div class="mb-8 p-6 rounded-2xl bg-emerald-500/10 text-primary border border-emerald-500/30 shadow-float">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-extrabold text-xl shrink-0">
              &check;
            </div>
            <div class="flex-1">
              <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400">Application Telemetry Locked Into MySQL Reactor!</h3>
              <p class="text-xs text-muted mt-1 leading-relaxed">
                Your credentials have been securely stored in the <code class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">team_applications</code> sector of <code class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">rentora_db</code>. The founding architects have received your transmission.
              </p>
              
              <div class="mt-4 p-4 rounded-xl bg-surface-subtle border border-subtle grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-mono">
                <div>
                  <span class="text-muted block text-[10px]">APPLICATION REFERENCE</span>
                  <span class="text-accent font-bold text-sm"><?php echo htmlspecialchars($success_data['ref_code']); ?></span>
                </div>
                <div>
                  <span class="text-muted block text-[10px]">RECORD ID</span>
                  <span class="text-primary font-bold text-sm">#<?php echo htmlspecialchars((string)$success_data['id']); ?></span>
                </div>
                <div>
                  <span class="text-muted block text-[10px]">APPLICANT</span>
                  <span class="text-primary"><?php echo htmlspecialchars($success_data['name']); ?></span>
                </div>
                <div>
                  <span class="text-muted block text-[10px]">TARGET ROLE</span>
                  <span class="text-emerald-600 dark:text-emerald-400 font-bold"><?php echo htmlspecialchars($success_data['role']); ?></span>
                </div>
              </div>

              <div class="mt-4 flex gap-3">
                <a href="<?php echo $base_path; ?>/join.php" class="btn-secondary px-3.5 py-1.5 rounded-lg text-xs font-semibold">
                  Submit Another Telemetry Node
                </a>
                <a href="<?php echo $base_path; ?>/about.php" class="btn-accent px-3.5 py-1.5 rounded-lg text-white text-xs font-bold">
                  Return to About Rentora &rarr;
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Error Notification Monolith -->
      <?php if (!empty($error_msg)): ?>
        <div class="mb-8 p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-xs flex items-center gap-3">
          <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <div class="font-medium"><?php echo htmlspecialchars($error_msg); ?></div>
        </div>
      <?php endif; ?>

      <!-- Application Form -->
      <form action="join.php#apply-terminal" method="POST" class="space-y-6">
        <input type="hidden" name="action" value="submit_application">

        <!-- Grid 1: Basic Telemetry -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          
          <div>
            <label for="applicant_name" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Full Legal / Campus Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="applicant_name" name="applicant_name" required
              value="<?php echo htmlspecialchars($_POST['applicant_name'] ?? $prefill['applicant_name']); ?>"
              placeholder="e.g. Shakib Al Hasan"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium">
          </div>

          <div>
            <label for="university_email" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              University / Personal Email <span class="text-rose-500">*</span>
            </label>
            <input type="email" id="university_email" name="university_email" required
              value="<?php echo htmlspecialchars($_POST['university_email'] ?? $prefill['university_email']); ?>"
              placeholder="e.g. student_46000@bscse.puc.ac.bd"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium">
          </div>

          <div>
            <label for="student_id" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Premier University Student ID <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="student_id" name="student_id" required
              value="<?php echo htmlspecialchars($_POST['student_id'] ?? $prefill['student_id']); ?>"
              placeholder="e.g. 0222420005101000"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-mono font-medium">
          </div>

          <div>
            <label for="department" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Academic Department <span class="text-rose-500">*</span>
            </label>
            <select id="department" name="department" required
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium">
              <?php
                $depts = [
                    'Computer Science & Engineering',
                    'Electrical & Electronic Engineering',
                    'Business Administration',
                    'Law',
                    'Economics',
                    'English Literature'
                ];
                $cur_dept = $_POST['department'] ?? $prefill['department'];
                foreach ($depts as $d):
              ?>
                <option value="<?php echo htmlspecialchars($d); ?>" <?php echo ($cur_dept === $d) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($d); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>

        <!-- Grid 2: Role & Communications -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          
          <div>
            <label for="role_applied" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Target Specialization Vector <span class="text-rose-500">*</span>
            </label>
            <select id="role_applied" name="role_applied" required
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium">
              <option value="" disabled <?php echo empty($_POST['role_applied']) ? 'selected' : ''; ?>>Select target role...</option>
              <option value="Systems &amp; Backend Engineer" <?php echo (($_POST['role_applied'] ?? '') === 'Systems &amp; Backend Engineer') ? 'selected' : ''; ?>>
                Systems &amp; Backend Engineer (PHP / MySQL)
              </option>
              <option value="Frontend UI/UX Systems" <?php echo (($_POST['role_applied'] ?? '') === 'Frontend UI/UX Systems') ? 'selected' : ''; ?>>
                Frontend UI/UX Systems (HTML5 / CSS / DOM)
              </option>
              <option value="Database Architect &amp; Performance" <?php echo (($_POST['role_applied'] ?? '') === 'Database Architect &amp; Performance') ? 'selected' : ''; ?>>
                Database Architect &amp; Performance (ACID / Schema)
              </option>
              <option value="Security, QA &amp; Platform Testing" <?php echo (($_POST['role_applied'] ?? '') === 'Security, QA &amp; Platform Testing') ? 'selected' : ''; ?>>
                Security, QA &amp; Platform Testing
              </option>
              <option value="Campus Community &amp; Handover Coordinator" <?php echo (($_POST['role_applied'] ?? '') === 'Campus Community &amp; Handover Coordinator') ? 'selected' : ''; ?>>
                Campus Community &amp; Handover Coordinator
              </option>
            </select>
          </div>

          <div>
            <label for="phone_number" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Mobile Contact Number <span class="text-rose-500">*</span>
            </label>
            <input type="tel" id="phone_number" name="phone_number" required
              value="<?php echo htmlspecialchars($_POST['phone_number'] ?? $prefill['phone_number']); ?>"
              placeholder="e.g. 01700000000"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-mono font-medium">
          </div>

        </div>

        <!-- Links and Skills -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          
          <div>
            <label for="portfolio_link" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Portfolio / GitHub / LinkedIn / Drive Link
            </label>
            <input type="url" id="portfolio_link" name="portfolio_link"
              value="<?php echo htmlspecialchars($_POST['portfolio_link'] ?? ''); ?>"
              placeholder="https://github.com/username"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium">
          </div>

          <div>
            <label for="technical_skills" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
              Technical Stack &amp; Skills
            </label>
            <input type="text" id="technical_skills" name="technical_skills"
              value="<?php echo htmlspecialchars($_POST['technical_skills'] ?? ''); ?>"
              placeholder="e.g. PHP, MySQL, JavaScript, Git, Figma, Python"
              class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-mono font-medium">
          </div>

        </div>

        <!-- Statement of Purpose -->
        <div>
          <label for="statement_of_purpose" class="block text-xs font-bold text-primary uppercase tracking-wider mb-1.5">
            Mission Alignment &amp; Statement of Purpose <span class="text-rose-500">*</span>
          </label>
          <textarea id="statement_of_purpose" name="statement_of_purpose" rows="4" required
            placeholder="Explain why you want to join the Rentora Development Monolith and what campus problems you want to solve..."
            class="input-subtle w-full px-3.5 py-2.5 rounded-xl text-sm font-medium"><?php echo htmlspecialchars($_POST['statement_of_purpose'] ?? ''); ?></textarea>
        </div>

        <!-- Terminal Submission Action -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-xs text-muted">
            By submitting, your telemetry is permanently recorded in the university database for evaluation by the founding team.
          </p>
          <button type="submit"
            class="btn-accent w-full sm:w-auto px-8 py-3 rounded-xl text-white font-extrabold text-sm shadow-float shrink-0 flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Lock Telemetry to Schema</span>
          </button>
        </div>

      </form>

    </div>

  </section>

</main>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
