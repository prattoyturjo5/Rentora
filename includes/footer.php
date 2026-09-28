<?php
/**
 * Shared Footer Partial - Modern Floating UI Design System
 */
$base_path = $base_path ?? '.';
?>
<?php if (!empty($has_page_container)): ?>
</div><!-- /#page-container -->
<?php endif; ?>
  <!-- Modern Floating Footer -->
  <footer class="mt-auto bg-surface text-muted py-12 border-t border-subtle transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        
        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-accent flex items-center justify-center text-white font-bold text-xs shadow-sm shadow-accent/20">
              R
            </div>
            <span class="text-lg font-bold text-primary tracking-tight">Rentora</span>
          </div>
          <p class="text-xs text-muted leading-relaxed">
            Campus Equipment Exchange &amp; Rental Hub. Secure peer-to-peer equipment sharing, refundable security deposits, and handover token verification.
          </p>
        </div>

        <div>
          <h4 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Quick Navigation</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="<?php echo $base_path; ?>/index.php" class="hover:text-primary transition-colors">Browse All Equipment</a></li>
            <li><a href="<?php echo $base_path; ?>/about.php" class="hover:text-primary transition-colors">About Rentora Hub</a></li>
            <li><a href="<?php echo $base_path; ?>/join.php" class="hover:text-primary transition-colors text-accent font-semibold flex items-center gap-1"><span>Join Development Team</span> <span class="text-[10px]">&rarr;</span></a></li>
            <li><a href="<?php echo $base_path; ?>/user/equipment.php" class="hover:text-primary transition-colors">Equipment Listings</a></li>
            <li><a href="<?php echo $base_path; ?>/user/rentals.php" class="hover:text-primary transition-colors">Rental Agreements</a></li>
            <li><a href="<?php echo $base_path; ?>/user/exchanges.php" class="hover:text-primary transition-colors">Exchange Hub</a></li>
            <li><a href="<?php echo $base_path; ?>/terms.php" class="hover:text-primary transition-colors">Terms &amp; Conditions</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Official Handover Spots</h4>
          <ul class="space-y-2 text-xs">
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Hazari Lane</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-accent"></span> Wasa</li>
            <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-accent"></span> GEC Campus</li>
          </ul>
        </div>

        <div class="space-y-3">
          <h4 class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Protection &amp; Safety</h4>
          <div class="p-3 bg-surface-subtle rounded-xl border border-subtle text-xs">
            <div class="flex items-center gap-2 mb-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span class="font-bold text-primary">In-Person Handover</span>
            </div>
            <p class="text-[11px] text-muted">
              Physical handover on campus with refundable deposit protection and zero platform surcharge.
            </p>
          </div>
        </div>

      </div>

      <div class="mt-10 pt-6 border-t border-subtle flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-muted">
        <div>&copy; <?php echo date('Y'); ?> Rentora Hub. University DBMS Platform.</div>
        <div class="flex gap-4">
          <a href="<?php echo $base_path; ?>/terms.php" class="hover:text-primary transition-colors">Terms &amp; Conditions</a>
          <a href="<?php echo $base_path; ?>/admin/login.php" class="text-accent hover:underline font-semibold">Admin Console</a>
          <a href="<?php echo $base_path; ?>/auth/login.php" class="hover:text-primary transition-colors">Member Login</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="<?php echo $base_path; ?>/assets/js/app.js"></script>
</body>
</html>
