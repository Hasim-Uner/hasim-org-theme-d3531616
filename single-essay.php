<?php
/**
 * Single Template: Essay
 * 
 * Longform-Artikel mit Beitragsbild-Hero, Inhaltsverzeichnis, Lesedauer, Topic-Pills.
 * TOC wird via JS aus H2/H3-Elementen generiert.
 *
 * @package Hasimuener_Journal
 * @version 2.2.0
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<main id="main-content">
<article class="essay-article" aria-label="<?php the_title_attribute(); ?>">

    <?php if ( has_post_thumbnail() ) : ?>

        <!-- HERO: Full-bleed Beitragsbild mit Overlay-Text -->
        <header class="essay-hero">
            <div class="essay-hero__image-wrap">
                <?php the_post_thumbnail( 'full', array(
                    'class'   => 'essay-hero__img',
                    'loading' => 'eager',
                ) ); ?>
                <div class="essay-hero__overlay" aria-hidden="true"></div>
            </div>

            <div class="essay-hero__content">
                <span class="hp-kicker hp-kicker--light">Essay</span>
                <h1 class="essay-hero__title"><?php the_title(); ?></h1>

                <?php if ( has_excerpt() ) : ?>
                    <p class="essay-hero__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                <?php endif; ?>
            </div>
        </header>

        <!-- Meta-Leiste unter dem Hero -->
        <div class="essay-meta-bar">
            <div class="essay-meta-bar__inner">
                <div class="hp-meta">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date( 'j. F Y' ) ); ?>
                    </time>
                    <span class="hp-meta__separator"></span>
                    <span class="hp-reading-time"><?php echo esc_html( hp_reading_time() ); ?></span>
                </div>

                <?php
                $topics = get_the_terms( get_the_ID(), 'topic' );
                if ( $topics && ! is_wp_error( $topics ) ) : ?>
                    <ul class="hp-topics" aria-label="Themenfelder">
                        <?php foreach ( $topics as $topic ) : ?>
                            <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            </div>

            <?php
            $caption = get_the_post_thumbnail_caption();
            if ( $caption ) : ?>
                <p class="essay-meta-bar__caption"><?php echo esc_html( $caption ); ?></p>
            <?php endif; ?>
        </div>

    <?php else : ?>

        <!-- FALLBACK: Kein Beitragsbild → klassischer Header -->
        <header class="single-header">
            <span class="hp-kicker">Essay</span>
            <h1 class="single-header__title"><?php the_title(); ?></h1>

            <?php if ( has_excerpt() ) : ?>
                <p class="single-header__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
            <?php endif; ?>

            <div class="hp-meta">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                    <?php echo esc_html( get_the_date( 'j. F Y' ) ); ?>
                </time>
                <span class="hp-meta__separator"></span>
                <span class="hp-reading-time"><?php echo esc_html( hp_reading_time() ); ?></span>
            </div>

            <?php
            $topics = get_the_terms( get_the_ID(), 'topic' );
            if ( $topics && ! is_wp_error( $topics ) ) : ?>
                <ul class="hp-topics" aria-label="Themenfelder">
                    <?php foreach ( $topics as $topic ) : ?>
                        <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </header>

    <?php endif; ?>

    <!-- Lesefortschritt -->
    <div class="reading-progress" id="js-reading-progress" aria-hidden="true">
        <div class="reading-progress__bar" id="js-reading-bar"></div>
    </div>

    <!-- Inhalt mit Sticky TOC -->
    <div class="single-body single-body--with-toc">

        <!-- TOC: auf Desktop sticky neben dem Text -->
        <aside class="hp-toc" id="js-toc" aria-label="Inhaltsverzeichnis" hidden>
            <span class="hp-toc__title">Inhalt</span>
            <ol id="js-toc-list"></ol>
        </aside>

        <div class="single-body__main">
            <div class="prose">
                <?php the_content(); ?>
            </div>

            <?php
            // Mini-Graph: zeigt direkt verbundene Knoten als Lese-Begleiter
            if ( function_exists( 'hp_render_mini_graph' ) ) {
                hp_render_mini_graph( get_the_ID() );
            }

            hp_render_newsletter_form( [
                'id'           => 'newsletter-signup-essay',
                'context'      => 'essay',
                'variant'      => 'essay',
                'eyebrow'      => 'Neue Texte nicht verpassen',
                'title'        => 'Nächste Texte per E-Mail.',
                'lede'         => 'Eine kurze Mail, wenn hier ein neuer Essay oder eine relevante Notiz erscheint.',
                'submit_label' => 'Anmelden',
            ] );
            ?>

            <!-- Artikel-Fußzeile -->
            <footer class="essay-footer">
                <hr class="journal-rule" aria-hidden="true">

                <!-- Bewertung -->
                <div class="hp-vote-section">
                    <p class="hp-vote-question">Hat es dir gefallen?</p>
                    <?php echo hp_get_vote_buttons( get_the_ID() ); ?>
                </div>

                <!-- Teilen -->
                <?php
                $share_url   = rawurlencode( get_permalink() );
                $share_title = rawurlencode( get_the_title() );
                $share_text  = rawurlencode( get_the_title() . ' — ' . get_bloginfo( 'name' ) );
                ?>
                <nav class="hp-share" aria-label="Beitrag teilen">
                    <span class="hp-share__label">Teilen</span>
                    <ul class="hp-share__list">
                        <li><a class="hp-share__link hp-share__link--x" href="https://x.com/intent/tweet?url=<?php echo $share_url; ?>&amp;text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" aria-label="Auf X teilen"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a></li>
                        <li><a class="hp-share__link hp-share__link--li" href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" aria-label="Auf LinkedIn teilen"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a></li>
                        <li><a class="hp-share__link hp-share__link--fb" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" aria-label="Auf Facebook teilen"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a></li>
                        <li><button class="hp-share__link hp-share__link--copy" data-url="<?php echo esc_url( get_permalink() ); ?>" aria-label="Link kopieren" type="button"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button></li>
                    </ul>
                </nav>

                <?php
                $topics_footer = get_the_terms( get_the_ID(), 'topic' );
                if ( $topics_footer && ! is_wp_error( $topics_footer ) ) : ?>
                    <ul class="hp-topics" aria-label="Themenfelder">
                        <?php foreach ( $topics_footer as $topic ) : ?>
                            <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </footer>
        </div>

    </div>

    <!-- Sog-Block: Essay als Teil von Dossier(s) + zentrale Begriffe -->
    <?php
    $hp_se_id        = get_the_ID();
    $hp_se_dossiers  = function_exists( 'hp_post_get_dossiers' )  ? hp_post_get_dossiers( $hp_se_id )    : [];
    $hp_se_terms     = function_exists( 'hp_get_central_terms' )  ? hp_get_central_terms( $hp_se_id, 6 ) : [];

    if ( $hp_se_dossiers || $hp_se_terms ) : ?>
        <aside class="hp-essay-sog" aria-label="Weiterführende Lektüre">
            <div class="hp-essay-sog__inner">
                <?php if ( $hp_se_dossiers ) : ?>
                    <section class="hp-essay-sog__section hp-essay-sog__section--dossiers">
                        <h2 class="hp-essay-sog__heading">Teil dieser Dossiers</h2>
                        <ul class="hp-essay-sog__dossier-list">
                            <?php foreach ( $hp_se_dossiers as $hp_se_d ) :
                                $hp_se_d_intro = get_post_meta( $hp_se_d->ID, '_hp_dossier_intro', true );
                            ?>
                                <li>
                                    <a class="hp-essay-sog__dossier" href="<?php echo esc_url( get_permalink( $hp_se_d ) ); ?>">
                                        <span class="hp-essay-sog__dossier-kicker">Dossier</span>
                                        <span class="hp-essay-sog__dossier-title"><?php echo esc_html( get_the_title( $hp_se_d ) ); ?></span>
                                        <?php if ( $hp_se_d_intro ) : ?>
                                            <span class="hp-essay-sog__dossier-lede"><?php echo esc_html( wp_trim_words( $hp_se_d_intro, 22, ' …' ) ); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if ( $hp_se_terms ) : ?>
                    <section class="hp-essay-sog__section hp-essay-sog__section--terms">
                        <h2 class="hp-essay-sog__heading">Zentrale Begriffe</h2>
                        <div class="hp-essay-sog__term-list">
                            <?php foreach ( $hp_se_terms as $hp_se_t ) : ?>
                                <a class="hp-glossar-term hp-begriff-chip" href="<?php echo esc_url( get_permalink( $hp_se_t ) ); ?>"><?php echo esc_html( get_the_title( $hp_se_t ) ); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </aside>
    <?php endif; ?>

    <!-- Kommentarbereich -->
    <section class="hp-comments" aria-label="Kommentare">
        <div class="hp-comments__inner">
            <?php comments_template(); ?>
        </div>
    </section>

    <!-- Verwandte Essays -->
    <?php
    $hp_current_id    = get_the_ID();
    $hp_current_topics = get_the_terms( $hp_current_id, 'topic' );
    $hp_related_args   = [
        'post_type'      => 'essay',
        'posts_per_page' => 3,
        'post__not_in'   => [ $hp_current_id ],
        'post_status'    => 'publish',
    ];

    // Bevorzugt: gleiche Topics
    if ( $hp_current_topics && ! is_wp_error( $hp_current_topics ) ) {
        $hp_related_args['tax_query'] = [ [
            'taxonomy' => 'topic',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck( $hp_current_topics, 'term_id' ),
        ] ];
    }

    $hp_related = new WP_Query( $hp_related_args );

    // Fallback: neueste Essays, wenn zu wenig verwandte
    if ( $hp_related->found_posts < 2 ) {
        $hp_related = new WP_Query( [
            'post_type'      => 'essay',
            'posts_per_page' => 3,
            'post__not_in'   => [ $hp_current_id ],
            'post_status'    => 'publish',
        ] );
    }

    if ( $hp_related->have_posts() ) : ?>
    <section class="hp-related" aria-label="Verwandte Essays">
        <h2 class="hp-related__title">Weiterlesen</h2>
        <div class="hp-related__grid">
            <?php while ( $hp_related->have_posts() ) : $hp_related->the_post(); ?>
            <article class="hp-related__item">
                <div class="hp-meta">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date( 'j. F Y' ) ); ?>
                    </time>
                    <span class="hp-meta__separator"></span>
                    <span class="hp-reading-time"><?php echo esc_html( hp_reading_time() ); ?></span>
                </div>
                <h3 class="hp-related__item-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <?php if ( has_excerpt() ) : ?>
                    <p class="hp-related__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, ' …' ) ); ?></p>
                <?php endif; ?>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    endif; ?>

    <!-- Prev / Next Navigation -->
    <?php
    $hp_prev = get_previous_post( true, '', 'topic' );
    $hp_next = get_next_post( true, '', 'topic' );

    if ( $hp_prev || $hp_next ) : ?>
    <nav class="hp-post-nav" aria-label="Beitragsnavigation">
        <div class="hp-post-nav__inner">
            <?php if ( $hp_prev ) : ?>
            <a class="hp-post-nav__link hp-post-nav__link--prev" href="<?php echo esc_url( get_permalink( $hp_prev ) ); ?>">
                <span class="hp-post-nav__label">&larr; Vorheriger Essay</span>
                <span class="hp-post-nav__title"><?php echo esc_html( get_the_title( $hp_prev ) ); ?></span>
            </a>
            <?php else : ?>
            <span class="hp-post-nav__link hp-post-nav__link--empty"></span>
            <?php endif; ?>

            <?php if ( $hp_next ) : ?>
            <a class="hp-post-nav__link hp-post-nav__link--next" href="<?php echo esc_url( get_permalink( $hp_next ) ); ?>">
                <span class="hp-post-nav__label">Nächster Essay &rarr;</span>
                <span class="hp-post-nav__title"><?php echo esc_html( get_the_title( $hp_next ) ); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

</article>
</main>

<?php endwhile; ?>

<?php get_footer(); ?>
