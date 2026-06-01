<?php
/**
 * Contact mail templates and delivery helpers.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Betreff der automatischen Eingangsbestätigung.
 */
function hp_get_contact_autoreply_subject(): string {
	return 'Ihre Nachricht an hasimuener.org ist eingegangen';
}

/**
 * Liefert einen zustellungsfreundlichen Absendernamen.
 */
function hp_get_contact_mail_sender_name(): string {
	return 'Hasim Uener';
}

/**
 * Baut das HTML-Template der automatischen Eingangsbestätigung.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_get_contact_autoreply_html( array $fields ): string {
	$site_name      = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$contact_email  = hp_get_contact_email();
	$contact_mailto = 'mailto:' . $contact_email;
	$site_url       = home_url( '/' );
	$imprint_url    = home_url( '/impressum/' );
	$privacy_url    = home_url( '/datenschutz/' );
	$name_line      = '' !== $fields['name'] ? esc_html( $fields['name'] ) : 'Guten Tag';
	$inquiry_line   = esc_html( hp_get_contact_inquiry_type_label( (string) ( $fields['inquiry_type'] ?? '' ) ) );

	return '<!doctype html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( hp_get_contact_autoreply_subject() ) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;color:#222222;">
	<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f5f7;margin:0;padding:24px 0;">
		<tr>
			<td align="center">
				<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px;background:#ffffff;border:1px solid rgba(17,17,17,0.08);border-radius:18px;overflow:hidden;">
					<tr>
						<td style="padding:28px 32px 14px;border-top:4px solid #b12a2a;">
							<p style="margin:0 0 10px;font-family:Georgia,Times New Roman,serif;font-size:12px;line-height:1.5;letter-spacing:1.8px;text-transform:uppercase;color:#696969;">' . esc_html( $site_name ) . '</p>
							<h1 style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:30px;line-height:1.2;color:#111111;font-weight:700;">Ihre Nachricht ist eingegangen.</h1>
						</td>
					</tr>
					<tr>
						<td style="padding:0 32px 8px;">
							<p style="margin:0 0 14px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">' . $name_line . ', vielen Dank für Ihre Nachricht über hasimuener.org. Sie wurde direkt weitergeleitet.</p>
							<p style="margin:0 0 14px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Ich melde mich, sobald ich inhaltlich antworten kann. Wenn Sie in der Zwischenzeit etwas ergänzen möchten, können Sie direkt auf diese E-Mail antworten.</p>
						</td>
					</tr>
					<tr>
						<td style="padding:8px 32px 8px;">
							<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:14px;background:#faf8f5;">
								<tr>
									<td style="padding:16px 18px;">
										<p style="margin:0 0 8px;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.4;letter-spacing:1.5px;text-transform:uppercase;color:#696969;">Zusammenfassung</p>
										<p style="margin:0 0 6px;font-family:Georgia,Times New Roman,serif;font-size:15px;line-height:1.6;color:#222222;"><strong>Art der Anfrage:</strong> ' . $inquiry_line . '</p>
										<p style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:15px;line-height:1.6;color:#222222;"><strong>Antwortadresse:</strong> <a href="' . esc_url( $contact_mailto ) . '" style="color:#b12a2a;text-decoration:none;">' . esc_html( $contact_email ) . '</a></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:16px 32px 30px;">
							<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Mit freundlichen Grüßen<br>Haşim Üner</p>
							<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #ece7df;padding-top:14px;margin-top:14px;">
								<tr>
									<td style="padding-top:14px;">
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;">Haşim Üner</p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $contact_mailto ) . '" style="color:#b12a2a;text-decoration:none;">' . esc_html( $contact_email ) . '</a></p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $site_url ) . '" style="color:#b12a2a;text-decoration:none;">hasimuener.org</a></p>
										<p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $imprint_url ) . '" style="color:#b12a2a;text-decoration:none;">Impressum</a> · <a href="' . esc_url( $privacy_url ) . '" style="color:#b12a2a;text-decoration:none;">Datenschutz</a></p>
									</td>
								</tr>
							</table>
							<p style="margin:14px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;">Diese E-Mail wurde automatisch nach dem Absenden des Kontaktformulars erzeugt.</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>';
}

/**
 * Baut die Textversion der automatischen Eingangsbestätigung.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_get_contact_autoreply_text( array $fields ): string {
	$contact_email = hp_get_contact_email();
	$site_url      = home_url( '/' );
	$imprint_url   = home_url( '/impressum/' );
	$privacy_url   = home_url( '/datenschutz/' );
	$name_line     = '' !== $fields['name'] ? $fields['name'] : 'Guten Tag';
	$inquiry_line  = hp_get_contact_inquiry_type_label( (string) ( $fields['inquiry_type'] ?? '' ) );

	return implode(
		"\n\n",
		[
			'Ihre Nachricht ist eingegangen.',
			$name_line . ', vielen Dank für Ihre Nachricht über hasimuener.org. Sie wurde direkt weitergeleitet.',
			'Ich melde mich, sobald ich inhaltlich antworten kann. Wenn Sie in der Zwischenzeit etwas ergänzen möchten, können Sie direkt auf diese E-Mail antworten.',
			'Zusammenfassung',
			'Art der Anfrage: ' . $inquiry_line . "\n" . 'Antwortadresse: ' . $contact_email,
			'Mit freundlichen Grüßen' . "\n" . 'Haşim Üner',
			'Kontakt: ' . $contact_email . "\n" . 'Website: ' . $site_url . "\n" . 'Impressum: ' . $imprint_url . "\n" . 'Datenschutz: ' . $privacy_url,
			'Diese E-Mail wurde automatisch nach dem Absenden des Kontaktformulars erzeugt.',
		]
	);
}

/**
 * Versendet die automatische Eingangsbestätigung.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_send_contact_autoreply( array $fields ): bool {
	if ( hp_has_brevo_smtp_config() ) {
		$mail_sent = hp_send_wp_mail_via_brevo_smtp(
			$fields['email'],
			hp_get_contact_autoreply_subject(),
			hp_get_contact_autoreply_html( $fields ),
			[
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . hp_get_contact_mail_sender_name() . ' <' . hp_get_contact_sender_email() . '>',
				'Reply-To: ' . hp_get_contact_mail_sender_name() . ' <' . hp_get_contact_email() . '>',
				'Auto-Submitted: auto-replied',
				'X-Auto-Response-Suppress: All',
			],
			hp_get_contact_autoreply_text( $fields )
		);

		if ( $mail_sent ) {
			return true;
		}
	}

	if ( hp_has_brevo_api_key() ) {
		$response = hp_send_brevo_transactional_email( [
			'to_email'      => $fields['email'],
			'to_name'       => $fields['name'],
			'subject'       => hp_get_contact_autoreply_subject(),
			'html_content'  => hp_get_contact_autoreply_html( $fields ),
			'text_content'  => hp_get_contact_autoreply_text( $fields ),
			'reply_to_email'=> hp_get_contact_email(),
			'reply_to_name' => hp_get_contact_brevo_sender_name(),
			'tags'          => [ 'contact-form', 'contact-autoreply' ],
		] );

		if ( ! empty( $response['success'] ) ) {
			return true;
		}
	}

	$contact_email = hp_get_contact_email();
	$sender_email  = hp_get_contact_sender_email();
	$text_body     = hp_get_contact_autoreply_text( $fields );
	$headers       = [
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . hp_get_contact_mail_sender_name() . ' <' . $sender_email . '>',
		'Reply-To: ' . hp_get_contact_mail_sender_name() . ' <' . $contact_email . '>',
		'Auto-Submitted: auto-replied',
		'X-Auto-Response-Suppress: All',
	];

	$alt_body_setter = static function ( PHPMailer\PHPMailer\PHPMailer $phpmailer ) use ( $text_body ): void {
		$phpmailer->AltBody = $text_body;
	};

	add_action( 'phpmailer_init', $alt_body_setter );

	$mail_sent = wp_mail(
		$fields['email'],
		hp_get_contact_autoreply_subject(),
		hp_get_contact_autoreply_html( $fields ),
		$headers
	);

	remove_action( 'phpmailer_init', $alt_body_setter );

	return $mail_sent;
}

/**
 * Baut die Textversion der internen Kontaktbenachrichtigung.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_get_contact_notification_text( array $fields ): string {
	$inquiry_type = hp_get_contact_inquiry_type_label( (string) ( $fields['inquiry_type'] ?? '' ) );

	return implode(
		"\n\n",
		[
			'Neue Nachricht über das Kontaktformular von hasimuener.org',
			'Name: ' . $fields['name'],
			'E-Mail: ' . $fields['email'],
			'Art der Anfrage: ' . $inquiry_type,
			'Interner Betreff: ' . ( '' !== $fields['subject'] ? $fields['subject'] : 'Nicht angegeben' ),
			'Beschreibung des Anliegens:',
			$fields['message'],
		]
	);
}

/**
 * Versendet die interne Benachrichtigung über eine neue Kontaktanfrage.
 *
 * @param array<string, string> $fields Validierte Formularfelder.
 */
function hp_send_contact_notification( array $fields ): bool {
	$reply_name = preg_replace( '/[\r\n]+/', ' ', $fields['name'] );
	$subject    = '' !== $fields['subject'] ? $fields['subject'] : 'Neue Nachricht über das Kontaktformular';
	$mail_body  = hp_get_contact_notification_text( $fields );

	if ( hp_has_brevo_smtp_config() ) {
		$mail_sent = hp_send_wp_mail_via_brevo_smtp(
			hp_get_contact_email(),
			'[hasimuener.org] ' . $subject,
			$mail_body,
			[
					'Content-Type: text/plain; charset=UTF-8',
					'From: ' . hp_get_contact_mail_sender_name() . ' <' . hp_get_contact_sender_email() . '>',
					'Reply-To: ' . $reply_name . ' <' . $fields['email'] . '>',
				]
		);

		if ( $mail_sent ) {
			return true;
		}
	}

	if ( hp_has_brevo_api_key() ) {
		$response = hp_send_brevo_transactional_email( [
			'to_email'       => hp_get_contact_email(),
			'to_name'        => hp_get_contact_brevo_sender_name(),
			'subject'        => '[hasimuener.org] ' . $subject,
			'text_content'   => $mail_body,
			'reply_to_email' => $fields['email'],
			'reply_to_name'  => is_string( $reply_name ) && '' !== $reply_name ? $reply_name : $fields['email'],
			'tags'           => [ 'contact-form', 'contact-notification' ],
		] );

		if ( ! empty( $response['success'] ) ) {
			return true;
		}
	}

	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $reply_name . ' <' . $fields['email'] . '>',
	];

	return wp_mail(
		hp_get_contact_email(),
		'[hasimuener.org] ' . $subject,
		$mail_body,
		$headers
	);
}

/**
 * Versendet eine E-Mail gezielt über Brevo-SMTP.
 *
 * @param string        $to       Empfängeradresse.
 * @param string        $subject  Betreff.
 * @param string        $message  Nachricht.
 * @param array<int,string> $headers Header.
 * @param string|null   $alt_body Textalternative für HTML-Mails.
 */
function hp_send_wp_mail_via_brevo_smtp( string $to, string $subject, string $message, array $headers = [], ?string $alt_body = null ): bool {
	if ( ! hp_has_brevo_smtp_config() ) {
		return false;
	}

	$smtp_configurator = static function ( PHPMailer\PHPMailer\PHPMailer $phpmailer ) use ( $alt_body ): void {
		$phpmailer->isSMTP();
		$phpmailer->Host       = 'smtp-relay.brevo.com';
		$phpmailer->Port       = 587;
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = hp_get_brevo_smtp_login();
		$phpmailer->Password   = hp_get_brevo_smtp_key();
		$phpmailer->SMTPSecure = '';
		$phpmailer->CharSet    = 'UTF-8';

		if ( null !== $alt_body ) {
			$phpmailer->AltBody = $alt_body;
		}
	};

	add_action( 'phpmailer_init', $smtp_configurator );
	$mail_sent = wp_mail( $to, $subject, $message, $headers );
	remove_action( 'phpmailer_init', $smtp_configurator );

	return $mail_sent;
}

/**
 * Versendet eine transaktionale E-Mail über die Brevo API.
 *
 * @param array<string, mixed> $args Versanddaten.
 * @return array{success:bool,message_id?:string,error?:string}
 */
function hp_send_brevo_transactional_email( array $args ): array {
	$api_key = hp_get_brevo_api_key();

	if ( '' === $api_key ) {
		return [
			'success' => false,
			'error'   => 'missing_api_key',
		];
	}

	$payload = [
		'sender'  => [
			'name'  => hp_get_contact_brevo_sender_name(),
			'email' => hp_get_contact_sender_email(),
		],
		'to'      => [
			[
				'email' => (string) ( $args['to_email'] ?? '' ),
				'name'  => (string) ( $args['to_name'] ?? '' ),
			],
		],
		'subject' => (string) ( $args['subject'] ?? '' ),
	];

	if ( ! empty( $args['reply_to_email'] ) ) {
		$payload['replyTo'] = [
			'email' => (string) $args['reply_to_email'],
			'name'  => (string) ( $args['reply_to_name'] ?? $args['reply_to_email'] ),
		];
	}

	if ( ! empty( $args['html_content'] ) ) {
		$payload['htmlContent'] = (string) $args['html_content'];
	}

	if ( ! empty( $args['text_content'] ) ) {
		$payload['textContent'] = (string) $args['text_content'];
	}

	if ( ! empty( $args['tags'] ) && is_array( $args['tags'] ) ) {
		$payload['tags'] = array_values(
			array_filter(
				array_map( 'strval', $args['tags'] ),
				static function ( string $tag ): bool {
					return '' !== $tag;
				}
			)
		);
	}

	$response = wp_remote_post(
		'https://api.brevo.com/v3/smtp/email',
		[
			'headers' => [
				'accept'       => 'application/json',
				'api-key'      => $api_key,
				'content-type' => 'application/json',
			],
			'body'        => wp_json_encode( $payload ),
			'timeout'     => 20,
			'data_format' => 'body',
		]
	);

	if ( is_wp_error( $response ) ) {
		return [
			'success' => false,
			'error'   => $response->get_error_message(),
		];
	}

	$status_code = (int) wp_remote_retrieve_response_code( $response );
	$body        = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( $status_code >= 200 && $status_code < 300 ) {
		return [
			'success'    => true,
			'message_id' => is_array( $body ) && isset( $body['messageId'] ) ? (string) $body['messageId'] : '',
		];
	}

	return [
		'success' => false,
		'error'   => is_array( $body ) && isset( $body['message'] ) ? (string) $body['message'] : 'brevo_api_error',
	];
}
