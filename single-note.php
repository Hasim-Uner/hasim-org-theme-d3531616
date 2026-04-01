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

    <!-- Vote-Buttons -->
    <?php echo hp_get_vote_buttons( get_the_ID() ); ?>
</header>

<article class="single-body" aria-label="<?php the_title_attribute(); ?>">

    <div class="prose">
        <?php the_content(); ?>
    </div>

</article>

    <section class="hp-comments" aria-label="Kommentare">
        <div class="hp-comments__inner">
            <?php comments_template(); ?>
        </div>
    </section>

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
