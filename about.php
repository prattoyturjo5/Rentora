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
  from { opacity:0; transform:translateY(24px); }
  to   { opacity:1; transform:translateY(0);    }
}
.ab-fade { opacity:0; }
.ab-fade.visible { animation: ab-fadeUp 0.55s cubic-bezier(0.16,1,0.3,1) forwards; }
.ab-fade-d1.visible { animation-delay:.05s; }
.ab-fade-d2.visible { animation-delay:.12s; }
.ab-fade-d3.visible { animation-delay:.19s; }
.ab-fade-d4.visible { animation-delay:.26s; }
.ab-fade-d5.visible { animation-delay:.33s; }
.ab-fade-d6.visible { animation-delay:.40s; }
@media (prefers-reduced-motion:reduce) {
  .ab-fade, .ab-fade.visible { animation:none; opacity:1; }
}

/* ── Glow blobs & shapes ── */
.ab-blob {
  position:absolute; border-radius:9999px;
  filter:blur(80px); pointer-events:none; opacity:.16;
}

/* ── SVG wave divider ── */
.ab-wave { display:block; line-height:0; margin-bottom:-2px; }
.ab-wave svg { display:block; width:100%; height:auto; }

/* ── Feature Cards (Why Rentora Grid) ── */
.ab-why-card {
  background: #FFFFFF;
  border: 1px solid #D1DFEE;
  border-radius: 1.25rem;
  padding: 1.75rem;
  box-shadow: 0 4px 16px rgba(15, 35, 65, 0.05);
  transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
}
.ab-why-card:hover {
  transform: translateY(-4px);
  border-color: #93C5FD;
  box-shadow: 0 14px 30px rgba(15, 35, 65, 0.10);
}

/* ── Step number circle (Timeline) ── */
.ab-step-wrap { position:relative; }
.ab-connector {
  position:absolute; top:24px;
  left:calc(50% + 28px); right:0;
  height:2px;
  background:linear-gradient(90deg, #3B82F6, #10B981);
}
.ab-num {
  width:48px; height:48px; border-radius:50%;
  background:linear-gradient(135deg,#1d4ed8,#2563eb);
  color:#fff; font-size:.9rem; font-weight:800;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; position:relative; z-index:1;
  box-shadow:0 0 20px rgba(37,99,235,.5);
}

/* ── Feature Panels (Rent + Exchange) ── */
.ab-feat-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border-radius: 1.5rem;
  box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
  transition: transform 220ms ease, box-shadow 220ms ease;
}
.ab-feat-panel:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.14);
}

/* ── Trust cards ── */
.ab-trust-card {
  background: #FFFFFF;
  border: 1px solid #D9E2EF;
  border-radius: 1.25rem;
  padding: 1.5rem;
  box-shadow: 0 4px 14px rgba(15, 35, 65, 0.06);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
}
.ab-trust-card:hover {
  transform: translateY(-3px);
  border-color: #BFDBFE;
  box-shadow: 0 10px 24px rgba(15, 35, 65, 0.10);
}

/* ── Benefit tile ── */
.ab-benefit {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 1.25rem;
  padding: 1.6rem;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
}
.ab-benefit:hover {
  transform: translateY(-3px);
  border-color: #93C5FD;
  box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.09);
}

/* ── Campus Gear Tile ── */
.ab-gear-tile {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(8px);
  border-radius: 1rem;
  padding: 1.25rem;
  transition: transform 200ms ease, background 200ms ease;
}
.ab-gear-tile:hover {
  transform: translateY(-3px);
  background: rgba(255, 255, 255, 0.20);
}
</style>

<!-- ═══════════════════════════════════════════════════════════════
     01  HERO — Rich Dark Navy & Blue Gradient
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-br from-navy-950 via-navy-900 to-slate-900 text-white overflow-hidden pt-20 pb-28 sm:pt-24 sm:pb-36">
  <div class="ab-blob w-96 h-96 bg-blue-500" style="top:-5rem;right:-3rem;"></div>
  <div class="ab-blob w-80 h-80 bg-primary-600" style="bottom:1rem;left:-4rem;"></div>
  <div class="ab-blob w-64 h-64 bg-emerald-500" style="top:40%;left:45%;opacity:.08;"></div>
  
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.05) 1px,transparent 1px);background-size:32px 32px;"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      
      <!-- Left Hero Text Column -->
      <div class="lg:col-span-7 text-center lg:text-left">
        <div class="ab-fade ab-fade-d1 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-blue-200 mb-6 backdrop-blur-md">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Campus Equipment Exchange &amp; Rental Hub
        </div>

        <h1 class="ab-fade ab-fade-d2 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
          About <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-emerald-300 to-teal-300">Rentora Hub</span>
        </h1>

        <p class="ab-fade ab-fade-d3 mt-5 text-xl sm:text-2xl text-blue-100 font-semibold leading-relaxed">
          Making campus equipment easier to rent, exchange, and share.
        </p>

        <p class="ab-fade ab-fade-d4 mt-4 text-base text-slate-300 max-w-2xl leading-relaxed">
          Rentora Hub is a dedicated campus marketplace designed for university students to access essential academic and project equipment — without the burden of buying everything outright.
        </p>

        <div class="ab-fade ab-fade-d5 mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
          <a href="<?php echo $base_path; ?>/index.php"
             class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-0.5 hover:shadow-blue-600/50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Browse Equipment
          </a>
          <a href="<?php echo $base_path; ?>/user/add_item.php"
             class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-semibold backdrop-blur-md transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            List Your Equipment
          </a>
        </div>
      </div>

      <!-- Right Abstract Brand & Campus Equipment Visual -->
      <div class="lg:col-span-5 ab-fade ab-fade-d4 relative flex items-center justify-center min-h-[360px] sm:min-h-[400px]">
        
        <!-- Ambient Glassmorphic Concentric Glow Rings -->
        <div class="absolute w-72 h-72 sm:w-80 sm:h-80 rounded-full border border-white/10 bg-white/5 backdrop-blur-xl flex items-center justify-center shadow-2xl shadow-navy-950/50">
          <div class="w-52 h-52 sm:w-60 sm:h-60 rounded-full border border-blue-400/20 bg-blue-500/10 flex items-center justify-center">
            <div class="w-36 h-36 sm:w-40 sm:h-40 rounded-full border border-emerald-400/25 bg-emerald-500/10 flex items-center justify-center">
              
              <!-- Center Brand Monogram Badge -->
              <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-primary-600 to-blue-400 p-0.5 shadow-xl shadow-blue-500/30 flex items-center justify-center transform rotate-3 hover:rotate-0 transition-transform duration-300">
                <div class="w-full h-full bg-navy-900 rounded-[14px] flex flex-col items-center justify-center text-center p-2">
                  <span class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-300">R</span>
                  <span class="text-[9px] font-bold tracking-widest text-blue-200 uppercase">Rentora</span>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Floating Category Icon Badges (Category Representations) -->
        <!-- 1. Lab Equipment -->
        <div class="absolute -top-2 left-4 sm:left-6 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform -rotate-3 hover:rotate-0 transition-transform">
          <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
          <span>Lab &amp; Academic</span>
        </div>

        <!-- 2. Cameras & Media -->
        <div class="absolute top-10 right-2 sm:right-4 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform rotate-6 hover:rotate-0 transition-transform">
          <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Cameras &amp; Media</span>
        </div>

        <!-- 3. Electronics & IoT -->
        <div class="absolute bottom-12 left-2 sm:left-4 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform rotate-3 hover:rotate-0 transition-transform">
          <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
          <span>Electronics &amp; IoT</span>
        </div>

        <!-- 4. Books & Tools -->
        <div class="absolute -bottom-2 right-6 sm:right-8 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform -rotate-6 hover:rotate-0 transition-transform">
          <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          <span>Books &amp; Tools</span>
        </div>

      </div>

    </div>
  </div>

  <div class="ab-wave absolute bottom-0 left-0 right-0">
    <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M0,40 C480,70 960,10 1440,40 L1440,60 L0,60 Z" fill="#F4F8FF"/>
    </svg>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     02  WHAT IS RENTORA — Light Blue Background (#F4F8FF)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 border-b border-blue-100/60" style="background: #F4F8FF;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-blue-100/80 px-3.5 py-1 rounded-full">The Platform</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">What is Rentora Hub?</h2>
      <p class="mt-4 text-slate-600 max-w-2xl mx-auto text-base leading-relaxed">
        A student-focused marketplace where verified university members rent, lend, and exchange gear directly within their campus community.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

      <!-- Left Text Content -->
      <div class="lg:col-span-6 ab-fade ab-fade-d1 space-y-5 text-slate-600 leading-relaxed text-base">
        <p>University coursework constantly requires specialized gear — scientific calculators, digital cameras, drafting tools, lab kits, and microcontrollers. Buying every item new is financially impractical for short-term semester projects.</p>
        <p>Rentora Hub bridges the gap between students who <strong class="text-navy-900 font-semibold">need</strong> gear for a brief period and classmates who <strong class="text-navy-900 font-semibold">own</strong> equipment sitting idle in their dorms.</p>
        <p>All transactions happen between verified Premier University students with in-person handovers at designated campus spots — zero online fee markups and straightforward cash payment.</p>
      </div>

      <!-- Right Visual Product Capability Card -->
      <div class="lg:col-span-6 ab-fade ab-fade-d2 bg-white rounded-2xl border border-blue-200/70 p-6 sm:p-8 shadow-xl shadow-blue-900/5">
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rentora Marketplace Features</span>
          <span class="px-2.5 py-1 rounded-full bg-blue-50 text-primary-600 text-xs font-bold">Campus Edition</span>
        </div>

        <!-- 4 Product Capability Blocks -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100">
            <div class="text-xl mb-1">🔎</div>
            <h4 class="font-bold text-navy-900 text-sm">Browse</h4>
            <p class="text-xs text-slate-500 mt-0.5">Filter by category or pickup spot</p>
          </div>
          <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
            <div class="text-xl mb-1">🤝</div>
            <h4 class="font-bold text-navy-900 text-sm">Rent</h4>
            <p class="text-xs text-slate-500 mt-0.5">Daily, weekly, or semester rates</p>
          </div>
          <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-100">
            <div class="text-xl mb-1">🔄</div>
            <h4 class="font-bold text-navy-900 text-sm">Exchange</h4>
            <p class="text-xs text-slate-500 mt-0.5">Swap gear directly with peers</p>
          </div>
          <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-100">
            <div class="text-xl mb-1">📍</div>
            <h4 class="font-bold text-navy-900 text-sm">Connect</h4>
            <p class="text-xs text-slate-500 mt-0.5">Safe campus handover points</p>
          </div>
        </div>

        <!-- Equipment Category Pills -->
        <div class="pt-2">
          <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Popular Campus Gear</p>
          <div class="flex flex-wrap gap-2">
            <?php
            $gear = [
              ['🧮','Calculators'],['🔬','Lab Gear'],['📷','Cameras'],
              ['📐','Drafting Tools'],['🤖','IoT Kits'],['💻','Electronics'],
              ['📚','Books'],['🎸','Instruments'],
            ];
            foreach ($gear as [$ico,$lbl]): ?>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 border border-slate-200 rounded-full text-xs font-medium text-slate-700">
                <span><?php echo $ico; ?></span><?php echo htmlspecialchars($lbl); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     03  WHY RENTORA — Soft Slate/Blue Tint (#EBF2FA)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: #EBF2FA;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-blue-100/80 px-3.5 py-1 rounded-full">The Solution</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Why Rentora Hub?</h2>
      <p class="mt-4 text-slate-600 max-w-xl mx-auto text-base">Designed specifically to solve real student equipment challenges.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $why_cards = [
        [
          'ico' => '⚡',
          'bg'  => 'bg-blue-100 text-blue-700',
          'title' => 'Easy to Rent',
          'desc' => 'Find equipment listed by fellow students without unnecessary market hassle.'
        ],
        [
          'ico' => '💰',
          'bg'  => 'bg-emerald-100 text-emerald-700',
          'title' => 'Affordable',
          'desc' => 'Access specialized project gear at student rates without buying outright.'
        ],
        [
          'ico' => '🎓',
          'bg'  => 'bg-indigo-100 text-indigo-700',
          'title' => 'Campus Focused',
          'desc' => 'Built around Premier University life with safe campus handover spots.'
        ],
        [
          'ico' => '🔄',
          'bg'  => 'bg-amber-100 text-amber-700',
          'title' => 'Share & Exchange',
          'desc' => 'Give unused equipment a second life by exchanging directly with peers.'
        ],
      ];
      foreach ($why_cards as $i => $c): ?>
        <div class="ab-why-card ab-fade ab-fade-d<?php echo $i+1; ?>">
          <div class="<?php echo $c['bg']; ?> w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm">
            <?php echo $c['ico']; ?>
          </div>
          <h3 class="font-bold text-navy-900 text-lg mb-2"><?php echo htmlspecialchars($c['title']); ?></h3>
          <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($c['desc']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     04  HOW IT WORKS — Dark Navy Background (#0A0F1D)
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-navy-950 text-white py-20 sm:py-28 overflow-hidden" style="background: #0A0F1D;">
  <div class="ab-blob w-96 h-96 bg-blue-600" style="top:2rem;left:-6rem;opacity:.15;"></div>
  <div class="ab-blob w-80 h-80 bg-emerald-500" style="bottom:1rem;right:-4rem;opacity:.12;"></div>

  <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-16 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-3 bg-white/10 border border-white/15 px-3.5 py-1 rounded-full backdrop-blur-md">The Process</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">How Rentora Works</h2>
      <p class="mt-4 text-slate-300 max-w-xl mx-auto text-base">Four simple steps from searching gear to completing handover.</p>
    </div>

    <?php
    $steps = [
      ['01','🔎','Browse','Find equipment listed by students around your campus and filter by category or pickup spot.'],
      ['02','💬','Connect','Review full equipment details — condition, deposit, and owner profile — then proceed to request.'],
      ['03','📝','Agree','Confirm rental or exchange terms, the rental period, and any applicable refundable security deposit.'],
      ['04','🤝','Handover','Meet the owner at an agreed campus pickup point — Hazari Lane, Wasa, or GEC Campus — and complete the handover.'],
    ];
    ?>

    <!-- Desktop horizontal process timeline -->
    <div class="hidden sm:grid grid-cols-4 gap-6">
      <?php foreach ($steps as $i => [$num,$ico,$title,$desc]): ?>
        <div class="ab-step-wrap ab-fade ab-fade-d<?php echo $i+1; ?> relative flex flex-col items-center text-center">
          <?php if ($i < 3): ?>
            <div class="ab-connector"></div>
          <?php endif; ?>
          <div class="ab-num mb-5"><?php echo $num; ?></div>
          <div class="bg-white/10 border border-white/15 rounded-2xl p-6 backdrop-blur-md w-full hover:bg-white/15 transition-all">
            <div class="text-3xl mb-3"><?php echo $ico; ?></div>
            <h3 class="font-bold text-white text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
            <p class="text-xs text-slate-300 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Mobile vertical process timeline -->
    <div class="sm:hidden space-y-6">
      <?php foreach ($steps as $i => [$num,$ico,$title,$desc]): ?>
        <div class="ab-fade ab-fade-d<?php echo $i+1; ?> flex gap-4 items-start bg-white/10 border border-white/15 rounded-2xl p-5 backdrop-blur-md">
          <div class="ab-num text-sm"><?php echo $num; ?></div>
          <div>
            <h3 class="font-bold text-white text-base mb-1 flex items-center gap-2">
              <span><?php echo $ico; ?></span><?php echo htmlspecialchars($title); ?>
            </h3>
            <p class="text-xs text-slate-300 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     05  RENT + EXCHANGE — Feature Panels (Gradient Background)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: linear-gradient(135deg, #EFF6FF 0%, #ECFDF5 100%);">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-white px-3.5 py-1 rounded-full shadow-sm">Core Features</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Two Ways to Get Equipment</h2>
      <p class="mt-4 text-slate-600 max-w-xl mx-auto text-base">Choose the option that fits your current coursework requirements.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

      <!-- Panel 1: Rent Equipment -->
      <div class="ab-feat-panel ab-fade ab-fade-d1 p-8 sm:p-10 border border-blue-200/80">
        <div class="flex items-center justify-between mb-6">
          <div class="w-12 h-12 rounded-2xl bg-primary-600 flex items-center justify-center text-white text-2xl shadow-md shadow-blue-500/20">
            📦
          </div>
          <span class="px-3 py-1 rounded-full bg-blue-100 text-primary-700 text-xs font-bold">Flexible Duration</span>
        </div>

        <h3 class="text-2xl font-extrabold text-navy-900 mb-3">Rent Equipment</h3>
        <p class="text-slate-600 text-sm leading-relaxed mb-6">Need gear for a project, lab session, or semester? Rent equipment from classmates at affordable student rates instead of buying new.</p>
        
        <ul class="space-y-3 mb-8">
          <?php foreach ([
            'Flexible rental periods — days, weeks, or full semesters',
            'Affordable student-to-student rental pricing',
            'Filter listings by category or campus handover spot',
            'Clear refundable security deposit terms where applicable',
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
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-navy-900 hover:bg-primary-600 text-white text-sm font-bold shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Browse Rentals
        </a>
      </div>

      <!-- Panel 2: Exchange Equipment -->
      <div class="ab-feat-panel ab-fade ab-fade-d2 p-8 sm:p-10 border border-emerald-200/80">
        <div class="flex items-center justify-between mb-6">
          <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-2xl shadow-md shadow-emerald-500/20">
            🔄
          </div>
          <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Peer Swapping</span>
        </div>

        <h3 class="text-2xl font-extrabold text-navy-900 mb-3">Exchange Equipment</h3>
        <p class="text-slate-600 text-sm leading-relaxed mb-6">Have gear sitting unused after finishing a course? Exchange it with another student who has what you need for this semester.</p>

        <ul class="space-y-3 mb-8">
          <?php foreach ([
            'Direct student-to-student equipment swapping',
            'Resource sustainability — reduce waste on campus',
            'Meet at Hazari Lane, Wasa, or GEC handover spots',
            'No middleman transaction commission',
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
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-bold shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          Explore Exchanges
        </a>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     06  TRUST & SAFETY — Light Contrasting Cool Blue (#F5F8FC)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: #F5F8FC;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-blue-100/80 px-3.5 py-1 rounded-full">Trust &amp; Safety</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Built Around Student Trust</h2>
      <p class="mt-4 text-slate-600 max-w-xl mx-auto text-base">
        Every aspect of Rentora is designed around transparency, honesty, and campus practicalities.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $trust = [
        ['🎓','bg-blue-50 text-blue-600','Verified Students',
         'Only registered Premier University students can use the platform with admin account verification.'],
        ['📍','bg-amber-50 text-amber-600','Campus Pickup Points',
         'Handovers take place at three designated spots — Hazari Lane, Wasa, and GEC Campus.'],
        ['📋','bg-slate-50 text-slate-600','Clear Equipment Details',
         'Listings clearly display item name, condition, rental rate, and security deposit up front.'],
        ['💵','bg-emerald-50 text-emerald-600','Cash Handover Only',
         'Cash handover in person — no complex online payment steps or digital wallet fees.'],
        ['🔒','bg-indigo-50 text-indigo-600','Refundable Security Deposit',
         'Security deposits are collected at handover to protect equipment owners.'],
        ['🪙','bg-rose-50 text-rose-600','No Platform Fees',
         'Rentora takes zero commission on rentals or exchanges between students.'],
      ];
      foreach ($trust as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-trust-card ab-fade ab-fade-d<?php echo ($i%6)+1; ?>">
          <div class="flex items-start gap-4">
            <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm"><?php echo $ico; ?></div>
            <div>
              <h3 class="font-bold text-navy-900 text-base mb-1.5"><?php echo htmlspecialchars($title); ?></h3>
              <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     07  MADE FOR CAMPUS LIFE — Rich Soft Blue Gradient Section
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);">
  <div class="ab-blob w-96 h-96 bg-blue-400" style="top:-6rem;right:-4rem;opacity:.20;"></div>
  
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-200 mb-3 bg-white/10 px-3.5 py-1 rounded-full backdrop-blur-md">Campus Ecosystem</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Made for Campus Life</h2>
      <p class="mt-4 text-blue-100 max-w-2xl mx-auto text-lg font-medium">
        "Useful equipment should be accessible when you need it."
      </p>
    </div>

    <!-- 8 Gear Tile Icons Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
      <?php
      $campus_tiles = [
        ['💻', 'Laptops & Computing', 'Laptops & workstations for coding'],
        ['🧮', 'Scientific Calculators', 'Standard & graphic calculators'],
        ['📷', 'Cameras & Video', 'DSLRs & gear for media projects'],
        ['🔬', 'Lab Equipment', 'Microscopes & testing instruments'],
        ['📐', 'Drafting Tools', 'T-squares, boards & drawing gear'],
        ['🤖', 'IoT & Arduino Kits', 'Sensors, microcontrollers & modules'],
        ['📚', 'Reference Books', 'Course textbooks & lab manuals'],
        ['🔊', 'Electronics & Audio', 'Speakers, microphones & AV tools'],
      ];
      foreach ($campus_tiles as $i => [$ico, $name, $sub]): ?>
        <div class="ab-gear-tile ab-fade ab-fade-d<?php echo ($i%4)+1; ?>">
          <div class="text-3xl mb-2"><?php echo $ico; ?></div>
          <h4 class="font-bold text-white text-sm mb-1"><?php echo htmlspecialchars($name); ?></h4>
          <p class="text-xs text-blue-100/80 leading-snug"><?php echo htmlspecialchars($sub); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     08  OUR VISION — Dark Navy Background (#0F172A)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-navy-900 text-white relative overflow-hidden" style="background: #0F172A;">
  <div class="ab-blob w-80 h-80 bg-blue-500" style="top:50%;left:50%;transform:translate(-50%,-50%);opacity:.10;"></div>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
    <div class="ab-fade">
      <span class="inline-block text-4xl mb-4 text-blue-400">“</span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-white mb-6">
        Making campus equipment easier to rent, exchange, and share.
      </h2>
      <p class="text-lg text-blue-100 max-w-2xl mx-auto leading-relaxed mb-10">
        We believe university life is better when resources are shared, costs are minimized, and students empower each other to complete their academic projects.
      </p>

      <div class="inline-flex flex-wrap items-center justify-center gap-4 pt-4 border-t border-white/10 text-xs font-semibold text-blue-200">
        <span class="flex items-center gap-1.5">🎓 Premier University Focused</span>
        <span>&bull;</span>
        <span class="flex items-center gap-1.5">🤝 100% Peer-to-Peer</span>
        <span>&bull;</span>
        <span class="flex items-center gap-1.5">📍 Safe Campus Spots</span>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     09  BENEFITS GRID — Slate-50 (#F8FAFC)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: #F8FAFC;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-blue-100/80 px-3.5 py-1 rounded-full">Student Benefits</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">Why Students Love Rentora</h2>
      <p class="mt-4 text-slate-600 max-w-xl mx-auto text-base">Key advantages built into every interaction on Rentora Hub.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $benefits = [
        ['💳','bg-blue-100 text-primary-600','Affordable Access',
         'Access specialized equipment without buying expensive items outright.'],
        ['♻️','bg-emerald-100 text-emerald-700','Less Unused Equipment',
         'Give idle gear a second life by renting or exchanging with classmates.'],
        ['🎯','bg-amber-100 text-amber-700','Easy Discovery',
         'Quickly search equipment by category or your preferred campus pickup spot.'],
        ['👥','bg-purple-100 text-purple-700','Campus Community',
         'Connect with fellow students through direct peer-to-peer handovers.'],
        ['⚙️','bg-teal-100 text-teal-700','Flexible Options',
         'Choose rental or exchange based on your current semester needs.'],
        ['✨','bg-indigo-100 text-indigo-700','Simple Experience',
         'No complicated online fees — straightforward cash payment at pickup.'],
      ];
      foreach ($benefits as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-benefit ab-fade ab-fade-d<?php echo ($i%6)+1; ?>">
          <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl mb-4 shadow-sm"><?php echo $ico; ?></div>
          <h3 class="font-bold text-navy-900 text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     10  FINAL CTA — Dark Blue & Navy Gradient Banner
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-r from-navy-950 via-primary-900 to-navy-900 text-white py-20 sm:py-28 overflow-hidden">
  <div class="ab-blob w-96 h-96 bg-blue-500" style="top:-5rem;right:-5rem;opacity:.12;"></div>
  <div class="ab-blob w-64 h-64 bg-emerald-500" style="bottom:0;left:-3rem;opacity:.08;"></div>
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);background-size:28px 28px;"></div>

  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade mb-6">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-4 bg-white/10 px-3.5 py-1 rounded-full">Get Started</span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
        Ready to make equipment<br class="hidden sm:block"> easier to access?
      </h2>
    </div>

    <p class="ab-fade ab-fade-d1 text-blue-100 text-base sm:text-lg mb-10 max-w-xl mx-auto leading-relaxed">
      Browse available equipment or share something useful with your campus community today.
    </p>

    <div class="ab-fade ab-fade-d2 flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="<?php echo $base_path; ?>/index.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-white text-navy-900 text-sm font-bold shadow-xl hover:bg-blue-50 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Equipment
      </a>
      <a href="<?php echo $base_path; ?>/user/add_item.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/25 text-white text-sm font-semibold backdrop-blur-md transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        List Your Equipment
      </a>
    </div>

    <p class="ab-fade ab-fade-d3 mt-10 text-xs text-blue-200/80 flex items-center justify-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Pickup points: Hazari Lane &nbsp;&middot;&nbsp; Wasa &nbsp;&middot;&nbsp; GEC Campus
    </p>
  </div>
</section>

<!-- Scroll-triggered fade-up animation script -->
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
