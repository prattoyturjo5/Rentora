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

<main class="flex-1 flex flex-col w-full bg-[#F8FAFC]">

  <!-- ═══════════════════════════════════════════════════════════════
       1. HERO VACUUM — Dense Night Blue (#151B54) Monolith
  ════════════════════════════════════════════════════════════════ -->
  <section class="join-hero-vacuum bg-[#151B54] text-white pt-14 pb-16 sm:pt-20 sm:pb-24 border-b border-white/10 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      
      <div class="flex flex-wrap items-center gap-3 mb-5">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-mono font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          RECRUITMENT MATRIX ACTIVE
        </span>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-white/10 text-sky-200 border border-white/15 text-xs font-mono">
          <span>Schema Sector:</span>
          <span class="font-bold text-white">rentora_db.team_applications</span>
        </span>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-mono">
          <span>Premier University CSE</span>
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-8">
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
            Join the Rentora <br class="hidden sm:inline">
            <span class="text-sky-300">Development Team</span>
          </h1>
          <p class="mt-5 text-base sm:text-lg text-slate-300 max-w-3xl leading-relaxed">
            Rentora Hub is engineered by Premier University BSc in Computer Science &amp; Engineering students to eradicate equipment scarcity across campus. We are opening our development pipeline to passionate campus engineers, architects, designers, and systems builders.
          </p>
          <div class="mt-8 flex flex-wrap gap-4 text-xs font-mono text-slate-300">
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <span>4 Founding Architects</span>
            </div>
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
              <span>Direct MySQL Schema Storage</span>
            </div>
            <div class="flex items-center gap-2 bg-white/5 border border-white/10 px-3.5 py-2 rounded-xl">
              <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
              <span>Zero Platform Fee Infrastructure</span>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4 flex justify-center lg:justify-end">
          <div class="w-full max-w-sm bg-[#0E133C] border border-white/15 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center gap-3 pb-4 border-b border-white/10">
              <div class="w-10 h-10 rounded-xl bg-blue-600/30 border border-blue-400/40 flex items-center justify-center text-sky-300 font-bold font-mono">
                &lt;/&gt;
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">Engineering Cohort</h3>
                <p class="text-xs text-slate-400 font-mono">Hazari Lane &amp; GEC Hub</p>
              </div>
            </div>
            <div class="mt-4 space-y-2.5 text-xs">
              <div class="flex justify-between text-slate-300">
                <span class="text-slate-400">Current Status:</span>
                <span class="text-emerald-400 font-bold">Applications Open</span>
              </div>
              <div class="flex justify-between text-slate-300">
                <span class="text-slate-400">Core Stack:</span>
                <span class="font-mono text-white">PHP 8.2 &bull; MySQL &bull; Tailwind</span>
              </div>
              <div class="flex justify-between text-slate-300">
                <span class="text-slate-400">Target Cohort:</span>
                <span class="text-sky-300 font-semibold">PU Students / CSE Dept</span>
              </div>
              <div class="flex justify-between text-slate-300">
                <span class="text-slate-400">Review Cycle:</span>
                <span class="text-white">Continuous Telemetry</span>
              </div>
            </div>
            <div class="mt-6 pt-4 border-t border-white/10">
              <a href="#apply-terminal" class="block w-full py-2.5 px-4 text-center rounded-xl bg-sky-500 hover:bg-sky-400 text-[#151B54] font-bold text-xs shadow-md transition-none">
                Jump to Application Terminal &darr;
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       2. FOUNDING MEMBERS' IDENTITY NODES — The 4 Core Architects
  ════════════════════════════════════════════════════════════════ -->
  <section class="py-16 sm:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    
    <div class="text-center max-w-3xl mx-auto mb-12">
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#151B54]/10 text-[#151B54] text-xs font-mono font-bold uppercase tracking-wider mb-3">
        Core Engineering Nexus
      </div>
      <h2 class="text-2xl sm:text-4xl font-extrabold text-[#151B54] tracking-tight">
        The Founding Development Nodes
      </h2>
      <p class="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed">
        Meet the four founding Premier University CSE engineers who built the Rentora core transactional matrix, verified handover protocol, and anti-gravity design system.
      </p>
    </div>

    <!-- Identity Nodes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

      <!-- Node 1: Aiman Hussain -->
      <div class="join-founder-card rounded-2xl p-6 border border-slate-200 bg-white shadow-sm flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/aiman_hussain.jpg" alt="Aiman Hussain" class="w-16 h-16 rounded-2xl object-cover object-center border-2 border-blue-400/40 shadow-md ring-2 ring-blue-500/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 border border-blue-300 text-[10px] font-mono font-extrabold tracking-wide">
              LEAD ARCHITECT
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-[#151B54] tracking-tight">Aiman Hussain</h3>
          <p class="text-xs font-semibold text-sky-600 mb-2">Systems Architect &amp; Core Engine Lead</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-slate-500 mb-4 bg-slate-100 px-2 py-0.5 rounded">
            <span>ID:</span>
            <span class="font-bold text-slate-700">0222420005101197</span>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed mb-4">
            Forged the zero-gravity architectural pipeline, high-throughput PDO engine, and binary SVG hologram studio. Designed the mathematical centering matrices and state machines.
          </p>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
          <div class="text-[11px] font-mono text-slate-500">
            <span class="text-slate-400">Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">PHP Core</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">MySQL ACID</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Architectural DOM</span>
          </div>
        </div>
      </div>

      <!-- Node 2: Prattoy Barua Turja -->
      <div class="join-founder-card rounded-2xl p-6 border border-slate-200 bg-white shadow-sm flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/prattoy_turja.jpg" alt="Prattoy Barua Turja" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-purple-400/40 shadow-md ring-2 ring-purple-500/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-300 text-[10px] font-mono font-extrabold tracking-wide">
              FULL-STACK
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-[#151B54] tracking-tight">Prattoy Barua Turja</h3>
          <p class="text-xs font-semibold text-purple-600 mb-2">Full-Stack Engineer &amp; Database Strategist</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-slate-500 mb-4 bg-slate-100 px-2 py-0.5 rounded">
            <span>ID:</span>
            <span class="font-bold text-slate-700">0222420005101171</span>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed mb-4">
            Engineered the relational exchange agreement schemas, multi-table joins, equipment category indexes, and transactional token verification backend.
          </p>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
          <div class="text-[11px] font-mono text-slate-500">
            <span class="text-slate-400">Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Relational SQL</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Auth Vectors</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Backend API</span>
          </div>
        </div>
      </div>

      <!-- Node 3: Shreya Chakraborty -->
      <div class="join-founder-card rounded-2xl p-6 border border-slate-200 bg-white shadow-sm flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/shreya_chakraborty.jpg" alt="Shreya Chakraborty" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-rose-400/40 shadow-md ring-2 ring-rose-500/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 border border-rose-300 text-[10px] font-mono font-extrabold tracking-wide">
              UI/UX STRATEGIST
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-[#151B54] tracking-tight">Shreya Chakraborty</h3>
          <p class="text-xs font-semibold text-rose-600 mb-2">UI/UX Strategist &amp; Frontend Systems</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-slate-500 mb-4 bg-slate-100 px-2 py-0.5 rounded">
            <span>ID:</span>
            <span class="font-bold text-slate-700">0222420005101183</span>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed mb-4">
            Architected the Trinary chromatic stratification (#151B54, #EFF3FF, #FFFFFF), interaction density heuristics, profile singularity pocket, and accessible form UX.
          </p>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
          <div class="text-[11px] font-mono text-slate-500">
            <span class="text-slate-400">Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">UI Stratification</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Figma Design</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Tailwind CSS</span>
          </div>
        </div>
      </div>

      <!-- Node 4: Samia Akter -->
      <div class="join-founder-card rounded-2xl p-6 border border-slate-200 bg-white shadow-sm flex flex-col justify-between">
        <div>
          <!-- Header & Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="relative shrink-0">
              <img src="<?php echo $base_path; ?>/assets/images/founders/samia_akter.png" alt="Samia Akter" class="w-16 h-16 rounded-2xl object-cover object-top border-2 border-emerald-400/40 shadow-md ring-2 ring-emerald-500/20">
              <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 ring-2 ring-white" title="Verified Founder"></span>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-mono font-extrabold tracking-wide">
              OPERATIONS &amp; QA
            </span>
          </div>

          <h3 class="text-lg font-extrabold text-[#151B54] tracking-tight">Samia Akter</h3>
          <p class="text-xs font-semibold text-emerald-600 mb-2">Platform Operations &amp; Quality Lead</p>
          <div class="inline-flex items-center gap-1 text-[11px] font-mono text-slate-500 mb-4 bg-slate-100 px-2 py-0.5 rounded">
            <span>ID:</span>
            <span class="font-bold text-slate-700">0222420005101172</span>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed mb-4">
            Formulated the physical campus handover guidelines, student verification guardrails, deposit refund checks, and end-to-end platform regression testing.
          </p>
        </div>

        <div class="pt-4 border-t border-slate-100 space-y-2">
          <div class="text-[11px] font-mono text-slate-500">
            <span class="text-slate-400">Discipline:</span> BSc in CSE, Premier University
          </div>
          <div class="flex flex-wrap gap-1 text-[10px] font-mono font-semibold">
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Security Protocols</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">QA Regression</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Campus Ops</span>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       3. RECRUITMENT VECTORS — Open Engineering Specializations
  ════════════════════════════════════════════════════════════════ -->
  <section class="py-12 bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
          <span class="text-xs font-mono font-bold text-sky-600 uppercase tracking-widest">Active Specializations</span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-[#151B54] tracking-tight mt-1">
            Where Can You Inject Your Capabilities?
          </h2>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md">
          Every role interfaces directly with live campus infrastructure. We value clean code, strong architectural intuition, and team-first execution.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-slate-700">
        
        <div class="p-5 rounded-2xl bg-[#EFF3FF] border border-[#CBD5E1]">
          <div class="w-8 h-8 rounded-lg bg-[#151B54] text-white flex items-center justify-center font-mono font-bold text-sm mb-3">01</div>
          <h3 class="text-base font-bold text-[#151B54]">Systems &amp; Backend Engineering</h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Expand relational MySQL pipelines, PDO connection pooling, role-based authorization vectors, and automated token invalidation timers.
          </p>
          <div class="mt-3 text-[11px] font-mono text-[#151B54] font-semibold">Stack: PHP 8.2, MySQL, Apache, Git</div>
        </div>

        <div class="p-5 rounded-2xl bg-[#EFF3FF] border border-[#CBD5E1]">
          <div class="w-8 h-8 rounded-lg bg-[#151B54] text-white flex items-center justify-center font-mono font-bold text-sm mb-3">02</div>
          <h3 class="text-base font-bold text-[#151B54]">Frontend UI/UX Systems</h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Maintain the trinary dark-matter aesthetic, zero-latency state machines, responsive typography, and frictionless SVG visual pipelines.
          </p>
          <div class="mt-3 text-[11px] font-mono text-[#151B54] font-semibold">Stack: Vanilla CSS, Tailwind, JS DOM, SVG</div>
        </div>

        <div class="p-5 rounded-2xl bg-[#EFF3FF] border border-[#CBD5E1]">
          <div class="w-8 h-8 rounded-lg bg-[#151B54] text-white flex items-center justify-center font-mono font-bold text-sm mb-3">03</div>
          <h3 class="text-base font-bold text-[#151B54]">Security, QA &amp; Campus Logistics</h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Execute manual and automated test matrices, verify deposit reconciliation logic, and coordinate physical handover points at campus landmarks.
          </p>
          <div class="mt-3 text-[11px] font-mono text-[#151B54] font-semibold">Stack: DBMS Auditing, Manual QA, Operations</div>
        </div>

      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════════
       4. APPLICATION TERMINAL — Photonic Ice (#EFF3FF) & Night Blue Form
  ════════════════════════════════════════════════════════════════ -->
  <section id="apply-terminal" class="py-16 sm:py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    
    <div class="join-terminal-container rounded-3xl p-6 sm:p-10 bg-[#EFF3FF] border border-[#CBD5E1] shadow-xl">
      
      <!-- Terminal Header -->
      <div class="border-b border-[#CBD5E1] pb-6 mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#151B54] text-white text-xs font-mono font-bold tracking-wider">
            TERMINAL INPUT NODE
          </span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-[#151B54] tracking-tight mt-2">
            Submit Your Engineering Telemetry
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 mt-1">
            Data is written directly to the <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-300 font-bold text-[#151B54]">team_applications</code> table in MySQL.
          </p>
        </div>

        <?php if ($is_logged_in_member): ?>
          <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>Linked to Student Account #<?php echo (int)$_SESSION['user_id']; ?></span>
          </div>
        <?php else: ?>
          <div class="text-xs font-mono text-slate-500 bg-white/70 px-3 py-1.5 rounded-xl border border-slate-300">
            Guest Protocol (Open Submission)
          </div>
        <?php endif; ?>
      </div>

      <!-- Success Notification Monolith -->
      <?php if ($success_data): ?>
        <div class="mb-8 p-6 rounded-2xl bg-emerald-950 text-white border border-emerald-500/40 shadow-xl">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-[#151B54] font-extrabold text-xl shrink-0">
              &check;
            </div>
            <div class="flex-1">
              <h3 class="text-base font-bold text-emerald-300">Application Telemetry Locked Into MySQL Reactor!</h3>
              <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                Your credentials have been securely stored in the <code class="text-emerald-400 font-mono font-bold">team_applications</code> sector of <code class="text-emerald-400 font-mono font-bold">rentora_db</code>. The founding architects have received your transmission.
              </p>
              
              <div class="mt-4 p-4 rounded-xl bg-black/40 border border-white/10 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs font-mono">
                <div>
                  <span class="text-slate-400 block text-[10px]">APPLICATION REFERENCE</span>
                  <span class="text-white font-bold text-sm text-sky-400"><?php echo htmlspecialchars($success_data['ref_code']); ?></span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px]">RECORD ID</span>
                  <span class="text-white font-bold text-sm">#<?php echo htmlspecialchars((string)$success_data['id']); ?></span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px]">APPLICANT</span>
                  <span class="text-slate-200"><?php echo htmlspecialchars($success_data['name']); ?></span>
                </div>
                <div>
                  <span class="text-slate-400 block text-[10px]">TARGET ROLE</span>
                  <span class="text-emerald-400 font-bold"><?php echo htmlspecialchars($success_data['role']); ?></span>
                </div>
              </div>

              <div class="mt-4 flex gap-3">
                <a href="<?php echo $base_path; ?>/join.php" class="px-3.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition-none">
                  Submit Another Telemetry Node
                </a>
                <a href="<?php echo $base_path; ?>/about.php" class="px-3.5 py-1.5 rounded-lg bg-sky-500 hover:bg-sky-400 text-[#151B54] text-xs font-bold transition-none">
                  Return to About Rentora &rarr;
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Error Notification Monolith -->
      <?php if (!empty($error_msg)): ?>
        <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
          <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <div class="font-medium"><?php echo htmlspecialchars($error_msg); ?></div>
        </div>
      <?php endif; ?>

      <!-- Application Form -->
      <form action="join.php#apply-terminal" method="POST" class="space-y-6">
        <input type="hidden" name="action" value="submit_application">

        <!-- Grid 1: Basic Telemetry -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          
          <div>
            <label for="applicant_name" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Full Legal / Campus Name <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="applicant_name" name="applicant_name" required
              value="<?php echo htmlspecialchars($_POST['applicant_name'] ?? $prefill['applicant_name']); ?>"
              placeholder="e.g. Shakib Al Hasan"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

          <div>
            <label for="university_email" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              University / Personal Email <span class="text-rose-500">*</span>
            </label>
            <input type="email" id="university_email" name="university_email" required
              value="<?php echo htmlspecialchars($_POST['university_email'] ?? $prefill['university_email']); ?>"
              placeholder="e.g. student_46000@bscse.puc.ac.bd"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

          <div>
            <label for="student_id" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Premier University Student ID <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="student_id" name="student_id" required
              value="<?php echo htmlspecialchars($_POST['student_id'] ?? $prefill['student_id']); ?>"
              placeholder="e.g. 0222420005101000"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm font-mono border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

          <div>
            <label for="department" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Academic Department <span class="text-rose-500">*</span>
            </label>
            <select id="department" name="department" required
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
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
            <label for="role_applied" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Target Specialization Vector <span class="text-rose-500">*</span>
            </label>
            <select id="role_applied" name="role_applied" required
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
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
            <label for="phone_number" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Mobile Contact Number <span class="text-rose-500">*</span>
            </label>
            <input type="tel" id="phone_number" name="phone_number" required
              value="<?php echo htmlspecialchars($_POST['phone_number'] ?? $prefill['phone_number']); ?>"
              placeholder="e.g. 01700000000"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm font-mono border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

        </div>

        <!-- Links and Skills -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          
          <div>
            <label for="portfolio_link" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Portfolio / GitHub / LinkedIn / Drive Link
            </label>
            <input type="url" id="portfolio_link" name="portfolio_link"
              value="<?php echo htmlspecialchars($_POST['portfolio_link'] ?? ''); ?>"
              placeholder="https://github.com/username"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

          <div>
            <label for="technical_skills" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
              Technical Stack &amp; Skills
            </label>
            <input type="text" id="technical_skills" name="technical_skills"
              value="<?php echo htmlspecialchars($_POST['technical_skills'] ?? ''); ?>"
              placeholder="e.g. PHP, MySQL, JavaScript, Git, Figma, Python"
              class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm font-mono border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none">
          </div>

        </div>

        <!-- Statement of Purpose -->
        <div>
          <label for="statement_of_purpose" class="block text-xs font-bold text-[#151B54] uppercase tracking-wider mb-1.5">
            Mission Alignment &amp; Statement of Purpose <span class="text-rose-500">*</span>
          </label>
          <textarea id="statement_of_purpose" name="statement_of_purpose" rows="4" required
            placeholder="Explain why you want to join the Rentora Development Monolith and what campus problems you want to solve..."
            class="join-input-field w-full px-3.5 py-2.5 rounded-xl text-sm border border-[#CBD5E1] bg-white focus:outline-none focus:ring-2 focus:ring-[#151B54] focus:border-[#151B54] transition-none"><?php echo htmlspecialchars($_POST['statement_of_purpose'] ?? ''); ?></textarea>
        </div>

        <!-- Terminal Submission Action -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-xs text-slate-500">
            By submitting, your telemetry is permanently recorded in the university database for evaluation by the founding team.
          </p>
          <button type="submit"
            class="join-submit-btn w-full sm:w-auto px-8 py-3 rounded-xl bg-[#151B54] hover:bg-[#1E2570] text-white font-extrabold text-sm shadow-md transition-none shrink-0 flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span>Lock Telemetry to Schema</span>
          </button>
        </div>

      </form>

    </div>

  </section>

</main>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
