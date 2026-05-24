<?php
/**
 * Newsletter public and admin form handlers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Verarbeitet Newsletter-Anmeldungen.
 */
function hp_handle_newsletter_form_submission(): void {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	$email      = isset( $_POST['hp_newsletter_email'] ) ? sanitize_email( (string) wp_unslash( $_POST['hp_newsletter_email'] ) ) : '';
	$source     = isset( $_POST['hp_newsletter_source'] ) ? sanitize_key( (string) wp_unslash( $_POST['hp_newsletter_source'] ) ) : 'site';
	$target_url = isset( $_POST['hp_newsletter_redirect'] ) ? hp_get_newsletter_redirect_target( (string) wp_unslash( $_POST['hp_newsletter_redirect'] ) ) : home_url( '/' );
	$consent    = isset( $_POST['hp_newsletter_consent'] ) ? (string) wp_unslash( $_POST['hp_newsletter_consent'] ) : '';
	$flash      = [
		'status' => 'error',
		'source' => $source,
		'fields' => [
			'email' => $email,
		],
	];

	$nonce = isset( $_POST['hp_newsletter_nonce'] ) ? (string) wp_unslash( $_POST['hp_newsletter_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'hp_newsletter_submit' ) ) {
		$flash['message'] = 'Das Formular ist nicht mehr gültig. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$honeypot = isset( $_POST['hp_newsletter_website'] ) ? trim( (string) wp_unslash( $_POST['hp_newsletter_website'] ) ) : '';

	if ( '' !== $honeypot ) {
		$flash['message'] = 'Die Anmeldung konnte nicht verarbeitet werden. Bitte versuchen Sie es erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$settings    = hp_get_newsletter_form_settings();
	$rendered_at = isset( $_POST['hp_newsletter_rendered_at'] ) ? (int) wp_unslash( $_POST['hp_newsletter_rendered_at'] ) : 0;
	$token       = isset( $_POST['hp_newsletter_render_token'] ) ? (string) wp_unslash( $_POST['hp_newsletter_render_token'] ) : '';

	if ( $rendered_at <= 0 || '' === $token || ! hash_equals( hp_get_newsletter_form_render_token( $rendered_at ), $token ) ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$elapsed = time() - $rendered_at;

	if ( $elapsed < $settings['min_seconds'] ) {
		$flash['message'] = 'Bitte nehmen Sie sich einen kurzen Moment Zeit und senden Sie das Formular dann erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( $elapsed > $settings['max_age'] ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( '' === $email || ! is_email( $email ) ) {
		$flash['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	if ( '1' !== $consent ) {
		$flash['message'] = 'Bitte bestätigen Sie, dass Sie Hinweise auf neue Texte per E-Mail erhalten möchten.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$rate_key     = hp_get_newsletter_rate_key( $email );
	$last_sent_at = (int) get_transient( $rate_key );

	if ( $last_sent_at > 0 && ( time() - $last_sent_at ) < $settings['rate_window'] ) {
		$flash['message'] = 'Bitte warten Sie einen kurzen Moment, bevor Sie dieselbe Adresse erneut eintragen.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$existing = hp_get_newsletter_subscriber_by_email( $email );

	if ( $existing && 'active' === $existing['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'success',
				'source'  => $source,
				'message' => 'Diese Adresse ist bereits eingetragen. Neue Texte gehen künftig an dieses Postfach.',
			]
		);
	}

	$subscriber = hp_upsert_pending_newsletter_subscriber( $email, $source, $target_url );

	if ( is_wp_error( $subscriber ) ) {
		$flash['message'] = 'Die Anmeldung konnte technisch nicht abgeschlossen werden. Bitte schreiben Sie alternativ an ' . hp_get_newsletter_contact_email() . '.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	$mail_sent = hp_send_newsletter_confirmation_request( $subscriber );

	if ( ! $mail_sent ) {
		$flash['message'] = 'Die Bestätigungs-E-Mail konnte im Moment nicht versendet werden. Bitte versuchen Sie es später erneut.';
		hp_redirect_newsletter( $target_url, $flash );
	}

	set_transient( $rate_key, time(), $settings['rate_window'] );

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'source'  => $source,
			'message' => 'Fast geschafft. Bitte bestätigen Sie Ihre Anmeldung über die E-Mail, die gerade unterwegs ist.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_subscribe_newsletter', 'hp_handle_newsletter_form_submission' );
add_action( 'admin_post_hp_subscribe_newsletter', 'hp_handle_newsletter_form_submission' );

/**
 * Verarbeitet DOI-Bestätigungen.
 */
function hp_handle_newsletter_confirmation(): void {
	$token      = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
	$subscriber = hp_get_newsletter_subscriber_by_token( 'confirm_token', $token );
	$target_url = $subscriber ? hp_get_newsletter_redirect_target( $subscriber['source_url'] ) : home_url( '/' );

	if ( ! $subscriber ) {
		hp_redirect_newsletter(
			home_url( '/' ),
			[
				'status'  => 'error',
				'message' => 'Der Bestätigungslink ist nicht mehr gültig. Bitte tragen Sie sich bei Bedarf erneut ein.',
			]
		);
	}

	if ( 'active' === $subscriber['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'success',
				'message' => 'Ihre Anmeldung war bereits bestätigt.',
			]
		);
	}

	if ( 'pending' !== $subscriber['status'] ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Dieser Bestätigungslink kann nicht mehr verwendet werden. Bitte tragen Sie sich erneut ein.',
			]
		);
	}

	$updated = hp_update_newsletter_subscriber_status(
		(int) $subscriber['id'],
		'active',
		[
			'confirmed_at' => current_time( 'mysql' ),
		]
	);

	if ( ! $updated ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Die Anmeldung konnte technisch nicht bestätigt werden. Bitte versuchen Sie es erneut.',
			]
		);
	}

	$active_subscriber = hp_get_newsletter_subscriber_by_email( $subscriber['email'] );

	if ( $active_subscriber ) {
		hp_send_newsletter_welcome_mail( $active_subscriber );
	}

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'message' => 'Ihre Anmeldung ist bestätigt. Künftige Hinweise auf neue Texte gehen an dieses Postfach.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_confirm_newsletter', 'hp_handle_newsletter_confirmation' );
add_action( 'admin_post_hp_confirm_newsletter', 'hp_handle_newsletter_confirmation' );

/**
 * Verarbeitet Abmeldungen.
 */
function hp_handle_newsletter_unsubscribe(): void {
	$token      = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
	$subscriber = hp_get_newsletter_subscriber_by_token( 'unsubscribe_token', $token );
	$target_url = $subscriber ? hp_get_newsletter_redirect_target( $subscriber['source_url'] ) : home_url( '/' );

	if ( ! $subscriber ) {
		hp_redirect_newsletter(
			home_url( '/' ),
			[
				'status'  => 'error',
				'message' => 'Der Abmeldelink ist nicht mehr gültig.',
			]
		);
	}

	$unsubscribed = hp_suppress_newsletter_subscriber( $subscriber );

	if ( ! $unsubscribed ) {
		hp_redirect_newsletter(
			$target_url,
			[
				'status'  => 'error',
				'message' => 'Die Abmeldung konnte technisch nicht verarbeitet werden.',
			]
		);
	}

	hp_send_newsletter_unsubscribed_mail( $subscriber );

	hp_redirect_newsletter(
		$target_url,
		[
			'status'  => 'success',
			'message' => 'Die Adresse wurde abgemeldet. Sie erhalten keine Hinweise auf neue Texte mehr.',
		]
	);
}
add_action( 'admin_post_nopriv_hp_unsubscribe_newsletter', 'hp_handle_newsletter_unsubscribe' );
add_action( 'admin_post_hp_unsubscribe_newsletter', 'hp_handle_newsletter_unsubscribe' );

/**
 * Verarbeitet manuelle Austragungen im Admin.
 */
function hp_handle_newsletter_admin_unsubscribe(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sie haben nicht die erforderlichen Rechte.', 'hasimuener-journal' ) );
	}

	$subscriber_id = isset( $_GET['subscriber'] ) ? absint( $_GET['subscriber'] ) : 0;
	$redirect_url  = admin_url( 'admin.php?page=hp-newsletter' );

	if ( $subscriber_id <= 0 ) {
		wp_safe_redirect( add_query_arg( 'notice', 'invalid', $redirect_url ) );
		exit;
	}

	check_admin_referer( 'hp_newsletter_admin_unsubscribe_' . $subscriber_id );

	$subscriber = hp_get_newsletter_subscriber_by_id( $subscriber_id );

	if ( ! $subscriber ) {
		wp_safe_redirect( add_query_arg( 'notice', 'missing', $redirect_url ) );
		exit;
	}

	if ( hp_suppress_newsletter_subscriber( $subscriber ) ) {
		hp_send_newsletter_unsubscribed_mail( $subscriber );
	}

	wp_safe_redirect( add_query_arg( 'notice', 'manual_unsubscribed', $redirect_url ) );
	exit;
}
add_action( 'admin_post_hp_admin_unsubscribe_newsletter', 'hp_handle_newsletter_admin_unsubscribe' );
