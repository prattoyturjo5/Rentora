<?php
session_start();
require_once(__DIR__ . '/includes/auth_guard.php');

$base_path = '.';
$page_title = 'About Us — Rentora Hub | Campus Equipment Exchange & Rental';
$active_nav = 'about';
require_once(__DIR__ . '/includes/header.php');
require_once(__DIR__ . '/includes/nav.php');
?>

<style>
/* ── Fade-up animation ── */
@keyframes ab-fadeUp {
  from { opacity:0; transform:translateY(26px); }
  to   { opacity:1; transform:translateY(0);    }
}
.ab-fade { opacity:0; }
.ab-fade.visible { animation: ab-fadeUp 0.55s cubic-bezier(0.16,1,0.3,1) forwards; }
.ab-fade-d1.visible { animation-delay:.05s; }
.ab-fade-d2.visible { animation-delay:.13s; }
.ab-fade-d3.visible { animation-delay:.21s; }
.ab-fade-d4.visible { animation-delay:.29s; }
.ab-fade-d5.visible { animation-delay:.37s; }
.ab-fade-d6.visible { animation-delay:.45s; }
@media (prefers-reduced-motion:reduce) {
  .ab-fade, .ab-fade.visible { animation:none; opacity:1; }
}

/* ── Hero blobs ── */
.ab-blob {
  position:absolute; border-radius:9999px;
  filter:blur(80px); pointer-events:none; opacity:.18;
}

/* ── SVG wave divider ── */
.ab-wave { display:block; line-height:0; margin-bottom:-2px; }
.ab-wave svg { display:block; width:100%; height:auto; }

/* ── Card hover lift ── */
.ab-card {
  transition: transform 200ms cubic-bezier(0.16,1,0.3,1),
              box-shadow 200ms cubic-bezier(0.16,1,0.3,1);
}
.ab-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px -8px rgba(15,23,42,.13); }

/* ── Feature card (rent/exchange) ── */
.ab-feat {
  background:rgba(255,255,255,.93);
  backdrop-filter:blur(8px);
  border:1px solid rgba(203,213,225,.7);
  border-radius:20px;
  box-shadow:0 4px 18px -4px rgba(15,23,42,.08);
  transition:transform 200ms ease, box-shadow 200ms ease;
}
.ab-feat:hover { transform:translateY(-4px); box-shadow:0 14px 36px -8px rgba(15,23,42,.13); }

/* ── Step number circle ── */
.ab-num {
  width:48px; height:48px; border-radius:50%;
  background:linear-gradient(135deg,#1d4ed8,#2563eb);
  color:#fff; font-size:.85rem; font-weight:800;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; position:relative; z-index:1;
  box-shadow:0 4px 14px -2px rgba(37,99,235,.35);
}

/* ── Connector line (desktop timeline) ── */
.ab-step-wrap { position:relative; }
.ab-connector {
  position:absolute; top:24px;
  left:calc(50% + 28px); right:0;
  height:2px;
  background:linear-gradient(90deg,#bfdbfe,#e0e7ff);
}

/* ── Vision quote ── */
.ab-quote { border-left:4px solid #3b82f6; }

/* ── Benefit tile ── */
.ab-benefit {
  background:#fff; border:1px solid #e2e8f0;
  border-radius:16px; padding:1.5rem;
  box-shadow:0 2px 8px rgba(0,0,0,.04);
  transition:transform 180ms ease, box-shadow 180ms ease;
}
.ab-benefit:hover { transform:translateY(-3px); box-shadow:0 8px 22px -4px rgba(15,23,42,.09); }

/* ── Trust cards ── */
.ab-trust-card {
  background: #FFFFFF;
  border: 1px solid #D9E2EF;
  border-radius: 1rem;
  box-shadow: 0 4px 14px rgba(15, 35, 65, 0.06);
  transition: transform 200ms ease, box-shadow 200ms ease;
}
.ab-trust-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 22px rgba(15, 35, 65, 0.12);
}
</style>

<!-- ═══════════════════════════════════════════════════════════════
     01  HERO
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-b from-navy-900 via-navy-900 to-slate-900 text-white overflow-hidden pt-20 pb-24 sm:pt-24 sm:pb-32">
  <div class="ab-blob w-96 h-96 bg-blue-500" style="top:-6rem;right:-4rem;"></div>
  <div class="ab-blob w-72 h-72 bg-primary-600" style="bottom:0;left:-3rem;"></div>
  <div class="ab-blob w-56 h-56 bg-emerald-500" style="top:50%;left:50%;transform:translate(-50%,-50%);opacity:.07;"></div>
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px;"></div>

  <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade ab-fade-d1 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-blue-200 mb-6 backdrop-blur-sm">
      <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
      Campus Equipment Exchange &amp; Rental Hub
    </div>

    <h1 class="ab-fade ab-fade-d2 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight max-w-3xl mx-auto">
      About <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">Rentora Hub</span>
    </h1>

    <p class="ab-fade ab-fade-d3 mt-5 text-lg sm:text-xl text-blue-100 font-medium max-w-2xl mx-auto leading-relaxed">
      Making campus equipment easier to rent, exchange, and share.
    </p>

    <p class="ab-fade ab-fade-d4 mt-4 text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
      Rentora Hub is a campus-focused equipment rental and exchange platform designed to help university students access the gear they need — without the cost of buying everything themselves.
    </p>

    <div class="ab-fade ab-fade-d5 mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
      <a href="<?php echo $base_path; ?>/index.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-0.5 hover:shadow-blue-600/50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Equipment
      </a>
      <a href="<?php echo $base_path; ?>/user/add_item.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-semibold backdrop-blur-sm transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        List Your Equipment
      </a>
    </div>

    <div class="ab-fade ab-fade-d6 mt-14 flex justify-center animate-bounce">
      <div class="flex flex-col items-center gap-1 text-slate-400 text-xs font-medium">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        Scroll to learn more
      </div>
    </div>
  </div>

  <div class="ab-wave absolute bottom-0 left-0 right-0">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,40 C480,70 960,10 1440,40 L1440,60 L0,60 Z" fill="#ffffff"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     02  WHAT IS RENTORA HUB?
════════════════════════════════════════════════════════════════ -->
<section class="bg-white py-20 sm:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">The Platform</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">What is Rentora Hub?</h2>
      <p class="mt-4 text-slate-500 max-w-2xl mx-auto text-base leading-relaxed">
        A student-focused marketplace where verified students can rent, lend, and exchange useful equipment within their university community.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

      <div class="ab-fade ab-fade-d1 space-y-5 text-slate-600 leading-relaxed text-base">
        <p>University life demands access to a wide range of equipment — from scientific calculators and lab instruments to cameras, drafting tools, and IoT project kits. Purchasing all of this outright is expensive, especially for equipment needed only for one semester or a single project.</p>
        <p>Rentora Hub connects students who <strong class="text-navy-900">need</strong> equipment with students who <strong class="text-navy-900">own</strong> equipment they aren't currently using — creating a practical peer-to-peer sharing economy right on campus.</p>
        <p>Every transaction happens between verified Premier University students, with in-person handover at agreed campus spots and cash-only payment — no online payment complexity, no hidden platform fees.</p>
      </div>

      <div class="ab-fade ab-fade-d2 bg-slate-50 rounded-2xl border border-slate-200 p-6 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Equipment you'll find on Rentora</p>
        <div class="flex flex-wrap gap-2">
          <?php
          $gear = [
            ['🧮','Scientific Calculators'],['🔬','Lab Equipment'],['📷','Cameras'],
            ['📐','Drafting Tools'],['🤖','IoT & Arduino Kits'],['💻','Electronics'],
            ['📚','Books & Stationery'],['🎸','Musical Instruments'],['⚽','Sports Equipment'],
            ['🔧','Project Tools'],['🎒','Campus Gear'],['📡','Electronic Modules'],
          ];
          foreach ($gear as [$ico,$lbl]): ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs font-medium text-slate-700 shadow-sm">
              <span><?php echo $ico; ?></span><?php echo htmlspecialchars($lbl); ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     03  WHY RENTORA? — Problem cards
════════════════════════════════════════════════════════════════ -->
<section class="bg-slate-50 py-20 sm:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">The Problem</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Why Rentora Hub?</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">Students face real equipment challenges. Rentora was built to solve them.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php
      $problems = [
        ['💸','bg-rose-50 text-rose-600','Expensive Purchases',
         'Buying specialized equipment for a single project or course drains student budgets unnecessarily.'],
        ['📦','bg-amber-50 text-amber-600','Equipment Sitting Idle',
         'After a course ends, useful equipment gathers dust instead of being put to use by fellow students.'],
        ['🔍','bg-blue-50 text-blue-600','Hard to Find Nearby',
         'Sourcing specialized campus equipment from general markets is inconvenient, slow, and uncertain.'],
        ['💰','bg-emerald-50 text-emerald-600','No Affordable Alternative',
         'Students needed a peer-to-peer option to access equipment affordably — without buying outright.'],
        ['🏪','bg-purple-50 text-purple-600','No Dedicated Campus Space',
         'General marketplaces aren\'t built for campus life — there was no focused platform for students.'],
        ['🤝','bg-slate-100 text-slate-700','Trust & Convenience',
         'Dealing with strangers on generic platforms raised concerns about trust, safety, and logistics.'],
      ];
      foreach ($problems as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-card ab-fade ab-fade-d<?php echo ($i%6)+1; ?> bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
          <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl mb-4"><?php echo $ico; ?></div>
          <h3 class="font-bold text-navy-900 text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     04  HOW IT WORKS — 4-step timeline
════════════════════════════════════════════════════════════════ -->
<section class="bg-white py-20 sm:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-16 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">The Process</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">How Rentora Works</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">Four simple steps from browsing to handover.</p>
    </div>

    <?php
    $steps = [
      ['01','🔎','Browse','Find equipment listed by students around your campus and filter by category or pickup spot.'],
      ['02','💬','Connect','Review full equipment details — condition, deposit, and owner profile — then proceed to request.'],
      ['03','📝','Agree','Confirm rental or exchange terms, the rental period, and any applicable refundable security deposit.'],
      ['04','🤝','Handover','Meet the owner at an agreed campus pickup point — Hazari Lane, Wasa, or GEC Campus — and complete the handover.'],
    ];
    ?>

    <!-- Desktop horizontal -->
    <div class="hidden sm:grid grid-cols-4 gap-6">
      <?php foreach ($steps as $i => [$num,$ico,$title,$desc]): ?>
        <div class="ab-step-wrap ab-fade ab-fade-d<?php echo $i+1; ?> relative flex flex-col items-center text-center">
          <?php if ($i < 3): ?>
            <div class="ab-connector" style="left:calc(50% + 28px);"></div>
          <?php endif; ?>
          <div class="ab-num mb-4"><?php echo $num; ?></div>
          <div class="text-2xl mb-3"><?php echo $ico; ?></div>
          <h3 class="font-bold text-navy-900 text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Mobile vertical -->
    <div class="sm:hidden space-y-6">
      <?php foreach ($steps as $i => [$num,$ico,$title,$desc]): ?>
        <div class="ab-fade ab-fade-d<?php echo $i+1; ?> flex gap-4 items-start">
          <div class="flex flex-col items-center">
            <div class="ab-num text-sm"><?php echo $num; ?></div>
            <?php if ($i < 3): ?><div class="w-0.5 flex-1 mt-2 min-h-[36px] bg-gradient-to-b from-blue-200 to-indigo-100"></div><?php endif; ?>
          </div>
          <div class="pb-4">
            <h3 class="font-bold text-navy-900 text-base mb-1 flex items-center gap-2">
              <span><?php echo $ico; ?></span><?php echo htmlspecialchars($title); ?>
            </h3>
            <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     05  RENT + EXCHANGE — two feature cards
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%);">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">Core Features</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Two Ways to Get Equipment</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">Choose what works best for your situation.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-7">

      <!-- Rent -->
      <div class="ab-feat ab-fade ab-fade-d1 p-8 sm:p-10">
        <div class="w-12 h-12 rounded-2xl bg-primary-600 flex items-center justify-center text-white mb-6 shadow-md shadow-blue-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="text-xl font-extrabold text-navy-900 mb-3">Rent Equipment</h3>
        <p class="text-slate-600 text-sm leading-relaxed mb-6">Need something for a few days, weeks, or a semester? Rent equipment from fellow students instead of purchasing something you may only use temporarily.</p>
        <ul class="space-y-2.5 mb-8">
          <?php foreach ([
            'Flexible rental periods — days, weeks, or a semester',
            'Affordable student-to-student rental rates',
            'Browse by category or campus pickup spot',
            'Refundable security deposit where applicable',
          ] as $pt): ?>
            <li class="flex items-start gap-2.5 text-sm text-slate-600">
              <span class="mt-0.5 w-4 h-4 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-2.5 h-2.5 text-primary-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </span>
              <?php echo htmlspecialchars($pt); ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo $base_path; ?>/index.php"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-navy-900 hover:bg-primary-600 text-white text-sm font-bold shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Browse Equipment
        </a>
      </div>

      <!-- Exchange -->
      <div class="ab-feat ab-fade ab-fade-d2 p-8 sm:p-10">
        <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white mb-6 shadow-md shadow-emerald-500/20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <h3 class="text-xl font-extrabold text-navy-900 mb-3">Exchange Equipment</h3>
        <p class="text-slate-600 text-sm leading-relaxed mb-6">Have equipment you no longer need? Exchange it with another student and give useful gear a second life on campus.</p>
        <ul class="space-y-2.5 mb-8">
          <?php foreach ([
            'Student-to-student equipment swapping',
            'Useful equipment reuse — less waste on campus',
            'Direct exchange at official campus handover spots',
            'No unnecessary platform complexity',
          ] as $pt): ?>
            <li class="flex items-start gap-2.5 text-sm text-slate-600">
              <span class="mt-0.5 w-4 h-4 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-2.5 h-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </span>
              <?php echo htmlspecialchars($pt); ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo $base_path; ?>/user/exchanges.php"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-bold shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          Explore Exchanges
        </a>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     06  TRUST & SAFETY
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: #F5F8FC;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">Trust & Safety</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Built Around Student Trust</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">
        Every aspect of Rentora is designed around transparency, honesty, and the practicalities of campus life.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $trust = [
        ['🎓','bg-blue-50 text-blue-600','Verified Students',
         'Only registered Premier University students can use the platform. Accounts go through an admin verification step before full access is granted.'],
        ['📍','bg-amber-50 text-amber-600','Campus Pickup Points',
         'All handovers happen at three designated campus spots — Hazari Lane, Wasa, and GEC Campus — keeping exchanges safe and convenient.'],
        ['📋','bg-slate-50 text-slate-600','Clear Equipment Details',
         'Each listing shows equipment name, condition (New/Good/Fair), category, rental rate, and deposit amount — no hidden surprises.'],
        ['💵','bg-emerald-50 text-emerald-600','Cash Handover Only',
         'Cash handover only — no online payments, no digital wallets. Payment happens in person at the handover point, keeping things simple.'],
        ['🔒','bg-indigo-50 text-indigo-600','Refundable Security Deposit',
         'Where applicable, a refundable security deposit is collected at handover to protect the equipment owner\'s interests.'],
        ['🪙','bg-rose-50 text-rose-600','No Platform Fees',
         'Rentora does not take a cut of any transaction. The rental rate agreed between students is what gets paid — nothing more.'],
      ];
      foreach ($trust as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-trust-card ab-fade ab-fade-d<?php echo ($i%6)+1; ?> p-6">
          <div class="flex items-start gap-4">
            <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0"><?php echo $ico; ?></div>
            <div>
              <h3 class="font-bold text-navy-900 text-base mb-1.5"><?php echo htmlspecialchars($title); ?></h3>
              <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     07  WHO IS IT FOR?
════════════════════════════════════════════════════════════════ -->
<section class="bg-slate-50 py-20 sm:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">Who It's For</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Made for Campus Life</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">Rentora Hub fits naturally into the daily rhythm of university student life.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php
      $audience = [
        ['🧑‍🔬','The Project Builder',
         'Working on a semester project and need lab instruments or a camera for two weeks? Rent exactly what you need, for as long as you need it.'],
        ['📦','The Equipment Owner',
         'Have a calculator, drafter, or IoT kit sitting unused after finishing a course? List it on Rentora and let it be rented or swapped.'],
        ['💡','The Budget-Conscious Student',
         'Equipment is expensive to buy new. Renting from a fellow student is a practical, affordable way to access what you need without overspending.'],
        ['🔄','The Swapper',
         'Already have something useful but need something different? Exchange gear directly with another student and keep campus resources moving.'],
        ['🎓','The Lab Prepper',
         'Preparing for lab sessions or presentations and need to borrow specific instruments? Rentora is designed for exactly that use case.'],
        ['🌍','The Community Contributor',
         'Students who believe in a sharing campus culture — where useful equipment stays within the community instead of sitting unused.'],
      ];
      foreach ($audience as $i => [$emoji,$title,$desc]): ?>
        <div class="ab-card ab-fade ab-fade-d<?php echo ($i%6)+1; ?> bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
          <div class="text-3xl mb-3"><?php echo $emoji; ?></div>
          <h3 class="font-bold text-navy-900 text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     08  VISION — dark navy
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-br from-navy-900 via-navy-900 to-primary-900 text-white py-20 sm:py-28 overflow-hidden">
  <div class="ab-blob w-80 h-80 bg-blue-500" style="top:-5rem;right:-5rem;opacity:.12;"></div>
  <div class="ab-blob w-64 h-64 bg-emerald-500" style="bottom:0;left:-3rem;opacity:.09;"></div>
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);background-size:28px 28px;"></div>

  <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-4">Our Vision</span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight mb-8">
        Our <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">Vision</span>
      </h2>
    </div>

    <p class="ab-fade ab-fade-d1 text-blue-100 text-base sm:text-lg leading-relaxed max-w-3xl mx-auto mb-12">
      We envision a campus where students can access the equipment they need without unnecessary expense — while useful equipment stays within the student community instead of sitting unused after a semester ends.
    </p>

    <div class="ab-fade ab-fade-d2 ab-quote text-left bg-white/5 border border-white/10 rounded-2xl p-8 sm:p-10 backdrop-blur-sm">
      <svg class="w-8 h-8 text-blue-400 mb-4 opacity-70" fill="currentColor" viewBox="0 0 24 24">
        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
      </svg>
      <p class="text-xl sm:text-2xl font-bold text-white leading-snug tracking-tight">
        "Use what you need. Share what you have.<br class="hidden sm:block"> Keep campus resources moving."
      </p>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     09  BENEFITS GRID
════════════════════════════════════════════════════════════════ -->
<section class="bg-white py-20 sm:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3">Why Students Love It</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">How Students Benefit</h2>
      <p class="mt-4 text-slate-500 max-w-xl mx-auto text-base">Real advantages built into every interaction on Rentora Hub.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php
      $benefits = [
        ['💰','bg-blue-50 text-primary-600','Affordable Access',
         'Get access to useful equipment without always having to purchase it outright.'],
        ['🤝','bg-emerald-50 text-emerald-600','Student Community',
         'Connect with fellow students through practical, peer-to-peer equipment sharing.'],
        ['⚡','bg-amber-50 text-amber-600','Convenience',
         'Find equipment around your campus and arrange a convenient campus handover point.'],
        ['♻️','bg-teal-50 text-teal-600','Reuse & Reduce Waste',
         'Give unused equipment a second life by renting or exchanging it within the community.'],
        ['🔄','bg-violet-50 text-violet-600','Flexibility',
         'Choose rental or exchange depending on what works best for your situation.'],
        ['🏫','bg-rose-50 text-rose-600','Campus Focused',
         'Designed entirely around the practical needs of university student life.'],
      ];
      foreach ($benefits as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-benefit ab-fade ab-fade-d<?php echo ($i%6)+1; ?>">
          <div class="<?php echo $cls; ?> w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-4"><?php echo $ico; ?></div>
          <h3 class="font-bold text-navy-900 text-sm sm:text-base mb-1.5"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-500 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     10  CTA — dark navy
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-r from-navy-900 to-primary-900 text-white py-20 sm:py-24 overflow-hidden">
  <div class="ab-blob w-96 h-96 bg-blue-500" style="top:-5rem;right:-5rem;opacity:.10;"></div>
  <div class="ab-blob w-64 h-64 bg-emerald-500" style="bottom:0;left:-3rem;opacity:.07;"></div>
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.03) 1px,transparent 1px);background-size:28px 28px;"></div>

  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade mb-6">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-4">Get Started</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">
        Ready to Find Your Next<br class="hidden sm:block"> Piece of Equipment?
      </h2>
    </div>

    <p class="ab-fade ab-fade-d1 text-blue-100 text-base sm:text-lg mb-10 max-w-xl mx-auto leading-relaxed">
      Browse equipment available from fellow students — or list something you already own and let it earn for you.
    </p>

    <div class="ab-fade ab-fade-d2 flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="<?php echo $base_path; ?>/index.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-white text-navy-900 text-sm font-bold shadow-lg hover:bg-blue-50 transition-all hover:-translate-y-0.5 hover:shadow-xl">
        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Equipment
      </a>
      <a href="<?php echo $base_path; ?>/user/add_item.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-semibold backdrop-blur-sm transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        List Equipment
      </a>
    </div>

    <p class="ab-fade ab-fade-d3 mt-10 text-xs text-blue-300 flex items-center justify-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Pickup points: Hazari Lane &nbsp;&middot;&nbsp; Wasa &nbsp;&middot;&nbsp; GEC Campus
    </p>
  </div>
</section>


<!-- Scroll-triggered fade-up -->
<script>
(function(){
  'use strict';
  if(!window.IntersectionObserver) return;
  var io = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
    });
  },{threshold:0.10});
  document.querySelectorAll('.ab-fade').forEach(function(el){ io.observe(el); });
})();
</script>

<?php require_once(__DIR__ . '/includes/footer.php'); ?>
