<?php
/**
 * Contact form submission handlers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Verarbeitet native Kontaktanfragen.
 */
function hp_handle_contact_form_submission(): void {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
		wp_safe_redirect( hp_get_contact_page_url() );
		exit;
	}

	$fields = [
		'name'         => isset( $_POST['hp_contact_name'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['hp_contact_name'] ) ) : '',
		'email'        => isset( $_POST['hp_contact_email'] ) ? sanitize_email( (string) wp_unslash( $_POST['hp_contact_email'] ) ) : '',
		'inquiry_type' => isset( $_POST['hp_contact_inquiry_type'] ) ? sanitize_key( (string) wp_unslash( $_POST['hp_contact_inquiry_type'] ) ) : '',
		'subject'      => '',
		'message'      => isset( $_POST['hp_contact_message'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['hp_contact_message'] ) ) ) : '',
	];

	$flash = [
		'status' => 'error',
		'fields' => $fields,
	];

	$nonce = isset( $_POST['hp_contact_nonce'] ) ? (string) wp_unslash( $_POST['hp_contact_nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'hp_contact_submit' ) ) {
		$flash['message'] = 'Das Formular ist nicht mehr gültig. Bitte laden Sie die Seite neu und versuchen Sie es erneut.';
		hp_redirect_contact_form( $flash );
	}

	$honeypot = isset( $_POST['hp_contact_website'] ) ? trim( (string) wp_unslash( $_POST['hp_contact_website'] ) ) : '';

	if ( '' !== $honeypot ) {
		$flash['message'] = 'Die Nachricht konnte nicht gesendet werden. Bitte versuchen Sie es erneut.';
		hp_redirect_contact_form( $flash );
	}

	$settings    = hp_get_contact_form_settings();
	$rendered_at = isset( $_POST['hp_contact_rendered_at'] ) ? (int) wp_unslash( $_POST['hp_contact_rendered_at'] ) : 0;
	$token       = isset( $_POST['hp_contact_render_token'] ) ? (string) wp_unslash( $_POST['hp_contact_render_token'] ) : '';

	if ( $rendered_at <= 0 || '' === $token || ! hash_equals( hp_get_contact_form_render_token( $rendered_at ), $token ) ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu und versuchen Sie es erneut.';
		hp_redirect_contact_form( $flash );
	}

	$elapsed = time() - $rendered_at;

	if ( $elapsed < $settings['min_seconds'] ) {
		$flash['message'] = 'Bitte nehmen Sie sich einen kurzen Moment Zeit und senden Sie die Nachricht dann erneut.';
		hp_redirect_contact_form( $flash );
	}

	if ( $elapsed > $settings['max_age'] ) {
		$flash['message'] = 'Das Formular ist abgelaufen. Bitte laden Sie die Seite neu und versuchen Sie es erneut.';
		hp_redirect_contact_form( $flash );
	}

	$link_count = preg_match_all( '/(?:https?:\/\/|www\.|<a\s)/iu', $fields['message'] );

	if ( false !== $link_count && $link_count > $settings['max_links'] ) {
		$flash['message'] = 'Bitte reduzieren Sie die Zahl der Links in Ihrer Nachricht.';
		hp_redirect_contact_form( $flash );
	}

	$rate_key     = 'hp_contact_rate_' . hp_get_contact_form_rate_key();
	$last_sent_at = (int) get_transient( $rate_key );

	if ( $last_sent_at > 0 && ( time() - $last_sent_at ) < $settings['rate_window'] ) {
		$flash['message'] = 'Bitte warten Sie einen kurzen Moment, bevor Sie eine weitere Nachricht senden.';
		hp_redirect_contact_form( $flash );
	}

	if ( '' === $fields['name'] ) {
		$flash['message'] = 'Bitte geben Sie Ihren Namen an.';
		hp_redirect_contact_form( $flash );
	}

	if ( '' === $fields['email'] || ! is_email( $fields['email'] ) ) {
		$flash['message'] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
		hp_redirect_contact_form( $flash );
	}

	if ( ! array_key_exists( $fields['inquiry_type'], hp_get_contact_inquiry_type_options() ) ) {
		$flash['message'] = 'Bitte wählen Sie die Art Ihrer Anfrage aus.';
		hp_redirect_contact_form( $flash );
	}

	if ( '' === $fields['message'] ) {
		$flash['message'] = 'Bitte beschreiben Sie Ihr Anliegen kurz.';
		hp_redirect_contact_form( $flash );
	}

	$fields['subject'] = hp_get_contact_submission_subject( $fields );

	$mail_sent = hp_send_contact_notification( $fields );

	$autoresponse_sent = false;

	if ( $mail_sent && strtolower( $fields['email'] ) !== strtolower( hp_get_contact_email() ) ) {
		$autoresponse_sent = hp_send_contact_autoreply( $fields );
	}

	if ( function_exists( 'hp_store_contact_submission' ) ) {
		hp_store_contact_submission( $fields, $mail_sent, $autoresponse_sent );
	}

	if ( ! $mail_sent ) {
		$flash['message'] = 'Die Nachricht konnte technisch nicht versendet werden. Sie können alternativ direkt an ' . hp_get_contact_email() . ' schreiben.';
		hp_redirect_contact_form( $flash );
	}

	set_transient( $rate_key, time(), $settings['rate_window'] );

	hp_redirect_contact_form( [
		'status'  => 'success',
		'message' => $autoresponse_sent
			? 'Vielen Dank. Ihre Nachricht wurde versendet. Eine kurze Bestätigung ist per E-Mail unterwegs.'
			: 'Vielen Dank. Ihre Nachricht wurde versendet.',
	] );
}
add_action( 'admin_post_nopriv_hp_send_contact', 'hp_handle_contact_form_submission' );
add_action( 'admin_post_hp_send_contact', 'hp_handle_contact_form_submission' );
