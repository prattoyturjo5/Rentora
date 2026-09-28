/**
 * ============================================================================
 * RENTORA HUB - CRYSTALLINE OPTICAL VACUUM PHYSICS ENGINE
 * ============================================================================
 * - Frictionless wave of energy navigation with cubic-bezier continuity
 * - Optical white crystalline spacetime lattice & subtle geodesic curvature
 * - Refined 3D card elevation physics with dynamic specular blue refraction
 * - Web Audio API acoustic resonance synthesizer
 * ============================================================================
 */

(function () {
  'use strict';

  /* Crystalline Telemetry Vector */
  const Telemetry = {
    cursor: { x: window.innerWidth / 2, y: window.innerHeight / 2, vx: 0, vy: 0 },
    screen: { w: window.innerWidth, h: window.innerHeight },
    shockwaves: [],
    audioEnabled: false,
    audioCtx: null
  };

  /* Cursor Tracking */
  window.addEventListener('mousemove', function (e) {
    Telemetry.cursor.vx = e.clientX - Telemetry.cursor.x;
    Telemetry.cursor.vy = e.clientY - Telemetry.cursor.y;
    Telemetry.cursor.x = e.clientX;
    Telemetry.cursor.y = e.clientY;
  }, { passive: true });

  window.addEventListener('resize', function () {
    Telemetry.screen.w = window.innerWidth;
    Telemetry.screen.h = window.innerHeight;
    if (CrystallineCanvas.canvas) CrystallineCanvas.resize();
  });

  /* Crystalline Optical Pulse on Click */
  window.addEventListener('click', function (e) {
    if (e.target && ['INPUT', 'SELECT', 'BUTTON', 'A'].includes(e.target.tagName)) return;
    Telemetry.shockwaves.push({
      x: e.clientX,
      y: e.clientY,
      radius: 4,
      maxRadius: Math.max(Telemetry.screen.w, Telemetry.screen.h) * 0.6,
      speed: 20,
      intensity: 0.8
    });
    if (Telemetry.audioEnabled) AudioMatrix.playPulse();
  });

  /* ==========================================================================
     1. SUBTLE CRYSTALLINE SPACETIME GRID CANVAS
     ========================================================================== */
  const CrystallineCanvas = {
    canvas: null,
    ctx: null,
    gridCols: 16,
    gridRows: 10,
    nodes: [],
    particles: [],

    init: function () {
      this.canvas = document.getElementById('spacetime-canvas');
      if (!this.canvas) {
        this.canvas = document.createElement('canvas');
        this.canvas.id = 'spacetime-canvas';
        document.body.prepend(this.canvas);
      }
      this.ctx = this.canvas.getContext('2d');
      this.resize();
      this.buildGrid();
      this.buildParticles(24);
      this.loop();
    },

    resize: function () {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.canvas.width = window.innerWidth * dpr;
      this.canvas.height = window.innerHeight * dpr;
      this.ctx.scale(dpr, dpr);
    },

    buildGrid: function () {
      this.nodes = [];
      const stepX = Telemetry.screen.w / (this.gridCols - 1);
      const stepY = Telemetry.screen.h / (this.gridRows - 1);

      for (let r = 0; r < this.gridRows; r++) {
        for (let c = 0; c < this.gridCols; c++) {
          const originX = c * stepX;
          const originY = r * stepY;
          this.nodes.push({
            origX: originX,
            origY: originY,
            x: originX,
            y: originY,
            vx: 0,
            vy: 0
          });
        }
      }
    },

    buildParticles: function (count) {
      this.particles = [];
      const colors = ['#151B54', '#6C75D4', '#A5B4FC', '#EFF3FF'];
      for (let i = 0; i < count; i++) {
        this.particles.push({
          x: Math.random() * Telemetry.screen.w,
          y: Math.random() * Telemetry.screen.h,
          vx: (Math.random() - 0.5) * 0.7,
          vy: (Math.random() - 0.5) * 0.7,
          size: Math.random() * 2 + 1,
          color: colors[Math.floor(Math.random() * colors.length)],
          alpha: Math.random() * 0.35 + 0.15
        });
      }
    },

    update: function () {
      const curX = Telemetry.cursor.x;
      const curY = Telemetry.cursor.y;
      const gravityRadius = 380;
      const gravityRadiusSq = gravityRadius * gravityRadius;

      // Update Spacetime Grid
      for (let i = 0; i < this.nodes.length; i++) {
        const node = this.nodes[i];
        const dx = curX - node.origX;
        const dy = curY - node.origY;
        const distSq = dx * dx + dy * dy;

        let targetX = node.origX;
        let targetY = node.origY;

        if (distSq < gravityRadiusSq && distSq > 15) {
          const dist = Math.sqrt(distSq);
          const force = (1 - dist / gravityRadius) * 28;
          targetX += (dx / dist) * force;
          targetY += (dy / dist) * force;
        }

        // Apply Shockwaves
        for (let s = 0; s < Telemetry.shockwaves.length; s++) {
          const sw = Telemetry.shockwaves[s];
          const swDx = node.origX - sw.x;
          const swDy = node.origY - sw.y;
          const swDist = Math.sqrt(swDx * swDx + swDy * swDy);
          const waveDelta = Math.abs(swDist - sw.radius);
          if (waveDelta < 45) {
            const waveForce = (1 - waveDelta / 45) * sw.intensity * 18;
            targetX += (swDx / (swDist || 1)) * waveForce;
            targetY += (swDy / (swDist || 1)) * waveForce;
          }
        }

        node.vx += (targetX - node.x) * 0.12;
        node.vy += (targetY - node.y) * 0.12;
        node.vx *= 0.78;
        node.vy *= 0.78;
        node.x += node.vx;
        node.y += node.vy;
      }

      // Update Shockwaves
      for (let s = Telemetry.shockwaves.length - 1; s >= 0; s--) {
        const sw = Telemetry.shockwaves[s];
        sw.radius += sw.speed;
        sw.intensity *= 0.95;
        if (sw.radius > sw.maxRadius || sw.intensity < 0.04) {
          Telemetry.shockwaves.splice(s, 1);
        }
      }

      // Update Floating Crystalline Nodes
      for (let i = 0; i < this.particles.length; i++) {
        const p = this.particles[i];
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < 0) p.x = Telemetry.screen.w;
        if (p.x > Telemetry.screen.w) p.x = 0;
        if (p.y < 0) p.y = Telemetry.screen.h;
        if (p.y > Telemetry.screen.h) p.y = 0;
      }
    },

    render: function () {
      const ctx = this.ctx;
      ctx.clearRect(0, 0, Telemetry.screen.w, Telemetry.screen.h);

      // Fine-line Crystalline Grid in Delicate Azure
      ctx.lineWidth = 0.8;
      ctx.strokeStyle = 'rgba(21, 27, 84, 0.06)';

      for (let r = 0; r < this.gridRows; r++) {
        ctx.beginPath();
        for (let c = 0; c < this.gridCols; c++) {
          const n = this.nodes[r * this.gridCols + c];
          if (c === 0) ctx.moveTo(n.x, n.y);
          else ctx.lineTo(n.x, n.y);
        }
        ctx.stroke();
      }

      for (let c = 0; c < this.gridCols; c++) {
        ctx.beginPath();
        for (let r = 0; r < this.gridRows; r++) {
          const n = this.nodes[r * this.gridCols + c];
          if (r === 0) ctx.moveTo(n.x, n.y);
          else ctx.lineTo(n.x, n.y);
        }
        ctx.stroke();
      }

      // Shockwaves in Soft Blue
      for (let s = 0; s < Telemetry.shockwaves.length; s++) {
        const sw = Telemetry.shockwaves[s];
        ctx.beginPath();
        ctx.arc(sw.x, sw.y, sw.radius, 0, Math.PI * 2);
        ctx.strokeStyle = `rgba(37, 99, 235, ${sw.intensity * 0.35})`;
        ctx.lineWidth = 1.5;
        ctx.stroke();
      }

      // Crystalline Particles
      for (let i = 0; i < this.particles.length; i++) {
        const p = this.particles[i];
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fillStyle = p.color;
        ctx.globalAlpha = p.alpha;
        ctx.fill();
        ctx.globalAlpha = 1.0;
      }
    },

    loop: function () {
      const self = CrystallineCanvas;
      self.update();
      self.render();
      requestAnimationFrame(self.loop);
    }
  };

  /* ==========================================================================
     2. FRICTIONLESS WAVE OF ENERGY NAVIGATION
     Gliding effortlessly with cubic-bezier continuity and zero drag
     ========================================================================== */
  const FrictionlessWaveNav = {
    nav: null,
    pill: null,
    activeLink: null,
    currentKey: null,

    init: function () {
      this.nav = document.getElementById('main-nav');
      this.pill = document.getElementById('nav-pill');
      if (!this.nav || !this.pill) return;

      this.activeLink = this.nav.querySelector('a[aria-current="page"]');
      this.currentKey = this.activeLink ? this.activeLink.getAttribute('data-nav-key') : 'browse';

      // Insert subtle laser conduit line if not present
      const header = document.querySelector('header');
      if (header && !header.querySelector('.laser-guide-conduit')) {
        const conduit = document.createElement('div');
        conduit.className = 'laser-guide-conduit';
        header.appendChild(conduit);
      }

      // Initial placement without transition lag
      this.glideTo(this.activeLink, false);
      this.bindEvents();
    },

    measure: function (elem) {
      if (!elem || !this.nav) return { left: 0, top: 0, width: 0, height: 0, cx: 0, cy: 0 };
      const nR = this.nav.getBoundingClientRect();
      const eR = elem.getBoundingClientRect();
      return {
        left: eR.left - nR.left,
        top: eR.top - nR.top,
        width: eR.width,
        height: eR.height,
        cx: eR.left + eR.width / 2,
        cy: eR.top + eR.height / 2
      };
    },

    glideTo: function (targetElem, animate) {
      if (!targetElem || !this.pill) return;
      const geom = this.measure(targetElem);

      if (!animate) {
        this.pill.style.transition = 'none';
        this.pill.style.left = geom.left + 'px';
        this.pill.style.top = geom.top + 'px';
        this.pill.style.width = geom.width + 'px';
        this.pill.style.height = geom.height + 'px';
        this.pill.style.opacity = '1';
        return;
      }

      // Frictionless cubic-bezier wave acceleration
      this.pill.style.transition = 'left 240ms cubic-bezier(0.16, 1, 0.3, 1), width 240ms cubic-bezier(0.16, 1, 0.3, 1), top 240ms cubic-bezier(0.16, 1, 0.3, 1), height 240ms cubic-bezier(0.16, 1, 0.3, 1), opacity 150ms ease';
      this.pill.style.left = geom.left + 'px';
      this.pill.style.top = geom.top + 'px';
      this.pill.style.width = geom.width + 'px';
      this.pill.style.height = geom.height + 'px';
      this.pill.style.opacity = '1';

      this.spawnWavePulse(geom.cx, geom.cy);
      if (Telemetry.audioEnabled) AudioMatrix.playGlide();
    },

    spawnWavePulse: function (x, y) {
      const ring = document.createElement('div');
      ring.className = 'photonic-burst-ring';
      ring.style.left = x + 'px';
      ring.style.top = y + 'px';
      ring.style.width = '44px';
      ring.style.height = '44px';
      document.body.appendChild(ring);
      setTimeout(() => {
        if (ring.parentNode) ring.parentNode.removeChild(ring);
      }, 260);
    },

    bindEvents: function () {
      const links = this.nav.querySelectorAll('a[data-nav-key]');

      links.forEach((link) => {
        // Effortless frictionless gliding on hover
        link.addEventListener('mouseenter', () => {
          this.glideTo(link, true);
        });

        // Instant smooth snap on click
        link.addEventListener('click', () => {
          const destKey = link.getAttribute('data-nav-key');
          if (destKey === this.currentKey) return;
          this.glideTo(link, true);
        });
      });

      // Reset to active link on mouse leave (or fade out if active is in orbital rim)
      this.nav.addEventListener('mouseleave', () => {
        if (this.activeLink) {
          this.glideTo(this.activeLink, true);
        } else if (this.pill) {
          this.pill.style.opacity = '0';
        }
      });
    }
  };

  /* ==========================================================================
     3. REFINED 3D CARD ELEVATIONS (Cursor Gravitation Physics)
     ========================================================================== */
  const ElevatedCards = {
    cards: [],
    initialized: false,

    init: function () {
      const cardElems = document.querySelectorAll('.holo-equipment-card');
      if (!cardElems.length) return;

      this.cards = [];
      cardElems.forEach((card, index) => {
        const wrapper = card.closest('.zero-g-card-wrapper') || card.parentElement;
        const phase = index % 4;
        wrapper.classList.add(`zero-g-phase-${phase}`);

        // Ensure crystalline sheen layer & corner reticles
        if (!card.querySelector('.holo-sheen-layer')) {
          const sheen = document.createElement('div');
          sheen.className = 'holo-sheen-layer';
          card.prepend(sheen);
        }
        if (!card.querySelector('.hud-corner-tl')) {
          const tl = document.createElement('div');
          tl.className = 'hud-corner-tl';
          const br = document.createElement('div');
          br.className = 'hud-corner-br';
          card.appendChild(tl);
          card.appendChild(br);
        }

        this.cards.push({
          card: card,
          wrapper: wrapper,
          curRotX: 0,
          curRotY: 0,
          curTransZ: 0,
          targetRotX: 0,
          targetRotY: 0,
          targetTransZ: 0
        });

        card.addEventListener('mouseenter', () => {
          wrapper.classList.add('gravitational-lock');
          if (Telemetry.audioEnabled) AudioMatrix.playCardHover();
        });

        card.addEventListener('mouseleave', () => {
          wrapper.classList.remove('gravitational-lock');
        });
      });

      this.initialized = true;
      this.loop();
    },

    update: function () {
      if (!this.initialized) return;
      const curX = Telemetry.cursor.x;
      const curY = Telemetry.cursor.y;
      const influenceDist = 550;

      for (let i = 0; i < this.cards.length; i++) {
        const item = this.cards[i];
        const rect = item.card.getBoundingClientRect();
        const cx = rect.left + rect.width / 2;
        const cy = rect.top + rect.height / 2;

        const dx = curX - cx;
        const dy = curY - cy;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < influenceDist) {
          item.wrapper.classList.add('gravitational-lock');
          item.card.classList.add('gravitationally-engaged');

          const strength = 1 - dist / influenceDist;
          item.targetRotX = (-dy / (rect.height / 2)) * 8 * strength;
          item.targetRotY = (dx / (rect.width / 2)) * 8 * strength;
          item.targetTransZ = strength * 18;

          const sheenX = Math.round(50 + (dx / (rect.width / 2)) * 30);
          const sheenY = Math.round(50 + (dy / (rect.height / 2)) * 30);
          item.card.style.setProperty('--holo-x', `${sheenX}%`);
          item.card.style.setProperty('--holo-y', `${sheenY}%`);
        } else {
          item.card.classList.remove('gravitationally-engaged');
          item.targetRotX = 0;
          item.targetRotY = 0;
          item.targetTransZ = 0;
          if (item.curTransZ < 0.5) {
            item.wrapper.classList.remove('gravitational-lock');
          }
        }

        // Controlled harmonic smoothing
        item.curRotX += (item.targetRotX - item.curRotX) * 0.12;
        item.curRotY += (item.targetRotY - item.curRotY) * 0.12;
        item.curTransZ += (item.targetTransZ - item.curTransZ) * 0.12;

        item.card.style.transform = `
          perspective(1000px)
          rotateX(${item.curRotX.toFixed(2)}deg)
          rotateY(${item.curRotY.toFixed(2)}deg)
          translateZ(${item.curTransZ.toFixed(2)}px)
        `;
      }
    },

    loop: function () {
      const self = ElevatedCards;
      self.update();
      requestAnimationFrame(self.loop);
    }
  };

  /* ==========================================================================
     4. ACOUSTIC MATRIX RESONANCE SYNTHESIZER
     ========================================================================== */
  const AudioMatrix = {
    init: function () {
      const btn = document.getElementById('quantum-audio-btn');
      if (!btn) return;

      btn.addEventListener('click', () => {
        if (!Telemetry.audioCtx) {
          const AudioContext = window.AudioContext || window.webkitAudioContext;
          Telemetry.audioCtx = new AudioContext();
        }
        if (Telemetry.audioCtx.state === 'suspended') {
          Telemetry.audioCtx.resume();
        }
        Telemetry.audioEnabled = !Telemetry.audioEnabled;
        btn.classList.toggle('active', Telemetry.audioEnabled);
        const label = btn.querySelector('.audio-label');
        if (label) label.textContent = Telemetry.audioEnabled ? 'Resonance ON' : 'Audio Matrix';
        if (Telemetry.audioEnabled) this.playGlide();
      });
    },

    playGlide: function () {
      if (!Telemetry.audioCtx || !Telemetry.audioEnabled) return;
      const ctx = Telemetry.audioCtx;
      const now = ctx.currentTime;

      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(1320, now);
      osc.frequency.exponentialRampToValueAtTime(1760, now + 0.12);

      gain.gain.setValueAtTime(0.04, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now);
      osc.stop(now + 0.22);
    },

    playCardHover: function () {
      if (!Telemetry.audioCtx || !Telemetry.audioEnabled) return;
      const ctx = Telemetry.audioCtx;
      const now = ctx.currentTime;

      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(220, now);
      osc.frequency.exponentialRampToValueAtTime(440, now + 0.08);

      gain.gain.setValueAtTime(0.025, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.12);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now);
      osc.stop(now + 0.14);
    },

    playPulse: function () {
      if (!Telemetry.audioCtx || !Telemetry.audioEnabled) return;
      const ctx = Telemetry.audioCtx;
      const now = ctx.currentTime;

      const osc = ctx.createOscillator();
      const gain = ctx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(130, now);
      osc.frequency.exponentialRampToValueAtTime(60, now + 0.22);

      gain.gain.setValueAtTime(0.06, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);

      osc.connect(gain);
      gain.connect(ctx.destination);

      osc.start(now);
      osc.stop(now + 0.26);
    }
  };

  /* Initialize */
  function boot() {
    document.documentElement.classList.add('theme-crystalline');
    document.body.classList.add('theme-crystalline');

    CrystallineCanvas.init();
    FrictionlessWaveNav.init();
    ElevatedCards.init();
    AudioMatrix.init();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

})();
