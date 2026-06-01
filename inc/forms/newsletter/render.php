<?php
/**
 * Newsletter frontend rendering.
 *
 * @package Hasimuener_Journal
 */


defined( 'ABSPATH' ) || exit;

/**
 * Rendert das Newsletter-Formular.
 *
 * @param array<string, mixed> $args Anzeigeparameter.
 */
function hp_render_newsletter_form( array $args = [] ): void {
	$defaults = [
		'id'           => 'newsletter-signup',
		'context'      => 'site',
		'variant'      => 'home',
		'eyebrow'      => hp_get_newsletter_label(),
		'title'        => 'Neue Texte per E-Mail.',
		'lede'         => 'Eine kurze Mail, wenn ein neuer Essay oder eine relevante Notiz erscheint.',
		'promises'     => [],
		'submit_label' => 'Anmelden',
		'show_x_link'  => false,
		'class_name'   => '',
		'return_url'   => hp_get_newsletter_current_url(),
	];

	$args               = wp_parse_args( $args, $defaults );
	$flash              = hp_get_newsletter_flash();
	$status             = isset( $flash['status'] ) ? (string) $flash['status'] : '';
	$message            = isset( $flash['message'] ) ? (string) $flash['message'] : '';
	$fields             = isset( $flash['fields'] ) && is_array( $flash['fields'] ) ? $flash['fields'] : [];
	$email_value        = isset( $fields['email'] ) ? (string) $fields['email'] : '';
	$privacy_url        = get_privacy_policy_url();
	$rendered_at        = time();
	$render_token       = hp_get_newsletter_form_render_token( $rendered_at );
	$section_classes    = trim( 'hp-newsletter hp-newsletter--' . sanitize_html_class( (string) $args['variant'] ) . ' ' . (string) $args['class_name'] );
	$return_url         = hp_get_newsletter_redirect_target( (string) $args['return_url'] );
	$promises           = is_array( $args['promises'] ) ? $args['promises'] : [];
	$x_url              = hp_get_newsletter_x_url();
	$form_id            = (string) $args['id'];
	$form_context       = sanitize_key( (string) $args['context'] );
	$consent_copy       = hp_get_newsletter_consent_copy();
?>
	<section id="<?php echo esc_attr( $form_id ); ?>" class="<?php echo esc_attr( $section_classes ); ?>" aria-labelledby="<?php echo esc_attr( $form_id . '-title' ); ?>">
	<?php if ( '' !== $message ) : ?>
		<script>
		(function() {
			var id = <?php echo wp_json_encode( $form_id ); ?>;
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
		<div class="hp-newsletter__shell">
			<div class="hp-newsletter__intro">
				<p class="hp-newsletter__eyebrow"><?php echo esc_html( (string) $args['eyebrow'] ); ?></p>
				<h2 id="<?php echo esc_attr( $form_id . '-title' ); ?>" class="hp-newsletter__title"><?php echo esc_html( (string) $args['title'] ); ?></h2>
				<p class="hp-newsletter__lede"><?php echo esc_html( (string) $args['lede'] ); ?></p>

				<?php if ( $promises ) : ?>
					<ul class="hp-newsletter__promises" aria-label="Was Sie erhalten">
						<?php foreach ( $promises as $promise ) : ?>
							<li><?php echo esc_html( (string) $promise ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="hp-newsletter__form-wrap">
				<?php if ( '' !== $message ) : ?>
					<div class="hp-newsletter__notice hp-newsletter__notice--<?php echo 'success' === $status ? 'success' : 'error'; ?>" aria-live="polite">
						<p><?php echo esc_html( $message ); ?></p>
					</div>
				<?php endif; ?>

				<form class="hp-newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="hp_subscribe_newsletter">
					<input type="hidden" name="hp_newsletter_source" value="<?php echo esc_attr( $form_context ); ?>">
					<input type="hidden" name="hp_newsletter_redirect" value="<?php echo esc_attr( $return_url ); ?>">
					<input type="hidden" name="hp_newsletter_rendered_at" value="<?php echo esc_attr( (string) $rendered_at ); ?>">
					<input type="hidden" name="hp_newsletter_render_token" value="<?php echo esc_attr( $render_token ); ?>">
					<?php wp_nonce_field( 'hp_newsletter_submit', 'hp_newsletter_nonce' ); ?>

					<div class="hp-newsletter__honeypot" aria-hidden="true">
						<label for="<?php echo esc_attr( $form_id . '-website' ); ?>">Website</label>
						<input id="<?php echo esc_attr( $form_id . '-website' ); ?>" type="text" name="hp_newsletter_website" value="" tabindex="-1" autocomplete="off">
					</div>

					<div class="hp-newsletter__primary">
						<p class="hp-newsletter__field">
							<label for="<?php echo esc_attr( $form_id . '-email' ); ?>">E-Mail-Adresse</label>
							<input id="<?php echo esc_attr( $form_id . '-email' ); ?>" name="hp_newsletter_email" type="email" maxlength="190" autocomplete="email" value="<?php echo esc_attr( $email_value ); ?>" placeholder="name@beispiel.de" required aria-describedby="<?php echo esc_attr( $form_id . '-trust' ); ?>">
						</p>

						<button class="hp-newsletter__submit" type="submit"><?php echo esc_html( (string) $args['submit_label'] ); ?></button>
					</div>

					<p id="<?php echo esc_attr( $form_id . '-trust' ); ?>" class="hp-newsletter__trust">Double-Opt-in. Kein Tracking im Newsletter. Jederzeit abbestellbar.</p>

					<label class="hp-newsletter__consent" for="<?php echo esc_attr( $form_id . '-consent' ); ?>">
						<input id="<?php echo esc_attr( $form_id . '-consent' ); ?>" name="hp_newsletter_consent" type="checkbox" value="1" required>
						<span><?php echo esc_html( $consent_copy ); ?><?php if ( $privacy_url ) : ?> Mehr in der <a href="<?php echo esc_url( $privacy_url ); ?>">Datenschutzerklärung</a>.<?php endif; ?></span>
					</label>

					<?php if ( ! empty( $args['show_x_link'] ) ) : ?>
						<div class="hp-newsletter__actions">
							<a class="hp-newsletter__secondary" href="<?php echo esc_url( $x_url ); ?>" target="_blank" rel="noopener noreferrer">Oder auf X folgen</a>
						</div>
					<?php endif; ?>

					<p class="hp-newsletter__footnote">Danach folgt eine Bestätigungs-E-Mail.</p>
				</form>
			</div>
		</div>
	</section>
	<?php
}
