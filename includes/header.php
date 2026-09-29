<?php
/**
 * Shared Header Partial - Modern Floating UI Design System
 */
$base_path = $base_path ?? '.';
$page_title = $page_title ?? 'Rentora - Campus Equipment Exchange & Rental Hub';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <!-- Immediate Theme Initializer to Prevent Flash -->
  <script>
    (function() {
      try {
        var urlParams = new URLSearchParams(window.location.search);
        var themeParam = urlParams.get('theme');
        if (themeParam === 'light' || themeParam === 'dark') {
          localStorage.setItem('rentora_theme', themeParam);
        }
        var savedTheme = localStorage.getItem('rentora_theme');
        var theme = savedTheme || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      } catch (e) {}
    })();
  </script>
  
  <!-- Tailwind CSS CDN with Custom Semantic Theme -->
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
            },
            navy: {
              800: '#1e293b',
              900: '#0f172a',
              950: '#0b0f17',
            }
          },
          boxShadow: {
            'float': 'var(--shadow-float)',
            'elevated': 'var(--shadow-elevated)',
            'glow': '0 0 20px var(--accent-glow)',
          },
          fontFamily: {
            sans: ['Inter', 'Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
            mono: ['Space Grotesk', 'ui-monospace', 'monospace'],
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/quantum-matrix.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/dark-matter-sectors.css?v=<?php echo time(); ?>">

</head>
<body class="flex flex-col min-h-screen bg-canvas text-primary antialiased selection:bg-accent selection:text-white">
