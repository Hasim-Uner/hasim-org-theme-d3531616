<?php
/**
 * Stitch Design Studio — WordPress Admin-Integration.
 *
 * Zeigt eine Admin-Seite unter „Werkzeuge → Stitch Design Studio",
 * auf der generierte Designs aus dem _stitch/output/ Verzeichnis
 * angesehen und verwaltet werden können.
 *
 * @package Hasimuener_Journal
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin-Menü registrieren.
 */
function hp_stitch_admin_menu() {
	add_management_page(
		'Stitch Design Studio',
		'Stitch Designs',
		'manage_options',
		'hp-stitch-designs',
		'hp_stitch_admin_page'
	);
}
add_action( 'admin_menu', 'hp_stitch_admin_menu' );

/**
 * Admin-Seite rendern.
 */
function hp_stitch_admin_page() {
	$output_dir = get_stylesheet_directory() . '/_stitch/output';
	$output_url = get_stylesheet_directory_uri() . '/_stitch/output';

	// HTML-Dateien sammeln.
	$html_files = array();
	if ( is_dir( $output_dir ) ) {
		$files = glob( $output_dir . '/*.html' );
		if ( $files ) {
			rsort( $files ); // Neueste zuerst.
			foreach ( $files as $file ) {
				$basename  = basename( $file );
				$png_file  = str_replace( '.html', '.png', $file );
				$has_image = file_exists( $png_file );
				$filesize  = size_format( filesize( $file ) );
				$modified  = date_i18n( 'd.m.Y H:i', filemtime( $file ) );

				$html_files[] = array(
					'name'      => $basename,
					'url'       => $output_url . '/' . $basename,
					'image_url' => $has_image ? $output_url . '/' . basename( $png_file ) : null,
					'size'      => $filesize,
					'date'      => $modified,
				);
			}
		}
	}

	?>
	<div class="wrap">
		<h1>Stitch Design Studio</h1>
		<p>
			AI-generierte Designs für hasimuener.org via
			<a href="https://stitch.withgoogle.com" target="_blank" rel="noopener">Google Stitch</a>.
		</p>

		<div class="card" style="max-width:700px;margin-bottom:20px;">
			<h2>Schnellstart</h2>
			<p>Generiere Designs per Terminal im Theme-Verzeichnis:</p>
			<pre style="background:#f0f0f0;padding:12px;border-radius:4px;overflow-x:auto;"><code>cd _stitch
npm install
npm run generate -- --prompt "Artikelseite im Editorial-Stil" --export</code></pre>
			<p style="margin-top:10px;">
				<strong>Weitere Befehle:</strong>
			</p>
			<ul>
				<li><code>npm run list</code> — Projekte und Screens auflisten</li>
				<li><code>npm run export -- --project ID --screen SCREEN_ID</code> — Screen exportieren</li>
				<li><code>npm run design-system -- --project ID</code> — Hasim Design-System erstellen</li>
			</ul>
		</div>

		<?php if ( empty( $html_files ) ) : ?>
			<div class="notice notice-info" style="max-width:700px;">
				<p>
					Noch keine Designs vorhanden. Generiere dein erstes Design mit dem Befehl oben.
				</p>
			</div>
		<?php else : ?>
			<h2>Generierte Designs (<?php echo count( $html_files ); ?>)</h2>
			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;margin-top:15px;">
				<?php foreach ( $html_files as $design ) : ?>
					<div class="card" style="margin:0;">
						<?php if ( $design['image_url'] ) : ?>
							<div style="background:#f5f5f5;border-bottom:1px solid #ddd;padding:10px;text-align:center;">
								<img
									src="<?php echo esc_url( $design['image_url'] ); ?>"
									alt="<?php echo esc_attr( $design['name'] ); ?>"
									style="max-width:100%;height:auto;max-height:200px;object-fit:contain;"
								/>
							</div>
						<?php endif; ?>
						<div style="padding:12px;">
							<strong><?php echo esc_html( $design['name'] ); ?></strong>
							<br>
							<span class="description">
								<?php echo esc_html( $design['date'] ); ?> &middot; <?php echo esc_html( $design['size'] ); ?>
							</span>
							<br style="margin-bottom:8px;">
							<a
								href="<?php echo esc_url( $design['url'] ); ?>"
								target="_blank"
								class="button button-small"
								style="margin-top:8px;"
							>
								HTML anzeigen
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
