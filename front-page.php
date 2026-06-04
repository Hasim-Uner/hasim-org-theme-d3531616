<?php
/**
 * Template: Front Page — Hasimuener Journal
 *
 * Editoriales Layout:
 *   1. Entity-Hero mit Featured Essay
 *   2. Drei Einstiege (Neu / Thema / Begriff)
 *   3. Newsletter
 *   4. Autor-/Entity-Sektion
 *   5. Themenfelder
 *
 * Strategische Verschiebung: aus der reinen Chronologie wird eine
 * dreigeteilte Orientierung — Leserin entscheidet ob sie nach Datum
 * (Neu), nach Thema (Dossiers) oder nach Begriff (Glossar) einsteigt.
 *
 * @package Hasimuener_Journal
 * @version 6.0.0
 */

get_header(); ?>

<main id="main-content" class="journal-front" role="main">

    <!-- ==========================================
         1. ENTITY-HERO — Claim + Featured Essay
         ========================================== -->
    <?php
    $hp_hero_posts = get_posts( [
        'post_type'           => 'essay',
        'posts_per_page'      => 1,
        'post_status'         => 'publish',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );
    $hp_hero_post      = ! empty( $hp_hero_posts[0] ) && $hp_hero_posts[0] instanceof WP_Post ? $hp_hero_posts[0] : null;
    $hp_hero_id        = $hp_hero_post ? (int) $hp_hero_post->ID : 0;
    $hp_hero_has_image = $hp_hero_id && has_post_thumbnail( $hp_hero_id );
    $hp_hero_classes   = 'editorial-hero editorial-hero--atmospheric';

    if ( $hp_hero_has_image ) {
        $hp_hero_classes .= ' editorial-hero--has-image';
    }
    ?>

    <section class="<?php echo esc_attr( $hp_hero_classes ); ?>" aria-labelledby="front-hero-title">
        <?php if ( $hp_hero_has_image ) : ?>
            <div class="editorial-hero__media" aria-hidden="true">
                <?php
                echo wp_get_attachment_image(
                    get_post_thumbnail_id( $hp_hero_id ),
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

            <div class="editorial-hero__claim">
                <h1 id="front-hero-title" class="editorial-hero__title">Zusammendenken&#8288;, was getrennt erscheint.</h1>
                <p class="editorial-hero__subline">Essays und Notizen über Macht, Medien, Erinnerung, Sprache und Gesellschaft.</p>

                <div class="editorial-hero__actions" aria-label="Startpunkte">
                    <?php if ( $hp_hero_post ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $hp_hero_post ) ); ?>" class="editorial-hero__button editorial-hero__button--primary">Aktuellen Essay lesen</a>
                    <?php endif; ?>
                    <a href="#newsletter-signup" class="editorial-hero__button editorial-hero__button--secondary">Kostenlos abonnieren</a>
                </div>
            </div>

            <?php if ( $hp_hero_post ) : ?>
                <article class="editorial-hero__feature" aria-labelledby="front-feature-title">
                    <div class="editorial-hero__meta hp-overline">
                        <span>Aktueller Essay</span>
                        <span class="hp-overline__sep" aria-hidden="true"></span>
                        <time datetime="<?php echo esc_attr( get_the_date( 'c', $hp_hero_post ) ); ?>">
                            <?php echo esc_html( get_the_date( 'j. F Y', $hp_hero_post ) ); ?>
                        </time>
                        <span class="hp-overline__sep" aria-hidden="true"></span>
                        <span><?php echo esc_html( hp_reading_time( $hp_hero_id ) ); ?></span>
                    </div>

                    <h2 id="front-feature-title" class="editorial-hero__feature-title">
                        <a href="<?php echo esc_url( get_permalink( $hp_hero_post ) ); ?>"><?php echo esc_html( get_the_title( $hp_hero_post ) ); ?></a>
                    </h2>

                    <?php if ( has_excerpt( $hp_hero_id ) ) : ?>
                        <p class="editorial-hero__excerpt"><?php echo esc_html( get_the_excerpt( $hp_hero_post ) ); ?></p>
                    <?php endif; ?>

                    <a href="<?php echo esc_url( get_permalink( $hp_hero_post ) ); ?>" class="editorial-hero__cta" aria-label="<?php echo esc_attr( get_the_title( $hp_hero_post ) ); ?> — Ganzen Essay lesen">Ganzen Essay lesen &rarr;</a>
                </article>
            <?php else : ?>
                <article class="editorial-hero__feature editorial-hero__feature--empty" aria-label="Journal-Status">
                    <p class="editorial-hero__excerpt">Die ersten Essays entstehen gerade. Bis dahin führen Mission, Begriffe und Themenfelder in die Struktur des Journals.</p>
                    <a href="<?php echo esc_url( home_url( '/mission/' ) ); ?>" class="editorial-hero__cta">Mission lesen &rarr;</a>
                </article>
            <?php endif; ?>
        </div>
    </section>

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
                'no_found_rows'  => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
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
                'no_found_rows'  => true,
                'update_post_term_cache' => false,
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
                'no_found_rows'  => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
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
         3. NEWSLETTER
         ========================================== -->
    <?php
    if ( function_exists( 'hp_render_newsletter_form' ) ) {
        hp_render_newsletter_form( [
            'id'           => 'newsletter-signup',
            'context'      => 'front_page',
            'variant'      => 'home',
            'eyebrow'      => 'Brief aus dem Journal',
            'title'        => 'Seltene Texte. Keine Dauerbeschallung.',
            'lede'         => 'Eine kurze Mail, wenn ein neuer Essay erscheint oder ein Gedanke wirklich trägt.',
            'promises'     => [
                'Essays, Notizen und Begriffe aus dem Journal',
                'Keine künstliche Frequenz',
                'Jederzeit abbestellbar',
            ],
            'submit_label' => 'Bestätigungslink erhalten',
        ] );
    }
    ?>

    <hr class="journal-rule" aria-hidden="true">

    <!-- ==========================================
         4. AUTOR / ENTITY
         ========================================== -->
    <section class="hp-front-entity" aria-labelledby="front-entity-title">
        <div class="hp-front-entity__inner">
            <div class="hp-front-entity__copy">
                <p class="hp-kicker">Autor &amp; Journal</p>
                <h2 id="front-entity-title" class="hp-front-entity__title">Haşim Üner schreibt über die Stellen, an denen Macht unsichtbar wird.</h2>
                <p class="hp-front-entity__text">Dieses Journal verbindet Essays, Notizen, Begriffe und Dossiers zu einer ruhigen Wissensplattform: gegen einfache Gewissheiten, für präzisere Sprache und für Perspektiven, die nicht sofort in Lager zerfallen.</p>
            </div>
            <a class="hp-front-entity__link" href="<?php echo esc_url( home_url( '/mission/' ) ); ?>">Warum dieses Journal existiert <span aria-hidden="true">&rarr;</span></a>
        </div>
    </section>

    <hr class="journal-rule" aria-hidden="true">

    <!-- ==========================================
         5. THEMENFELDER (Taxonomie)
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
