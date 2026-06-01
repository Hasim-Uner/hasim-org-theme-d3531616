<?php
/**
 * Header & Navigation — Hasimuener Journal
 *
 * Registriert eine eigene Menu-Location und ersetzt den
 * GeneratePress-Standard-Header durch ein redaktionelles
 * Zeitungsstil-Layout: Titel zentriert, Navigation darunter.
 *
 * Menü-Punkte werden hardcoded generiert (CPT-Archives + Pages),
 * damit sie IMMER synchron mit den registrierten CPTs sind.
 * Optional kann ein WP-Menü als Override angelegt werden.
 *
 * @package Hasimuener_Journal
 * @since   5.3.0
 */

defined( 'ABSPATH' ) || exit;

/* =========================================
   1. MENÜ-LOCATION REGISTRIEREN
   ========================================= */

function hp_register_nav_menus(): void {
	register_nav_menus( [
		'hp-primary' => 'Hauptnavigation (Zeitungsstil)',
	] );
}
add_action( 'after_setup_theme', 'hp_register_nav_menus' );

/* =========================================
   2. GP-HEADER UNTERDRÜCKEN
   ========================================= */

/**
 * Entfernt den gesamten GeneratePress-Header-Bereich,
 * damit unser eigener Header ohne Konflikte gerendert wird.
 *
 * GP nutzt generate_header / generate_inside_header Hooks.
 * Wir entfernen alle relevanten Actions.
 */
function hp_remove_gp_header(): void {
	// GP header output
	remove_action( 'generate_header', 'generate_construct_header' );
	// GP navigation
	remove_action( 'generate_after_header', 'generate_add_navigation_after_header', 5 );

	// Falls GP-Premium Module weitere Header-Hooks nutzen
	if ( function_exists( 'generate_menu_plus_setup' ) ) {
		remove_action( 'generate_after_header', 'generate_menu_plus_mobile_header' );
	}
}
add_action( 'after_setup_theme', 'hp_remove_gp_header', 50 );

/* =========================================
   3. EIGENEN HEADER EINFÜGEN
   ========================================= */

/**
 * Rendert den redaktionellen Zeitungsstil-Header.
 *
 * Struktur:
 * ┌──────────────────────────────────────┐
 * │          HAŞIM ÜNER                  │  ← Titel, zentriert
 * │   Macht. Medien. Perspektive.        │  ← Claim
 * ├──────────────────────────────────────┤
 * │  Essays · Notizen · Glossar · Mission│  ← Nav-Leiste
 * └──────────────────────────────────────┘
 */
function hp_render_journal_header(): void {
	$hp_current_url = trailingslashit( home_url( add_query_arg( [] ) ) );

	// Navigation: hardcoded Items (immer synchron mit CPTs)
	$hp_nav_items = [
		[
			'label' => 'Essays',
			'url'   => get_post_type_archive_link( 'essay' ),
			'match' => [ 'post_type_archive' => 'essay', 'singular' => 'essay' ],
		],
		[
			'label' => 'Notizen',
			'url'   => get_post_type_archive_link( 'note' ),
			'match' => [ 'post_type_archive' => 'note', 'singular' => 'note' ],
		],
		[
			'label' => 'Dossiers',
			'url'   => get_post_type_archive_link( 'dossier' ),
			'match' => [ 'post_type_archive' => 'dossier', 'singular' => 'dossier' ],
		],
		[
			'label' => 'Glossar',
			'url'   => get_post_type_archive_link( 'glossar' ),
			'match' => [ 'post_type_archive' => 'glossar', 'singular' => 'glossar' ],
		],
		[
			'label' => 'Graph',
			'url'   => home_url( '/wissensgraph/' ),
			'match' => [ 'page_slug' => 'wissensgraph' ],
		],
		[
			'label' => 'Mission',
			'url'   => home_url( '/mission/' ),
			'match' => [ 'page_slug' => 'mission' ],
		],
	];
	$hp_show_header_newsletter = ! is_page( 'kontakt' );
	?>

	<a class="hp-skip-link" href="#main-content">Zum Inhalt springen</a>

	<header class="hp-site-header" role="banner">

		<!-- Masthead v2: Editorial Issue-Plate (Meta-Zeile + Wortmarke) -->
		<div class="hp-masthead">
			<div class="hp-masthead__meta">
				<span class="hp-masthead__meta-left">Hannover</span>
				<span class="hp-masthead__meta-right">
					<span class="hp-masthead__meta-dot" aria-hidden="true"></span>
					Ein Journal von Haşim Üner
				</span>
			</div>

			<a class="hp-masthead__home" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="Zur Startseite">
				<p class="hp-masthead__wordmark">
					<span class="hp-masthead__wordmark-upper">Macht. Medien.</span>
					<span class="hp-masthead__wordmark-frame">
						<span class="hp-masthead__wordmark-lower">
							<span class="hp-masthead__wordmark-lower-wrap">Perspektive.</span>
						</span>
					</span>
				</p>
			</a>
		</div>
	</header>

	<div class="hp-header-bar">
		<!-- Navigation -->
		<nav class="hp-nav" aria-label="Hauptnavigation">
			<div class="hp-nav__inner">

				<?php if ( has_nav_menu( 'hp-primary' ) ) : ?>
					<?php
					wp_nav_menu( [
						'theme_location'  => 'hp-primary',
						'container'       => false,
						'menu_class'      => 'hp-nav__list',
						'depth'           => 1,
						'fallback_cb'     => false,
					] );
					?>
				<?php else : ?>
					<!-- Automatische Navigation aus CPT-Archiven -->
					<ul class="hp-nav__list">
						<?php foreach ( $hp_nav_items as $item ) :
							$is_active = hp_nav_is_active( $item['match'] );
						?>
							<li class="hp-nav__item<?php echo $is_active ? ' hp-nav__item--active' : ''; ?>">
								<a class="hp-nav__link" href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
									<?php echo esc_html( $item['label'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<div class="hp-nav__actions">
					<!-- Suche -->
					<button class="hp-nav__search-toggle" aria-label="Suche öffnen" aria-expanded="false" aria-controls="hp-nav-search" type="button">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
					</button>

					<?php if ( $hp_show_header_newsletter ) : ?>
						<!-- Abonnieren-Pille (öffnet Newsletter-Modal) -->
						<!-- Behält .hp-nav__bell-toggle als JS-Hook (siehe nav.js),
						     erbt .hp-nav__abo-btn für das neue Styling. -->
						<button class="hp-nav__abo-btn hp-nav__bell-toggle" aria-label="Newsletter abonnieren" aria-expanded="false" aria-controls="hp-nav-bell-modal" type="button">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M4 6h16v12H4z"/>
								<path d="m4 7 8 6 8-6"/>
							</svg>
							<span class="hp-nav__abo-label">Abonnieren</span>
						</button>
					<?php endif; ?>

					<!-- Hamburger (Mobile) -->
					<button class="hp-nav__toggle" aria-label="Menü öffnen" aria-expanded="false" aria-controls="hp-nav-mobile">
						<span class="hp-nav__toggle-icon" aria-hidden="true">
							<span class="hp-nav__toggle-bar"></span>
							<span class="hp-nav__toggle-bar"></span>
							<span class="hp-nav__toggle-bar"></span>
						</span>
						<span class="hp-nav__toggle-label">Menü</span>
					</button>
				</div>

			</div>
		</nav>

		<!-- Suchfeld (ausklappbar) -->
		<div class="hp-nav-search" id="hp-nav-search" hidden>
			<div class="hp-nav-search__inner">
				<?php get_search_form(); ?>
			</div>
		</div>

		<?php if ( $hp_show_header_newsletter ) : ?>
			<!-- Newsletter-Modal (Bell-Trigger) -->
			<?php
			$hp_bell_flash      = function_exists( 'hp_get_newsletter_flash' ) ? hp_get_newsletter_flash() : [];
			$hp_bell_source     = isset( $hp_bell_flash['source'] ) ? (string) $hp_bell_flash['source'] : '';
			$hp_bell_open_attr  = ( 'header_bell' === $hp_bell_source ) ? ' data-open-on-load="1"' : '';
			?>
			<div class="hp-nav-bell-modal" id="hp-nav-bell-modal" role="dialog" aria-modal="true" aria-labelledby="header-newsletter-signup-title" hidden<?php echo $hp_bell_open_attr; ?>>
				<div class="hp-nav-bell-modal__backdrop" data-bell-close="1" aria-hidden="true"></div>
				<div class="hp-nav-bell-modal__card" role="document">
					<button type="button" class="hp-nav-bell-modal__close" data-bell-close="1" aria-label="Schließen">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
					</button>
					<?php
					if ( function_exists( 'hp_render_newsletter_form' ) ) {
						hp_render_newsletter_form( [
							'id'           => 'header-newsletter-signup',
							'context'      => 'header_bell',
							'variant'      => 'modal',
							'eyebrow'      => 'Benachrichtigung',
							'title'        => 'Neue Texte per E-Mail.',
							'lede'         => 'Eine kurze Nachricht, wenn ein neuer Essay oder eine relevante Notiz erscheint.',
							'submit_label' => 'Bestätigungslink erhalten',
						] );
					}
					?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Mobile-Menü (ausklappbar) -->
		<div class="hp-nav-mobile" id="hp-nav-mobile" hidden>
			<?php if ( has_nav_menu( 'hp-primary' ) ) : ?>
				<?php
				wp_nav_menu( [
					'theme_location' => 'hp-primary',
					'container'      => false,
					'menu_class'     => 'hp-nav-mobile__list',
					'depth'          => 1,
					'fallback_cb'    => false,
				] );
				?>
			<?php else : ?>
				<ul class="hp-nav-mobile__list">
					<?php foreach ( $hp_nav_items as $item ) :
						$is_active = hp_nav_is_active( $item['match'] );
					?>
						<li class="hp-nav-mobile__item<?php echo $is_active ? ' hp-nav-mobile__item--active' : ''; ?>">
							<a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html( $item['label'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

	</div>

	<?php
}
add_action( 'generate_before_header', 'hp_render_journal_header', 5 );

/* =========================================
   4. HILFS-FUNKTIONEN
   ========================================= */

/**
 * Prüft ob ein Nav-Item den aktuellen Seitenkontext matcht.
 *
 * @param array $match Assoziatives Array mit Match-Regeln.
 * @return bool
 */
function hp_nav_is_active( array $match ): bool {

	if ( isset( $match['post_type_archive'] ) && is_post_type_archive( $match['post_type_archive'] ) ) {
		return true;
	}

	if ( isset( $match['singular'] ) && is_singular( $match['singular'] ) ) {
		return true;
	}

	if ( isset( $match['page_slug'] ) && is_page( $match['page_slug'] ) ) {
		return true;
	}

	return false;
}

/**
 * Prüft, ob ein WP-Menüpunkt auf /mission/ zeigt.
 *
 * @param object $item Menüpunkt-Objekt.
 * @return bool
 */
function hp_nav_item_targets_mission( $item ): bool {
	if ( ! is_object( $item ) || empty( $item->url ) ) {
		return false;
	}

	$mission_path = wp_parse_url( home_url( '/mission/' ), PHP_URL_PATH );
	$item_path    = wp_parse_url( $item->url, PHP_URL_PATH );

	if ( ! is_string( $mission_path ) || ! is_string( $item_path ) ) {
		return false;
	}

	return untrailingslashit( $mission_path ) === untrailingslashit( $item_path );
}

/**
 * Erzwingt „Mission“ als Menülabel für /mission/,
 * auch wenn ein WP-Menü das Label überschreibt.
 *
 * @param string $title Menüpunkt-Titel.
 * @param object $item  Menüpunkt-Objekt.
 * @return string
 */
function hp_force_mission_nav_label( string $title, $item ): string {
	if ( is_admin() || ! hp_nav_item_targets_mission( $item ) ) {
		return $title;
	}

	return 'Mission';
}
add_filter( 'nav_menu_item_title', 'hp_force_mission_nav_label', 10, 2 );
