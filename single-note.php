<?php
/**
 * Single Template: Notiz
 * 
 * Kürzeres Format. Kein TOC. Kompakt, fokussiert.
 *
 * @package Hasimuener_Journal
 * @version 2.0.0
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<main id="main-content">

<header class="single-header">
    <span class="hp-kicker">Notiz</span>
    <h1 class="single-header__title"><?php the_title(); ?></h1>

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
        <ul class="hp-topics hp-topics--spaced" aria-label="Themenfelder">
            <?php foreach ( $topics as $topic ) : ?>
                <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</header>

<article class="single-body" aria-label="<?php the_title_attribute(); ?>">

    <div class="prose">
        <?php the_content(); ?>
    </div>

    <!-- Bewertung -->
    <div class="hp-vote-section">
        <p class="hp-vote-question">Hat es dir gefallen?</p>
        <?php echo hp_get_vote_buttons( get_the_ID() ); ?>
    </div>

</article>

    <section class="hp-comments" aria-label="Kommentare">
        <div class="hp-comments__inner">
            <?php comments_template(); ?>
        </div>
    </section>

    <!-- Verwandte Notizen -->
    <?php
    $hp_current_id     = get_the_ID();
    $hp_current_topics = get_the_terms( $hp_current_id, 'topic' );
    $hp_related_args   = [
        'post_type'      => 'note',
        'posts_per_page' => 3,
        'post__not_in'   => [ $hp_current_id ],
        'post_status'    => 'publish',
        'no_found_rows'  => true,
    ];

    if ( $hp_current_topics && ! is_wp_error( $hp_current_topics ) ) {
        $hp_related_args['tax_query'] = [ [
            'taxonomy' => 'topic',
            'field'    => 'term_id',
            'terms'    => wp_list_pluck( $hp_current_topics, 'term_id' ),
        ] ];
    }

    $hp_related = new WP_Query( $hp_related_args );

    if ( $hp_related->post_count < 2 ) {
        $hp_related = new WP_Query( [
            'post_type'      => 'note',
            'posts_per_page' => 3,
            'post__not_in'   => [ $hp_current_id ],
            'post_status'    => 'publish',
            'no_found_rows'  => true,
        ] );
    }

    if ( $hp_related->have_posts() ) : ?>
    <section class="hp-related" aria-label="Verwandte Notizen">
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
                <span class="hp-post-nav__label">&larr; Vorherige Notiz</span>
                <span class="hp-post-nav__title"><?php echo esc_html( get_the_title( $hp_prev ) ); ?></span>
            </a>
            <?php else : ?>
            <span class="hp-post-nav__link hp-post-nav__link--empty"></span>
            <?php endif; ?>

            <?php if ( $hp_next ) : ?>
            <a class="hp-post-nav__link hp-post-nav__link--next" href="<?php echo esc_url( get_permalink( $hp_next ) ); ?>">
                <span class="hp-post-nav__label">Nächste Notiz &rarr;</span>
                <span class="hp-post-nav__title"><?php echo esc_html( get_the_title( $hp_next ) ); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

</main>

<?php endwhile; ?>

<?php get_footer(); ?>
