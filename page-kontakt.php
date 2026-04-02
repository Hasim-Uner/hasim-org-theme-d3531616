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
?>

<main id="main-content" class="hp-contact" aria-label="<?php echo esc_attr( $hp_page_title ); ?>">
	<div class="hp-contact__inner">

		<header class="hp-contact__header">
			<span class="hp-kicker">Anfragen</span>
			<h1 class="hp-contact__title"><?php echo esc_html( $hp_page_title ); ?></h1>
			<p class="hp-contact__subline">hasimuener.org ist ein publizistisches Projekt. Diese Seite ist für redaktionelle Anfragen, Gespräche, Kooperationen und ausgewählte Schreib- oder Strategievorhaben gedacht, die inhaltlich zu dieser Arbeit passen.</p>
		</header>

		<section class="hp-contact__section" aria-labelledby="anfragebereiche">
			<div class="hp-contact__section-header">
				<p class="hp-contact__section-eyebrow">Orientierung</p>
				<h2 id="anfragebereiche" class="hp-contact__section-title">Wofür Sie mich kontaktieren können</h2>
			</div>

			<div class="hp-contact__cards">
				<article class="hp-contact__card">
					<h3 class="hp-contact__card-title">Redaktionelle Anfragen</h3>
					<p class="hp-contact__card-copy">Für Anfragen von Redaktionen, Herausgebern oder Formaten, die an einer publizistischen Einordnung, einem Beitrag oder einem sachlichen Austausch interessiert sind.</p>
				</article>

				<article class="hp-contact__card">
					<h3 class="hp-contact__card-title">Gastbeiträge und Essays</h3>
					<p class="hp-contact__card-copy">Für Einladungen zu Essays, Kommentaren oder Gastbeiträgen, wenn Thema, Medium und editorischer Rahmen erkennbar sind.</p>
				</article>

				<article class="hp-contact__card">
					<h3 class="hp-contact__card-title">Interviews, Gespräche, Vorträge</h3>
					<p class="hp-contact__card-copy">Für Interviews, moderierte Gespräche, Podien oder Vorträge in journalistischen, kulturellen oder bildungsbezogenen Zusammenhängen.</p>
				</article>

				<article class="hp-contact__card">
					<h3 class="hp-contact__card-title">Kooperationen</h3>
					<p class="hp-contact__card-copy">Für Kooperationen mit Journalen, Medien, Kultur- oder Bildungsprojekten, wenn ein gemeinsamer inhaltlicher Fokus erkennbar ist.</p>
				</article>

				<article class="hp-contact__card">
					<h3 class="hp-contact__card-title">Ausgewählte Schreib- und Strategieprojekte</h3>
					<p class="hp-contact__card-copy">Für einzelne Vorhaben, bei denen Sprache, Perspektive, konzeptionelle Klarheit und publizistische Sorgfalt eine tragende Rolle spielen.</p>
				</article>
			</div>
		</section>

		<section class="hp-contact__qualify" aria-labelledby="passung">
			<div class="hp-contact__panel">
				<p class="hp-contact__section-eyebrow">Passung</p>
				<h2 id="passung" class="hp-contact__panel-title">Was gut passt</h2>
				<ul class="hp-contact__panel-list">
					<li>Projekte mit inhaltlicher Substanz und einer klaren Frage, die mehr verlangt als routinierte Abwicklung.</li>
					<li>Publizistische, kulturelle, gesellschaftliche oder bildungsbezogene Formate mit erkennbarem Kontext.</li>
					<li>Vorhaben, bei denen Sprache, Perspektive, Argumentation und Klarheit nicht blo&szlig; Verpackung sind.</li>
				</ul>
			</div>

			<div class="hp-contact__panel hp-contact__panel--muted">
				<p class="hp-contact__section-eyebrow">Abgrenzung</p>
				<h2 class="hp-contact__panel-title">Was weniger gut passt</h2>
				<ul class="hp-contact__panel-list">
					<li>Rein werbliche Anfragen oder Formate, die in erster Linie Promotion leisten sollen.</li>
					<li>Generische SEO-, Ghostwriting- oder Massencontent-Anfragen ohne erkennbare inhaltliche Linie.</li>
					<li>Unklare oder rein transaktionale Projekte, bei denen Thema, Kontext oder Ziel des Vorhabens offen bleiben.</li>
				</ul>
			</div>
		</section>

		<div class="hp-contact__layout">
			<aside class="hp-contact__aside" aria-label="Hinweise zur Anfrage">
				<h2 class="hp-contact__aside-title">Was bei einer Anfrage hilft</h2>
				<p>Ein kurzer Hinweis auf Medium, Format, Kontext und Anlass reicht meist aus, um die Passung einschätzen zu können.</p>
				<p>Wenn bereits ein Link, eine Ausschreibung oder ein Terminkontext vorliegt, kann er direkt im Formular ergänzt werden.</p>
				<p>Für kürzere oder direkte Rückfragen ist auch eine E-Mail möglich.</p>
				<div class="hp-contact__aside-links">
					<a href="<?php echo esc_url( $hp_contact_mailto ); ?>"><?php echo wp_kses_post( $hp_contact_email_label ); ?></a>
				</div>
			</aside>

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
					<p class="hp-contact__form-eyebrow">Anfragebereich</p>
					<h2 id="kontakt-formular-title" class="hp-contact__form-title">Anfrage senden</h2>
					<p class="hp-contact__form-lede">Das Formular ist bewusst knapp gehalten. Ein kurzer, konkreter Hinweis auf Anliegen, Rahmen und gegebenenfalls Terminbezug ist hilfreicher als allgemeine Selbstdarstellungen.</p>
				</div>

				<?php if ( '' !== $hp_contact_message ) : ?>
					<div class="hp-contact__notice hp-contact__notice--<?php echo 'success' === $hp_contact_status ? 'success' : 'error'; ?>" aria-live="polite">
						<p><?php echo esc_html( $hp_contact_message ); ?></p>
					</div>
				<?php endif; ?>

				<form id="kontakt-formular" class="hp-contact__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="hp_send_contact">
					<input type="hidden" name="hp_contact_rendered_at" value="<?php echo esc_attr( (string) $hp_rendered_at ); ?>">
					<input type="hidden" name="hp_contact_render_token" value="<?php echo esc_attr( $hp_render_token ); ?>">
					<?php wp_nonce_field( 'hp_contact_submit', 'hp_contact_nonce' ); ?>

					<div class="hp-contact__honeypot" aria-hidden="true">
						<label for="hp-contact-website">Website</label>
						<input id="hp-contact-website" type="text" name="hp_contact_website" value="" tabindex="-1" autocomplete="off">
					</div>

					<p class="hp-contact__field">
						<label for="hp-contact-name">Name</label>
						<input id="hp-contact-name" name="hp_contact_name" type="text" maxlength="120" autocomplete="name" value="<?php echo esc_attr( $hp_name_value ); ?>" required>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-email">E-Mail</label>
						<input id="hp-contact-email" name="hp_contact_email" type="email" maxlength="190" autocomplete="email" value="<?php echo esc_attr( $hp_email_value ); ?>" required>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-organization">Organisation / Medium / Projekt <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-organization" name="hp_contact_organization" type="text" maxlength="190" value="<?php echo esc_attr( $hp_organization_value ); ?>">
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-website-url">Website oder Link <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-website-url" name="hp_contact_website_url" type="text" maxlength="255" inputmode="url" placeholder="https://..." value="<?php echo esc_attr( $hp_website_value ); ?>">
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-inquiry-type">Art der Anfrage</label>
						<select id="hp-contact-inquiry-type" name="hp_contact_inquiry_type" required>
							<option value="">Bitte wählen</option>
							<?php foreach ( $hp_inquiry_options as $hp_option_value => $hp_option_label ) : ?>
								<option value="<?php echo esc_attr( $hp_option_value ); ?>"<?php selected( $hp_inquiry_value, $hp_option_value ); ?>><?php echo esc_html( $hp_option_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<p class="hp-contact__field">
						<label for="hp-contact-timeframe">Zeitraum / Terminbezug <span class="hp-contact__field-optional">optional</span></label>
						<input id="hp-contact-timeframe" name="hp_contact_timeframe" type="text" maxlength="190" value="<?php echo esc_attr( $hp_timeframe_value ); ?>">
					</p>

					<p class="hp-contact__field hp-contact__field--full">
						<label for="hp-contact-message">Kurze Beschreibung des Anliegens</label>
						<textarea id="hp-contact-message" name="hp_contact_message" rows="10" maxlength="8000" required><?php echo esc_textarea( $hp_message_value ); ?></textarea>
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

				<p class="hp-contact__closing">Wenn Sie denken, dass meine Perspektive, Sprache oder thematische Arbeit zu Ihrem Format passt, freue ich mich über eine Nachricht.</p>
			</section>
		</div>
	</div>
</main>

<?php get_footer(); ?>
