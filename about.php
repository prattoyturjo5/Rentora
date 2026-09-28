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
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 1.25rem;
  padding: 1.75rem;
  box-shadow: var(--shadow-float);
  transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
}
.ab-why-card:hover {
  transform: translateY(-4px);
  border-color: var(--accent-primary);
  box-shadow: var(--shadow-elevated);
}

/* ── Step number circle (Timeline) ── */
.ab-step-wrap { position:relative; }
.ab-connector {
  position:absolute; top:24px;
  left:calc(50% + 28px); right:0;
  height:2px;
  background:linear-gradient(90deg, var(--accent-primary), #10B981);
}
.ab-num {
  width:48px; height:48px; border-radius:50%;
  background:linear-gradient(135deg, var(--accent-primary), var(--accent-hover));
  color:#fff; font-size:.9rem; font-weight:800;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; position:relative; z-index:1;
  box-shadow:0 0 20px var(--accent-glow);
}

/* ── Feature Panels (Rent + Exchange) ── */
.ab-feat-panel {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 1.5rem;
  box-shadow: var(--shadow-float);
  transition: transform 220ms ease, box-shadow 220ms ease;
}
.ab-feat-panel:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-elevated);
}

/* ── Trust cards ── */
.ab-trust-card {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 1.25rem;
  padding: 1.5rem;
  box-shadow: var(--shadow-float);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
}
.ab-trust-card:hover {
  transform: translateY(-3px);
  border-color: var(--accent-primary);
  box-shadow: var(--shadow-elevated);
}

/* ── Benefit tile ── */
.ab-benefit {
  background: var(--bg-surface);
  border: 1px solid var(--border-subtle);
  border-radius: 1.25rem;
  padding: 1.6rem;
  box-shadow: var(--shadow-float);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
}
.ab-benefit:hover {
  transform: translateY(-3px);
  border-color: var(--accent-primary);
  box-shadow: var(--shadow-elevated);
}

/* ── Campus Gear Tile ── */
.ab-gear-tile {
  background: var(--bg-surface-subtle);
  border: 1px solid var(--border-subtle);
  backdrop-filter: blur(8px);
  border-radius: 1rem;
  padding: 1.25rem;
  transition: transform 200ms ease, background 200ms ease;
}
.ab-gear-tile:hover {
  transform: translateY(-3px);
  background: var(--bg-surface-elevated);
}

/* ── Design-Vectors Light Mode Contrast Fix ── */
.about-vector-deck {
  background-color: #F1F5F9;
  border: 1px solid #CBD5E1;
}
.about-vector-deck svg {
  stroke: #0F172A;
}
.about-vector-deck .vector-badge {
  background-color: #FFFFFF;
  border: 1px solid #CBD5E1;
  color: #020617;
}
[data-theme="dark"] .about-vector-deck,
.dark .about-vector-deck {
  background-color: rgba(24, 34, 52, 0.60);
  border-color: rgba(255, 255, 255, 0.10);
}
[data-theme="dark"] .about-vector-deck svg,
.dark .about-vector-deck svg {
  stroke: currentColor;
}
[data-theme="dark"] .about-vector-deck .vector-badge,
.dark .about-vector-deck .vector-badge {
  background-color: var(--surface);
  border-color: var(--border-subtle);
  color: var(--text-primary);
}
</style>

<!-- ═══════════════════════════════════════════════════════════════
     01  HERO — Elevated Modern Floating Aesthetic
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-surface-elevated text-primary border-b border-subtle overflow-hidden pt-20 pb-28 sm:pt-24 sm:pb-36">
  <div class="ab-blob w-96 h-96 bg-accent/20" style="top:-5rem;right:-3rem;"></div>
  <div class="ab-blob w-80 h-80 bg-accent/15" style="bottom:1rem;left:-4rem;"></div>
  
  <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(var(--border-subtle) 1px,transparent 1px);background-size:32px 32px;"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      
      <!-- Left Hero Text Column -->
      <div class="lg:col-span-7 text-center lg:text-left">
        <div class="ab-fade ab-fade-d1 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-surface-subtle border border-subtle text-xs font-semibold text-accent mb-6 shadow-sm">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          Campus Equipment Exchange &amp; Rental Hub
        </div>

        <h1 class="ab-fade ab-fade-d2 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight text-primary">
          About <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 dark:from-sky-400 dark:via-cyan-400 dark:to-teal-300">Rentora Hub</span>
        </h1>

        <p class="ab-fade ab-fade-d3 mt-5 text-xl sm:text-2xl text-primary font-semibold leading-relaxed">
          Making campus equipment easier to rent, exchange, and share.
        </p>

        <p class="ab-fade ab-fade-d4 mt-4 text-base text-muted max-w-2xl leading-relaxed">
          Rentora Hub is a dedicated campus marketplace designed for university students to access essential academic and project equipment — without the burden of buying everything outright.
        </p>

        <div class="ab-fade ab-fade-d5 mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
          <a href="<?php echo $base_path; ?>/index.php"
             class="btn-accent w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl text-white text-sm font-bold shadow-float">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Browse Equipment
          </a>
          <a href="<?php echo $base_path; ?>/user/equipment.php"
             class="btn-secondary w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            List Your Equipment
          </a>
        </div>
      </div>

      <!-- Right Abstract Brand & Campus Equipment Visual (Design-Vectors) -->
      <div class="lg:col-span-5 ab-fade ab-fade-d4 relative">
        <div class="about-vector-deck relative flex items-center justify-center p-6 sm:p-10 rounded-3xl shadow-elevated min-h-[380px] sm:min-h-[420px] overflow-hidden">
          
          <!-- Ambient Glassmorphic Concentric Glow Rings -->
          <div class="absolute w-72 h-72 sm:w-80 sm:h-80 rounded-full border border-slate-300 dark:border-white/10 bg-white/70 dark:bg-surface-subtle/50 backdrop-blur-xl flex items-center justify-center shadow-float">
            <div class="w-52 h-52 sm:w-60 sm:h-60 rounded-full border border-blue-700/20 dark:border-accent/20 bg-blue-700/5 dark:bg-accent/5 flex items-center justify-center">
              <div class="w-36 h-36 sm:w-40 sm:h-40 rounded-full border border-blue-800/20 dark:border-cyan-500/20 bg-blue-800/5 dark:bg-cyan-500/5 flex items-center justify-center">
                
                <!-- Center Brand Monogram Badge -->
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-800 dark:from-sky-500 dark:to-cyan-400 p-0.5 shadow-float flex items-center justify-center transform rotate-3 hover:rotate-0 transition-transform duration-300">
                  <div class="w-full h-full bg-white dark:bg-surface rounded-[14px] flex flex-col items-center justify-center text-center p-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-blue-700 dark:text-sky-500">R</span>
                    <span class="text-[9px] font-bold tracking-widest text-slate-700 dark:text-muted uppercase">Rentora</span>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Floating Category Icon Badges -->
          <div class="vector-badge absolute top-3 left-3 sm:left-6 px-3.5 py-2 rounded-xl text-xs font-bold shadow-float flex items-center gap-2 transform -rotate-3 hover:rotate-0 transition-transform">
            <svg class="w-4 h-4 text-blue-700 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            <span>Lab &amp; Academic</span>
          </div>

          <div class="vector-badge absolute top-12 right-2 sm:right-4 px-3.5 py-2 rounded-xl text-xs font-bold shadow-float flex items-center gap-2 transform rotate-6 hover:rotate-0 transition-transform">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>Cameras &amp; Media</span>
          </div>

          <div class="vector-badge absolute bottom-12 left-2 sm:left-4 px-3.5 py-2 rounded-xl text-xs font-bold shadow-float flex items-center gap-2 transform rotate-3 hover:rotate-0 transition-transform">
            <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            <span>Electronics &amp; IoT</span>
          </div>

          <div class="vector-badge absolute bottom-3 right-4 sm:right-8 px-3.5 py-2 rounded-xl text-xs font-bold shadow-float flex items-center gap-2 transform -rotate-6 hover:rotate-0 transition-transform">
            <svg class="w-4 h-4 text-blue-700 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>Books &amp; Tools</span>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     02  WHAT IS RENTORA — Canvas Background
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 border-b border-subtle bg-canvas">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">The Platform</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">What is Rentora Hub?</h2>
      <p class="mt-4 text-muted max-w-2xl mx-auto text-base leading-relaxed">
        A student-focused marketplace where verified university members rent, lend, and exchange gear directly within their campus community.
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

      <!-- Left Text Content -->
      <div class="lg:col-span-6 ab-fade ab-fade-d1 space-y-5 text-muted leading-relaxed text-base">
        <p>University coursework constantly requires specialized gear — scientific calculators, digital cameras, drafting tools, lab kits, and microcontrollers. Buying every item new is financially impractical for short-term semester projects.</p>
        <p>Rentora Hub bridges the gap between students who <strong class="text-primary font-semibold">need</strong> gear for a brief period and classmates who <strong class="text-primary font-semibold">own</strong> equipment sitting idle in their dorms.</p>
        <p>All transactions happen between verified Premier University students with in-person handovers at designated campus spots — zero online fee markups and straightforward cash payment.</p>
      </div>

      <!-- Right Visual Product Capability Card -->
      <div class="lg:col-span-6 ab-fade ab-fade-d2 bg-surface rounded-2xl border border-subtle p-6 sm:p-8 shadow-float">
        <div class="flex items-center justify-between mb-6 pb-3 border-b border-subtle">
          <span class="text-xs font-bold uppercase tracking-wider text-muted">Rentora Marketplace Features</span>
          <span class="px-2.5 py-1 rounded-full bg-accent/10 text-accent text-xs font-bold border border-accent/20">Campus Edition</span>
        </div>

        <!-- 4 Product Capability Blocks -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div class="p-3.5 rounded-xl bg-surface-subtle border border-subtle">
            <div class="text-xl mb-1">🔎</div>
            <h4 class="font-bold text-primary text-sm">Browse</h4>
            <p class="text-xs text-muted mt-0.5">Filter by category or pickup spot</p>
          </div>
          <div class="p-3.5 rounded-xl bg-surface-subtle border border-subtle">
            <div class="text-xl mb-1">🤝</div>
            <h4 class="font-bold text-primary text-sm">Rent</h4>
            <p class="text-xs text-muted mt-0.5">Daily, weekly, or semester rates</p>
          </div>
          <div class="p-3.5 rounded-xl bg-surface-subtle border border-subtle">
            <div class="text-xl mb-1">🔄</div>
            <h4 class="font-bold text-primary text-sm">Exchange</h4>
            <p class="text-xs text-muted mt-0.5">Swap gear directly with peers</p>
          </div>
          <div class="p-3.5 rounded-xl bg-surface-subtle border border-subtle">
            <div class="text-xl mb-1">📍</div>
            <h4 class="font-bold text-primary text-sm">Connect</h4>
            <p class="text-xs text-muted mt-0.5">Safe campus handover points</p>
          </div>
        </div>

        <!-- Equipment Category Pills -->
        <div class="pt-2">
          <p class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Popular Campus Gear</p>
          <div class="flex flex-wrap gap-2">
            <?php
            $gear = [
              ['🧮','Calculators'],['🔬','Lab Gear'],['📷','Cameras'],
              ['📐','Drafting Tools'],['🤖','IoT Kits'],['💻','Electronics'],
              ['📚','Books'],['🎸','Instruments'],
            ];
            foreach ($gear as [$ico,$lbl]): ?>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-surface-subtle border border-subtle rounded-full text-xs font-medium text-primary">
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
     03  WHY RENTORA — Surface Subtle Background
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-surface-subtle border-b border-subtle">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">The Solution</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">Why Rentora Hub?</h2>
      <p class="mt-4 text-muted max-w-xl mx-auto text-base">Designed specifically to solve real student equipment challenges.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $why_cards = [
        [
          'ico' => '⚡',
          'bg'  => 'bg-accent/10 text-accent',
          'title' => 'Easy to Rent',
          'desc' => 'Find equipment listed by fellow students without unnecessary market hassle.'
        ],
        [
          'ico' => '💰',
          'bg'  => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
          'title' => 'Affordable',
          'desc' => 'Access specialized project gear at student rates without buying outright.'
        ],
        [
          'ico' => '🎓',
          'bg'  => 'bg-accent/10 text-accent',
          'title' => 'Campus Focused',
          'desc' => 'Built around Premier University life with safe campus handover spots.'
        ],
        [
          'ico' => '🔄',
          'bg'  => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
          'title' => 'Share & Exchange',
          'desc' => 'Give unused equipment a second life by exchanging directly with peers.'
        ],
      ];
      foreach ($why_cards as $i => $c): ?>
        <div class="ab-why-card ab-fade ab-fade-d<?php echo $i+1; ?>">
          <div class="<?php echo $c['bg']; ?> w-12 h-12 rounded-xl flex items-center justify-center text-2xl mb-5 shadow-sm border border-subtle">
            <?php echo $c['ico']; ?>
          </div>
          <h3 class="font-bold text-primary text-lg mb-2"><?php echo htmlspecialchars($c['title']); ?></h3>
          <p class="text-sm text-muted leading-relaxed"><?php echo htmlspecialchars($c['desc']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     04  HOW IT WORKS — Elevated Process Section
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-surface-elevated text-primary py-20 sm:py-28 border-b border-subtle overflow-hidden">
  <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-16 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">The Process</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">How Rentora Works</h2>
      <p class="mt-4 text-muted max-w-xl mx-auto text-base">Four simple steps from searching gear to completing handover.</p>
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
          <div class="bg-surface border border-subtle rounded-2xl p-6 shadow-float w-full hover:border-accent transition-all">
            <div class="text-3xl mb-3"><?php echo $ico; ?></div>
            <h3 class="font-bold text-primary text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
            <p class="text-xs text-muted leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Mobile vertical process timeline -->
    <div class="sm:hidden space-y-6">
      <?php foreach ($steps as $i => [$num,$ico,$title,$desc]): ?>
        <div class="ab-fade ab-fade-d<?php echo $i+1; ?> flex gap-4 items-start bg-surface border border-subtle rounded-2xl p-5 shadow-float">
          <div class="ab-num text-sm"><?php echo $num; ?></div>
          <div>
            <h3 class="font-bold text-primary text-base mb-1 flex items-center gap-2">
              <span><?php echo $ico; ?></span><?php echo htmlspecialchars($title); ?>
            </h3>
            <p class="text-xs text-muted leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     05  RENT + EXCHANGE — Feature Panels
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-canvas border-b border-subtle">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">Core Features</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">Two Ways to Get Equipment</h2>
      <p class="mt-4 text-muted max-w-xl mx-auto text-base">Choose the option that fits your current coursework requirements.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

      <!-- Panel 1: Rent Equipment -->
      <div class="ab-feat-panel ab-fade ab-fade-d1 p-8 sm:p-10">
        <div class="flex items-center justify-between mb-6">
          <div class="w-12 h-12 rounded-2xl bg-accent flex items-center justify-center text-white text-2xl shadow-float">
            📦
          </div>
          <span class="px-3 py-1 rounded-full bg-accent/10 text-accent text-xs font-bold border border-accent/20">Flexible Duration</span>
        </div>

        <h3 class="text-2xl font-extrabold text-primary mb-3">Rent Equipment</h3>
        <p class="text-muted text-sm leading-relaxed mb-6">Need gear for a project, lab session, or semester? Rent equipment from classmates at affordable student rates instead of buying new.</p>
        
        <ul class="space-y-3 mb-8">
          <?php foreach ([
            'Flexible rental periods — days, weeks, or full semesters',
            'Affordable student-to-student rental pricing',
            'Filter listings by category or campus handover spot',
            'Clear refundable security deposit terms where applicable',
          ] as $pt): ?>
            <li class="flex items-start gap-2.5 text-sm text-muted">
              <span class="mt-0.5 w-4 h-4 rounded-full bg-accent/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-2.5 h-2.5 text-accent" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </span>
              <?php echo htmlspecialchars($pt); ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <a href="<?php echo $base_path; ?>/index.php"
           class="btn-accent inline-flex items-center gap-2 px-6 py-3 rounded-xl text-white text-sm font-bold shadow-float">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Browse Rentals
        </a>
      </div>

      <!-- Panel 2: Exchange Equipment -->
      <div class="ab-feat-panel ab-fade ab-fade-d2 p-8 sm:p-10">
        <div class="flex items-center justify-between mb-6">
          <div class="w-12 h-12 rounded-2xl bg-emerald-600 flex items-center justify-center text-white text-2xl shadow-float">
            🔄
          </div>
          <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20">Peer Swapping</span>
        </div>

        <h3 class="text-2xl font-extrabold text-primary mb-3">Exchange Equipment</h3>
        <p class="text-muted text-sm leading-relaxed mb-6">Have gear sitting unused after finishing a course? Exchange it with another student who has what you need for this semester.</p>

        <ul class="space-y-3 mb-8">
          <?php foreach ([
            'Direct student-to-student equipment swapping',
            'Resource sustainability — reduce waste on campus',
            'Meet at Hazari Lane, Wasa, or GEC handover spots',
            'No middleman transaction commission',
          ] as $pt): ?>
            <li class="flex items-start gap-2.5 text-sm text-muted">
              <span class="mt-0.5 w-4 h-4 rounded-full bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-2.5 h-2.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
              </span>
              <?php echo htmlspecialchars($pt); ?>
            </li>
          <?php endforeach; ?>
        </ul>

        <a href="<?php echo $base_path; ?>/user/exchanges.php"
           class="btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
          Explore Exchanges
        </a>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     06  TRUST & SAFETY — Surface Subtle Background
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-surface-subtle border-b border-subtle">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">Trust &amp; Safety</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">Built Around Student Trust</h2>
      <p class="mt-4 text-muted max-w-xl mx-auto text-base">
        Every aspect of Rentora is designed around transparency, honesty, and campus practicalities.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $trust = [
        ['🎓','bg-accent/10 text-accent','Verified Students',
         'Only registered Premier University students can use the platform with admin account verification.'],
        ['📍','bg-amber-500/10 text-amber-600 dark:text-amber-400','Campus Pickup Points',
         'Handovers take place at three designated spots — Hazari Lane, Wasa, and GEC Campus.'],
        ['📋','bg-surface-subtle text-muted','Clear Equipment Details',
         'Listings clearly display item name, condition, rental rate, and security deposit up front.'],
        ['💵','bg-emerald-500/10 text-emerald-600 dark:text-emerald-400','Cash Handover Only',
         'Cash handover in person — no complex online payment steps or digital wallet fees.'],
        ['🔒','bg-accent/10 text-accent','Refundable Security Deposit',
         'Security deposits are collected at handover to protect equipment owners.'],
        ['🪙','bg-rose-500/10 text-rose-600 dark:text-rose-400','No Platform Fees',
         'Rentora takes zero commission on rentals or exchanges between students.'],
      ];
      foreach ($trust as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-trust-card ab-fade ab-fade-d<?php echo ($i%6)+1; ?>">
          <div class="flex items-start gap-4">
            <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0 shadow-sm border border-subtle"><?php echo $ico; ?></div>
            <div>
              <h3 class="font-bold text-primary text-base mb-1.5"><?php echo htmlspecialchars($title); ?></h3>
              <p class="text-sm text-muted leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     07  MADE FOR CAMPUS LIFE — Surface Section
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-surface text-primary border-b border-subtle relative overflow-hidden">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">Campus Ecosystem</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">Made for Campus Life</h2>
      <p class="mt-4 text-muted max-w-2xl mx-auto text-lg font-medium">
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
          <h4 class="font-bold text-primary text-sm mb-1"><?php echo htmlspecialchars($name); ?></h4>
          <p class="text-xs text-muted leading-snug"><?php echo htmlspecialchars($sub); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     08  BENEFITS GRID — Canvas Background
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-canvas border-b border-subtle">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-3 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">Student Benefits</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-primary tracking-tight">Why Students Love Rentora</h2>
      <p class="mt-4 text-muted max-w-xl mx-auto text-base">Key advantages built into every interaction on Rentora Hub.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $benefits = [
        ['💳','bg-accent/10 text-accent','Affordable Access',
         'Access specialized equipment without buying expensive items outright.'],
        ['♻️','bg-emerald-500/10 text-emerald-600 dark:text-emerald-400','Less Unused Equipment',
         'Give idle gear a second life by renting or exchanging with classmates.'],
        ['🎯','bg-amber-500/10 text-amber-600 dark:text-amber-400','Easy Discovery',
         'Quickly search equipment by category or your preferred campus pickup spot.'],
        ['👥','bg-accent/10 text-accent','Campus Community',
         'Connect with fellow students through direct peer-to-peer handovers.'],
        ['⚙️','bg-teal-500/10 text-teal-600 dark:text-teal-400','Flexible Options',
         'Choose rental or exchange based on your current semester needs.'],
        ['✨','bg-accent/10 text-accent','Simple Experience',
         'No complicated online fees — straightforward cash payment at pickup.'],
      ];
      foreach ($benefits as $i => [$ico,$cls,$title,$desc]): ?>
        <div class="ab-benefit ab-fade ab-fade-d<?php echo ($i%6)+1; ?>">
          <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl mb-4 shadow-sm border border-subtle"><?php echo $ico; ?></div>
          <h3 class="font-bold text-primary text-base mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-muted leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     09  FINAL CTA — Surface Elevated Banner
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-surface-elevated text-primary py-20 sm:py-28 overflow-hidden">
  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade mb-6">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-accent mb-4 bg-accent/10 border border-accent/20 px-3.5 py-1 rounded-full">Get Started</span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight text-primary">
        Ready to make equipment<br class="hidden sm:block"> easier to access?
      </h2>
    </div>

    <p class="ab-fade ab-fade-d1 text-muted text-base sm:text-lg mb-10 max-w-xl mx-auto leading-relaxed">
      Browse available equipment or share something useful with your campus community today.
    </p>

    <div class="ab-fade ab-fade-d2 flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="<?php echo $base_path; ?>/index.php"
         class="btn-accent w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl text-white text-sm font-bold shadow-float">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Equipment
      </a>
      <a href="<?php echo $base_path; ?>/user/equipment.php"
         class="btn-secondary w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl text-sm font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        List Your Equipment
      </a>
    </div>

    <p class="ab-fade ab-fade-d3 mt-10 text-xs text-muted flex items-center justify-center gap-1.5">
      <svg class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
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
