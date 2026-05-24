<?php
/**
 * Newsletter mail delivery helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Generischer Versand für Newsletter-Mails.
 */
function hp_send_newsletter_mail( string $to_email, string $subject, string $html_content, string $text_content, array $tags = [] ): bool {
	$contact_email = hp_get_newsletter_contact_email();
	$from_header   = 'From: ' . hp_get_newsletter_sender_name() . ' <' . hp_get_newsletter_sender_email() . '>';
	$reply_header  = 'Reply-To: ' . hp_get_newsletter_sender_name() . ' <' . $contact_email . '>';
	$headers       = [
		'Content-Type: text/html; charset=UTF-8',
		$from_header,
		$reply_header,
	];

	if ( function_exists( 'hp_has_brevo_smtp_config' ) && hp_has_brevo_smtp_config() && function_exists( 'hp_send_wp_mail_via_brevo_smtp' ) ) {
		$mail_sent = hp_send_wp_mail_via_brevo_smtp(
			$to_email,
			$subject,
			$html_content,
			$headers,
			$text_content
		);

		if ( $mail_sent ) {
			return true;
		}
	}

	if ( function_exists( 'hp_has_brevo_api_key' ) && hp_has_brevo_api_key() && function_exists( 'hp_send_brevo_transactional_email' ) ) {
		$response = hp_send_brevo_transactional_email(
			[
				'to_email'       => $to_email,
				'subject'        => $subject,
				'html_content'   => $html_content,
				'text_content'   => $text_content,
				'reply_to_email' => $contact_email,
				'reply_to_name'  => hp_get_newsletter_sender_name(),
				'tags'           => $tags,
			]
		);

		if ( ! empty( $response['success'] ) ) {
			return true;
		}
	}

	$alt_body_setter = static function ( PHPMailer\PHPMailer\PHPMailer $phpmailer ) use ( $text_content ): void {
		$phpmailer->AltBody = $text_content;
	};

	add_action( 'phpmailer_init', $alt_body_setter );
	$mail_sent = wp_mail( $to_email, $subject, $html_content, $headers );
	remove_action( 'phpmailer_init', $alt_body_setter );

	return $mail_sent;
}

/**
 * DOI-Mail versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_confirmation_request( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_confirmation_subject(),
		hp_get_newsletter_confirmation_html( $subscriber ),
		hp_get_newsletter_confirmation_text( $subscriber ),
		[ 'newsletter', 'newsletter-confirmation' ]
	);
}

/**
 * Willkommensmail versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_welcome_mail( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_welcome_subject(),
		hp_get_newsletter_welcome_html( $subscriber ),
		hp_get_newsletter_welcome_text( $subscriber ),
		[ 'newsletter', 'newsletter-welcome' ]
	);
}

/**
 * Austragungsbestätigung versenden.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_send_newsletter_unsubscribed_mail( array $subscriber ): bool {
	return hp_send_newsletter_mail(
		$subscriber['email'],
		hp_get_newsletter_unsubscribed_subject(),
		hp_get_newsletter_unsubscribed_html( $subscriber ),
		hp_get_newsletter_unsubscribed_text( $subscriber ),
		[ 'newsletter', 'newsletter-unsubscribed' ]
	);
}
