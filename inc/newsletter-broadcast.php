<?php
/**
 * Newsletter-Broadcast — Hasimuener Journal
 *
 * Auto-Versand bei Publish von Essays/Dossiers.
 *
 * Strategischer Zweck (Pillar 3 — Verbreitungsnetzwerk):
 * Macht den Newsletter zum Push-Kanal in die eigene
 * Leserschaft — unabhängig von Algorithmen von X/
 * LinkedIn. Jeder neue Essay/Dossier erreicht alle
 * bestätigten Subscriber direkt im Postfach.
 *
 * Sicherheitsdesign:
 * - Opt-in pro Post via Meta-Box-Checkbox (Default OFF).
 *   Verhindert versehentlichen Massenversand bei
 *   Backfill, Migrations-Imports oder Datums-Updates.
 * - Idempotenz: Meta `_hp_newsletter_broadcast_sent_at`
 *   blockiert Zweitversand. Einmal raus = nie wieder.
 * - Hard-Cap: max. 250 Subscriber pro Request. Größere
 *   Listen brauchen Cron-Batching (bewusst nicht
 *   implementiert — explizite Eskalations-Schwelle).
 * - Status-Audit: gesendete Anzahl + Timestamp werden
 *   in Post-Meta gespeichert, in Editor sichtbar.
 *
 * @package Hasimuener_Journal
 * @since   5.8.0
 */

defined( 'ABSPATH' ) || exit;

const HP_BROADCAST_OPT_IN_META    = '_hp_broadcast_on_publish';
const HP_BROADCAST_SENT_AT_META   = '_hp_newsletter_broadcast_sent_at';
const HP_BROADCAST_SENT_COUNT_META = '_hp_newsletter_broadcast_sent_count';
const HP_BROADCAST_HARD_CAP       = 250;

/**
 * Post-Types, die per Broadcast versendet werden dürfen.
 */
function hp_broadcast_post_types(): array {
	return apply_filters( 'hp_broadcast_post_types', [ 'essay', 'dossier' ] );
}

/**
 * Liefert alle bestätigten (active) Subscriber.
 *
 * Ungebatched — für Listen bis HP_BROADCAST_HARD_CAP.
 *
 * @return array<int,array{id:string,email:string}>
 */
function hp_get_all_active_subscribers(): array {
	global $wpdb;
	$table = hp_get_newsletter_table_name();
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, email FROM {$table} WHERE status = %s ORDER BY id ASC",
			'active'
		),
		ARRAY_A
	);
	return is_array( $rows ) ? $rows : [];
}

/**
 * Rendert die HTML-Broadcast-Mail.
 */
function hp_render_broadcast_email_html( WP_Post $post, array $subscriber ): string {
	$title      = get_the_title( $post );
	$permalink  = get_permalink( $post );
	$type_label = 'dossier' === $post->post_type ? 'Dossier' : 'Essay';
	$excerpt    = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 55, ' …' );

	$intro_html = sprintf(
		'<p style="margin:0 0 0.4em;font-size:13px;letter-spacing:0.14em;text-transform:uppercase;color:#7a7268;">Neuer %s</p>',
		esc_html( $type_label )
	);

	$body_html  = sprintf(
		'<h1 style="font-family:Georgia,serif;font-size:26px;line-height:1.25;margin:0 0 0.6em;color:#14181d;">%s</h1>',
		esc_html( $title )
	);
	$body_html .= sprintf(
		'<p style="font-family:Georgia,serif;font-size:16px;line-height:1.6;color:#3a3530;margin:0 0 1.5em;">%s</p>',
		esc_html( $excerpt )
	);
	$body_html .= sprintf(
		'<p style="margin:0 0 1.5em;"><a href="%s" style="display:inline-block;padding:12px 22px;background:#bd4e2f;color:#fff;text-decoration:none;border-radius:999px;font-family:-apple-system,Segoe UI,sans-serif;font-size:15px;font-weight:600;">Jetzt lesen →</a></p>',
		esc_url( $permalink )
	);

	$unsubscribe_url = function_exists( 'hp_get_newsletter_unsubscribe_url' )
		? hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] ?? '' )
		: home_url( '/' );

	$footnote_html = sprintf(
		'Du erhältst diese Mail, weil du dich auf %s für Benachrichtigungen über neue Texte registriert hast. <a href="%s" style="color:#7a7268;">Abmelden</a>.',
		esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ),
		esc_url( $unsubscribe_url )
	);

	return hp_get_newsletter_mail_shell( $title, $intro_html, $body_html, $footnote_html );
}

/**
 * Rendert die Plain-Text-Broadcast-Mail.
 */
function hp_render_broadcast_email_text( WP_Post $post, array $subscriber ): string {
	$title      = get_the_title( $post );
	$permalink  = get_permalink( $post );
	$type_label = 'dossier' === $post->post_type ? 'Dossier' : 'Essay';
	$excerpt    = has_excerpt( $post->ID )
		? wp_strip_all_tags( get_the_excerpt( $post ) )
		: wp_trim_words( wp_strip_all_tags( $post->post_content ), 60, ' …' );

	$unsubscribe_url = function_exists( 'hp_get_newsletter_unsubscribe_url' )
		? hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] ?? '' )
		: home_url( '/' );

	return sprintf(
		"NEUER %s\n\n%s\n\n%s\n\nLesen: %s\n\n--\nDu erhältst diese Mail, weil du dich auf %s registriert hast.\nAbmelden: %s\n",
		strtoupper( $type_label ),
		$title,
		$excerpt,
		$permalink,
		wp_parse_url( home_url(), PHP_URL_HOST ),
		$unsubscribe_url
	);
}

/**
 * Versendet einen Broadcast für einen Post.
 *
 * @return array{sent:int,failed:int,skipped:string}
 */
function hp_broadcast_post_to_subscribers( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return [ 'sent' => 0, 'failed' => 0, 'skipped' => 'not_published' ];
	}
	if ( ! in_array( $post->post_type, hp_broadcast_post_types(), true ) ) {
		return [ 'sent' => 0, 'failed' => 0, 'skipped' => 'wrong_post_type' ];
	}
	if ( get_post_meta( $post_id, HP_BROADCAST_SENT_AT_META, true ) ) {
		return [ 'sent' => 0, 'failed' => 0, 'skipped' => 'already_sent' ];
	}

	$subscribers = hp_get_all_active_subscribers();
	if ( count( $subscribers ) > HP_BROADCAST_HARD_CAP ) {
		error_log( sprintf(
			'[Hasimuener] Broadcast skipped for post %d: %d subscribers exceeds hard cap %d. Implement cron batching.',
			$post_id,
			count( $subscribers ),
			HP_BROADCAST_HARD_CAP
		) );
		return [ 'sent' => 0, 'failed' => 0, 'skipped' => 'hard_cap_exceeded' ];
	}
	if ( ! $subscribers ) {
		// Trotzdem als "sent" markieren, damit Editor-UI klare Rückmeldung gibt.
		update_post_meta( $post_id, HP_BROADCAST_SENT_AT_META, current_time( 'mysql' ) );
		update_post_meta( $post_id, HP_BROADCAST_SENT_COUNT_META, 0 );
		return [ 'sent' => 0, 'failed' => 0, 'skipped' => 'no_subscribers' ];
	}

	$type_label = 'dossier' === $post->post_type ? 'Dossier' : 'Essay';
	$subject    = sprintf( 'Neuer %s: %s', $type_label, get_the_title( $post ) );

	$sent   = 0;
	$failed = 0;
	foreach ( $subscribers as $sub ) {
		// Unsubscribe-Token nachladen (nicht in der Active-Liste enthalten)
		$full = hp_get_newsletter_subscriber_by_id( (int) $sub['id'] );
		if ( ! $full ) {
			$failed++;
			continue;
		}
		$html = hp_render_broadcast_email_html( $post, $full );
		$text = hp_render_broadcast_email_text( $post, $full );
		$ok   = hp_send_newsletter_mail(
			$sub['email'],
			$subject,
			$html,
			$text,
			[ 'broadcast', $post->post_type, 'post-' . $post_id ]
		);
		if ( $ok ) { $sent++; } else { $failed++; }
	}

	update_post_meta( $post_id, HP_BROADCAST_SENT_AT_META, current_time( 'mysql' ) );
	update_post_meta( $post_id, HP_BROADCAST_SENT_COUNT_META, $sent );

	return [ 'sent' => $sent, 'failed' => $failed, 'skipped' => '' ];
}

/**
 * transition_post_status-Hook: feuert Broadcast, wenn
 * Post von non-publish → publish wechselt UND der
 * Opt-in-Checkbox gesetzt ist.
 */
function hp_maybe_broadcast_on_publish( string $new_status, string $old_status, WP_Post $post ): void {
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}
	if ( ! in_array( $post->post_type, hp_broadcast_post_types(), true ) ) {
		return;
	}
	$opt_in = get_post_meta( $post->ID, HP_BROADCAST_OPT_IN_META, true );
	if ( '1' !== (string) $opt_in ) {
		return;
	}
	hp_broadcast_post_to_subscribers( (int) $post->ID );
}
add_action( 'transition_post_status', 'hp_maybe_broadcast_on_publish', 20, 3 );

/**
 * Meta-Box im Post-Editor: Opt-in-Checkbox + Status.
 *
 * Klassisch via add_meta_box — funktioniert in Gutenberg
 * (im „Document"-Sidebar als Panel) und im Classic Editor.
 */
function hp_register_broadcast_meta_box(): void {
	foreach ( hp_broadcast_post_types() as $pt ) {
		add_meta_box(
			'hp_newsletter_broadcast',
			'Newsletter-Versand',
			'hp_render_broadcast_meta_box',
			$pt,
			'side',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'hp_register_broadcast_meta_box' );

function hp_render_broadcast_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'hp_broadcast_save_' . $post->ID, 'hp_broadcast_nonce' );

	$sent_at    = (string) get_post_meta( $post->ID, HP_BROADCAST_SENT_AT_META, true );
	$sent_count = (int) get_post_meta( $post->ID, HP_BROADCAST_SENT_COUNT_META, true );
	$opt_in     = (string) get_post_meta( $post->ID, HP_BROADCAST_OPT_IN_META, true );

	if ( $sent_at ) {
		$ts = strtotime( $sent_at );
		printf(
			'<p style="margin:0;padding:8px 10px;background:#e8f4ec;border-left:3px solid #1f7a37;border-radius:3px;font-size:12px;line-height:1.5;"><strong>Bereits versendet</strong><br>%s · %d Empfänger</p>',
			esc_html( $ts ? date_i18n( 'j. F Y · H:i', $ts ) : $sent_at ),
			$sent_count
		);
		return;
	}

	$active_count = count( hp_get_all_active_subscribers() );
	?>
	<p style="margin:0 0 10px;">
		<label style="display:flex;gap:8px;align-items:flex-start;cursor:pointer;">
			<input type="checkbox" name="hp_broadcast_opt_in" value="1" <?php checked( '1', $opt_in ); ?> style="margin-top:3px;">
			<span style="font-size:13px;line-height:1.45;">Beim Veröffentlichen <strong>an alle Abonnenten</strong> versenden.</span>
		</label>
	</p>
	<p style="margin:0;font-size:11px;color:#646970;line-height:1.5;">
		<?php
		printf(
			esc_html__( '%d aktive Abonnenten · einmaliger Versand · max. %d (Hard-Cap)', 'hp' ),
			(int) $active_count,
			(int) HP_BROADCAST_HARD_CAP
		);
		?>
	</p>
	<?php
}

function hp_save_broadcast_meta_box( int $post_id ): void {
	if ( ! isset( $_POST['hp_broadcast_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hp_broadcast_nonce'] ) ), 'hp_broadcast_save_' . $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$value = isset( $_POST['hp_broadcast_opt_in'] ) ? '1' : '0';
	update_post_meta( $post_id, HP_BROADCAST_OPT_IN_META, $value );
}
add_action( 'save_post', 'hp_save_broadcast_meta_box' );

/**
 * Macht die Meta-Felder REST-sichtbar für den Block-
 * Editor (Gutenberg), damit der Sidebar-Panel reibungslos
 * funktioniert.
 */
function hp_register_broadcast_post_meta(): void {
	foreach ( hp_broadcast_post_types() as $pt ) {
		register_post_meta( $pt, HP_BROADCAST_OPT_IN_META, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( $pt, HP_BROADCAST_SENT_AT_META, [
			'type'         => 'string',
			'single'       => true,
			'show_in_rest' => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
		register_post_meta( $pt, HP_BROADCAST_SENT_COUNT_META, [
			'type'         => 'integer',
			'single'       => true,
			'show_in_rest' => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		] );
	}
}
add_action( 'init', 'hp_register_broadcast_post_meta' );
