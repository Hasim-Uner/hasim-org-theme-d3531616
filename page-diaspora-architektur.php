<?php
/**
 * Template Name: Diaspora Architektur
 * Template Post Type: page
 *
 * Scroll-driven Storytelling: Neuorganisation der kurdischen
 * Diplomatie- und Öffentlichkeitsarbeit.
 *
 * Passwortgeschützt. Im Stil des Journals. Vanilla JS + CSS.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

/* -----------------------------------------
   Full-Width Layout + Header ausblenden
   ----------------------------------------- */
add_filter( 'generate_sidebar_layout', function () { return 'no-sidebar'; } );
add_filter( 'generate_page_class',     function ( $classes ) {
    $classes[] = 'full-width-content';
    return $classes;
} );
// Eigenen Journal-Header auf dieser Seite entfernen
remove_action( 'generate_before_header', 'hp_render_journal_header', 5 );

/* -----------------------------------------
   Assets nur auf dieser Seite
   ----------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'diaspora-scroll',
        get_stylesheet_directory_uri() . '/assets/css/diaspora-scroll.css',
        [],
        '1.1.0'
    );
    // D3 force library for Gesellschaft graph
    if ( ! wp_script_is( 'hp-d3', 'registered' ) ) {
        wp_register_script(
            'hp-d3',
            get_stylesheet_directory_uri() . '/assets/js/d3.min.js',
            [],
            (string) filemtime( get_stylesheet_directory() . '/assets/js/d3.min.js' ),
            true
        );
    }
    wp_enqueue_script( 'hp-d3' );
    wp_enqueue_script(
        'diaspora-scroll',
        get_stylesheet_directory_uri() . '/assets/js/diaspora-scroll.js',
        [ 'hp-d3' ],
        '1.1.0',
        true
    );
}, 30 );

/* -----------------------------------------
   Passwortschutz
   ----------------------------------------- */
if ( post_password_required() ) {
    get_header(); ?>

<main id="main-content" class="da-password-gate">
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

<main id="main-content" class="da-scroll-page">

<!-- ==========================================
     STICKY TOC (minimalistisch, immer sichtbar)
     ========================================== -->
<nav class="da-toc" id="da-toc" aria-label="Inhaltsnavigation">
    <div class="da-toc__inner">
        <a href="#da-hero" class="da-toc__item da-toc__item--active" data-section="da-hero" aria-label="Einführung">
            <span class="da-toc__dot"></span>
        </a>
        <a href="#da-inspiration" class="da-toc__item" data-section="da-inspiration" aria-label="Inspirationsquelle">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">Quelle</span>
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
        <a href="#da-mediale" class="da-toc__item" data-section="da-mediale" aria-label="Mediale Architektur">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">04</span>
        </a>
        <a href="#da-kampagne" class="da-toc__item" data-section="da-kampagne" aria-label="Kampagne 01">
            <span class="da-toc__dot"></span>
            <span class="da-toc__label">K01</span>
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

    <p class="da-hero__subtitle da-reveal">Architektur, nicht Appell. Augenhöhe, nicht Widerstand.</p>

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
     SEKTION 0b: INSPIRATIONSQUELLE
     ========================================== -->
<section class="da-section da-inspiration" id="da-inspiration" aria-label="Inspirationsquelle — Intellektuelle Grundlage">
    <div class="da-container da-narrow">

        <span class="da-label da-reveal">Inspirationsquelle</span>

        <h2 class="da-section-title da-reveal">
            Roadmap für eine demokratische Gesellschaft
        </h2>

        <div class="da-inspiration-body da-reveal">
            <p>
                Das architektonische Fundament dieses Konzepts stammt aus der
                2009 verfassten <strong>Roadmap</strong> von Abdullah Öcalan.
                Ihre zentrale These: Die demokratische Gesellschaft ist kein
                Gegenentwurf zum Staat, sondern sein <em>gleichberechtigter Pol</em> —
                aufgebaut auf Vielfalt, offenen Identitäten und Pluralismus,
                wo der Staat auf Homogenität, Zentralisierung und Assimilation setzt.
                Er lehnt somit einen Staat ohne Demokratie genauso ab wie die
                Vorstellung, man könne eine funktionierende Demokratie der Gesellschaft
                komplett ohne die Existenz eines Staates (zumindest in einer
                Übergangsphase) erzwingen. Beide Pole brauchen einander — und
                eine verfassungsmäßige Grundlage, die die demokratische Gesellschaft
                als eigenständigen Akteur schützt. In den Herkunftsregionen muss
                dieser Rahmen erst erkämpft werden. In Deutschland existiert er
                bereits — wir haben ihn nur nicht ausgeschöpft. Die Infrastruktur
                des Rechtsstaats steht. Die Frage ist, ob wir professionell genug
                organisiert sind, sie zu nutzen.
            </p>
        </div>

        <div class="da-inspiration-grid da-stagger">

            <div class="da-inspiration-card da-reveal" data-detail="konfoederalismus" tabindex="0" role="button" aria-label="Detail: Demokratischer Konföderalismus">
                <h4>Demokratischer Konföderalismus</h4>
                <p>
                    Kein eigener Staat, keine Assimilation an einen fremden.
                    Stattdessen: ein funktionales, demokratisches System
                    innerhalb der Zivilgesellschaft, das mit bestehenden
                    Strukturen koexistiert. Die Diaspora ist kein verlorenes
                    Exil — sie ist der Raum, in dem das gebaut wird.
                </p>
            </div>

            <div class="da-inspiration-card da-reveal" data-detail="kommune" tabindex="0" role="button" aria-label="Detail: Die Kommune als Grundzelle">
                <h4>Die Kommune als Grundzelle</h4>
                <p>
                    Die kleinste basisdemokratische Einheit, in der
                    Entscheidungen durch direkten Konsens entstehen.
                    Dezentral, transparent, nicht-hierarchisch.
                    Nicht Partei, nicht Verein — Kommune.
                </p>
            </div>

            <div class="da-inspiration-card da-reveal" data-detail="nicht_verhandelbar" tabindex="0" role="button" aria-label="Detail: Nicht verhandelbar">
                <h4>Nicht verhandelbar</h4>
                <p>
                    Zwei Säulen tragen alles: die Befreiung der Frau
                    und ökologische Nachhaltigkeit. Keine Zusätze,
                    keine Fußnoten — strukturelle Voraussetzungen
                    jeder demokratischen Organisation.
                </p>
            </div>

        </div>

        <blockquote class="da-inspiration-quote da-reveal">
            <p>
                Architektur ist nicht passives Gehäuse —
                sie ist Ideologie als Form.
            </p>
        </blockquote>

    </div>
</section>


<!-- ==========================================
     SEKTION 1: DIE ROSE — EINLEITUNG
     ========================================== -->
<section class="da-section da-rose-section" id="da-rose" aria-label="Einleitung — Die Rose">
    <div class="da-container da-narrow">

        <figure class="da-rose-figure" aria-label="Stilisierte Rose — innere Struktur als Metapher">
            <img class="da-rose-real-img" src="<?php echo esc_url( content_url( '/uploads/2026/03/Rose_Rot.png' ) ); ?>" alt="Eine rote Rose — Symbol für bewusste innere Struktur" loading="lazy" />
            <svg class="da-rose-svg da-draw-svg" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <!-- Äußere Blütenblätter — Phase 1 (coral/accent) -->
                <g class="da-rose-petals-group">
                    <path d="M200 200 C180 155,148 132,200 72 C252 132,220 155,200 200"
                        fill="rgba(209,100,55,0.10)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C242 184,270 158,316 120 C306 178,268 198,200 200"
                        fill="rgba(209,100,55,0.08)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C252 212,278 238,326 200 C278 162,252 188,200 200"
                        fill="rgba(209,100,55,0.08)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C232 222,256 252,296 280 C254 304,228 270,200 200"
                        fill="rgba(209,100,55,0.08)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C218 248,214 276,200 328 C186 276,182 248,200 200"
                        fill="rgba(209,100,55,0.10)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C168 222,144 252,104 280 C146 304,172 270,200 200"
                        fill="rgba(209,100,55,0.08)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C148 212,122 238,74 200 C122 162,148 188,200 200"
                        fill="rgba(209,100,55,0.08)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M200 200 C158 184,130 158,84 120 C94 178,132 198,200 200"
                        fill="rgba(209,100,55,0.10)" stroke="hsl(22 75% 52%)" stroke-width="1.5" stroke-linecap="round"/>
                </g>
                <!-- Innere Struktur — Phase 2 (teal) — bewusste innere Ordnung -->
                <g class="da-rose-structure-group">
                    <circle cx="200" cy="200" r="72" stroke="hsl(160 70% 38%)" stroke-width="1.0" opacity="0.50"/>
                    <circle cx="200" cy="200" r="52" stroke="hsl(160 70% 38%)" stroke-width="1.1" opacity="0.62"/>
                    <circle cx="200" cy="200" r="34" stroke="hsl(160 70% 38%)" stroke-width="1.3" opacity="0.76"/>
                    <circle cx="200" cy="200" r="17" stroke="hsl(160 70% 38%)" stroke-width="1.6" opacity="0.88"/>
                    <!-- Leitbahnen (Radiallinien) -->
                    <line x1="200" y1="128" x2="200" y2="183" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="251" y1="149" x2="218" y2="172" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="272" y1="200" x2="217" y2="200" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="251" y1="251" x2="218" y2="228" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="200" y1="272" x2="200" y2="217" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="149" y1="251" x2="182" y2="228" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="128" y1="200" x2="183" y2="200" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <line x1="149" y1="149" x2="182" y2="172" stroke="hsl(160 70% 38%)" stroke-width="0.8" opacity="0.38"/>
                    <!-- Kern -->
                    <circle cx="200" cy="200" r="5" stroke="hsl(160 70% 38%)" stroke-width="2.0" opacity="0.95"/>
                </g>
            </svg>
        </figure>

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

            <!-- Right: Ordnung — konzentrische Ringe (Rose-Querschnitt) -->
            <div class="da-freiheit__side da-freiheit__side--order da-reveal da-reveal--right">
                <p class="da-freiheit__side-label">Organisierte Freiheit</p>
                <div class="da-rings">
                    <div class="da-ring da-ring-outer da-reveal">
                        <div class="da-ring da-ring-middle da-reveal">
                            <div class="da-ring da-ring-inner da-reveal">
                                <span class="da-ring-core">Dauerhafte<br>Freiheit</span>
                            </div>
                        </div>
                    </div>
                    <div class="da-ring-labels" aria-label="Ring-Ebenen">
                        <button class="da-ring-label da-ring-label-outer" data-detail="rhythmus" type="button" aria-label="Detail: Rhythmus">
                            Rhythmus ←
                        </button>
                        <button class="da-ring-label da-ring-label-middle" data-detail="rollen" type="button" aria-label="Detail: Rollen">
                            Rollen ←
                        </button>
                        <button class="da-ring-label da-ring-label-inner" data-detail="routinen" type="button" aria-label="Detail: Routinen">
                            Routinen ←
                        </button>
                    </div>
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
<section class="da-section da-intelligenz" id="da-intelligenz" aria-label="Konzept 2 — Integrierte Intelligenz">
    <div class="da-container">

        <div class="da-section-header da-reveal">
            <p class="da-section-number">Konzept 02</p>
            <h2 class="da-section-title">Integrierte Intelligenz</h2>
            <p class="da-section-sub">Die Bewegung hat Mut und Empathie — aber zu wenig systematische Analytik. Diese Lücke ist die zentrale Verwundbarkeit.</p>
        </div>

        <!-- DER ANDERE POL -->
        <div class="da-intelligenz__system da-reveal" data-detail="system" tabindex="0" role="button" aria-label="Detail: Der andere Pol">
            <p class="da-intelligenz__system-label">Der andere Pol</p>
            <h3 class="da-intelligenz__system-title">Staat, Kapital und Patriarchat als institutionelle Realität</h3>
            <div class="da-intelligenz__system-pillars da-stagger">
                <span class="da-intelligenz__system-pill da-reveal">Organisiert</span>
                <span class="da-intelligenz__system-pill da-reveal">Analytisch</span>
                <span class="da-intelligenz__system-pill da-reveal">Professionell</span>
            </div>
        </div>

        <!-- Gegenüberstellung: Zwei Pole -->
        <div class="da-intelligenz__poles da-reveal" aria-label="Gegenüberstellung: Staat und Demokratische Gesellschaft">
            <div class="da-intelligenz__pole da-intelligenz__pole--staat da-reveal da-reveal--left">
                <p class="da-intelligenz__pole-label">Der Staat</p>
                <ul class="da-intelligenz__pole-list">
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Organisiert</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Analytisch</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Professionell</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Institutionell</li>
                </ul>
                <ul class="da-intelligenz__pole-list da-intelligenz__pole-list--tools">
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Lagebilder</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Strategien</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Kontinuität</li>
                    <li><span class="da-pole-check" aria-label="vorhanden">✓</span> Infrastruktur</li>
                </ul>
            </div>

            <div class="da-intelligenz__pole-separator" aria-hidden="true">
                <span class="da-intelligenz__pole-arrow">←→</span>
            </div>

            <div class="da-intelligenz__pole da-intelligenz__pole--gesellschaft da-reveal da-reveal--right" data-detail="luecke" tabindex="0" role="button" aria-label="Detail: Die Lücke — Warum wir noch nicht ebenbürtig sind">
                <p class="da-intelligenz__pole-label">Demokratische Gesellschaft</p>
                <ul class="da-intelligenz__pole-list">
                    <li><span class="da-pole-question" aria-label="offen">?</span> Organisiert?</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Analytisch?</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Professionell?</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Institutionell?</li>
                </ul>
                <ul class="da-intelligenz__pole-list da-intelligenz__pole-list--tools">
                    <li><span class="da-pole-question" aria-label="offen">?</span> Lagebilder</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Strategien</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Kontinuität</li>
                    <li><span class="da-pole-question" aria-label="offen">?</span> Infrastruktur</li>
                </ul>
            </div>
        </div>

        <!-- AUGENHÖHE -->
        <div class="da-intelligenz__weapon da-reveal" data-detail="waffe" tabindex="0" role="button" aria-label="Detail: Augenhöhe — Integrierte Intelligenz">
            <p class="da-intelligenz__weapon-label">Augenhöhe</p>
            <h3 class="da-intelligenz__weapon-title">Integrierte Intelligenz</h3>
            <p class="da-intelligenz__weapon-desc">Analytik, geführt von emotionaler Intelligenz. Nicht Verstand gegen Gefühl — sondern die gleiche Schärfe, verbunden mit dem, was der Staat nicht hat: Menschlichkeit.</p>
            <p class="da-intelligenz__weapon-note">Der Staat hat analytische Methoden — aber keine Empathie. Wir haben Empathie — aber zu wenig analytische Methoden. Integrierte Intelligenz schließt beides: Augenhöhe plus Menschlichkeit.</p>
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
                <span class="da-intelligenz__transform-state da-intelligenz__transform-state--mid">Ebenbürtig</span>
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

        <div class="da-rat__summary da-reveal" aria-label="Leselogik der Ratsarchitektur">
            <article class="da-rat__summary-card da-rat__summary-card--roof">
                <p class="da-rat__summary-label">1. Dach</p>
                <h3 class="da-rat__summary-title">Legitimation, Betrieb, Politik</h3>
                <p class="da-rat__summary-text">Der Rat trägt nur dann, wenn demokratische Legitimation, operative Infrastruktur und politische Vertretung als eine gemeinsame Architektur gedacht werden.</p>
            </article>
            <article class="da-rat__summary-card da-rat__summary-card--infra">
                <p class="da-rat__summary-label">2. Infrastruktur</p>
                <h3 class="da-rat__summary-title">Eigene Werkzeuge, eigene Daten</h3>
                <p class="da-rat__summary-text">Newsroom, Server, lokale KI und Datenhoheit machen die Struktur nicht nur sichtbar, sondern tatsächlich handlungsfähig und unabhängig.</p>
            </article>
            <article class="da-rat__summary-card da-rat__summary-card--myzel">
                <p class="da-rat__summary-label">3. Gesellschaft</p>
                <h3 class="da-rat__summary-title">Vom Zentrum in die Breite</h3>
                <p class="da-rat__summary-text">Nicht ein Apparat soll alles tragen, sondern ein föderatives Netzwerk aus Ortsgruppen, Fachbereichen und autonomen gesellschaftlichen Strukturen.</p>
            </article>
        </div>

        <div class="da-rat__shell">
            <p class="da-rat__eyebrow da-reveal">
                <span class="da-rat__eyebrow-dot" aria-hidden="true"></span>
                Von oben nach unten lesen: Dach, Säulen, Infrastruktur, Gesellschaft, Wertefundament.
            </p>

        <!-- RAT-VISUALISIERUNG (Inline SVG) -->
        <div class="da-rat__viz da-reveal">
            <svg viewBox="0 0 900 335" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Architektur-Diagramm des Kurdischen Rats — Dach, Säulen und Infrastruktur">

                <g aria-hidden="true">
                    <rect x="92" y="106" width="716" height="120" rx="28" fill="rgba(177,42,42,0.035)"/>
                    <rect x="108" y="230" width="684" height="74" rx="22" fill="rgba(34,126,99,0.05)"/>
                </g>

                <!-- ===== SCHICHT 1: DACH ===== -->
                <g class="da-reveal" style="transition-delay: 0ms">
                    <!-- Dach-Form (breites Vordach) -->
                    <path d="M150 80 L450 30 L750 80 L770 95 L130 95 Z" fill="rgba(177,42,42,0.14)" stroke="#a22327" stroke-width="1.5"/>
                    <text x="450" y="72" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="16" fill="#111111" letter-spacing="0.05em">KURDISCHER RAT</text>
                    <text x="450" y="90" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="rgba(17,17,17,0.55)" letter-spacing="0.08em">FÖDERATIVE DACHARCHITEKTUR</text>
                </g>

                <!-- ===== SCHICHT 2: DREI SÄULEN ===== -->
                <g class="da-stagger">
                    <!-- Säule 1: Demokratische Legitimation -->
                    <g class="da-reveal" data-detail="legitim" tabindex="0" role="button" aria-label="Detail: Demokratische Legitimation">
                        <rect x="130" y="115" width="200" height="100" rx="12" fill="rgba(255,255,255,0.94)" stroke="rgba(17,17,17,0.12)" stroke-width="1"/>
                        <rect x="130" y="115" width="200" height="100" rx="12" fill="rgba(177,42,42,0.06)" class="da-hover-fill"/>
                        <text x="230" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">DEMOKRATISCHE</text>
                        <text x="230" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">LEGITIMATION</text>
                        <text x="230" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="rgba(34,50,70,0.76)">Delegiertenversammlung</text>
                    </g>
                    <!-- Säule 2: Operative gGmbH -->
                    <g class="da-reveal" data-detail="operativ" tabindex="0" role="button" aria-label="Detail: Operative gGmbH">
                        <rect x="350" y="115" width="200" height="100" rx="12" fill="rgba(255,255,255,0.94)" stroke="rgba(17,17,17,0.12)" stroke-width="1"/>
                        <rect x="350" y="115" width="200" height="100" rx="12" fill="rgba(177,42,42,0.06)" class="da-hover-fill"/>
                        <text x="450" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">OPERATIVE</text>
                        <text x="450" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">gGmbH</text>
                        <text x="450" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="rgba(34,50,70,0.76)">Medien · Bildung · IT</text>
                    </g>
                    <!-- Säule 3: Politischer Arm -->
                    <g class="da-reveal" data-detail="politisch" tabindex="0" role="button" aria-label="Detail: Politischer Arm">
                        <rect x="570" y="115" width="200" height="100" rx="12" fill="rgba(255,255,255,0.94)" stroke="rgba(17,17,17,0.12)" stroke-width="1"/>
                        <rect x="570" y="115" width="200" height="100" rx="12" fill="rgba(177,42,42,0.06)" class="da-hover-fill"/>
                        <text x="670" y="155" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">POLITISCHER</text>
                        <text x="670" y="172" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="12" fill="#8f1e22">ARM</text>
                        <text x="670" y="198" text-anchor="middle" font-family="Figtree, sans-serif" font-size="10" fill="rgba(34,50,70,0.76)">Lobbying · Kampagnen</text>
                    </g>
                </g>

                <!-- ===== SCHICHT 3: INFRASTRUKTUR ===== -->
                <g class="da-reveal" style="transition-delay: 400ms">
                    <rect x="130" y="240" width="640" height="55" rx="10" fill="rgba(250,248,245,0.98)" stroke="rgba(17,17,17,0.12)" stroke-width="1"/>
                    <text x="200" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="rgba(34,50,70,0.76)">
                        <tspan data-detail="newsroom" tabindex="0" role="button" fill="#1c6c58">Newsroom</tspan>
                    </text>
                    <text x="340" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="rgba(34,50,70,0.76)">
                        <tspan data-detail="server" tabindex="0" role="button" fill="#1c6c58">Server &amp; IT</tspan>
                    </text>
                    <text x="490" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="rgba(34,50,70,0.76)">
                        <tspan data-detail="ki" tabindex="0" role="button" fill="#1c6c58">Lokale KI</tspan>
                    </text>
                    <text x="640" y="273" text-anchor="middle" font-family="Figtree, sans-serif" font-size="11" fill="rgba(34,50,70,0.76)">
                        <tspan data-detail="daten" tabindex="0" role="button" fill="#1c6c58">Datenhoheit</tspan>
                    </text>
                    <text x="450" y="254" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="rgba(17,17,17,0.58)" letter-spacing="0.12em">INFRASTRUKTURSCHICHT</text>
                </g>

                <!-- ===== SCHICHT 4: VERBINDUNGEN ZUR GESELLSCHAFT ===== -->
                <g class="da-draw-svg da-reveal" style="transition-delay: 600ms">
                    <!-- Verbindungen von Säulen nach unten in die Gesellschaft -->
                    <path d="M230 215 C230 275, 180 288, 180 310" stroke="rgba(34,50,70,0.34)" stroke-width="1.2" stroke-dasharray="4 3" fill="none"/>
                    <path d="M450 215 C450 275, 450 288, 450 310" stroke="rgba(34,50,70,0.34)" stroke-width="1.2" stroke-dasharray="4 3" fill="none"/>
                    <path d="M670 215 C670 275, 720 288, 720 310" stroke="rgba(34,50,70,0.34)" stroke-width="1.2" stroke-dasharray="4 3" fill="none"/>
                    <!-- Cross connections -->
                    <path d="M230 295 Q340 310, 450 295" stroke="rgba(34,50,70,0.22)" stroke-width="0.9" stroke-dasharray="3 4" fill="none"/>
                    <path d="M450 295 Q560 310, 670 295" stroke="rgba(34,50,70,0.22)" stroke-width="0.9" stroke-dasharray="3 4" fill="none"/>
                    <!-- Arrow tips pointing down -->
                    <path d="M174 305 L180 315 L186 305" stroke="rgba(34,50,70,0.4)" stroke-width="1" fill="none"/>
                    <path d="M444 305 L450 315 L456 305" stroke="rgba(34,50,70,0.4)" stroke-width="1" fill="none"/>
                    <path d="M714 305 L720 315 L726 305" stroke="rgba(34,50,70,0.4)" stroke-width="1" fill="none"/>
                </g>

                <!-- Label: Gesellschaft folgt unten (D3) -->
                <text x="450" y="330" text-anchor="middle" font-family="Outfit, sans-serif" font-weight="600" font-size="9" fill="rgba(17,17,17,0.45)" letter-spacing="0.12em" class="da-reveal" style="transition-delay: 700ms">↓ DEMOKRATISCHE GESELLSCHAFT — MYZEL-NETZWERK</text>

            </svg>
        </div>

        <!-- D3 Force-Graph: Gesellschaftsknoten -->
        <div class="da-rat__d3-wrap da-reveal" style="transition-delay: 800ms">
            <div class="da-rat__d3-container" id="da-gesellschaft-graph" aria-label="Interaktives Netzwerk der Gesellschaftsknoten — klicken für Details">
                <p class="da-rat__d3-placeholder">Netzwerk lädt…</p>
            </div>
            <div class="da-rat__d3-werte da-reveal" style="transition-delay: 1000ms">
                <p class="da-rat__d3-werte-label">Wertefundament</p>
                <div class="da-rat__d3-werte-pills">
                    <span>Freiheit</span>
                    <span>Gleichstellung</span>
                    <span>Org. Freiheit</span>
                    <span>Taktik</span>
                    <span>Integrierte Intelligenz</span>
                    <span>Würde</span>
                </div>
            </div>
        </div>

        <div class="da-rat__legend-wrap da-reveal" style="transition-delay: 200ms">
            <p class="da-rat__legend-title">Gesellschaftsknoten</p>
            <div class="da-rat__legend">
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
        </div>

    </div>
</section>


<!-- ==========================================
     SEKTION 5: KONZEPT 4 — MEDIALE ARCHITEKTUR
     ========================================== -->
<section class="da-section da-mediale" id="da-mediale" aria-label="Konzept 4 — Mediale Architektur">
    <div class="da-container">

        <div class="da-section-header da-reveal">
            <p class="da-section-number">Konzept 04</p>
            <h2 class="da-section-title">Mediale Architektur</h2>
            <p class="da-section-sub">Die Website als zentrales Operationssystem der demokratischen Gesellschaft.</p>
        </div>

        <blockquote class="da-mediale__intro da-reveal">
            Der Staat hat Medienarchitektur. Wir brauchen unsere eigene.
            Alles, was du auf dieser Seite siehst, läuft auf eigenen Servern.
            Eigene Daten. Kein Tracking.
            Keine Abhängigkeit von Plattformen, die morgen
            entscheiden können, uns abzuschalten.
            Mediale Architektur ist keine Technikfrage —
            sie ist die Voraussetzung für Augenhöhe.
        </blockquote>

        <!-- Schichtmodell: Das digitale Ökosystem -->
        <div class="da-stack-label-intro da-reveal">
            <p class="da-label">Das digitale Ökosystem als Schichtmodell</p>
        </div>

        <div class="da-stack da-stagger">
            <div class="da-stack-layer da-stack-5 da-reveal" data-detail="oeffentlichkeit" tabindex="0" role="button" aria-label="Detail: Schicht 5 — Öffentlichkeit & Wirkung">
                <span class="da-stack-label">Schicht 5</span>
                <h4>Öffentlichkeit &amp; Wirkung</h4>
                <p>Kampagnen · Stellungnahmen · Bündnisse · Allianzen</p>
            </div>
            <div class="da-stack-layer da-stack-4 da-reveal" data-detail="analyse" tabindex="0" role="button" aria-label="Detail: Schicht 4 — Inhalt & Analyse">
                <span class="da-stack-label">Schicht 4</span>
                <h4>Inhalt &amp; Analyse</h4>
                <p>Lagebilder · Hypothesen · Reviews · Entscheidungsnotizen</p>
            </div>
            <div class="da-stack-layer da-stack-3 da-reveal" data-detail="kommunikation" tabindex="0" role="button" aria-label="Detail: Schicht 3 — Kommunikation & Koordination">
                <span class="da-stack-label">Schicht 3</span>
                <h4>Kommunikation &amp; Koordination</h4>
                <p>Plattform · Protokolle · Krisenkommunikation</p>
            </div>
            <div class="da-stack-layer da-stack-2 da-reveal" data-detail="medien" tabindex="0" role="button" aria-label="Detail: Schicht 2 — Medienproduktion">
                <span class="da-stack-label">Schicht 2</span>
                <h4>Medienproduktion</h4>
                <p>Newsroom · Bild- und Sprachlinie · Video · Social</p>
            </div>
            <div class="da-stack-layer da-stack-1 da-reveal" data-detail="souveraenitaet" tabindex="0" role="button" aria-label="Detail: Schicht 1 — Digitale Souveränität">
                <span class="da-stack-label">Schicht 1 — Fundament</span>
                <h4>Digitale Souveränität</h4>
                <p>Eigene Server · Datenhoheit · Verschlüsselung · Lokale KI · Kein Tracking</p>
                <span class="da-stack-badge">← Diese Seite läuft hier</span>
            </div>
        </div>

        <!-- Drei Module -->
        <div class="da-module-grid da-stagger">
            <div class="da-module-card da-reveal" data-detail="newsroom_med" tabindex="0" role="button" aria-label="Detail: Newsroom">
                <span class="da-module-icon" aria-hidden="true">◉</span>
                <h4>Newsroom</h4>
                <p>Eigene Stimme, eigene Begriffe. Keine Abhängigkeit von Plattformen,
                   die morgen entscheiden können, uns abzuschalten. Abgestimmte
                   Bild- und Sprachlinie. Krisenkommunikationsprotokoll.</p>
            </div>
            <div class="da-module-card da-reveal" data-detail="wissensgraph_med" tabindex="0" role="button" aria-label="Detail: Wissensgraph">
                <span class="da-module-icon" aria-hidden="true">◎</span>
                <h4>Wissensgraph</h4>
                <p>Begriffe vernetzen statt isolieren. Politisches Wissen wird
                   strukturiert, durchsuchbar, verlinkbar. Kein statisches
                   Glossar — ein lebendiges Denk-Netzwerk.</p>
                <a href="https://hasimuener.org/wissensgraph/" class="da-module-link">
                    → Live auf dieser Seite
                </a>
            </div>
            <div class="da-module-card da-reveal" data-detail="sicherheit_med" tabindex="0" role="button" aria-label="Detail: Digitaler Selbstschutz">
                <span class="da-module-icon" aria-hidden="true">◈</span>
                <h4>Digitaler Selbstschutz</h4>
                <p>Digitale Sicherheit als Organisationsstandard, nicht als
                   Sonderwissen. Zugänge, Passwortverwaltung, Verschlüsselung,
                   Datensparsamkeit — eingebaut, nicht nachgerüstet.</p>
            </div>
        </div>

        <!-- Abschluss-Statement -->
        <blockquote class="da-mediale__statement da-reveal">
            Wer seine Medien nicht kontrolliert,<br>
            wird von den Medien anderer kontrolliert.<br>
            <span class="da-mediale__statement-em">Mediale Architektur ist der vierte Pfeiler<br>
            der demokratischen Gesellschaft.</span>
        </blockquote>

    </div>
</section>


<!-- ==========================================
     KAMPAGNE 01: WIR VERTRETEN DIE KURDISCHE COMMUNITY
     ========================================== -->
<section class="da-section da-kampagne" id="da-kampagne" aria-label="Kampagne 01 — Wir vertreten die kurdische Community">
    <div class="da-container">

        <span class="da-label da-reveal">In der Praxis</span>
        <h2 class="da-section-title da-reveal">
            Kampagne 01: Wir vertreten die kurdische Community
        </h2>

        <p class="da-section-sub da-reveal">
            Der Kurdische Rat stellt sich vor — als demokratisch legitimierte Vertretung
            der kurdischen Community in Deutschland. So arbeiten alle Schichten zusammen.
        </p>

        <div class="da-kampagne-widget da-reveal">
            <!-- Phase-Navigation -->
            <div class="da-k-nav" id="da-k-nav"></div>

            <!-- Hauptbereich: Schichten-Stack + Detail-Panel -->
            <div class="da-k-main">
                <div class="da-k-stack" id="da-k-stack"></div>
                <div class="da-k-detail" id="da-k-detail"></div>
            </div>

            <!-- Timeline -->
            <div class="da-k-timeline" id="da-k-timeline"></div>

            <!-- Schichten-Erklärung -->
            <div class="da-k-insight" id="da-k-insight"></div>
        </div>

    </div>
</section>


<!-- ==========================================
     SEKTION 6: ZITAT / SCHLUSS
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
                <p class="da-schluss__principle-title">Demokratische Gesellschaft als gleichberechtigter Pol</p>
                <p class="da-schluss__principle-desc">Der Staat verschwindet nicht — aber eine organisierte Gesellschaft kann ihm als eigenständige institutionelle Kraft gegenübertreten. Auf Augenhöhe. Mit derselben Professionalität. Und mit dem, was er nicht hat.</p>
            </div>
            <div class="da-schluss__principle da-reveal">
                <p class="da-schluss__principle-title">Integrierte Intelligenz als Grundlage</p>
                <p class="da-schluss__principle-desc">Die Vereinigung von Analytik und emotionaler Intelligenz ist die Voraussetzung für Augenhöhe — dieselbe Schärfe wie der Staat, verbunden mit dem, was er strukturell nicht kann.</p>
            </div>
        </div>

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

        <p class="da-meta-note da-reveal">
            Diese Seite läuft auf eigenen Servern. Kein Tracking. Keine Cookies.<br>
            Keine Abhängigkeit von Plattformen Dritter. Das ist Schicht 1.
        </p>

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
