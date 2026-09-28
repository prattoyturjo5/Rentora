<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════
 *  STANDARDIZED CASUAL PRESET AVATAR MODULE & SVG BINARY TRANSMUTER
 *  Classic Archetypes: Boy, Girl, Happy Face, Star, Cool, Robot, Cat, Rocket
 *  Zero SQL Schema Mutations — Compiles pure SVG vector physical binary files
 * ═════════════════════════════════════════════════════════════════════════════
 */

function get_casual_avatar_presets(): array {
    return [
        'boy' => [
            'name'    => 'Boy',
            'tagline' => 'Casual Classic',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><path d="M25 110 C25 88 42 78 60 78 C78 78 95 88 95 110 Z" fill="#2563EB"/><circle cx="60" cy="54" r="26" fill="#FCD34D"/><path d="M34 50 C34 32 46 22 60 22 C74 22 86 32 86 50 C86 52 82 46 76 45 C68 44 65 38 60 38 C55 38 52 44 44 45 C38 46 34 52 34 50 Z" fill="#151B54"/><ellipse cx="51" cy="54" rx="3.5" ry="4" fill="#151B54"/><ellipse cx="69" cy="54" rx="3.5" ry="4" fill="#151B54"/><circle cx="52" cy="53" r="1" fill="#FFFFFF"/><circle cx="70" cy="53" r="1" fill="#FFFFFF"/><path d="M53 66 Q60 72 67 66" fill="none" stroke="#151B54" stroke-width="2.5" stroke-linecap="round"/><circle cx="43" cy="58" r="3" fill="#F87171" opacity="0.4"/><circle cx="77" cy="58" r="3" fill="#F87171" opacity="0.4"/></svg>'
        ],
        'girl' => [
            'name'    => 'Girl',
            'tagline' => 'Casual Classic',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><path d="M25 110 C25 88 42 78 60 78 C78 78 95 88 95 110 Z" fill="#EC4899"/><path d="M30 65 C26 40 40 20 60 20 C80 20 94 40 90 65 C88 78 94 88 94 92 C88 88 84 80 84 74 C84 40 80 32 60 32 C40 32 36 40 36 74 C36 80 32 88 26 92 C26 88 32 78 30 65 Z" fill="#1E1B4B"/><circle cx="60" cy="54" r="24" fill="#FCD34D"/><path d="M38 48 C44 34 56 34 60 38 C64 34 76 34 82 48 C76 42 68 40 60 42 C52 40 44 42 38 48 Z" fill="#1E1B4B"/><circle cx="36" cy="38" r="4" fill="#EC4899"/><circle cx="84" cy="38" r="4" fill="#EC4899"/><ellipse cx="51" cy="54" rx="3.5" ry="4" fill="#151B54"/><ellipse cx="69" cy="54" rx="3.5" ry="4" fill="#151B54"/><circle cx="52" cy="53" r="1.2" fill="#FFFFFF"/><circle cx="70" cy="53" r="1.2" fill="#FFFFFF"/><path d="M53 65 Q60 71 67 65" fill="none" stroke="#E11D48" stroke-width="2.5" stroke-linecap="round"/><circle cx="44" cy="58" r="3.5" fill="#FB7185" opacity="0.5"/><circle cx="76" cy="58" r="3.5" fill="#FB7185" opacity="0.5"/></svg>'
        ],
        'happy_face' => [
            'name'    => 'Happy Face',
            'tagline' => 'Radiant Joy',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><circle cx="60" cy="60" r="46" fill="#FBBF24" stroke="#F59E0B" stroke-width="2"/><path d="M42 52 Q50 42 58 52" fill="none" stroke="#151B54" stroke-width="4.5" stroke-linecap="round"/><path d="M62 52 Q70 42 78 52" fill="none" stroke="#151B54" stroke-width="4.5" stroke-linecap="round"/><path d="M42 68 Q60 92 78 68" fill="#151B54" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><path d="M49 72 Q60 84 71 72" fill="#EF4444"/><circle cx="36" cy="64" r="5" fill="#F87171" opacity="0.6"/><circle cx="84" cy="64" r="5" fill="#F87171" opacity="0.6"/></svg>'
        ],
        'star' => [
            'name'    => 'Star',
            'tagline' => 'Cosmic Beacon',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#0A0E2E"/><path d="M60 16 L72 44 L102 46 L78 66 L86 96 L60 80 L34 96 L42 66 L18 46 L48 44 Z" fill="#FBBF24" stroke="#F59E0B" stroke-width="2" stroke-linejoin="round"/><ellipse cx="52" cy="58" rx="2.5" ry="3.5" fill="#151B54"/><ellipse cx="68" cy="58" rx="2.5" ry="3.5" fill="#151B54"/><circle cx="53" cy="57" r="1" fill="#FFFFFF"/><circle cx="69" cy="57" r="1" fill="#FFFFFF"/><path d="M54 68 Q60 73 66 68" fill="none" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><circle cx="46" cy="63" r="2.5" fill="#EF4444" opacity="0.5"/><circle cx="74" cy="63" r="2.5" fill="#EF4444" opacity="0.5"/><circle cx="24" cy="28" r="2" fill="#FDE047"/><circle cx="96" cy="30" r="2.5" fill="#FDE047"/><circle cx="94" cy="88" r="1.5" fill="#FDE047"/></svg>'
        ],
        'cool' => [
            'name'    => 'Cool',
            'tagline' => 'Shades & Chill',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><circle cx="60" cy="60" r="46" fill="#FBBF24" stroke="#F59E0B" stroke-width="2"/><path d="M30 48 Q45 46 60 50 Q75 46 90 48 L88 62 Q75 66 64 62 L60 53 L56 62 Q45 66 32 62 Z" fill="#151B54"/><line x1="36" y1="52" x2="52" y2="60" stroke="#FFFFFF" stroke-width="2" opacity="0.7"/><line x1="68" y1="52" x2="84" y2="60" stroke="#FFFFFF" stroke-width="2" opacity="0.7"/><path d="M46 75 Q60 88 74 75" fill="none" stroke="#151B54" stroke-width="4" stroke-linecap="round"/><path d="M72 73 Q76 75 74 78" fill="none" stroke="#151B54" stroke-width="3" stroke-linecap="round"/></svg>'
        ],
        'robot' => [
            'name'    => 'Robot',
            'tagline' => 'Cybernetic Unit',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><rect x="57" y="18" width="6" height="14" fill="#64748B"/><circle cx="60" cy="18" r="5" fill="#06B6D4"/><rect x="30" y="32" width="60" height="52" rx="14" fill="#334155" stroke="#1E293B" stroke-width="2"/><rect x="22" y="48" width="8" height="18" rx="3" fill="#64748B"/><rect x="90" y="48" width="8" height="18" rx="3" fill="#64748B"/><rect x="38" y="44" width="44" height="20" rx="6" fill="#0F172A"/><circle cx="49" cy="54" r="5" fill="#22D3EE"/><circle cx="71" cy="54" r="5" fill="#22D3EE"/><circle cx="50" cy="53" r="1.5" fill="#FFFFFF"/><circle cx="72" cy="53" r="1.5" fill="#FFFFFF"/><path d="M46 72 H74" stroke="#38BDF8" stroke-width="3" stroke-linecap="round" stroke-dasharray="3,3"/><path d="M42 98 C42 88 50 84 60 84 C70 84 78 88 78 98 Z" fill="#475569"/></svg>'
        ],
        'cat' => [
            'name'    => 'Cat',
            'tagline' => 'Feline Spirit',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#EFF3FF"/><path d="M30 35 L48 58 L28 64 Z" fill="#F97316"/><path d="M33 42 L44 56 L31 60 Z" fill="#FECDD3"/><path d="M90 35 L72 58 L92 64 Z" fill="#F97316"/><path d="M87 42 L76 56 L89 60 Z" fill="#FECDD3"/><circle cx="60" cy="66" r="34" fill="#FB923C"/><ellipse cx="48" cy="62" rx="4" ry="5" fill="#151B54"/><ellipse cx="72" cy="62" rx="4" ry="5" fill="#151B54"/><circle cx="49" cy="61" r="1.5" fill="#FFFFFF"/><circle cx="73" cy="61" r="1.5" fill="#FFFFFF"/><polygon points="60,70 56,74 64,74" fill="#F43F5E"/><path d="M56 74 Q60 78 60 80 Q60 78 64 74" fill="none" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><line x1="28" y1="68" x2="42" y2="70" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><line x1="28" y1="76" x2="42" y2="74" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><line x1="92" y1="68" x2="78" y2="70" stroke="#151B54" stroke-width="2" stroke-linecap="round"/><line x1="92" y1="76" x2="78" y2="74" stroke="#151B54" stroke-width="2" stroke-linecap="round"/></svg>'
        ],
        'rocket' => [
            'name'    => 'Rocket',
            'tagline' => 'Velocity Vector',
            'svg'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="100%" height="100%"><rect width="120" height="120" rx="60" fill="#0A0E2E"/><circle cx="28" cy="30" r="1.5" fill="#93C5FD"/><circle cx="92" cy="24" r="1.5" fill="#93C5FD"/><circle cx="88" cy="85" r="1.5" fill="#93C5FD"/><circle cx="22" cy="80" r="1.5" fill="#93C5FD"/><path d="M60 20 C72 35 76 60 72 75 L48 75 C44 60 48 35 60 20 Z" fill="#F8FAFC"/><path d="M60 20 C64 26 66 34 66 40 L54 40 C54 34 56 26 60 20 Z" fill="#EF4444"/><circle cx="60" cy="50" r="7" fill="#0284C7" stroke="#BAE6FD" stroke-width="2"/><path d="M48 65 L36 78 L48 75 Z" fill="#2563EB"/><path d="M72 65 L84 78 L72 75 Z" fill="#2563EB"/><polygon points="53,75 67,75 60,95" fill="#F97316"/><polygon points="56,75 64,75 60,88" fill="#FDE047"/></svg>'
        ]
    ];
}

/**
 * SVG Binary Transmuter: Compiles the selected preset archetype
 * into a permanent, physical .svg file within uploads/avatars/
 * Zero database schema mutation. Absolute structural integrity.
 */
function transmute_svg_preset(string $preset_key, int $user_id, string $avatar_dir): array {
    $presets = get_casual_avatar_presets();
    if (!isset($presets[$preset_key])) {
        return ['success' => false, 'error' => 'Invalid preset archetype selected.'];
    }

    if (!is_dir($avatar_dir)) {
        @mkdir($avatar_dir, 0777, true);
    }
    @chmod($avatar_dir, 0777);

    // Purge any older avatar files for this user (jpg, png, webp, svg)
    foreach (glob($avatar_dir . '/avatar_' . $user_id . '.*') as $old_file) {
        @unlink($old_file);
    }

    $target_filename = 'avatar_' . $user_id . '.svg';
    $target_path     = $avatar_dir . '/' . $target_filename;
    $svg_raw         = $presets[$preset_key]['svg'];

    $written = @file_put_contents($target_path, $svg_raw, LOCK_EX);
    if ($written === false) {
        return ['success' => false, 'error' => 'Filesystem write failed for path: ' . $target_path];
    }

    @chmod($target_path, 0666);
    return [
        'success'   => true,
        'filename'  => $target_filename,
        'rel_path'  => 'uploads/avatars/' . $target_filename,
        'name'      => $presets[$preset_key]['name']
    ];
}
