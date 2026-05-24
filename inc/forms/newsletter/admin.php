<?php
/**
 * Newsletter admin page and export UI.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Statuszahlen für die Verwaltung.
 *
 * @return array{active:int,pending:int,unsubscribed:int,total:int}
 */
function hp_get_newsletter_admin_counts(): array {
	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$rows       = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table_name} GROUP BY status", ARRAY_A );
	$counts     = [
		'active'       => 0,
		'pending'      => 0,
		'unsubscribed' => 0,
		'total'        => 0,
	];

	if ( ! is_array( $rows ) ) {
		return $counts;
	}

	foreach ( $rows as $row ) {
		$status = isset( $row['status'] ) ? (string) $row['status'] : '';
		$total  = isset( $row['total'] ) ? (int) $row['total'] : 0;

		if ( isset( $counts[ $status ] ) ) {
			$counts[ $status ] = $total;
			$counts['total']  += $total;
		}
	}

	$counts['unsubscribed'] = hp_get_newsletter_suppression_count();
	$counts['total']       += $counts['unsubscribed'];

	return $counts;
}

/**
 * Management-Seite registrieren.
 */
function hp_register_newsletter_management_page(): void {
	add_submenu_page(
		'hp-contacts',
		'Newsletter',
		'Newsletter',
		'manage_options',
		'hp-newsletter',
		'hp_render_newsletter_management_page'
	);
}
add_action( 'admin_menu', 'hp_register_newsletter_management_page', 20 );

/**
 * CSV-Export der Newsletter-Einträge.
 */
function hp_export_newsletter_subscribers(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sie haben nicht die erforderlichen Rechte.', 'hasimuener-journal' ) );
	}

	check_admin_referer( 'hp_newsletter_export' );

	global $wpdb;

	$table_name = hp_get_newsletter_table_name();
	$status     = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : 'active';
	$allowed    = [ 'all', 'active', 'pending', 'unsubscribed' ];

	if ( ! in_array( $status, $allowed, true ) ) {
		$status = 'active';
	}

	if ( 'all' === $status ) {
		$rows = $wpdb->get_results(
			"SELECT email, status, source, source_url, subscribed_at, confirmed_at, unsubscribed_at FROM {$table_name} ORDER BY email ASC",
			ARRAY_A
		);
	} else {
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT email, status, source, source_url, subscribed_at, confirmed_at, unsubscribed_at
				FROM {$table_name}
				WHERE status = %s
				ORDER BY email ASC",
				$status
			),
			ARRAY_A
		);
	}

	if ( ! is_array( $rows ) ) {
		$rows = [];
	}

	nocache_headers();
	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="hasimuener-newsletter-' . $status . '-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$output = fopen( 'php://output', 'w' );

	if ( false === $output ) {
		exit;
	}

	fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $output, [ 'email', 'status', 'source', 'source_url', 'subscribed_at', 'confirmed_at', 'unsubscribed_at' ] );

	foreach ( $rows as $row ) {
		fputcsv(
			$output,
			[
				(string) ( $row['email'] ?? '' ),
				(string) ( $row['status'] ?? '' ),
				(string) ( $row['source'] ?? '' ),
				(string) ( $row['source_url'] ?? '' ),
				(string) ( $row['subscribed_at'] ?? '' ),
				(string) ( $row['confirmed_at'] ?? '' ),
				(string) ( $row['unsubscribed_at'] ?? '' ),
			]
		);
	}

	fclose( $output );
	exit;
}
add_action( 'admin_post_hp_export_newsletter_subscribers', 'hp_export_newsletter_subscribers' );

/**
 * Rendert die Newsletter-Verwaltung.
 */
function hp_render_newsletter_management_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$counts        = hp_get_newsletter_admin_counts();
	$status_filter = isset( $_GET['status'] ) ? sanitize_key( (string) wp_unslash( $_GET['status'] ) ) : 'all';
	$search        = isset( $_GET['s'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['s'] ) ) : '';
	$notice        = isset( $_GET['notice'] ) ? sanitize_key( (string) wp_unslash( $_GET['notice'] ) ) : '';
	$status_filter = in_array( $status_filter, [ 'all', 'active', 'pending', 'unsubscribed' ], true ) ? $status_filter : 'all';
	$subscribers   = hp_get_recent_newsletter_subscribers( 80, $search, $status_filter );
	$suppressions  = hp_get_recent_newsletter_suppressions( 12 );
	$export_url  = wp_nonce_url(
		add_query_arg(
			[
				'action' => 'hp_export_newsletter_subscribers',
				'status' => 'active',
			],
			admin_url( 'admin-post.php' )
		),
		'hp_newsletter_export'
	);
	$export_all_url = wp_nonce_url(
		add_query_arg(
			[
				'action' => 'hp_export_newsletter_subscribers',
				'status' => 'all',
			],
			admin_url( 'admin-post.php' )
		),
		'hp_newsletter_export'
	);
	?>
	<div class="wrap">
		<h1>Newsletter</h1>
		<p>Lokale Double-Opt-in-Liste für Hinweise auf neue Texte. Die E-Mails selbst laufen serverseitig über denselben Versandweg wie das Kontaktformular.</p>
		<p>Nach einer Austragung bleibt hier nicht die volle Adresse gespeichert, sondern nur eine minimierte Sperrnotiz für die begrenzte Frist.</p>

		<?php if ( 'manual_unsubscribed' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p>Die Adresse wurde aus dem Verteiler ausgetragen. Eine Bestätigung wurde versendet.</p></div>
		<?php elseif ( 'missing' === $notice || 'invalid' === $notice ) : ?>
			<div class="notice notice-error is-dismissible"><p>Die gewünschte Newsletter-Adresse konnte nicht gefunden werden.</p></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:760px;margin:20px 0;">
			<tbody>
				<tr>
					<td><strong>Aktiv</strong></td>
					<td><?php echo esc_html( (string) $counts['active'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Ausstehend</strong></td>
					<td><?php echo esc_html( (string) $counts['pending'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Abgemeldet</strong></td>
					<td><?php echo esc_html( (string) $counts['unsubscribed'] ); ?></td>
				</tr>
				<tr>
					<td><strong>Gesamt</strong></td>
					<td><?php echo esc_html( (string) $counts['total'] ); ?></td>
				</tr>
			</tbody>
		</table>

		<p>
			<a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>">Aktive Abonnenten als CSV exportieren</a>
			<a class="button" href="<?php echo esc_url( $export_all_url ); ?>">Alle Einträge exportieren</a>
		</p>

		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end;max-width:980px;margin:20px 0;">
			<input type="hidden" name="page" value="hp-newsletter">
			<p style="margin:0;">
				<label for="hp-newsletter-status" style="display:block;font-weight:600;margin-bottom:6px;">Status</label>
				<select id="hp-newsletter-status" name="status">
					<option value="all"<?php selected( 'all', $status_filter ); ?>>Alle</option>
					<option value="active"<?php selected( 'active', $status_filter ); ?>>Aktiv</option>
					<option value="pending"<?php selected( 'pending', $status_filter ); ?>>Ausstehend</option>
					<option value="unsubscribed"<?php selected( 'unsubscribed', $status_filter ); ?>>Abgemeldet</option>
				</select>
			</p>
			<p style="margin:0;min-width:280px;flex:1 1 320px;">
				<label for="hp-newsletter-search" style="display:block;font-weight:600;margin-bottom:6px;">Suche</label>
				<input id="hp-newsletter-search" class="regular-text" type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="E-Mail oder Quelle">
			</p>
			<p style="margin:0;">
				<button class="button button-secondary" type="submit">Filtern</button>
			</p>
		</form>

		<h2>Letzte Einträge</h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th>E-Mail</th>
					<th>Status</th>
					<th>Quelle</th>
					<th>Eingetragen</th>
					<th>Bestätigt</th>
					<th>Aktionen</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $subscribers ) : ?>
					<?php foreach ( $subscribers as $subscriber ) : ?>
						<tr>
							<td><?php echo esc_html( $subscriber['email'] ); ?></td>
							<td><?php echo esc_html( $subscriber['status'] ); ?></td>
							<td><?php echo esc_html( $subscriber['source'] ); ?></td>
							<td><?php echo esc_html( $subscriber['subscribed_at'] ); ?></td>
							<td><?php echo esc_html( $subscriber['confirmed_at'] ); ?></td>
							<td>
								<?php if ( 'unsubscribed' !== $subscriber['status'] ) : ?>
									<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'hp_admin_unsubscribe_newsletter', 'subscriber' => absint( $subscriber['id'] ) ], admin_url( 'admin-post.php' ) ), 'hp_newsletter_admin_unsubscribe_' . absint( $subscriber['id'] ) ) ); ?>">Abmelden</a>
								<?php else : ?>
									<span>Bereits abgemeldet</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="6">Keine Newsletter-Einträge für diese Auswahl gefunden.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $suppressions ) : ?>
			<h2 style="margin-top:28px;">Zuletzt abgemeldet</h2>
			<p>Aus Datenschutzgründen wird hier nur eine maskierte Darstellung gezeigt.</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>Adresse</th>
						<th>Quelle</th>
						<th>Abgemeldet am</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $suppressions as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( $entry['email_mask'] ); ?></td>
							<td><?php echo esc_html( $entry['source'] ); ?></td>
							<td><?php echo esc_html( $entry['unsubscribed_at'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
