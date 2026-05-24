<?php
/**
 * Taxonomy Template: Themenfeld (topic) — Pillar Page
 *
 * Strategische Funktion: Topic-Archive nicht als Liste, sondern als
 * thematische Pillar-Page. Sammelt Essays, Notizen, Glossar-Begriffe
 * und Cross-Links zu anderen Themenfeldern unter einem Term.
 *
 * Layout:
 *   1. Header mit Kicker + Term-Description (prominent)
 *   2. Essays (max. die ersten N, mit Excerpt)
 *   3. Notizen (komprimierter)
 *   4. Verwandte Begriffe (Glossar mit diesem Topic)
 *   5. Andere Themenfelder (Cross-Link-Modul)
 *   6. Pagination (rest of posts)
 *
 * SEO-Hebel: Mehr semantischer Content pro Topic-URL, internes Linking
 * zu Glossar und Cross-Topics, klare H2-Struktur.
 *
 * @package Hasimuener_Journal
 * @since   6.0.0 — Pillar-Page-Umstellung
 */

get_header();

$hp_term     = get_queried_object();
$hp_term_id  = $hp_term instanceof WP_Term ? (int) $hp_term->term_id : 0;
$hp_is_paged = is_paged();
?>

<main id="main-content" class="hp-topic-archive hp-topic-archive--pillar">
    <div class="hp-topic-archive__inner">

        <header class="hp-topic-archive__header">
            <span class="hp-kicker">Themenfeld</span>
            <h1 class="hp-topic-archive__title"><?php single_term_title(); ?></h1>
            <?php if ( $hp_term && $hp_term->description ) : ?>
                <p class="hp-topic-archive__desc"><?php echo esc_html( $hp_term->description ); ?></p>
            <?php endif; ?>
            <?php if ( ! $hp_is_paged && $hp_term ) :
                $hp_total = (int) $hp_term->count;
                if ( $hp_total > 0 ) : ?>
                    <p class="hp-topic-archive__count"><?php echo esc_html( sprintf( _n( '%d Beitrag', '%d Beiträge', $hp_total, 'hasim' ), $hp_total ) ); ?></p>
                <?php endif;
            endif; ?>
        </header>

        <?php if ( $hp_is_paged ) : /* --- Paginierte Folgeseiten: schlankes Listing --- */ ?>

            <?php if ( have_posts() ) : ?>
                <div class="hp-topic-archive__list">
                    <?php while ( have_posts() ) : the_post();
                        switch ( get_post_type() ) {
                            case 'essay':   $hp_type_label = 'Essay';   break;
                            case 'note':    $hp_type_label = 'Notiz';   break;
                            case 'glossar': $hp_type_label = 'Glossar'; break;
                            default:        $hp_type_label = 'Beitrag'; break;
                        }
                        ?>
                        <article class="archive-item" id="post-<?php the_ID(); ?>">
                            <div class="hp-meta">
                                <span class="hp-search__type"><?php echo esc_html( $hp_type_label ); ?></span>
                                <span class="hp-meta__separator"></span>
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j. F Y' ) ); ?></time>
                                <span class="hp-meta__separator"></span>
                                <span class="hp-reading-time"><?php echo esc_html( hp_reading_time() ); ?></span>
                            </div>
                            <h2 class="archive-item__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <?php if ( has_excerpt() || get_the_content() ) : ?>
                                <p class="archive-item__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, ' …' ) ); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endwhile; ?>
                </div>
                <?php the_posts_pagination( [
                    'mid_size'  => 1,
                    'prev_text' => '&larr; Zurück',
                    'next_text' => 'Weiter &rarr;',
                ] ); ?>
            <?php endif; ?>

        <?php else : /* --- Seite 1: Pillar-Layout mit Sektionen --- */ ?>

            <?php
            // Essays in diesem Topic
            $hp_essays = new WP_Query( [
                'post_type'      => 'essay',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'tax_query'      => [ [
                    'taxonomy' => 'topic',
                    'field'    => 'term_id',
                    'terms'    => [ $hp_term_id ],
                ] ],
            ] );

            // Notizen in diesem Topic
            $hp_notes = new WP_Query( [
                'post_type'      => 'note',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'tax_query'      => [ [
                    'taxonomy' => 'topic',
                    'field'    => 'term_id',
                    'terms'    => [ $hp_term_id ],
                ] ],
            ] );

            // Glossar-Begriffe in diesem Topic
            $hp_terms_in_topic = new WP_Query( [
                'post_type'      => 'glossar',
                'posts_per_page' => 12,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'tax_query'      => [ [
                    'taxonomy' => 'topic',
                    'field'    => 'term_id',
                    'terms'    => [ $hp_term_id ],
                ] ],
            ] );
            ?>

            <?php if ( $hp_essays->have_posts() ) : ?>
                <section class="hp-topic-archive__section hp-topic-archive__section--essays" aria-label="Essays">
                    <h2 class="hp-topic-archive__section-title">Essays</h2>
                    <div class="hp-topic-archive__list">
                        <?php while ( $hp_essays->have_posts() ) : $hp_essays->the_post(); ?>
                            <article class="archive-item" id="post-<?php the_ID(); ?>">
                                <div class="hp-meta">
                                    <span class="hp-search__type">Essay</span>
                                    <span class="hp-meta__separator"></span>
                                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j. F Y' ) ); ?></time>
                                    <span class="hp-meta__separator"></span>
                                    <span class="hp-reading-time"><?php echo esc_html( hp_reading_time() ); ?></span>
                                </div>
                                <h3 class="archive-item__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <?php if ( has_excerpt() || get_the_content() ) : ?>
                                    <p class="archive-item__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, ' …' ) ); ?></p>
                                <?php endif; ?>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>

            <?php if ( $hp_notes->have_posts() ) : ?>
                <section class="hp-topic-archive__section hp-topic-archive__section--notes" aria-label="Notizen">
                    <h2 class="hp-topic-archive__section-title">Notizen</h2>
                    <ul class="hp-topic-archive__notes">
                        <?php while ( $hp_notes->have_posts() ) : $hp_notes->the_post(); ?>
                            <li class="hp-topic-archive__note">
                                <a href="<?php the_permalink(); ?>" class="hp-topic-archive__note-link">
                                    <span class="hp-topic-archive__note-title"><?php the_title(); ?></span>
                                    <time class="hp-topic-archive__note-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j. F Y' ) ); ?></time>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </section>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>

            <?php if ( $hp_terms_in_topic->have_posts() ) : ?>
                <section class="hp-topic-archive__section hp-topic-archive__section--glossar" aria-label="Verwandte Begriffe">
                    <h2 class="hp-topic-archive__section-title">Verwandte Begriffe</h2>
                    <div class="hp-topic-archive__terms">
                        <?php while ( $hp_terms_in_topic->have_posts() ) : $hp_terms_in_topic->the_post(); ?>
                            <a class="hp-glossar-term hp-begriff-chip" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>

            <?php
            // Cross-Link auf andere Themenfelder
            $hp_other_topics = [];
            if ( function_exists( 'hp_get_curated_topics' ) ) {
                $hp_other_topics = array_values( array_filter(
                    hp_get_curated_topics( true ),
                    static function ( $t ) use ( $hp_term_id ) {
                        return $t instanceof WP_Term && (int) $t->term_id !== $hp_term_id;
                    }
                ) );
            }
            if ( $hp_other_topics ) : ?>
                <section class="hp-topic-archive__section hp-topic-archive__section--cross" aria-label="Andere Themenfelder">
                    <h2 class="hp-topic-archive__section-title">Andere Themenfelder</h2>
                    <div class="hp-topic-archive__cross">
                        <?php foreach ( $hp_other_topics as $hp_ot ) : ?>
                            <a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $hp_ot ) ); ?>">
                                <?php echo esc_html( $hp_ot->name ); ?>
                                <?php if ( (int) $hp_ot->count > 0 ) : ?>
                                    <span>(<?php echo (int) $hp_ot->count; ?>)</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php
            // Fallback wenn weder Essays noch Notizen vorhanden
            if ( ! $hp_essays->have_posts() && ! $hp_notes->have_posts() && ! $hp_terms_in_topic->have_posts() ) : ?>
                <div class="hp-topic-archive__empty">
                    <p class="hp-empty">Noch keine Beiträge in diesem Themenfeld.</p>
                </div>
            <?php endif; ?>

            <?php
            // Wenn mehr als die angezeigten 6 Essays/Notizen vorhanden sind,
            // Default-Pagination (zeigt restliche Posts ab Seite 2)
            if ( have_posts() && ( $hp_essays->max_num_pages > 1 || $hp_notes->max_num_pages > 1 ) ) : ?>
                <div class="hp-topic-archive__pagination">
                    <?php the_posts_pagination( [
                        'mid_size'  => 1,
                        'prev_text' => '&larr; Zurück',
                        'next_text' => 'Weiter &rarr;',
                    ] ); ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
