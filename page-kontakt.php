<?php
/**
 * Template Name: Kontakt
 *
 * Template: Kontakt
 *
 * Kuratierte Seite für Anfragen, redaktionelle Zusammenarbeit
 * und ausgewählte inhaltliche Projekte.
 *
 * @package Hasimuener_Journal
 * @since   6.6.0
 */

get_header();

$hp_contact_flash   = hp_consume_contact_flash();
$hp_contact_status  = isset( $hp_contact_flash['status'] ) ? (string) $hp_contact_flash['status'] : '';
$hp_contact_message = isset( $hp_contact_flash['message'] ) ? (string) $hp_contact_flash['message'] : '';
$hp_contact_fields  = isset( $hp_contact_flash['fields'] ) && is_array( $hp_contact_flash['fields'] ) ? $hp_contact_flash['fields'] : [];

$hp_name_value         = isset( $hp_contact_fields['name'] ) ? (string) $hp_contact_fields['name'] : '';
$hp_email_value        = isset( $hp_contact_fields['email'] ) ? (string) $hp_contact_fields['email'] : '';
$hp_organization_value = isset( $hp_contact_fields['organization'] ) ? (string) $hp_contact_fields['organization'] : '';
$hp_website_value      = isset( $hp_contact_fields['website_url'] ) ? (string) $hp_contact_fields['website_url'] : '';
$hp_inquiry_value      = isset( $hp_contact_fields['inquiry_type'] ) ? (string) $hp_contact_fields['inquiry_type'] : '';
$hp_timeframe_value    = isset( $hp_contact_fields['timeframe'] ) ? (string) $hp_contact_fields['timeframe'] : '';
$hp_message_value      = isset( $hp_contact_fields['message'] ) ? (string) $hp_contact_fields['message'] : '';

$hp_page_title         = hp_get_contact_page_title();
$hp_contact_email      = hp_get_contact_email();
$hp_contact_email_label = antispambot( $hp_contact_email );
$hp_contact_mailto     = 'mailto:' . $hp_contact_email;
$hp_privacy_url        = get_privacy_policy_url();
$hp_rendered_at        = time();
$hp_render_token       = hp_get_contact_form_render_token( $hp_rendered_at );
$hp_inquiry_options    = hp_get_contact_inquiry_type_options();
$hp_notice_role        = 'success' === $hp_contact_status ? 'status' : 'alert';
?>

<main id="main-content" class="hp-contact" aria-labelledby="kontakt-title">
	<div class="hp-contact__inner">

		<header class="hp-contact__header">
			<span class="hp-kicker">Anfragen</span>
			<h1 id="kontakt-title" class="hp-contact__title"><?php echo esc_html( $hp_page_title ); ?></h1>
			<p id="kontakt-intro" class="hp-contact__subline">Für redaktionelle Anfragen, Gespräche, Kooperationen und ausgewählte Schreib- oder Strategievorhaben mit erkennbarem inhaltlichem Fokus.</p>
			<nav class="hp-contact__quicklinks" aria-label="Kontaktoptionen">
				<a class="hp-contact__primary-link" href="#kontakt-formular">Zum Formular</a>
				<a class="hp-contact__secondary-link" href="<?php echo esc_url( $hp_contact_mailto ); ?>">Direkt per E-Mail</a>
			</nav>
		</header>

		<div class="hp-contact__layout">
			<section id="kontakt-formular-bereich" class="hp-contact__form-shell" aria-labelledby="kontakt-formular-title">
			<?php if ( '' !== $hp_contact_message ) : ?>
				<script>
				(function() {
					var id = 'kontakt-formular-bereich';
					function scrollToForm() {
						var el = document.getElementById( id );
						if ( el ) { el.scrollIntoView( { behavior: 'smooth', block: 'start' } ); }
					}
					if ( document.readyState === 'loading' ) {
						document.addEventListener( 'DOMContentLoaded', scrollToForm );
					} else {
						scrollToForm();
					}
				})();
				</script>
			<?php endif; ?>
				<div class="hp-contact__form-header">
					<h2 id="kontakt-formular-title" class="hp-contact__form-title">Anfrage senden</h2>
					<p id="kontakt-formular-hinweis" class="hp-contact__form-lede">Kurz reicht. Beschreiben Sie Anliegen, Rahmen und gegebenenfalls Terminbezug so konkret wie möglich.</p>
					<p id="kontakt-pflicht-hinweis" class="hp-contact__required-note">Pflichtfelder sind mit „erforderlich“ gekennzeichnet.</p>
				</div>

				<?php if ( '' !== $hp_contact_message ) : ?>
					<div class="hp-contact__notice hp-contact__notice--<?php echo 'success' === $hp_contact_status ? 'success' : 'error'; ?>" role="<?php echo esc_attr( $hp_notice_role ); ?>" aria-live="polite">
						<p><?php echo esc_html( $hp_contact_message ); ?></p>
					</div>
				<?php endif; ?>

				<form id="kontakt-formular" class="hp-contact__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" aria-describedby="kontakt-formular-hinweis kontakt-pflicht-hinweis">
					<input type="hidden" name="action" value="hp_send_contact">
					<input type="hidden" name="hp_contact_rendered_at" value="<?php echo esc_attr( (string) $hp_rendered_at ); ?>">
					<input type="hidden" name="hp_contact_render_token" value="<?php echo esc_attr( $hp_render_token ); ?>">
					<?php wp_nonce_field( 'hp_contact_submit', 'hp_contact_nonce' ); ?>

					<div class="hp-contact__honeypot" aria-hidden="true">
						<label for="hp-contact-website">Website</label>
						<input id="hp-contact-website" type="text" name="hp_contact_website" value="" tabindex="-1" autocomplete="off">
					</div>

					<p class="hp-contact__field">
						<label for="hp-contact-name">Name <span class="hp-contact__field-required">erforderlich</span></label>
						<input id="hp-contact-name" name="hp_contact_name" type="text" maxlength="120" autocomplete="name" value="<?php echo esc_attr( $hp_name_value ); ?>" required aria-describedby="hp-contact-name-help">
						<span id="hp-contact-name-help" class="hp-contact__field-help">Vor- und Nachname oder der Name, unter dem ich antworten soll.</span>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-email">E-Mail <span class="hp-contact__field-required">erforderlich</span></label>
						<input id="hp-contact-email" name="hp_contact_email" type="email" maxlength="190" autocomplete="email" value="<?php echo esc_attr( $hp_email_value ); ?>" required aria-describedby="hp-contact-email-help">
						<span id="hp-contact-email-help" class="hp-contact__field-help">Wird ausschließlich zur Bearbeitung dieser Anfrage genutzt.</span>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-organization">Organisation / Medium / Projekt <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-organization" name="hp_contact_organization" type="text" maxlength="190" value="<?php echo esc_attr( $hp_organization_value ); ?>" aria-describedby="hp-contact-organization-help">
						<span id="hp-contact-organization-help" class="hp-contact__field-help">Hilft, den Kontext der Anfrage einzuordnen.</span>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-website-url">Website oder Link <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-website-url" name="hp_contact_website_url" type="text" maxlength="255" inputmode="url" placeholder="https://..." value="<?php echo esc_attr( $hp_website_value ); ?>" aria-describedby="hp-contact-link-help">
						<span id="hp-contact-link-help" class="hp-contact__field-help">Zum Beispiel Projektseite, Ausschreibung oder Redaktionsprofil.</span>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-inquiry-type">Art der Anfrage <span class="hp-contact__field-required">erforderlich</span></label>
						<select id="hp-contact-inquiry-type" name="hp_contact_inquiry_type" required aria-describedby="hp-contact-type-help">
							<option value="">Bitte wählen</option>
							<?php foreach ( $hp_inquiry_options as $hp_option_value => $hp_option_label ) : ?>
								<option value="<?php echo esc_attr( $hp_option_value ); ?>"<?php selected( $hp_inquiry_value, $hp_option_value ); ?>><?php echo esc_html( $hp_option_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<span id="hp-contact-type-help" class="hp-contact__field-help">Wählen Sie die passendste Kategorie. „Sonstiges“ ist möglich.</span>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-timeframe">Zeitraum / Terminbezug <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-timeframe" name="hp_contact_timeframe" type="text" maxlength="190" value="<?php echo esc_attr( $hp_timeframe_value ); ?>" aria-describedby="hp-contact-timeframe-help">
						<span id="hp-contact-timeframe-help" class="hp-contact__field-help">Nur relevant, wenn es Fristen, Termine oder Veröffentlichungspläne gibt.</span>
					</p>

					<p class="hp-contact__field hp-contact__field--full">
						<label for="hp-contact-message">Kurze Beschreibung des Anliegens <span class="hp-contact__field-required">erforderlich</span></label>
						<textarea id="hp-contact-message" name="hp_contact_message" rows="8" maxlength="8000" required aria-describedby="hp-contact-message-help"><?php echo esc_textarea( $hp_message_value ); ?></textarea>
						<span id="hp-contact-message-help" class="hp-contact__field-help">Ein bis drei Absätze genügen meistens.</span>
					</p>

					<p class="hp-contact__privacy">
						Mit dem Absenden wird Ihre Nachricht ausschließlich zur Bearbeitung Ihrer Anfrage verarbeitet.
						<?php if ( $hp_privacy_url ) : ?>
							Mehr in der <a href="<?php echo esc_url( $hp_privacy_url ); ?>">Datenschutzerklärung</a>.
						<?php endif; ?>
					</p>

					<div class="hp-contact__actions">
						<button class="hp-contact__submit" type="submit">Nachricht senden</button>
						<a class="hp-contact__mail-link" href="<?php echo esc_url( $hp_contact_mailto ); ?>">Direkt per E-Mail schreiben</a>
					</div>
				</form>
			</section>

			<aside class="hp-contact__aside" aria-label="Hinweise zur Anfrage">
				<h2 class="hp-contact__aside-title">Passt besonders für</h2>
				<ul class="hp-contact__aside-list">
					<li>Redaktionelle Anfragen, Essays und Gastbeiträge.</li>
					<li>Interviews, Gespräche, Vorträge und Kooperationen.</li>
					<li>Schreib- oder Strategieprojekte mit klarer inhaltlicher Frage.</li>
				</ul>
				<h2 class="hp-contact__aside-title hp-contact__aside-title--spaced">Hilfreich sind</h2>
				<ul class="hp-contact__aside-list">
					<li>Medium, Format oder Projektkontext.</li>
					<li>Anlass, Zeitraum und relevante Links.</li>
					<li>Eine konkrete Frage statt allgemeiner Selbstdarstellung.</li>
				</ul>
				<div class="hp-contact__aside-links">
					<a href="<?php echo esc_url( $hp_contact_mailto ); ?>"><?php echo wp_kses_post( $hp_contact_email_label ); ?></a>
				</div>
			</aside>
		</div>
	</div>
</main>

<?php get_footer(); ?>
