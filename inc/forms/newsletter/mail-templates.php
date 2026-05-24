<?php
/**
 * Newsletter mail subjects and templates.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Baut eine HTML-Mailhülle für Newsletter-Mails.
 */
function hp_get_newsletter_mail_shell( string $title, string $intro_html, string $body_html, string $footnote_html ): string {
	$site_name     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$contact_email = hp_get_newsletter_contact_email();
	$contact_url   = 'mailto:' . $contact_email;
	$imprint_url   = home_url( '/impressum/' );
	$privacy_url   = home_url( '/datenschutz/' );

	return '<!doctype html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $title ) . '</title>
</head>
<body style="margin:0;padding:0;background:#f4f3ef;color:#1b1b1b;">
	<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f3ef;margin:0;padding:24px 0;">
		<tr>
			<td align="center">
				<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px;background:#ffffff;border:1px solid rgba(17,17,17,0.08);border-radius:22px;overflow:hidden;">
					<tr>
						<td style="padding:30px 34px 16px;border-top:4px solid #b12a2a;">
							<p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;letter-spacing:1.8px;text-transform:uppercase;color:#696969;">' . esc_html( $site_name ) . '</p>
							<h1 style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:30px;line-height:1.2;color:#111111;font-weight:700;">' . esc_html( $title ) . '</h1>
						</td>
					</tr>
					<tr>
						<td style="padding:0 34px 0;">' . $intro_html . '</td>
					</tr>
					<tr>
						<td style="padding:4px 34px 10px;">' . $body_html . '</td>
					</tr>
					<tr>
						<td style="padding:10px 34px 30px;">
							' . $footnote_html . '
							<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-top:1px solid #ece7df;padding-top:14px;margin-top:16px;">
								<tr>
									<td style="padding-top:14px;">
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;">Haşim Üner</p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $contact_url ) . '" style="color:#b12a2a;text-decoration:none;">' . esc_html( $contact_email ) . '</a></p>
										<p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( home_url( '/' ) ) . '" style="color:#b12a2a;text-decoration:none;">hasimuener.org</a></p>
										<p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#696969;"><a href="' . esc_url( $imprint_url ) . '" style="color:#b12a2a;text-decoration:none;">Impressum</a> · <a href="' . esc_url( $privacy_url ) . '" style="color:#b12a2a;text-decoration:none;">Datenschutz</a></p>
									</td>
								</tr>
							</table>
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
 * Betreff der DOI-Mail.
 */
function hp_get_newsletter_confirmation_subject(): string {
	return 'Bitte bestätigen Sie Ihre Anmeldung bei hasimuener.org';
}

/**
 * Betreff der Willkommensmail.
 */
function hp_get_newsletter_welcome_subject(): string {
	return 'Ihre Anmeldung für neue Texte ist bestätigt';
}

/**
 * HTML der DOI-Mail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_confirmation_html( array $subscriber ): string {
	$confirm_url = hp_get_newsletter_confirm_url( $subscriber['confirm_token'] );
	$intro_html  = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Sie haben sich für <strong>' . esc_html( hp_get_newsletter_label() ) . '</strong> eingetragen. Bitte bestätigen Sie die Anmeldung mit einem Klick.</p>';
	$body_html   = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 12px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Sie erhalten danach kurze Hinweise auf neue Essays und ausgewählte Notizen. Keine Werbung. Keine täglichen Strecken. Nur dann, wenn ein neuer Text wirklich erschienen ist.</p>
				<p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;letter-spacing:1.4px;text-transform:uppercase;color:#696969;">Double-Opt-in erforderlich</p>
				<p style="margin:0;"><a href="' . esc_url( $confirm_url ) . '" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#111111;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;">Anmeldung bestätigen</a></p>
			</td>
		</tr>
	</table>';
	$footnote_html = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie sich nicht selbst eingetragen haben, ignorieren Sie diese E-Mail einfach. Ohne Bestätigung erfolgt keine Anmeldung.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_confirmation_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der DOI-Mail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_confirmation_text( array $subscriber ): string {
	return implode(
		"\n\n",
		[
			hp_get_newsletter_confirmation_subject(),
			'Sie haben sich für "' . hp_get_newsletter_label() . '" eingetragen.',
			'Bitte bestätigen Sie die Anmeldung über diesen Link:',
			hp_get_newsletter_confirm_url( $subscriber['confirm_token'] ),
			'Sie erhalten danach kurze Hinweise auf neue Essays und ausgewählte Notizen. Keine Werbung. Keine täglichen Strecken.',
			'Wenn Sie sich nicht selbst eingetragen haben, ignorieren Sie diese E-Mail einfach. Ohne Bestätigung erfolgt keine Anmeldung.',
		]
	);
}

/**
 * HTML der Willkommensmail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_welcome_html( array $subscriber ): string {
	$archive_url      = get_post_type_archive_link( 'essay' ) ?: home_url( '/' );
	$unsubscribe_url  = hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] );
	$x_url            = hp_get_newsletter_x_url();
	$intro_html       = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Ihre Anmeldung ist bestätigt. Künftig erhalten Sie eine kurze Nachricht, wenn ein neuer Essay erscheint oder eine Notiz den laufenden Gedanken sinnvoll vertieft.</p>';
	$body_html        = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 10px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;letter-spacing:1.4px;text-transform:uppercase;color:#696969;">Was Sie erwarten können</p>
				<ul style="margin:0;padding-left:20px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.8;color:#333333;">
					<li>neue Essays direkt nach Veröffentlichung</li>
					<li>ausgewählte Notizen nur dann, wenn sie den Gedanken erweitern</li>
					<li>keine Werbung, kein Tracking in den E-Mails</li>
				</ul>
				<p style="margin:16px 0 0;"><a href="' . esc_url( $archive_url ) . '" style="display:inline-block;padding:14px 22px;border-radius:999px;background:#111111;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:13px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;text-decoration:none;">Zu den Essays</a></p>
			</td>
		</tr>
	</table>
	<p style="margin:16px 0 0;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Für kürzere Hinweise und laufende Gedanken können Sie mir auch auf <a href="' . esc_url( $x_url ) . '" style="color:#b12a2a;text-decoration:none;">X folgen</a>.</p>';
	$footnote_html    = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie diese Hinweise nicht mehr erhalten möchten, können Sie sich jederzeit mit einem Klick wieder abmelden: <a href="' . esc_url( $unsubscribe_url ) . '" style="color:#b12a2a;text-decoration:none;">Newsletter abbestellen</a>.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_welcome_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der Willkommensmail.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_welcome_text( array $subscriber ): string {
	$archive_url     = get_post_type_archive_link( 'essay' ) ?: home_url( '/' );
	$unsubscribe_url = hp_get_newsletter_unsubscribe_url( $subscriber['unsubscribe_token'] );

	return implode(
		"\n\n",
		[
			hp_get_newsletter_welcome_subject(),
			'Ihre Anmeldung ist bestätigt.',
			'Sie erhalten künftig kurze Hinweise auf neue Essays und ausgewählte Notizen.',
			'Was Sie erwarten können:',
			'- neue Essays direkt nach Veröffentlichung' . "\n" . '- ausgewählte Notizen nur dann, wenn sie den Gedanken erweitern' . "\n" . '- keine Werbung, kein Tracking in den E-Mails',
			'Zu den Essays: ' . $archive_url,
			'Abmelden: ' . $unsubscribe_url,
			'Für kurze Hinweise: ' . hp_get_newsletter_x_url(),
		]
	);
}

/**
 * Betreff der Austragungsbestätigung.
 */
function hp_get_newsletter_unsubscribed_subject(): string {
	return 'Ihre Adresse wurde aus dem Verteiler ausgetragen';
}

/**
 * HTML der Austragungsbestätigung.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_unsubscribed_html( array $subscriber ): string {
	$resubscribe_url = hp_get_newsletter_anchor_url();
	$intro_html      = '<p style="margin:0 0 16px;font-family:Georgia,Times New Roman,serif;font-size:17px;line-height:1.75;color:#333333;">Die Adresse <strong>' . esc_html( $subscriber['email'] ) . '</strong> wurde aus dem Verteiler für neue Texte ausgetragen.</p>';
	$body_html       = '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e6e1d8;border-radius:16px;background:#faf8f5;">
		<tr>
			<td style="padding:18px 20px;">
				<p style="margin:0 0 12px;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Sie erhalten an diese Adresse keine weiteren Hinweise auf neue Essays oder Notizen mehr.</p>
				<p style="margin:0;font-family:Georgia,Times New Roman,serif;font-size:16px;line-height:1.7;color:#333333;">Wenn das ein Irrtum war, können Sie sich jederzeit erneut eintragen: <a href="' . esc_url( $resubscribe_url ) . '" style="color:#b12a2a;text-decoration:none;">Zur Anmeldung</a>.</p>
			</td>
		</tr>
	</table>';
	$footnote_html   = '<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.7;color:#696969;">Wenn Sie diese Austragung nicht veranlasst haben, antworten Sie bitte direkt auf diese E-Mail.</p>';

	return hp_get_newsletter_mail_shell(
		hp_get_newsletter_unsubscribed_subject(),
		$intro_html,
		$body_html,
		$footnote_html
	);
}

/**
 * Textversion der Austragungsbestätigung.
 *
 * @param array<string, string> $subscriber Datensatz.
 */
function hp_get_newsletter_unsubscribed_text( array $subscriber ): string {
	return implode(
		"\n\n",
		[
			hp_get_newsletter_unsubscribed_subject(),
			'Die Adresse ' . $subscriber['email'] . ' wurde aus dem Verteiler für neue Texte ausgetragen.',
			'Sie erhalten an diese Adresse keine weiteren Hinweise mehr.',
			'Neu anmelden: ' . hp_get_newsletter_anchor_url(),
			'Wenn Sie diese Austragung nicht veranlasst haben, antworten Sie bitte direkt auf diese E-Mail.',
		]
	);
}

