<?php
/**
 * Single Template: Dossier
 *
 * Struktur: Hero (Kicker, Titel, Intro, Kuratiert-Von, Themenfelder)
 * → Editor-Inhalt (Methode, Auswahlkriterien, redaktionelle Position)
 * → Leseplan (sortierte Liste der Essays/Notizen)
 * → Begriffsapparat (Chip-Wolke verlinkter Begriffe)
 * → Quellen
 * → Stand & Version.
 *
 * @package Hasimuener_Journal
 * @version 5.4.0 — Wissensplattform Phase 3
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post();
    $hp_d_id          = get_the_ID();
    $hp_d_intro       = get_post_meta( $hp_d_id, '_hp_dossier_intro', true );
    $hp_d_kuratiert   = get_post_meta( $hp_d_id, '_hp_dossier_kuratiert_von', true );
    $hp_d_quellen     = get_post_meta( $hp_d_id, '_hp_dossier_quellen', true );
    $hp_d_version     = get_post_meta( $hp_d_id, '_hp_dossier_version', true );
    $hp_d_stand       = get_post_meta( $hp_d_id, '_hp_dossier_stand', true );
    $hp_d_leseplan    = hp_dossier_get_leseplan( $hp_d_id );
    $hp_d_begriffe    = hp_dossier_get_begriffe( $hp_d_id );
?>

<main id="main-content">

<header class="hp-dossier-hero">
    <div class="hp-dossier-hero__inner">
        <span class="hp-kicker">Dossier</span>
        <h1 class="hp-dossier-hero__title"><?php the_title(); ?></h1>

        <?php if ( $hp_d_intro ) : ?>
            <p class="hp-dossier-hero__intro"><?php echo esc_html( $hp_d_intro ); ?></p>
        <?php endif; ?>

        <div class="hp-dossier-hero__meta">
            <?php if ( $hp_d_kuratiert ) : ?>
                <span class="hp-dossier-hero__kurator">
                    <span class="hp-dossier-hero__label">Kuratiert von</span>
                    <?php echo esc_html( $hp_d_kuratiert ); ?>
                </span>
            <?php endif; ?>

            <?php if ( $hp_d_leseplan ) : ?>
                <span><?php echo count( $hp_d_leseplan ); ?> Beiträge</span>
            <?php endif; ?>

            <?php if ( $hp_d_begriffe ) : ?>
                <span><?php echo count( $hp_d_begriffe ); ?> Begriffe</span>
            <?php endif; ?>
        </div>

        <?php
        $topics = get_the_terms( $hp_d_id, 'topic' );
        if ( $topics && ! is_wp_error( $topics ) ) : ?>
            <ul class="hp-topics hp-dossier-hero__topics" aria-label="Themenfelder">
                <?php foreach ( $topics as $topic ) : ?>
                    <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</header>

<article class="hp-dossier-body" aria-label="<?php the_title_attribute(); ?>">

    <?php if ( get_the_content() ) : ?>
        <div class="hp-dossier-body__content prose">
            <?php the_content(); ?>
        </div>
    <?php endif; ?>

    <?php if ( $hp_d_leseplan ) : ?>
        <section class="hp-dossier-section hp-dossier-leseplan" aria-label="Leseplan">
            <h2 class="hp-dossier-section__heading">Leseplan</h2>
            <ol class="hp-dossier-leseplan__list">
                <?php foreach ( $hp_d_leseplan as $hp_lp_post ) :
                    $hp_lp_type = get_post_type( $hp_lp_post );
                    $hp_lp_label = 'essay' === $hp_lp_type ? 'Essay' : 'Notiz';
                    $hp_lp_date  = get_the_date( 'j. F Y', $hp_lp_post );
                    $hp_lp_time  = function_exists( 'hp_reading_time' ) ? hp_reading_time( $hp_lp_post->ID ) : '';
                ?>
                    <li class="hp-dossier-leseplan__item">
                        <a class="hp-dossier-leseplan__link" href="<?php echo esc_url( get_permalink( $hp_lp_post ) ); ?>">
                            <span class="hp-dossier-leseplan__kicker"><?php echo esc_html( $hp_lp_label ); ?> · <?php echo esc_html( $hp_lp_date ); ?><?php if ( $hp_lp_time ) : ?> · <?php echo esc_html( $hp_lp_time ); ?><?php endif; ?></span>
                            <span class="hp-dossier-leseplan__title"><?php echo esc_html( get_the_title( $hp_lp_post ) ); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
    <?php endif; ?>

    <?php if ( $hp_d_begriffe ) : ?>
        <section class="hp-dossier-section hp-dossier-begriffe" aria-label="Begriffsapparat">
            <h2 class="hp-dossier-section__heading">Begriffsapparat</h2>
            <div class="hp-dossier-begriffe__list">
                <?php foreach ( $hp_d_begriffe as $hp_b_post ) : ?>
                    <a class="hp-glossar-term hp-begriff-chip" href="<?php echo esc_url( get_permalink( $hp_b_post ) ); ?>"><?php echo esc_html( get_the_title( $hp_b_post ) ); ?></a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( $hp_d_quellen ) :
        $hp_d_quellen_lines = array_filter( array_map( 'trim', explode( "\n", $hp_d_quellen ) ) );
        if ( $hp_d_quellen_lines ) : ?>
            <section class="hp-dossier-section hp-dossier-quellen" aria-label="Quellen">
                <h2 class="hp-dossier-section__heading">Quellen</h2>
                <ol class="hp-dossier-quellen__list">
                    <?php foreach ( $hp_d_quellen_lines as $line ) : ?>
                        <li><?php echo wp_kses( make_clickable( $line ), [ 'a' => [ 'href' => [], 'rel' => [], 'target' => [] ] ] ); ?></li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif;
    endif; ?>

    <?php if ( $hp_d_version || $hp_d_stand ) : ?>
        <footer class="hp-begriff-stand" aria-label="Versionsinformation">
            <?php if ( $hp_d_version ) : ?>
                <span class="hp-begriff-stand__item"><span class="hp-begriff-stand__label">Version</span> <?php echo esc_html( $hp_d_version ); ?></span>
            <?php endif; ?>
            <?php if ( $hp_d_stand ) :
                $hp_d_stand_ts = strtotime( $hp_d_stand );
            ?>
                <span class="hp-begriff-stand__item"><span class="hp-begriff-stand__label">Stand</span>
                    <time datetime="<?php echo esc_attr( $hp_d_stand ); ?>"><?php echo esc_html( $hp_d_stand_ts ? date_i18n( 'j. F Y', $hp_d_stand_ts ) : $hp_d_stand ); ?></time>
                </span>
            <?php endif; ?>
        </footer>
    <?php endif; ?>

    <nav class="hp-glossar-backnav" aria-label="Dossier-Navigation">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'dossier' ) ); ?>">
            <span aria-hidden="true">&larr;</span> Alle Dossiers
        </a>
    </nav>

</article>
</main>

<?php endwhile; ?>

<?php get_footer(); ?>
