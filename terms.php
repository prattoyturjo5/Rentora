<?php
session_start();
require_once(__DIR__ . '/includes/auth_guard.php');

$base_path = '.';
$page_title = 'Terms & Conditions | Rentora Hub';
$active_nav = 'terms';
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

/* ── Glow blobs & background elements ── */
.ab-blob {
  position:absolute; border-radius:9999px;
  filter:blur(80px); pointer-events:none; opacity:.16;
}

/* ── SVG wave divider ── */
.ab-wave { display:block; line-height:0; margin-bottom:-2px; }
.ab-wave svg { display:block; width:100%; height:auto; }

/* ── Cards & Panels ── */
.ab-term-card {
  background: #FFFFFF;
  border: 1px solid #D1DFEE;
  border-radius: 1.25rem;
  padding: 1.75rem;
  box-shadow: 0 4px 16px rgba(15, 35, 65, 0.05);
  transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
}
.ab-term-card:hover {
  transform: translateY(-3px);
  border-color: #93C5FD;
  box-shadow: 0 12px 28px rgba(15, 35, 65, 0.09);
}

/* ── Agreement UI Panel ── */
.ab-agreement-panel {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 1.5rem;
  box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.5);
  color: #FFFFFF;
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
  width:44px; height:44px; border-radius:50%;
  background:linear-gradient(135deg,#1d4ed8,#2563eb);
  color:#fff; font-size:.85rem; font-weight:800;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; position:relative; z-index:1;
  box-shadow:0 0 18px rgba(37,99,235,.5);
}

/* ── Warning Box ── */
.ab-warning-box {
  background: linear-gradient(135deg, #1E1014 0%, #0F172A 100%);
  border: 1px solid rgba(244, 63, 94, 0.3);
  border-radius: 1.5rem;
  color: #FFFFFF;
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
          <span class="w-2.5 h-2.5 rounded-full bg-blue-400 animate-pulse"></span>
          Rentora Platform Guidelines
        </div>

        <h1 class="ab-fade ab-fade-d2 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
          Terms &amp; <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-emerald-300 to-teal-300">Conditions</span>
        </h1>

        <p class="ab-fade ab-fade-d3 mt-5 text-xl sm:text-2xl text-blue-100 font-semibold leading-relaxed">
          Clear rules for safe, fair, and responsible equipment rentals and exchanges on Rentora Hub.
        </p>

        <p class="ab-fade ab-fade-d4 mt-4 text-base text-slate-300 max-w-2xl leading-relaxed">
          Designed specifically for university students to ensure transparent, accountable peer-to-peer equipment sharing and campus handovers.
        </p>
      </div>

      <!-- Right Abstract Legal & Document Visual -->
      <div class="lg:col-span-5 ab-fade ab-fade-d4 relative flex items-center justify-center min-h-[320px] sm:min-h-[380px]">
        
        <!-- Glassmorphic Shield & Document Container -->
        <div class="relative w-72 h-72 sm:w-80 sm:h-80 rounded-full border border-white/10 bg-white/5 backdrop-blur-xl flex items-center justify-center shadow-2xl shadow-navy-950/50">
          <div class="w-52 h-52 sm:w-60 sm:h-60 rounded-full border border-blue-400/20 bg-blue-500/10 flex items-center justify-center">
            
            <!-- Central Shield Icon Monogram -->
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-tr from-primary-600 to-blue-400 p-0.5 shadow-2xl shadow-blue-500/30 flex items-center justify-center transform -rotate-3 hover:rotate-0 transition-transform duration-300">
              <div class="w-full h-full bg-navy-900 rounded-[14px] flex flex-col items-center justify-center text-center p-3">
                <svg class="w-8 h-8 text-emerald-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span class="text-[10px] font-extrabold tracking-widest text-blue-200 uppercase">Verified</span>
              </div>
            </div>

          </div>
        </div>

        <!-- Floating Rule Pills -->
        <div class="absolute -top-2 left-4 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform -rotate-3">
          <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span>Fair Equipment Use</span>
        </div>

        <div class="absolute top-12 right-2 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform rotate-6">
          <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
          <span>Safe Campus Handover</span>
        </div>

        <div class="absolute bottom-8 left-2 px-3.5 py-2 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white text-xs font-semibold shadow-lg flex items-center gap-2 transform rotate-3">
          <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <span>Honest Disclosure</span>
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
     02  QUICK OVERVIEW — 4 Visual Cards (#F4F8FF)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20 border-b border-blue-100/60" style="background: #F4F8FF;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-12 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">At A Glance</span>
      <h2 class="text-3xl font-extrabold text-navy-900 tracking-tight">Platform Rules Summary</h2>
      <p class="mt-2 text-slate-600 text-sm max-w-xl mx-auto">Four core principles governing every rental and exchange on Rentora.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      $overview = [
        ['01','🤝','bg-blue-100 text-blue-700','Rent Responsibly',
         'Respect agreed rental terms, use gear with care, and return equipment promptly as arranged.'],
        ['02','🔄','bg-emerald-100 text-emerald-700','Exchange Fairly',
         'Accurately describe offered items and ensure mutual agreement before completing swaps.'],
        ['03','🛡️','bg-indigo-100 text-indigo-700','Handle Carefully',
         'Treat borrowed equipment as your own and disclose any pre-existing or new damage immediately.'],
        ['04','💬','bg-amber-100 text-amber-700','Communicate Clearly',
         'Discuss pickup spots, timing, condition, and deposits honestly with fellow students.'],
      ];
      foreach ($overview as $i => [$num,$ico,$cls,$title,$desc]): ?>
        <div class="ab-term-card ab-fade ab-fade-d<?php echo $i+1; ?>">
          <div class="flex items-center justify-between mb-4">
            <div class="<?php echo $cls; ?> w-11 h-11 rounded-xl flex items-center justify-center text-xl shadow-sm"><?php echo $ico; ?></div>
            <span class="text-xs font-extrabold text-slate-300 font-mono">STEP <?php echo $num; ?></span>
          </div>
          <h3 class="font-bold text-navy-900 text-lg mb-2"><?php echo htmlspecialchars($title); ?></h3>
          <p class="text-sm text-slate-600 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     03  GENERAL TERMS — (#EBF2FA)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #EBF2FA;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-8">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Section 1</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">1. General Terms of Use</h2>
    </div>

    <div class="ab-term-card ab-fade ab-fade-d1 space-y-4 text-slate-700 leading-relaxed text-sm sm:text-base">
      <p>
        <strong>1.1 Purpose of Rentora Hub:</strong> Rentora Hub operates as an online marketplace platform designed specifically to facilitate peer-to-peer equipment rentals and exchanges among university students.
      </p>
      <p>
        <strong>1.2 User Authenticity &amp; Accuracy:</strong> Users must provide accurate profile details and submit truthful descriptions, photographs, and conditions for all listed equipment.
      </p>
      <p>
        <strong>1.3 Honest Communication:</strong> All members agree to communicate transparently regarding item condition, availability, pickup timing, and rental terms.
      </p>
      <p>
        <strong>1.4 Respect of Agreements:</strong> Once a rental or exchange arrangement is confirmed between two members, both parties are expected to honor the agreed schedule and terms.
      </p>
      <p>
        <strong>1.5 Lawful Conduct:</strong> Users must not use Rentora Hub for any illegal, fraudulent, or unauthorized activity, nor list prohibited, stolen, or dangerous items.
      </p>
      <p>
        <strong>1.6 User Responsibility:</strong> Each member remains fully responsible for their account activity, personal conduct, and the individual agreements they enter into with other users.
      </p>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     04  EQUIPMENT RENTAL AGREEMENT — MAJOR SECTION (Dark Navy Panel)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-navy-950 text-white relative overflow-hidden" style="background: #0A0F1D;">
  <div class="ab-blob w-96 h-96 bg-blue-600" style="top:2rem;left:-6rem;opacity:.15;"></div>
  
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-3 bg-white/10 px-3.5 py-1 rounded-full backdrop-blur-md">Section 2</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">2. Equipment Rental Agreement</h2>
      <p class="mt-3 text-slate-300 max-w-xl mx-auto text-sm">Visual framework of rights and duties during equipment rental transactions.</p>
    </div>

    <!-- Visual Rental Agreement UI Frame -->
    <div class="ab-agreement-panel ab-fade ab-fade-d1 p-6 sm:p-10 space-y-8">
      
      <!-- Panel Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-white/15">
        <div>
          <span class="text-xs font-mono text-emerald-400 font-bold uppercase tracking-wider">Official Guidelines</span>
          <h3 class="text-xl sm:text-2xl font-bold text-white mt-1">Standard Rental Agreement Structure</h3>
        </div>
        <span class="px-3.5 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold">
          Peer-to-Peer Rental
        </span>
      </div>

      <!-- 4 Visual Agreement Blocks Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Block 1: Rental Parties -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-2">
          <div class="flex items-center gap-2 text-blue-400 font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Rental Parties
          </div>
          <ul class="text-xs text-slate-300 space-y-1.5 pt-1">
            <li>&bull; <strong>Equipment Owner:</strong> Member offering gear for temporary use.</li>
            <li>&bull; <strong>Renter:</strong> Verified member requesting and using the equipment.</li>
            <li>&bull; Both parties must be registered Premier University members.</li>
          </ul>
        </div>

        <!-- Block 2: Equipment Details -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-2">
          <div class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Equipment Specification
          </div>
          <ul class="text-xs text-slate-300 space-y-1.5 pt-1">
            <li>&bull; Item name, listing description, and condition rating.</li>
            <li>&bull; Accessories, cables, chargers, or cases included.</li>
            <li>&bull; Pre-existing cosmetic wear or known functional quirks.</li>
          </ul>
        </div>

        <!-- Block 3: Rental Terms -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-2">
          <div class="flex items-center gap-2 text-teal-400 font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Rental Terms &amp; Rates
          </div>
          <ul class="text-xs text-slate-300 space-y-1.5 pt-1">
            <li>&bull; Rental period duration (Daily, Weekly, or Semester).</li>
            <li>&bull; Agreed rental fee and refundable deposit (where applicable).</li>
            <li>&bull; Designated campus handover spot (Hazari Lane, Wasa, GEC).</li>
          </ul>
        </div>

        <!-- Block 4: Key Responsibilities -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-2">
          <div class="flex items-center gap-2 text-amber-400 font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Member Responsibilities
          </div>
          <ul class="text-xs text-slate-300 space-y-1.5 pt-1">
            <li>&bull; Renter must handle equipment carefully and return on time.</li>
            <li>&bull; Owner must provide equipment matching listing details.</li>
            <li>&bull; Both parties inspect item together during handover.</li>
          </ul>
        </div>

      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     05  EQUIPMENT CONDITION, DAMAGE & LOSS — (#F5F8FC)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #F5F8FC;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-10 text-center sm:text-left">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Section 3</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">3. Equipment Condition, Damage &amp; Loss</h2>
      <p class="mt-2 text-slate-600 text-sm">Guidelines for inspection, care during use, and return condition.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="ab-term-card ab-fade ab-fade-d1">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl mb-4 font-bold">1</div>
        <h3 class="font-bold text-navy-900 text-base mb-2">Before Rental (Handover)</h3>
        <ul class="text-xs text-slate-600 space-y-2">
          <li>&bull; Inspect equipment condition together at the pickup point.</li>
          <li>&bull; Verify all accessories, cables, and parts are present.</li>
          <li>&bull; Note pre-existing cosmetic scratches or minor wear.</li>
        </ul>
      </div>

      <div class="ab-term-card ab-fade ab-fade-d2">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-4 font-bold">2</div>
        <h3 class="font-bold text-navy-900 text-base mb-2">During Rental (Care)</h3>
        <ul class="text-xs text-slate-600 space-y-2">
          <li>&bull; Use equipment strictly for intended academic &amp; project work.</li>
          <li>&bull; Do not modify, disassemble, or alter equipment hardware.</li>
          <li>&bull; Store safely in protective bags or cases when not in use.</li>
        </ul>
      </div>

      <div class="ab-term-card ab-fade ab-fade-d3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-4 font-bold">3</div>
        <h3 class="font-bold text-navy-900 text-base mb-2">After Rental (Return)</h3>
        <ul class="text-xs text-slate-600 space-y-2">
          <li>&bull; Return equipment in agreed condition (normal wear excepted).</li>
          <li>&bull; Report any new damage or loss to owner immediately.</li>
          <li>&bull; Resolve minor damage based on agreed deposit terms.</li>
        </ul>
      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     06  RENTAL FEES & PAYMENTS — (#F8FAFC)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #F8FAFC;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-8">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Section 4</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">4. Rental Fees &amp; Payments</h2>
    </div>

    <div class="ab-term-card ab-fade ab-fade-d1 space-y-4 text-slate-700 leading-relaxed text-sm sm:text-base">
      <p>
        <strong>4.1 Agreed Pricing:</strong> All rental rates and optional security deposits must be agreed upon between the equipment owner and renter prior to handover.
      </p>
      <p>
        <strong>4.2 Cash Handover Policy:</strong> Rentora Hub operates on an in-person, cash-only handover policy at designated campus pickup spots. The platform does not process online payments or charge hidden middleman commissions.
      </p>
      <p>
        <strong>4.3 Clear Expectations:</strong> Both parties should maintain clear written agreement records (via listing details and chat messages) regarding agreed pricing, duration, and deposit return terms.
      </p>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     07  RENTAL HANDOVER PROCESS TIMELINE — (#0F172A)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-navy-900 text-white relative overflow-hidden" style="background: #0F172A;">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative">

    <div class="text-center mb-16 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-3 bg-white/10 px-3.5 py-1 rounded-full">Handover Workflow</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Rental Handover &amp; Return Lifecycle</h2>
    </div>

    <?php
    $lifecycle = [
      ['01','AGREE','Confirm terms, pricing & rental duration.'],
      ['02','INSPECT','Inspect item condition together at pickup.'],
      ['03','HANDOVER','Exchange item & cash payment in person.'],
      ['04','USE','Use equipment responsibly for coursework.'],
      ['05','RETURN','Meet at handover spot & return equipment.'],
      ['06','CONFIRM','Confirm return & refund security deposit.'],
    ];
    ?>

    <!-- Desktop horizontal grid -->
    <div class="hidden md:grid grid-cols-6 gap-4">
      <?php foreach ($lifecycle as $i => [$num,$step,$desc]): ?>
        <div class="ab-fade ab-fade-d<?php echo $i+1; ?> bg-white/5 border border-white/10 rounded-xl p-4 text-center backdrop-blur-md flex flex-col items-center">
          <div class="ab-num text-xs mb-3"><?php echo $num; ?></div>
          <h4 class="font-bold text-emerald-400 text-xs tracking-wider mb-1.5"><?php echo $step; ?></h4>
          <p class="text-[11px] text-slate-300 leading-snug"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Mobile vertical stack -->
    <div class="md:hidden space-y-4">
      <?php foreach ($lifecycle as $i => [$num,$step,$desc]): ?>
        <div class="ab-fade ab-fade-d<?php echo $i+1; ?> bg-white/5 border border-white/10 rounded-xl p-4 flex gap-4 items-center">
          <div class="ab-num text-xs"><?php echo $num; ?></div>
          <div>
            <h4 class="font-bold text-emerald-400 text-xs tracking-wider mb-0.5"><?php echo $step; ?></h4>
            <p class="text-xs text-slate-300"><?php echo htmlspecialchars($desc); ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     08  EQUIPMENT EXCHANGE AGREEMENT — MAJOR SECTION (Dual Gradient)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24" style="background: linear-gradient(135deg, #EFF6FF 0%, #ECFDF5 100%);">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-3 bg-white px-3.5 py-1 rounded-full shadow-sm">Section 5</span>
      <h2 class="text-3xl sm:text-4xl font-extrabold text-navy-900 tracking-tight">5. Equipment Exchange Agreement</h2>
      <p class="mt-3 text-slate-600 text-sm max-w-xl mx-auto">Rules governing peer-to-peer equipment swaps and exchanges.</p>
    </div>

    <!-- Visual Exchange Agreement Panel -->
    <div class="bg-white rounded-2xl border border-blue-200 p-6 sm:p-10 shadow-xl space-y-8 ab-fade ab-fade-d1">
      
      <div class="flex items-center justify-between pb-6 border-b border-slate-100">
        <div>
          <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Swap Framework</span>
          <h3 class="text-xl font-bold text-navy-900 mt-1">Peer Equipment Exchange Structure</h3>
        </div>
        <span class="px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
          Direct Exchange
        </span>
      </div>

      <!-- Diagram: User A <--> User B -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center text-center">
        
        <!-- User A Card -->
        <div class="md:col-span-2 bg-blue-50/80 border border-blue-100 rounded-xl p-5 text-left space-y-2">
          <div class="text-xs font-bold uppercase text-primary-700">Participant A (Offer)</div>
          <h4 class="font-bold text-navy-900 text-sm">Equipment Offered</h4>
          <p class="text-xs text-slate-600">Accurate item description, condition rating, and included accessories.</p>
        </div>

        <!-- Exchange Icon Center -->
        <div class="md:col-span-1 flex flex-col items-center justify-center py-2">
          <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-600/30">
            🔄
          </div>
          <span class="text-[11px] font-bold text-slate-400 mt-1">MUTUAL AGREEMENT</span>
        </div>

        <!-- User B Card -->
        <div class="md:col-span-2 bg-emerald-50/80 border border-emerald-100 rounded-xl p-5 text-left space-y-2">
          <div class="text-xs font-bold uppercase text-emerald-700">Participant B (Receive)</div>
          <h4 class="font-bold text-navy-900 text-sm">Equipment Received</h4>
          <p class="text-xs text-slate-600">Inspected item matching agreed exchange value and description.</p>
        </div>

      </div>

      <!-- Exchange Principles -->
      <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-600">
        <div class="flex items-start gap-2">
          <span class="text-emerald-500 font-bold">✓</span>
          <span>Both parties must voluntarily agree to exchange terms before meeting.</span>
        </div>
        <div class="flex items-start gap-2">
          <span class="text-emerald-500 font-bold">✓</span>
          <span>Equipment defects or damage must be disclosed honestly prior to swap.</span>
        </div>
        <div class="flex items-start gap-2">
          <span class="text-emerald-500 font-bold">✓</span>
          <span>Inspect both items thoroughly during in-person campus handover.</span>
        </div>
        <div class="flex items-start gap-2">
          <span class="text-emerald-500 font-bold">✓</span>
          <span>Confirm exchange completion only after receiving and verifying gear.</span>
        </div>
      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     09  EXCHANGE RESPONSIBILITIES & COMPLETION — (#F4F8FF)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20 border-b border-blue-100/60" style="background: #F4F8FF;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-10 text-center sm:text-left">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Exchange Guidelines</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">Exchange Responsibilities &amp; Completion</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php
      $ex_resp = [
        ['Honest Description','Describe equipment specifications and history truthfully.'],
        ['Condition Disclosure','Explicitly mention pre-existing defects, cracks, or flaws.'],
        ['Fair Agreement','Both parties must voluntarily understand and accept terms.'],
        ['Safe Handover','Complete the exchange at Hazari Lane, Wasa, or GEC spots.'],
      ];
      foreach ($ex_resp as $i => [$title,$desc]): ?>
        <div class="ab-term-card ab-fade ab-fade-d<?php echo $i+1; ?>">
          <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs mb-3">✓</div>
          <h4 class="font-bold text-navy-900 text-sm mb-1.5"><?php echo htmlspecialchars($title); ?></h4>
          <p class="text-xs text-slate-600 leading-relaxed"><?php echo htmlspecialchars($desc); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     10  CANCELLATION & CHANGES — (#EBF2FA)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #EBF2FA;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-8">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Section 6</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">6. Cancellation &amp; Changes</h2>
    </div>

    <div class="ab-term-card ab-fade ab-fade-d1 space-y-4 text-slate-700 leading-relaxed text-sm sm:text-base">
      <p>
        <strong>6.1 Early Notification:</strong> If a member is unable to complete an agreed rental or exchange, they must notify the other party as early as possible.
      </p>
      <p>
        <strong>6.2 Respect for Time:</strong> Members must not intentionally waste fellow students' time by failing to show up at handover points without notice.
      </p>
      <p>
        <strong>6.3 Cancellation Resolution:</strong> Cancellations prior to handover carry no financial penalty beyond returning any pre-collected deposit where applicable.
      </p>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     11  USER RESPONSIBILITIES CHECKLIST — (#F5F8FC)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #F5F8FC;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-fade mb-10 text-center sm:text-left">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-primary-600 mb-2 bg-blue-100/80 px-3.5 py-1 rounded-full">Section 7</span>
      <h2 class="text-2xl sm:text-3xl font-extrabold text-navy-900 tracking-tight">7. Member Conduct Checklist</h2>
      <p class="mt-2 text-slate-600 text-sm">Every Rentora member agrees to follow these behavioral standards.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php
      $checklist = [
        'Provide accurate profile info',
        'Describe equipment honestly',
        'Respect rental & exchange terms',
        'Handle equipment responsibly',
        'Communicate clearly with peers',
        'Return rented gear on time',
        'Disclose known item damage',
        'Respect fellow students',
      ];
      foreach ($checklist as $i => $item): ?>
        <div class="ab-term-card ab-fade ab-fade-d<?php echo ($i%4)+1; ?> flex items-center gap-3 p-4">
          <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-xs flex-shrink-0">✓</span>
          <span class="text-xs font-semibold text-navy-900"><?php echo htmlspecialchars($item); ?></span>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     12  PROHIBITED USE — Warning Section (Rose/Dark Gradient)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-navy-950 text-white relative overflow-hidden" style="background: #0F172A;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="ab-warning-box p-6 sm:p-10 space-y-6 ab-fade ab-fade-d1">
      <div class="flex items-center gap-3 text-rose-400">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
          <span class="text-xs font-bold uppercase tracking-widest text-rose-400">Section 8</span>
          <h3 class="text-2xl font-extrabold text-white">8. Prohibited Activities</h3>
        </div>
      </div>

      <p class="text-sm text-slate-300 leading-relaxed">
        The following activities are strictly prohibited on Rentora Hub and will lead to immediate account suspension:
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-300">
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>Fraudulent listings or non-existent equipment</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>False condition claims or deceptive photos</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>Deliberate damage or sabotage of equipment</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>Theft, non-return, or unauthorized selling</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>Harassment, intimidation, or abusive conduct</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-rose-400 font-bold">✕</span>
          <span>Creating duplicate or fake student profiles</span>
        </div>
      </div>
    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     13  DISPUTES, ACCOUNT RESPONSIBILITY & PLATFORM ROLE — (#0A0F1D)
════════════════════════════════════════════════════════════════ -->
<section class="py-20 sm:py-24 bg-navy-950 text-white relative overflow-hidden" style="background: #0A0F1D;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-14 ab-fade">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-3 bg-white/10 px-3.5 py-1 rounded-full">Sections 9, 10 &amp; 11</span>
      <h2 class="text-3xl font-extrabold text-white tracking-tight">Disputes, Security &amp; Platform Role</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Section 9: Disputes -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md ab-fade ab-fade-d1 space-y-3">
        <h3 class="font-bold text-white text-base text-blue-400">9. Disputes &amp; Resolution</h3>
        <p class="text-xs text-slate-300 leading-relaxed">
          Members should first attempt to resolve rental or exchange disagreements directly through respectful communication and reviewing listing records.
        </p>
      </div>

      <!-- Section 10: Account Responsibility -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md ab-fade ab-fade-d2 space-y-3">
        <h3 class="font-bold text-white text-base text-emerald-400">10. Account Security</h3>
        <p class="text-xs text-slate-300 leading-relaxed">
          Users are responsible for maintaining the confidentiality of their login credentials and reporting any unauthorized account access immediately.
        </p>
      </div>

      <!-- Section 11: Rentora's Role -->
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md ab-fade ab-fade-d3 space-y-3">
        <h3 class="font-bold text-white text-base text-teal-400">11. About Rentora's Role</h3>
        <p class="text-xs text-slate-300 leading-relaxed">
          Rentora Hub acts as a marketplace platform connecting students. Users enter into rental and exchange agreements directly with one another.
        </p>
      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     14  PRIVACY, COMMUNICATION & CHANGES TO TERMS — (#F8FAFC)
════════════════════════════════════════════════════════════════ -->
<section class="py-16 sm:py-20" style="background: #F8FAFC;">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

      <!-- Section 12: Privacy -->
      <div class="ab-term-card ab-fade ab-fade-d1 space-y-3">
        <span class="text-xs font-bold uppercase text-primary-600">Section 12</span>
        <h3 class="text-xl font-bold text-navy-900">Privacy &amp; Communication</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Users should avoid unnecessarily sharing sensitive personal financial details. Communication regarding rentals and exchanges should be conducted responsibly.
        </p>
      </div>

      <!-- Section 13: Changes to Terms -->
      <div class="ab-term-card ab-fade ab-fade-d2 space-y-3">
        <span class="text-xs font-bold uppercase text-primary-600">Section 13</span>
        <h3 class="text-xl font-bold text-navy-900">Changes to These Terms</h3>
        <p class="text-xs text-slate-600 leading-relaxed">
          Rentora Hub may update these Terms &amp; Conditions periodically as the platform evolves. Continued use of rental and exchange features constitutes acceptance of updated terms.
        </p>
      </div>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     15  FINAL AGREEMENT NOTICE & CTA — Dark Navy & Blue Banner
════════════════════════════════════════════════════════════════ -->
<section class="relative bg-gradient-to-r from-navy-950 via-primary-900 to-navy-900 text-white py-20 sm:py-28 overflow-hidden">
  <div class="ab-blob w-96 h-96 bg-blue-500" style="top:-5rem;right:-5rem;opacity:.12;"></div>
  <div class="ab-blob w-64 h-64 bg-emerald-500" style="bottom:0;left:-3rem;opacity:.08;"></div>

  <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

    <div class="ab-fade mb-6">
      <span class="inline-block text-xs font-bold uppercase tracking-widest text-blue-300 mb-4 bg-white/10 px-3.5 py-1 rounded-full">Final Notice</span>
      <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
        Before You Rent or Exchange
      </h2>
    </div>

    <p class="ab-fade ab-fade-d1 text-blue-100 text-base sm:text-lg mb-10 max-w-xl mx-auto leading-relaxed">
      Make sure you understand the equipment, condition, terms, handover process, and responsibilities before completing a rental or exchange.
    </p>

    <div class="ab-fade ab-fade-d2 flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="<?php echo $base_path; ?>/index.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-white text-navy-900 text-sm font-bold shadow-xl hover:bg-blue-50 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Browse Equipment
      </a>
      <a href="<?php echo $base_path; ?>/user/exchanges.php"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/25 text-white text-sm font-semibold backdrop-blur-md transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        Explore Exchanges
      </a>
    </div>

    <p class="ab-fade ab-fade-d3 mt-10 text-xs text-blue-200/80 flex items-center justify-center gap-1.5">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
      Official Campus Handover Spots: Hazari Lane &nbsp;&middot;&nbsp; Wasa &nbsp;&middot;&nbsp; GEC Campus
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
