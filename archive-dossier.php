<?php
/**
 * Archive Template: Dossiers
 *
 * Übersicht aller Dossiers als Kachel-Grid.
 * Jede Kachel zeigt Titel, Intro und Größenkennzahlen
 * (Lesplan-Beiträge, Begriffsapparat-Begriffe).
 *
 * @package Hasimuener_Journal
 * @version 5.4.0 — Wissensplattform Phase 3
 */

get_header(); ?>

<header class="hp-dossier-archive-header">
    <div class="hp-dossier-archive-header__inner">
        <span class="hp-kicker">Themenbündel</span>
        <h1 class="hp-dossier-archive-header__title">Dossiers</h1>
        <p class="hp-dossier-archive-header__desc">Kuratierte Themenbündel mit Intro, Leseplan, Begriffsapparat und Quellen — der thematische Einstieg in zusammenhängende Debatten.</p>
    </div>
</header>

<?php
$hp_dossier_query = new WP_Query( [
    'post_type'      => 'dossier',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
] );

if ( $hp_dossier_query->have_posts() ) : ?>

    <div class="hp-dossier-grid" role="main">
        <?php while ( $hp_dossier_query->have_posts() ) : $hp_dossier_query->the_post();
            $hp_d_id        = get_the_ID();
            $hp_d_intro     = get_post_meta( $hp_d_id, '_hp_dossier_intro', true );
            $hp_d_leseplan  = hp_dossier_parse_ids( (string) get_post_meta( $hp_d_id, '_hp_dossier_leseplan', true ) );
            $hp_d_begriffe  = hp_dossier_parse_ids( (string) get_post_meta( $hp_d_id, '_hp_dossier_begriffe', true ) );
        ?>

            <article class="hp-dossier-card">
                <a class="hp-dossier-card__link" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">

                    <header class="hp-dossier-card__head">
                        <span class="hp-kicker">Dossier</span>
                        <h2 class="hp-dossier-card__title"><?php the_title(); ?></h2>
                    </header>

                    <?php if ( $hp_d_intro ) : ?>
                        <p class="hp-dossier-card__intro"><?php echo esc_html( wp_trim_words( $hp_d_intro, 32, ' …' ) ); ?></p>
                    <?php elseif ( has_excerpt() ) : ?>
                        <p class="hp-dossier-card__intro"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32, ' …' ) ); ?></p>
                    <?php endif; ?>

                    <footer class="hp-dossier-card__meta">
                        <?php if ( $hp_d_leseplan ) : ?>
                            <span><?php echo (int) count( $hp_d_leseplan ); ?> Beiträge</span>
                        <?php endif; ?>
                        <?php if ( $hp_d_begriffe ) : ?>
                            <span><?php echo (int) count( $hp_d_begriffe ); ?> Begriffe</span>
                        <?php endif; ?>
                        <span class="hp-dossier-card__cta">Zum Dossier <span aria-hidden="true">&rarr;</span></span>
                    </footer>
                </a>
            </article>

        <?php endwhile; wp_reset_postdata(); ?>
    </div>

<?php else : ?>
    <div class="hp-dossier-grid hp-dossier-grid--empty" role="main">
        <p class="hp-empty">Die ersten Dossiers entstehen gerade. Noch nichts veröffentlicht.</p>
    </div>
<?php endif; ?>

<?php get_footer(); ?>
