<?php
/**
 * Template: Front Page — Hasimuener Journal
 *
 * Editoriales Layout:
 *   1. Hero (Latest Essay) — bleibt
 *   2. Drei Einstiege (Neu / Thema / Begriff) — Wissensplattform Phase 4
 *   3. Newsletter
 *   4. Themenfelder
 *
 * Strategische Verschiebung: aus der reinen Chronologie wird eine
 * dreigeteilte Orientierung — Leserin entscheidet ob sie nach Datum
 * (Neu), nach Thema (Dossiers) oder nach Begriff (Glossar) einsteigt.
 *
 * @package Hasimuener_Journal
 * @version 5.5.0 — Wissensplattform Phase 4
 */

get_header(); ?>

<main id="main-content" class="journal-front" role="main">

    <!-- ==========================================
         1. EDITORIAL HERO — Neuester Essay
         ========================================== -->
    <?php
    $hp_hero = new WP_Query( [
        'post_type'      => 'essay',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ] );

    if ( $hp_hero->have_posts() ) :
        while ( $hp_hero->have_posts() ) : $hp_hero->the_post();
            $hp_hero_has_image = has_post_thumbnail();
            $hp_hero_classes   = 'editorial-hero editorial-hero--atmospheric';

            if ( $hp_hero_has_image ) {
                $hp_hero_classes .= ' editorial-hero--has-image';
            }
            ?>

    <section class="<?php echo esc_attr( $hp_hero_classes ); ?>" aria-label="Aktueller Essay">
        <?php if ( $hp_hero_has_image ) : ?>
            <div class="editorial-hero__media" aria-hidden="true">
                <?php
                echo wp_get_attachment_image(
                    get_post_thumbnail_id(),
                    'large',
                    false,
                    [
                        'class'         => 'editorial-hero__image',
                        'loading'       => 'eager',
                        'fetchpriority' => 'high',
                        'decoding'      => 'async',
                        'sizes'         => '(min-width: 1140px) 1080px, calc(100vw - 2.4rem)',
                        'alt'           => '',
                    ]
                );
                ?>
            </div>
        <?php endif; ?>
        <div class="editorial-hero__grid">

            <div class="editorial-hero__meta hp-overline">
                <span>Essay</span>
                <span class="hp-overline__sep" aria-hidden="true"></span>
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date( 'j. F Y' ) ); ?>
                </time>
                <span class="hp-overline__sep" aria-hidden="true"></span>
                <span><?php echo esc_html( hp_reading_time() ); ?></span>
            </div>

            <h1 class="editorial-hero__title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h1>

            <?php if ( has_excerpt() ) : ?>
                <p class="editorial-hero__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>

            <a href="<?php the_permalink(); ?>" class="editorial-hero__cta" aria-label="<?php the_title_attribute(); ?> — Ganzen Essay lesen">Ganzen Essay lesen &rarr;</a>
        </div>
    </section>

        <?php endwhile;
    else : ?>

    <!-- Fallback: Kein Essay vorhanden -->
    <section class="editorial-hero editorial-hero--atmospheric editorial-hero--empty" aria-label="Aktueller Essay">
        <div class="editorial-hero__grid">
            <div class="editorial-hero__meta hp-overline"><span>Hasim Üner</span></div>
            <h1 class="editorial-hero__title">Macht. Medien. Gesellschaft.</h1>
            <p class="editorial-hero__excerpt">Essays und Analysen zu Macht, Medien und Gesellschaft. Von Hasim Üner.</p>
        </div>
    </section>

    <?php endif;
    wp_reset_postdata(); ?>

    <hr class="journal-rule" aria-hidden="true">

    <!-- ==========================================
         2. DREI EINSTIEGE — Neu / Thema / Begriff
         ========================================== -->
    <section class="hp-front-einstiege" aria-label="Drei Einstiege in das Journal">

        <!-- Spalte 1: NEU (chronologisch, Essays + Notizen gemischt) -->
        <article class="hp-einstieg hp-einstieg--neu">
            <header class="hp-einstieg__head">
                <span class="hp-kicker">Neu</span>
                <h2 class="hp-einstieg__title">Zuletzt erschienen</h2>
            </header>

            <?php
            $hp_neu = new WP_Query( [
                'post_type'      => [ 'essay', 'note' ],
                'post_status'    => 'publish',
                'posts_per_page' => 5,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );

            if ( $hp_neu->have_posts() ) : ?>
                <ul class="hp-einstieg__list">
                    <?php while ( $hp_neu->have_posts() ) : $hp_neu->the_post();
                        $hp_n_label = 'essay' === get_post_type() ? 'Essay' : 'Notiz';
                    ?>
                        <li class="hp-einstieg__item">
                            <a class="hp-einstieg__link" href="<?php the_permalink(); ?>">
                                <span class="hp-einstieg__kicker"><?php echo esc_html( $hp_n_label ); ?> · <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j. M Y' ) ); ?></time></span>
                                <span class="hp-einstieg__item-title"><?php the_title(); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p class="hp-einstieg__empty">Bisher keine Beiträge veröffentlicht.</p>
            <?php endif;
            wp_reset_postdata(); ?>

            <footer class="hp-einstieg__foot">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'essay' ) ); ?>">Alle Essays <span aria-hidden="true">&rarr;</span></a>
            </footer>
        </article>

        <!-- Spalte 2: THEMA (Dossiers) -->
        <article class="hp-einstieg hp-einstieg--thema">
            <header class="hp-einstieg__head">
                <span class="hp-kicker">Thema</span>
                <h2 class="hp-einstieg__title">Aktuelle Dossiers</h2>
            </header>

            <?php
            $hp_dossiers = new WP_Query( [
                'post_type'      => 'dossier',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ] );

            if ( $hp_dossiers->have_posts() ) : ?>
                <ul class="hp-einstieg__list">
                    <?php while ( $hp_dossiers->have_posts() ) : $hp_dossiers->the_post();
                        $hp_d_id        = get_the_ID();
                        $hp_d_intro     = get_post_meta( $hp_d_id, '_hp_dossier_intro', true );
                        $hp_d_leseplan  = function_exists( 'hp_dossier_parse_ids' ) ? hp_dossier_parse_ids( (string) get_post_meta( $hp_d_id, '_hp_dossier_leseplan', true ) ) : [];
                        $hp_d_begriffe  = function_exists( 'hp_dossier_parse_ids' ) ? hp_dossier_parse_ids( (string) get_post_meta( $hp_d_id, '_hp_dossier_begriffe', true ) ) : [];
                    ?>
                        <li class="hp-einstieg__item hp-einstieg__item--dossier">
                            <a class="hp-einstieg__link" href="<?php the_permalink(); ?>">
                                <span class="hp-einstieg__item-title"><?php the_title(); ?></span>
                                <?php if ( $hp_d_intro ) : ?>
                                    <span class="hp-einstieg__item-intro"><?php echo esc_html( wp_trim_words( $hp_d_intro, 18, ' …' ) ); ?></span>
                                <?php endif; ?>
                                <span class="hp-einstieg__item-meta">
                                    <?php if ( $hp_d_leseplan ) : ?>
                                        <?php echo (int) count( $hp_d_leseplan ); ?> Beiträge
                                    <?php endif; ?>
                                    <?php if ( $hp_d_leseplan && $hp_d_begriffe ) : ?> · <?php endif; ?>
                                    <?php if ( $hp_d_begriffe ) : ?>
                                        <?php echo (int) count( $hp_d_begriffe ); ?> Begriffe
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p class="hp-einstieg__empty">Die ersten Dossiers entstehen gerade — kuratierte Bündel mit Intro, Leseplan und Begriffsapparat.</p>
            <?php endif;
            wp_reset_postdata(); ?>

            <footer class="hp-einstieg__foot">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'dossier' ) ); ?>">Alle Dossiers <span aria-hidden="true">&rarr;</span></a>
            </footer>
        </article>

        <!-- Spalte 3: BEGRIFF (Glossar als Pill-Wolke) -->
        <article class="hp-einstieg hp-einstieg--begriff">
            <header class="hp-einstieg__head">
                <span class="hp-kicker">Begriff</span>
                <h2 class="hp-einstieg__title">Im Glossar nachschlagen</h2>
            </header>

            <?php
            $hp_begriffe = new WP_Query( [
                'post_type'      => 'glossar',
                'post_status'    => 'publish',
                'posts_per_page' => 12,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ] );

            if ( $hp_begriffe->have_posts() ) : ?>
                <div class="hp-einstieg__chips">
                    <?php while ( $hp_begriffe->have_posts() ) : $hp_begriffe->the_post(); ?>
                        <a class="hp-glossar-term hp-begriff-chip" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p class="hp-einstieg__empty">Das Glossar wächst mit dem Journal — die ersten Begriffe folgen.</p>
            <?php endif;
            wp_reset_postdata(); ?>

            <footer class="hp-einstieg__foot">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'glossar' ) ); ?>">Alle Begriffe <span aria-hidden="true">&rarr;</span></a>
            </footer>
        </article>

    </section>

    <hr class="journal-rule" aria-hidden="true">

    <!-- ==========================================
         3. NEWSLETTER (entfernt — globale Header-Glocke übernimmt)
         siehe inc/header-nav.php → hp-nav-bell-modal
         ========================================== -->

    <!-- ==========================================
         4. THEMENFELDER (Taxonomie)
         ========================================== -->
    <?php
    $hp_topics = hp_get_curated_topics();

    if ( $hp_topics ) : ?>
    <section class="topics-section" aria-label="Themenfelder">
        <header>
            <h2 class="hp-section-title">Themenfelder</h2>
        </header>
        <div class="topics-grid">
            <?php foreach ( $hp_topics as $topic ) : ?>
                <a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>">
                    <?php echo esc_html( $topic->name ); ?>
                    <?php if ( $topic->count > 0 ) : ?>
                        <span>(<?php echo (int) $topic->count; ?>)</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <hr class="journal-rule" aria-hidden="true">
    <?php endif; ?>

</main>

<?php get_footer(); ?>
