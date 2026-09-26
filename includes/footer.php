<?php
/**
 * Shared Footer Partial
 */
$base_path = $base_path ?? '.';
?>
<?php if (!empty($has_page_container)): ?>
</div><!-- /#page-container -->
<?php endif; ?>
  <!-- Footer -->
  <footer class="mt-auto bg-navy-950 text-slate-400 py-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-primary-600 flex items-center justify-center text-white font-bold text-xs">
              R
            </div>
            <span class="text-lg font-bold text-white tracking-tight">Rentora</span>
          </div>
          <p class="text-xs text-slate-400 leading-relaxed">
            Campus Equipment Exchange & Rental Hub. Secure peer-to-peer equipment sharing, refundable security deposits, and handover token verification.
          </p>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Quick Navigation</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="<?php echo $base_path; ?>/index.php" class="hover:text-white transition-colors">Browse All Equipment</a></li>
            <li><a href="<?php echo $base_path; ?>/about.php" class="hover:text-white transition-colors">About Rentora Hub</a></li>
            <li><a href="<?php echo $base_path; ?>/user/equipment.php" class="hover:text-white transition-colors">Equipment Listings</a></li>
            <li><a href="<?php echo $base_path; ?>/user/rentals.php" class="hover:text-white transition-colors">Rental Agreements</a></li>
            <li><a href="<?php echo $base_path; ?>/user/exchanges.php" class="hover:text-white transition-colors">Exchange Hub</a></li>
            <li><a href="<?php echo $base_path; ?>/terms.php" class="hover:text-white transition-colors">Terms &amp; Conditions</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Official Handover Spots</h4>
          <ul class="space-y-2 text-xs">
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Hazari Lane</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Wasa</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> GEC Campus</li>
          </ul>
        </div>

        <div class="space-y-3">
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Payment & Protection</h4>
          <div class="p-3 bg-slate-900 rounded-xl border border-slate-800 text-xs">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span class="font-bold text-white">Cash-Only Handover</span>
            </div>
            <p class="text-[11px] text-slate-400">
              Cash handover only, no online payments. Safe in-person handover with a refundable security deposit.
            </p>
          </div>
        </div>

      </div>

      <div class="mt-10 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-400">
        <div>&copy; <?php echo date('Y'); ?> Rentora Hub. University DBMS Platform.</div>
        <div class="flex gap-4">
          <a href="<?php echo $base_path; ?>/terms.php" class="hover:text-white">Terms &amp; Conditions</a>
          <a href="<?php echo $base_path; ?>/admin/login.php" class="text-amber-400 hover:underline font-semibold">Admin Console</a>
          <a href="<?php echo $base_path; ?>/auth/login.php" class="hover:text-white">Member Login</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="<?php echo $base_path; ?>/assets/js/app.js"></script>
</body>
</html>
