<?php
/**
 * Template Name: Diaspora Architektur
 * Template Post Type: page
 *
 * Scroll-driven Storytelling: Neuorganisation der kurdischen
 * Diplomatie- und Öffentlichkeitsarbeit.
 *
 * Passwortgeschützt. Dark Mode only. Vanilla JS + CSS.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------
   Assets nur auf dieser Seite
   ----------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'diaspora-scroll',
        get_stylesheet_directory_uri() . '/assets/css/diaspora-scroll.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'diaspora-scroll',
        get_stylesheet_directory_uri() . '/assets/js/diaspora-scroll.js',
        [],
        '1.0.0',
        true
    );
}, 30 );

/* -----------------------------------------
   Passwortschutz
   ----------------------------------------- */
if ( post_password_required() ) {
    get_header(); ?>

<main class="da-password-gate" data-theme="dark">
    <div class="da-password-container">
        <div class="da-password-brand">
            <span class="da-password-wordmark">HAŞIM ÜNER</span>
            <span class="da-password-sub">Macht. Medien. Gesellschaft.</span>
        </div>
        <h1 class="da-password-title">Diaspora-Architektur</h1>
        <p class="da-password-desc">
            Diese Seite ist passwortgeschützt.<br>
            Zugangsdaten wurden an Delegierte verteilt.
        </p>
        <?php echo get_the_password_form(); ?>
    </div>
</main>

<?php
    get_footer();
    return;
}

get_header();
?>

<main class="da-scroll-page" data-theme="dark">

<!-- ==========================================
     STICKY TOC (minimalistisch, immer sichtbar)
     ========================================== -->
<nav class="da-toc" id="da-toc" aria-label="Inhaltsnavigation">
    <div class="da-toc__inner">
        <a href="#da-hero" class="da-toc__item da-toc__item--active" data-section="da-hero" aria-label="Einführung">
            <span class="da-toc__dot"></span>
        </a>
        <a href="#da-rose" class="da-toc__item" data-section="da-rose" aria-label="Die Rose">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">Rose</span>
        </a>
        <a href="#da-freiheit" class="da-toc__item" data-section="da-freiheit" aria-label="Organisation als gelebte Freiheit">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">01</span>
        </a>
        <a href="#da-intelligenz" class="da-toc__item" data-section="da-intelligenz" aria-label="Integrierte Intelligenz">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">02</span>
        </a>
        <a href="#da-rat" class="da-toc__item" data-section="da-rat" aria-label="Der Kurdische Rat">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">03</span>
        </a>
        <a href="#da-schluss" class="da-toc__item" data-section="da-schluss" aria-label="Schluss">
            <span class="da-toc__dot"></span>
        </a>
    </div>
    <div class="da-toc__progress" aria-hidden="true"></div>
</nav>

<!-- ==========================================
     SEKTION 0: HERO
     ========================================== -->
<section class="da-section da-hero" id="da-hero" aria-label="Einführung">

    <p class="da-hero__overline da-reveal">Diaspora-Architektur</p>

    <h1 class="da-hero__title da-reveal">Neuorganisation der kurdischen Diplomatie- und Öffentlichkeitsarbeit</h1>

    <p class="da-hero__subtitle da-reveal">Architektur, nicht Appell. Struktur, nicht Stimmung.</p>

    <div class="da-hero__concepts da-stagger">
        <span class="da-hero__concept-pill da-reveal">Organisation</span>
        <span class="da-hero__concept-pill da-hero__concept-pill--accent da-reveal">Demokratische Gesellschaft</span>
        <span class="da-hero__concept-pill da-reveal">Integrierte Intelligenz</span>
    </div>

    <div class="da-hero__scroll-hint" aria-hidden="true">
        <span>Scroll</span>
        <div class="da-hero__scroll-line"></div>
    </div>
</section>


<!-- ==========================================
     SEKTION 1: DIE ROSE — EINLEITUNG
     ========================================== -->
<section class="da-section da-rose-section" id="da-rose" aria-label="Einleitung — Die Rose">
    <div class="da-container da-narrow">

        <!-- Rose SVG (botanischer, ruhiger, glaubwürdiger) -->
        <div class="da-rose-svg-wrap da-draw-svg da-reveal">
            <svg viewBox="0 0 240 320" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Rose mit natürlicher Blüte und subtil sichtbarer innerer Ordnung" role="img">
                <defs>
                    <linearGradient id="da-rose-stem-gradient" x1="120" y1="146" x2="120" y2="306" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(146, 34%, 42%)"/>
                        <stop offset="100%" stop-color="hsl(154, 44%, 22%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-leaf-fill-left" x1="48" y1="214" x2="122" y2="238" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(144, 42%, 36%)"/>
                        <stop offset="100%" stop-color="hsl(154, 46%, 18%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-leaf-fill-right" x1="118" y1="236" x2="194" y2="258" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(145, 36%, 34%)"/>
                        <stop offset="100%" stop-color="hsl(154, 44%, 17%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-petal-fill-back" x1="120" y1="30" x2="120" y2="178" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(18, 84%, 79%)"/>
                        <stop offset="36%" stop-color="hsl(9, 68%, 52%)"/>
                        <stop offset="100%" stop-color="hsl(1, 48%, 27%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-petal-fill-top" x1="120" y1="26" x2="120" y2="156" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(24, 88%, 84%)"/>
                        <stop offset="42%" stop-color="hsl(14, 74%, 58%)"/>
                        <stop offset="100%" stop-color="hsl(3, 46%, 30%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-petal-fill-mid" x1="86" y1="86" x2="170" y2="186" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(14, 76%, 65%)"/>
                        <stop offset="54%" stop-color="hsl(6, 64%, 42%)"/>
                        <stop offset="100%" stop-color="hsl(0, 44%, 24%)"/>
                    </linearGradient>
                    <linearGradient id="da-rose-petal-fill-front" x1="92" y1="122" x2="166" y2="198" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsl(22, 94%, 76%)"/>
                        <stop offset="46%" stop-color="hsl(11, 74%, 52%)"/>
                        <stop offset="100%" stop-color="hsl(1, 48%, 28%)"/>
                    </linearGradient>
                    <radialGradient id="da-rose-center-fill" cx="46%" cy="38%" r="60%">
                        <stop offset="0%" stop-color="hsl(26, 90%, 82%)"/>
                        <stop offset="36%" stop-color="hsl(14, 78%, 62%)"/>
                        <stop offset="100%" stop-color="hsl(2, 48%, 32%)"/>
                    </radialGradient>
                    <linearGradient id="da-rose-structure-line" x1="105" y1="108" x2="145" y2="160" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="hsla(161, 58%, 70%, 0.78)"/>
                        <stop offset="100%" stop-color="hsla(26, 86%, 76%, 0.2)"/>
                    </linearGradient>
                    <filter id="da-rose-shadow" x="-28%" y="-24%" width="156%" height="184%">
                        <feDropShadow dx="0" dy="10" stdDeviation="10" flood-color="hsla(0, 0%, 0%, 0.3)"/>
                    </filter>
                </defs>

                <ellipse class="da-rose-shadow" cx="119" cy="304" rx="36" ry="10" fill="hsla(0, 0%, 0%, 0.22)"/>

                <path class="da-rose-stem" d="M119 304 C116 272 115 241 118 209 C121 180 126 160 124 147" fill="none" stroke="url(#da-rose-stem-gradient)" stroke-width="4.6" stroke-linecap="round"/>
                <path class="da-rose-thorn" d="M117 236 C109 230 103 230 98 237" fill="none" stroke="hsl(150, 36%, 24%)" stroke-width="1.6" stroke-linecap="round"/>
                <path class="da-rose-thorn" d="M122 212 C129 205 135 204 140 208" fill="none" stroke="hsl(150, 36%, 24%)" stroke-width="1.6" stroke-linecap="round"/>

                <g class="da-rose-foliage">
                    <path class="da-rose-leaf da-rose-leaf--left" d="M116 228 C94 207 66 205 48 220 C67 238 95 242 116 228 Z" fill="url(#da-rose-leaf-fill-left)" stroke="hsla(150, 30%, 68%, 0.2)" stroke-width="1.1"/>
                    <path class="da-rose-leaf-line" d="M116 228 C97 224 78 223 60 220" fill="none" stroke="hsla(150, 28%, 74%, 0.36)" stroke-width="0.9" stroke-linecap="round"/>
                    <path class="da-rose-leaf-line" d="M102 220 C94 214 84 210 74 209" fill="none" stroke="hsla(150, 28%, 74%, 0.22)" stroke-width="0.75" stroke-linecap="round"/>
                    <path class="da-rose-leaf da-rose-leaf--right" d="M122 247 C144 228 172 226 194 240 C176 258 147 262 122 247 Z" fill="url(#da-rose-leaf-fill-right)" stroke="hsla(150, 30%, 68%, 0.18)" stroke-width="1.1"/>
                    <path class="da-rose-leaf-line" d="M122 247 C144 244 165 244 186 240" fill="none" stroke="hsla(150, 28%, 74%, 0.34)" stroke-width="0.9" stroke-linecap="round"/>
                    <path class="da-rose-leaf-line" d="M139 241 C148 234 159 231 170 229" fill="none" stroke="hsla(150, 28%, 74%, 0.2)" stroke-width="0.75" stroke-linecap="round"/>
                </g>

                <g class="da-rose-bloom" filter="url(#da-rose-shadow)">
                    <path class="da-rose-sepal" d="M106 155 C93 144 81 139 74 145 C84 149 95 158 102 170 Z" fill="hsla(148, 36%, 26%, 0.95)" stroke="hsla(150, 28%, 72%, 0.16)" stroke-width="0.95"/>
                    <path class="da-rose-sepal" d="M120 158 C116 143 119 133 129 126 C132 138 131 150 125 162 Z" fill="hsla(145, 34%, 24%, 0.92)" stroke="hsla(150, 28%, 72%, 0.14)" stroke-width="0.95"/>
                    <path class="da-rose-sepal" d="M134 154 C147 143 159 139 167 145 C156 149 145 157 138 169 Z" fill="hsla(148, 36%, 26%, 0.95)" stroke="hsla(150, 28%, 72%, 0.14)" stroke-width="0.95"/>

                    <path class="da-rose-petal da-rose-petal--back da-rose-petal--1" d="M123 160 C97 162 74 148 64 121 C55 96 60 62 89 40 C89 64 97 98 111 127 C117 139 121 151 123 160 Z" fill="url(#da-rose-petal-fill-back)" stroke="hsla(18, 82%, 84%, 0.22)" stroke-width="1.2"/>
                    <path class="da-rose-petal da-rose-petal--back da-rose-petal--2" d="M118 160 C144 163 169 152 179 127 C189 102 185 68 156 42 C155 66 147 100 133 127 C128 139 123 151 118 160 Z" fill="url(#da-rose-petal-fill-back)" stroke="hsla(18, 82%, 84%, 0.18)" stroke-width="1.2"/>
                    <path class="da-rose-petal da-rose-petal--back da-rose-petal--3" d="M120 147 C97 146 84 124 86 95 C88 61 109 37 136 34 C159 39 175 62 174 91 C173 121 150 142 120 147 Z" fill="url(#da-rose-petal-fill-top)" stroke="hsla(24, 90%, 88%, 0.24)" stroke-width="1.3"/>
                    <path class="da-rose-petal da-rose-petal--mid da-rose-petal--4" d="M129 177 C110 172 96 158 93 140 C90 121 101 105 120 102 C121 123 124 150 129 177 Z" fill="url(#da-rose-petal-fill-mid)" stroke="hsla(18, 82%, 82%, 0.2)" stroke-width="1.15"/>
                    <path class="da-rose-petal da-rose-petal--mid da-rose-petal--5" d="M116 178 C136 174 151 160 155 142 C159 123 149 106 130 102 C128 123 123 150 116 178 Z" fill="url(#da-rose-petal-fill-mid)" stroke="hsla(18, 82%, 82%, 0.18)" stroke-width="1.15"/>
                    <path class="da-rose-petal da-rose-petal--front da-rose-petal--6" d="M129 193 C111 190 97 180 95 164 C94 149 104 137 120 134 C123 152 126 172 129 193 Z" fill="url(#da-rose-petal-fill-front)" stroke="hsla(24, 90%, 90%, 0.2)" stroke-width="1.1"/>
                    <path class="da-rose-petal da-rose-petal--front da-rose-petal--7" d="M114 193 C132 191 147 182 149 166 C151 150 143 138 127 134 C124 152 120 172 114 193 Z" fill="url(#da-rose-petal-fill-front)" stroke="hsla(24, 90%, 90%, 0.18)" stroke-width="1.1"/>

                    <path class="da-rose-core" d="M121 184 C109 176 103 164 104 151 C106 138 115 129 127 129 C139 130 148 139 148 152 C148 165 140 177 128 184 C124 178 122 172 121 166 C121 172 121 178 121 184 Z" fill="url(#da-rose-center-fill)" stroke="hsla(28, 92%, 90%, 0.18)" stroke-width="1.1"/>

                    <path class="da-rose-crease" d="M95 70 C97 96 104 116 114 133" fill="none" stroke="hsla(28, 88%, 92%, 0.16)" stroke-width="1.5" stroke-linecap="round"/>
                    <path class="da-rose-crease" d="M153 67 C150 94 143 116 132 134" fill="none" stroke="hsla(26, 88%, 92%, 0.16)" stroke-width="1.5" stroke-linecap="round"/>
                    <path class="da-rose-crease" d="M119 50 C132 64 138 81 136 101" fill="none" stroke="hsla(30, 94%, 94%, 0.18)" stroke-width="1.7" stroke-linecap="round"/>

                    <path class="da-rose-inner" d="M127 130 C120 133 115 139 114 146 C113 154 118 161 126 163 C135 165 144 160 147 152 C149 144 146 136 139 132" fill="none" stroke="url(#da-rose-structure-line)" stroke-width="1.4" stroke-linecap="round"/>
                    <path class="da-rose-inner" d="M126 129 C129 118 136 111 147 107" fill="none" stroke="url(#da-rose-structure-line)" stroke-width="0.95" stroke-linecap="round"/>
                    <path class="da-rose-inner" d="M124 133 C114 127 106 119 102 108" fill="none" stroke="url(#da-rose-structure-line)" stroke-width="0.9" stroke-linecap="round"/>
                    <path class="da-rose-inner" d="M132 152 C138 156 144 161 148 169" fill="none" stroke="url(#da-rose-structure-line)" stroke-width="0.85" stroke-linecap="round"/>
                </g>
            </svg>
        </div>

        <blockquote class="da-quote-block da-reveal" data-detail="rose" tabindex="0" role="button" aria-label="Detail: Die Rose">
            Die Rose weiß, dass sie schön ist. Deshalb richtet sie ihre innere Struktur danach aus — konsequent, bewusst, ohne Kompromiss.
        </blockquote>

        <p class="da-leitfrage da-reveal">Die Leitfrage an uns:</p>
        <p class="da-leitfrage-text da-reveal">Wir wissen, was wir sein wollen — frei, dezentral, demokratisch. Richten wir unsere Struktur danach aus, so konsequent wie die Rose?</p>

        <p class="da-rose-transition da-reveal">Und wir?</p>
    </div>
</section>


<!-- ==========================================
     SEKTION 2: KONZEPT 1 — ORGANISATION ALS GELEBTE FREIHEIT
     ========================================== -->
<section class="da-section da-freiheit" id="da-freiheit" aria-label="Konzept 1 — Organisation als gelebte Freiheit">
    <div class="da-container">

        <div class="da-section-header da-reveal">
            <p class="da-section-number">Konzept 01</p>
            <h2 class="da-section-title">Organisation als gelebte Freiheit</h2>
            <p class="da-section-sub">Freiheit ist kein Zustand ohne Struktur — sondern organisierte Selbstbestimmung.</p>
        </div>

        <!-- Split View: Chaos vs. Ordnung -->
        <div class="da-freiheit__split">

            <!-- Left: Chaos -->
            <div class="da-freiheit__side da-freiheit__side--chaos da-reveal da-reveal--left">
                <p class="da-freiheit__side-label">Freiheit ohne Struktur</p>
                <p class="da-freiheit__chaos-desc">
                    Informelle Machtkonzentration. Wenige tragen alles. Engagement brennt aus.
                    Entscheidungen sind undurchsichtig. Konflikte werden nicht gelöst, sondern verschoben.
                </p>
                <div class="da-freiheit__chaos-nodes" aria-hidden="true">
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                    <span class="da-freiheit__chaos-node"></span>
                </div>
            </div>

            <!-- Right: Ordnung -->
            <div class="da-freiheit__side da-freiheit__side--order da-reveal da-reveal--right">
                <p class="da-freiheit__side-label">Organisierte Freiheit</p>
                <div class="da-freiheit__pillars da-stagger">
                    <button class="da-freiheit__pillar da-reveal" data-detail="rhythmus" type="button" aria-label="Detail: Rhythmus">
                        <div class="da-freiheit__pillar-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div class="da-freiheit__pillar-text">
                            <span class="da-freiheit__pillar-title">Rhythmus</span>
                            <p class="da-freiheit__pillar-desc">Verlässliche Zeitpunkte für Analyse und Entscheidung</p>
                        </div>
                    </button>
                    <button class="da-freiheit__pillar da-reveal" data-detail="rollen" type="button" aria-label="Detail: Rollen">
                        <div class="da-freiheit__pillar-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div class="da-freiheit__pillar-text">
                            <span class="da-freiheit__pillar-title">Rollen</span>
                            <p class="da-freiheit__pillar-desc">Sichtbare Zuständigkeiten verteilen Last</p>
                        </div>
                    </button>
                    <button class="da-freiheit__pillar da-reveal" data-detail="routinen" type="button" aria-label="Detail: Routinen">
                        <div class="da-freiheit__pillar-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16v16H4z"/><path d="M4 10h16"/><path d="M10 4v16"/></svg>
                        </div>
                        <div class="da-freiheit__pillar-text">
                            <span class="da-freiheit__pillar-title">Routinen</span>
                            <p class="da-freiheit__pillar-desc">Standards senken Hürden, erhöhen Qualität</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Rose-Prüffrage -->
        <div class="da-freiheit__rose-check da-reveal" data-detail="prueffrage" tabindex="0" role="button" aria-label="Detail: Die Prüffrage">
            <p class="da-freiheit__rose-check-title">Die Rose als Prüffrage</p>
            <p class="da-freiheit__rose-check-text">Sind wir das, was wir zu sein glauben?</p>
            <div class="da-freiheit__rose-check-cards">
                <div class="da-freiheit__rose-check-card">
                    <p class="da-freiheit__rose-check-card-label">Was wir sein wollen</p>
                    <p class="da-freiheit__rose-check-card-body">Frei, dezentral, demokratisch, selbstorganisiert</p>
                </div>
                <span class="da-freiheit__rose-check-eq">=?</span>
                <div class="da-freiheit__rose-check-card">
                    <p class="da-freiheit__rose-check-card-label">Wie wir strukturiert sind</p>
                    <p class="da-freiheit__rose-check-card-body">Rhythmus? Rollen? Routinen? Oder informell, reaktiv, überlastet?</p>
                </div>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================
     SEKTION 3: KONZEPT 2 — INTEGRIERTE INTELLIGENZ
     ========================================== -->
<section class="da-section da-intelligenz" id="da-intelligenz" aria-label="Konzept 2 — Integrierte Intelligenz als Waffe">
    <div class="da-container">

        <div class="da-section-header da-reveal">
            <p class="da-section-number">Konzept 02</p>
            <h2 class="da-section-title">Integrierte Intelligenz</h2>
            <p class="da-section-sub">Die Bewegung hat Mut und Empathie — aber zu wenig systematische Analytik. Diese Lücke ist die zentrale Verwundbarkeit.</p>
        </div>

        <!-- DAS SYSTEM (rot, drückend) -->
        <div class="da-intelligenz__system da-reveal" data-detail="system" tabindex="0" role="button" aria-label="Detail: Das System">
            <p class="da-intelligenz__system-label">Das System</p>
            <h3 class="da-intelligenz__system-title">Monopolisierte Analytik im Dienst der Herrschaft</h3>
            <div class="da-intelligenz__system-pillars da-stagger">
                <span class="da-intelligenz__system-pill da-reveal">Staat</span>
                <span class="da-intelligenz__system-pill da-reveal">Kapital</span>
                <span class="da-intelligenz__system-pill da-reveal">Patriarchat</span>
            </div>
        </div>

        <!-- Druckpfeile -->
        <div class="da-intelligenz__pressure da-reveal" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
        </div>

        <!-- ASYMMETRIE: Was wir haben | Die Lücke | Was fehlt -->
        <div class="da-intelligenz__asymmetry">

            <!-- Was wir haben -->
            <div class="da-intelligenz__have da-reveal da-reveal--left">
                <p class="da-intelligenz__have-label">Was wir haben</p>
                <ul class="da-intelligenz__have-list">
                    <li>Mut</li>
                    <li>Empathie</li>
                    <li>Solidarität</li>
                    <li>Mobilisierungskraft</li>
                    <li>Gemeinschaftssinn</li>
                    <li>Widerstandsfähigkeit</li>
                </ul>
            </div>

            <!-- Die Lücke -->
            <div class="da-intelligenz__gap da-reveal da-reveal--scale" data-detail="luecke" tabindex="0" role="button" aria-label="Detail: Die Lücke">
                <div class="da-intelligenz__gap-pulse da-pulse"></div>
                <p class="da-intelligenz__gap-label">Verwundbarkeit</p>
                <p class="da-intelligenz__gap-title">Die Lücke</p>
            </div>

            <!-- Was fehlt -->
            <div class="da-intelligenz__missing da-reveal da-reveal--right">
                <p class="da-intelligenz__missing-label">Was fehlt</p>
                <ul class="da-intelligenz__missing-list">
                    <li>Mustererkennung</li>
                    <li>Begriffsarbeit</li>
                    <li>Hypothesenbildung</li>
                    <li>Strukturierte Auswertung</li>
                    <li>Systematische Analytik</li>
                </ul>
            </div>
        </div>

        <!-- Gegenpfeil -->
        <div class="da-intelligenz__counter-arrow da-reveal" aria-hidden="true">
            <svg viewBox="0 0 32 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M16 44V4"/>
                <path d="M6 14l10-10 10 10"/>
            </svg>
        </div>

        <!-- DIE WAFFE -->
        <div class="da-intelligenz__weapon da-reveal" data-detail="waffe" tabindex="0" role="button" aria-label="Detail: Die Waffe — Integrierte Intelligenz">
            <p class="da-intelligenz__weapon-label">Die Waffe</p>
            <h3 class="da-intelligenz__weapon-title">Integrierte Intelligenz</h3>
            <p class="da-intelligenz__weapon-desc">Analytik, geführt von emotionaler Intelligenz. Nicht Verstand gegen Gefühl — sondern Gegenmacht durch Vereinigung.</p>
        </div>

        <!-- Werkzeuge -->
        <div class="da-intelligenz__tools da-stagger">
            <button class="da-intelligenz__tool da-reveal" data-detail="lagebilder" type="button" aria-label="Detail: Lagebilder">
                <p class="da-intelligenz__tool-title">Lagebilder</p>
                <p class="da-intelligenz__tool-desc">Tendenzen, Risiken, Chancen — monatlich</p>
            </button>
            <button class="da-intelligenz__tool da-reveal" data-detail="hypothesen" type="button" aria-label="Detail: Hypothesenlisten">
                <p class="da-intelligenz__tool-title">Hypothesenlisten</p>
                <p class="da-intelligenz__tool-desc">Vermutungen formulieren und prüfen</p>
            </button>
            <button class="da-intelligenz__tool da-reveal" data-detail="entscheid" type="button" aria-label="Detail: Entscheidungsnotizen">
                <p class="da-intelligenz__tool-title">Entscheidungsnotizen</p>
                <p class="da-intelligenz__tool-desc">Begründung, Termin, Verantwortung</p>
            </button>
            <button class="da-intelligenz__tool da-reveal" data-detail="reviews" type="button" aria-label="Detail: Strukturierte Reviews">
                <p class="da-intelligenz__tool-title">Strukturierte Reviews</p>
                <p class="da-intelligenz__tool-desc">Quartalsweise: Was hat gewirkt?</p>
            </button>
        </div>

        <!-- Transformation -->
        <div class="da-intelligenz__transformation da-reveal">
            <div class="da-intelligenz__transform-flow">
                <span class="da-intelligenz__transform-state da-intelligenz__transform-state--from">Reaktiv</span>
                <span class="da-intelligenz__transform-arrow" aria-hidden="true">&rarr;</span>
                <span class="da-intelligenz__transform-state da-intelligenz__transform-state--to">Gestaltend</span>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================
     SEKTION 4: KONZEPT 3 — DER KURDISCHE RAT
     ========================================== -->
<section class="da-section da-rat" id="da-rat" aria-label="Konzept 3 — Architektur des Kurdischen Rats">
    <div class="da-container">

        <div class="da-section-header da-reveal">
            <p class="da-section-number">Konzept 03</p>
            <h2 class="da-section-title">Architektur des Kurdischen Rats</h2>
            <p class="da-section-sub">Föderative Dacharchitektur der selbstorganisierten Diaspora — vom Dach bis zum Myzel.</p>
        </div>

        <!-- RAT-VISUALISIERUNG (Inline SVG) -->
        <div class="da-rat__viz da-reveal">
            <svg viewBox="0 0 900 820" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Architektur-Diagramm des Kurdischen Rats">

                <!-- ===== SCHICHT 1: DACH ===== -->
                <g class="da-reveal" style="transition-delay: 0ms">
                    <!-- Dach-Form (breites Vordach) -->
                    <path d="M150 80 L450 30 L750 80 L770 95 L130 95 Z" fill="hsla(22, 70%, 48%, 0.12)" stroke="hsl(22, 70%, 48%)" stroke-width="1.5"/>
                    <text x="450" y="72" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="16" fill="hsl(0, 0%, 93%)" letter-spacing="0.05em">KURDISCHER RAT</text>
                    <text x="450" y="90" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="hsl(0, 0%, 42%)" letter-spacing="0.08em">FÖDERATIVE DACHARCHITEKTUR</text>
                </g>

                <!-- ===== SCHICHT 2: DREI SÄULEN ===== -->
                <g class="da-stagger">
                    <!-- Säule 1: Demokratische Legitimation -->
                    <g class="da-reveal" data-detail="legitim" tabindex="0" role="button" aria-label="Detail: Demokratische Legitimation">
                        <rect x="130" y="115" width="200" height="100" rx="12" fill="hsl(220, 14%, 9%)" stroke="hsl(220, 14%, 22%)" stroke-width="1"/>
                        <rect x="130" y="115" width="200" height="100" rx="12" fill="hsla(22, 70%, 48%, 0.06)" class="da-hover-fill"/>
                        <text x="230" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">DEMOKRATISCHE</text>
                        <text x="230" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">LEGITIMATION</text>
                        <text x="230" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="hsl(0, 0%, 63%)">Delegiertenversammlung</text>
                    </g>
                    <!-- Säule 2: Operative gGmbH -->
                    <g class="da-reveal" data-detail="operativ" tabindex="0" role="button" aria-label="Detail: Operative gGmbH">
                        <rect x="350" y="115" width="200" height="100" rx="12" fill="hsl(220, 14%, 9%)" stroke="hsl(220, 14%, 22%)" stroke-width="1"/>
                        <rect x="350" y="115" width="200" height="100" rx="12" fill="hsla(22, 70%, 48%, 0.06)" class="da-hover-fill"/>
                        <text x="450" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">OPERATIVE</text>
                        <text x="450" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">gGmbH</text>
                        <text x="450" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="hsl(0, 0%, 63%)">Medien · Bildung · IT</text>
                    </g>
                    <!-- Säule 3: Politischer Arm -->
                    <g class="da-reveal" data-detail="politisch" tabindex="0" role="button" aria-label="Detail: Politischer Arm">
                        <rect x="570" y="115" width="200" height="100" rx="12" fill="hsl(220, 14%, 9%)" stroke="hsl(220, 14%, 22%)" stroke-width="1"/>
                        <rect x="570" y="115" width="200" height="100" rx="12" fill="hsla(22, 70%, 48%, 0.06)" class="da-hover-fill"/>
                        <text x="670" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">POLITISCHER</text>
                        <text x="670" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="hsl(22, 70%, 48%)">ARM</text>
                        <text x="670" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="hsl(0, 0%, 63%)">Lobbying · Kampagnen</text>
                    </g>
                </g>

                <!-- ===== SCHICHT 3: INFRASTRUKTUR ===== -->
                <g class="da-reveal" style="transition-delay: 400ms">
                    <rect x="130" y="240" width="640" height="55" rx="10" fill="hsl(220, 14%, 9%)" stroke="hsl(220, 14%, 16%)" stroke-width="1"/>
                    <text x="200" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="hsl(0, 0%, 63%)">
                        <tspan data-detail="newsroom" tabindex="0" role="button" fill="hsl(160, 70%, 38%)">Newsroom</tspan>
                    </text>
                    <text x="340" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="hsl(0, 0%, 63%)">
                        <tspan data-detail="server" tabindex="0" role="button" fill="hsl(160, 70%, 38%)">Server &amp; IT</tspan>
                    </text>
                    <text x="490" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="hsl(0, 0%, 63%)">
                        <tspan data-detail="ki" tabindex="0" role="button" fill="hsl(160, 70%, 38%)">Lokale KI</tspan>
                    </text>
                    <text x="640" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="hsl(0, 0%, 63%)">
                        <tspan data-detail="daten" tabindex="0" role="button" fill="hsl(160, 70%, 38%)">Datenhoheit</tspan>
                    </text>
                    <text x="450" y="254" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(0, 0%, 42%)" letter-spacing="0.12em">INFRASTRUKTURSCHICHT</text>
                </g>

                <!-- ===== SCHICHT 4: BIDIREKTIONALE VERBINDUNGEN ===== -->
                <g class="da-draw-svg da-reveal" style="transition-delay: 600ms">
                    <!-- Wellenförmige Verbindungen von Säulen zu Gesellschaft -->
                    <path d="M230 215 C230 330, 180 340, 180 380" stroke="hsl(220, 14%, 22%)" stroke-width="1" stroke-dasharray="4 3" fill="none"/>
                    <path d="M450 215 C450 340, 450 350, 450 380" stroke="hsl(220, 14%, 22%)" stroke-width="1" stroke-dasharray="4 3" fill="none"/>
                    <path d="M670 215 C670 330, 720 340, 720 380" stroke="hsl(220, 14%, 22%)" stroke-width="1" stroke-dasharray="4 3" fill="none"/>
                    <!-- Cross connections -->
                    <path d="M230 295 Q340 320, 450 295" stroke="hsl(220, 14%, 16%)" stroke-width="0.75" stroke-dasharray="3 4" fill="none"/>
                    <path d="M450 295 Q560 320, 670 295" stroke="hsl(220, 14%, 16%)" stroke-width="0.75" stroke-dasharray="3 4" fill="none"/>
                </g>

                <!-- ===== SCHICHT 5: GESELLSCHAFTSKNOTEN ===== -->
                <g class="da-stagger">
                    <!-- Lokale Räte -->
                    <g class="da-grow-node" data-detail="lokal" tabindex="0" role="button" aria-label="Detail: Lokale Räte">
                        <circle cx="150" cy="420" r="32" fill="hsla(120, 50%, 45%, 0.1)" stroke="hsl(120, 50%, 45%)" stroke-width="1.5"/>
                        <text x="150" y="416" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(120, 50%, 45%)">Lokale</text>
                        <text x="150" y="430" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(120, 50%, 45%)">Räte</text>
                    </g>
                    <!-- Jugend -->
                    <g class="da-grow-node" data-detail="jugend" tabindex="0" role="button" aria-label="Detail: Jugend & Studierende">
                        <circle cx="280" cy="400" r="30" fill="hsla(260, 55%, 58%, 0.1)" stroke="hsl(260, 55%, 58%)" stroke-width="1.5"/>
                        <text x="280" y="396" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(260, 55%, 58%)">Jugend &amp;</text>
                        <text x="280" y="410" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(260, 55%, 58%)">Stud.</text>
                    </g>
                    <!-- Wirtschaft -->
                    <g class="da-grow-node" data-detail="wirtschaft" tabindex="0" role="button" aria-label="Detail: Unternehmer:innen">
                        <circle cx="410" cy="430" r="28" fill="hsla(40, 85%, 55%, 0.1)" stroke="hsl(40, 85%, 55%)" stroke-width="1.5"/>
                        <text x="410" y="426" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(40, 85%, 55%)">Unter-</text>
                        <text x="410" y="438" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(40, 85%, 55%)">nehmer</text>
                    </g>
                    <!-- Frauenstrukturen -->
                    <g class="da-grow-node" data-detail="frauen" tabindex="0" role="button" aria-label="Detail: Frauenstrukturen">
                        <circle cx="540" cy="405" r="34" fill="hsla(340, 60%, 55%, 0.1)" stroke="hsl(340, 60%, 55%)" stroke-width="1.5"/>
                        <text x="540" y="401" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(340, 60%, 55%)">Frauen-</text>
                        <text x="540" y="415" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(340, 60%, 55%)">strukturen</text>
                    </g>
                    <!-- Kultur & Bildung -->
                    <g class="da-grow-node" data-detail="kultur" tabindex="0" role="button" aria-label="Detail: Kultur & Bildung">
                        <circle cx="680" cy="425" r="30" fill="hsla(160, 70%, 38%, 0.1)" stroke="hsl(160, 70%, 38%)" stroke-width="1.5"/>
                        <text x="680" y="421" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(160, 70%, 38%)">Kultur &amp;</text>
                        <text x="680" y="435" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="10" fill="hsl(160, 70%, 38%)">Bildung</text>
                    </g>
                    <!-- Fachnetzwerke -->
                    <g class="da-grow-node" data-detail="fach" tabindex="0" role="button" aria-label="Detail: Fachnetzwerke">
                        <circle cx="790" cy="400" r="26" fill="hsla(210, 70%, 50%, 0.1)" stroke="hsl(210, 70%, 50%)" stroke-width="1.5"/>
                        <text x="790" y="396" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(210, 70%, 50%)">Fach-</text>
                        <text x="790" y="408" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(210, 70%, 50%)">netzwerke</text>
                    </g>
                </g>

                <!-- ===== SCHICHT 6: MYZEL-NETZWERK ===== -->
                <g class="da-myzel da-reveal" style="transition-delay: 800ms">
                    <!-- Organische Verbindungen zwischen den Gesellschaftsknoten -->
                    <path d="M182 420 Q230 440, 280 400" stroke="hsl(220, 14%, 20%)" stroke-width="0.75" fill="none"/>
                    <path d="M280 430 Q345 450, 410 430" stroke="hsl(220, 14%, 20%)" stroke-width="0.75" fill="none"/>
                    <path d="M438 430 Q490 445, 540 405" stroke="hsl(220, 14%, 20%)" stroke-width="0.75" fill="none"/>
                    <path d="M574 405 Q627 430, 680 425" stroke="hsl(220, 14%, 20%)" stroke-width="0.75" fill="none"/>
                    <path d="M710 425 Q750 415, 790 400" stroke="hsl(220, 14%, 20%)" stroke-width="0.75" fill="none"/>
                    <!-- Cross-network connections -->
                    <path d="M150 452 Q280 490, 410 458" stroke="hsl(220, 14%, 18%)" stroke-width="0.5" fill="none"/>
                    <path d="M280 430 Q410 470, 540 439" stroke="hsl(220, 14%, 18%)" stroke-width="0.5" fill="none"/>
                    <path d="M410 458 Q560 480, 680 455" stroke="hsl(220, 14%, 18%)" stroke-width="0.5" fill="none"/>
                    <path d="M150 452 Q450 510, 790 426" stroke="hsl(220, 14%, 15%)" stroke-width="0.5" stroke-dasharray="2 4" fill="none"/>
                    <!-- Additional myzel branches -->
                    <path d="M180 440 Q200 470, 250 460" stroke="hsl(220, 14%, 16%)" stroke-width="0.5" fill="none"/>
                    <path d="M300 420 Q350 460, 380 450" stroke="hsl(220, 14%, 16%)" stroke-width="0.5" fill="none"/>
                    <path d="M560 430 Q600 460, 650 445" stroke="hsl(220, 14%, 16%)" stroke-width="0.5" fill="none"/>
                    <path d="M720 440 Q740 460, 770 420" stroke="hsl(220, 14%, 16%)" stroke-width="0.5" fill="none"/>
                </g>

                <!-- ===== SCHICHT 7: WERTEFUNDAMENT ===== -->
                <g class="da-reveal" style="transition-delay: 1000ms">
                    <rect x="100" y="510" width="700" height="48" rx="24" fill="hsl(220, 14%, 9%)" stroke="hsl(22, 70%, 48%)" stroke-width="1" stroke-opacity="0.3"/>
                    <text x="450" y="522" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(0, 0%, 42%)" letter-spacing="0.12em">WERTEFUNDAMENT</text>

                    <!-- Value pills -->
                    <g font-family="Figtree, sans-serif" font-size="10" fill="hsl(22, 75%, 52%)">
                        <text x="175" y="543" text-anchor="middle">Freiheit</text>
                        <text x="280" y="543" text-anchor="middle">Gleichstellung</text>
                        <text x="400" y="543" text-anchor="middle">Org. Freiheit</text>
                        <text x="520" y="543" text-anchor="middle">Taktik</text>
                        <text x="635" y="543" text-anchor="middle">Intelligenz</text>
                        <text x="735" y="543" text-anchor="middle">Würde</text>
                    </g>
                </g>

                <!-- Label: Gesellschaft -->
                <text x="450" y="490" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="hsl(0, 0%, 42%)" letter-spacing="0.12em" class="da-reveal" style="transition-delay: 900ms">DEMOKRATISCHE GESELLSCHAFT (MYZEL-NETZWERK)</text>

            </svg>
        </div>

        <!-- Legende -->
        <div class="da-rat__legend da-reveal" style="transition-delay: 200ms">
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--lokal"></span>
                <span>Lokale Räte</span>
            </div>
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--jugend"></span>
                <span>Jugend & Studierende</span>
            </div>
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--wirtschaft"></span>
                <span>Wirtschaft</span>
            </div>
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--frauen"></span>
                <span>Frauenstrukturen</span>
            </div>
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--kultur"></span>
                <span>Kultur & Bildung</span>
            </div>
            <div class="da-rat__legend-item">
                <span class="da-rat__legend-dot da-rat__legend-dot--fach"></span>
                <span>Fachnetzwerke</span>
            </div>
        </div>

    </div>
</section>


<!-- ==========================================
     SEKTION 5: ZITAT / SCHLUSS
     ========================================== -->
<section class="da-section da-schluss" id="da-schluss" aria-label="Schluss und Prinzipien">
    <div class="da-container da-narrow">

        <blockquote class="da-schluss__quote da-reveal">
            Freiheit in einem politischen Sinn heißt: Gesellschaft kann ihr Leben bewusst, organisiert und verantwortlich gestalten.
        </blockquote>

        <div class="da-schluss__principles da-stagger">
            <div class="da-schluss__principle da-reveal">
                <p class="da-schluss__principle-title">Organisation als Architektur der Freiheit</p>
                <p class="da-schluss__principle-desc">Rhythmus, Rollen und Routinen sind keine Einschränkung — sie sind die Voraussetzung für nachhaltige Selbstbestimmung.</p>
            </div>
            <div class="da-schluss__principle da-reveal">
                <p class="da-schluss__principle-title">Demokratische Gesellschaft als Gegenpol</p>
                <p class="da-schluss__principle-desc">Nicht der Staat, sondern die organisierte Gesellschaft ist der Ort, an dem Freiheit Wirklichkeit wird.</p>
            </div>
            <div class="da-schluss__principle da-reveal">
                <p class="da-schluss__principle-title">Integrierte Intelligenz als Grundlage</p>
                <p class="da-schluss__principle-desc">Die Vereinigung von Analytik und emotionaler Intelligenz ist der einzige wirksame Gegenpol zur instrumentellen Vernunft des Systems.</p>
            </div>
        </div>

        <p class="da-schluss__author da-reveal">Hasim Üner &middot; November 2025</p>

    </div>
</section>


<!-- ==========================================
     SEKTION 6: FOOTER / NAVIGATION
     ========================================== -->
<footer class="da-section da-footer" id="da-footer" aria-label="Seitenende">
    <div class="da-container da-narrow">

        <div class="da-footer__actions da-reveal">
            <a href="#da-hero" class="da-footer__link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
                Zurück zum Anfang
            </a>
        </div>

        <p class="da-footer__domain da-reveal">hasimuener.org</p>

    </div>
</footer>


<!-- ==========================================
     DETAIL OVERLAY
     ========================================== -->
<div class="da-detail-overlay" id="da-detail-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Detail-Information">
    <div class="da-detail-card">
        <button class="da-detail-close" aria-label="Schließen" type="button">&times;</button>
        <h3 class="da-detail-title"></h3>
        <p class="da-detail-body"></p>
    </div>
</div>

</main>

<?php get_footer(); ?>
