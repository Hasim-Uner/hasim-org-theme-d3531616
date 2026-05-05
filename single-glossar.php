<?php
/**
 * Single Template: Glossar-Eintrag
 *
 * Zeigt einen Begriff mit Kurzdefinition, ausführlichem Kontext,
 * Sprach-Entsprechungen (DE/KU/TR), verwandten Begriffen, Quellen
 * und Rückverlinkungen zu Essays/Notizen, die den Begriff verwenden.
 *
 * Design: Zentriert, großzügig, redaktionell — konsistent mit
 * single-essay.php und single-note.php. Wissens-Sektionen
 * (Verwandt/Quellen/Stand) sind dichter gesetzt als die Lesefläche.
 *
 * @package Hasimuener_Journal
 * @version 5.4.0 — Wissensplattform Phase 2
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

<?php
$hp_post_id  = get_the_ID();
$hp_kurz     = get_post_meta( $hp_post_id, '_hp_glossar_kurz', true );
$hp_synonyme = get_post_meta( $hp_post_id, '_hp_glossar_synonyme', true );
$hp_lang_ku  = get_post_meta( $hp_post_id, '_hp_glossar_lang_ku', true );
$hp_lang_tr  = get_post_meta( $hp_post_id, '_hp_glossar_lang_tr', true );
$hp_verwandt = get_post_meta( $hp_post_id, '_hp_glossar_verwandt', true );
$hp_quellen  = get_post_meta( $hp_post_id, '_hp_glossar_quellen', true );
$hp_version  = get_post_meta( $hp_post_id, '_hp_glossar_version', true );
$hp_stand    = get_post_meta( $hp_post_id, '_hp_glossar_stand', true );
?>

<main id="main-content">

<!-- Header: Zentriert, großzügig, editoriales Auftreten -->
<header class="hp-glossar-hero">
    <div class="hp-glossar-hero__inner">
        <span class="hp-kicker">Begriff</span>
        <h1 class="hp-glossar-hero__title"><?php the_title(); ?></h1>

        <?php if ( $hp_kurz ) : ?>
            <p class="hp-glossar-hero__kurz"><?php echo esc_html( $hp_kurz ); ?></p>
        <?php endif; ?>

        <?php if ( $hp_lang_ku || $hp_lang_tr ) : ?>
            <dl class="hp-begriff-sprachen" aria-label="Entsprechungen in anderen Sprachen">
                <div class="hp-begriff-sprachen__item">
                    <dt>DE</dt>
                    <dd><?php the_title(); ?></dd>
                </div>
                <div class="hp-begriff-sprachen__item<?php echo $hp_lang_ku ? '' : ' hp-begriff-sprachen__item--missing'; ?>">
                    <dt>KU</dt>
                    <dd><?php echo $hp_lang_ku ? esc_html( $hp_lang_ku ) : '<span class="hp-begriff-sprachen__missing">noch nicht erfasst</span>'; ?></dd>
                </div>
                <div class="hp-begriff-sprachen__item<?php echo $hp_lang_tr ? '' : ' hp-begriff-sprachen__item--missing'; ?>">
                    <dt>TR</dt>
                    <dd><?php echo $hp_lang_tr ? esc_html( $hp_lang_tr ) : '<span class="hp-begriff-sprachen__missing">noch nicht erfasst</span>'; ?></dd>
                </div>
            </dl>
        <?php endif; ?>

        <?php if ( $hp_synonyme ) :
            $hp_syn_list = array_map( 'trim', explode( ',', $hp_synonyme ) );
        ?>
            <div class="hp-glossar-hero__synonyme">
                <?php foreach ( $hp_syn_list as $syn ) : ?>
                    <span class="hp-glossar-syn-pill"><?php echo esc_html( $syn ); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        $topics = get_the_terms( $hp_post_id, 'topic' );
        if ( $topics && ! is_wp_error( $topics ) ) : ?>
            <ul class="hp-topics hp-glossar-hero__topics" aria-label="Themenfelder">
                <?php foreach ( $topics as $topic ) : ?>
                    <li><a class="hp-topic-pill" href="<?php echo esc_url( get_term_link( $topic ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</header>

<!-- Body: Prose mit Lesebreite -->
<article class="hp-glossar-body" aria-label="<?php the_title_attribute(); ?>">
    <div class="hp-glossar-body__content prose">
        <?php the_content(); ?>
    </div>

    <?php
    // Mini-Graph: verbundene Knoten als Lese-Begleiter
    if ( function_exists( 'hp_render_mini_graph' ) ) {
        hp_render_mini_graph( get_the_ID() );
    }
    ?>

    <?php
    // Verwandte Begriffe (kuratiert über _hp_glossar_verwandt)
    if ( $hp_verwandt ) :
        $hp_verwandt_ids = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', $hp_verwandt ) ) ) );

        if ( $hp_verwandt_ids ) :
            $hp_verwandt_q = new WP_Query( [
                'post_type'      => 'glossar',
                'post__in'       => $hp_verwandt_ids,
                'orderby'        => 'post__in',
                'posts_per_page' => count( $hp_verwandt_ids ),
                'post_status'    => 'publish',
            ] );

            if ( $hp_verwandt_q->have_posts() ) : ?>
                <aside class="hp-begriff-section hp-begriff-verwandt" aria-label="Verwandte Begriffe">
                    <h2 class="hp-begriff-section__heading">Verwandte Begriffe</h2>
                    <div class="hp-begriff-verwandt__list">
                        <?php while ( $hp_verwandt_q->have_posts() ) : $hp_verwandt_q->the_post(); ?>
                            <a class="hp-glossar-term hp-begriff-chip" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        <?php endwhile; ?>
                    </div>
                </aside>
            <?php
                wp_reset_postdata();
            endif;
        endif;
    endif;
    ?>

    <?php
    // Rückverlinkungen: Essays & Notizen, die diesen Begriff enthalten
    $hp_title   = get_the_title();
    $hp_related = new WP_Query( [
        'post_type'      => [ 'essay', 'note' ],
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        's'              => $hp_title,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    if ( $hp_related->have_posts() ) : ?>
        <aside class="hp-begriff-section hp-glossar-related" aria-label="Beiträge zu diesem Begriff">
            <h2 class="hp-begriff-section__heading">Diesen Begriff verwenden</h2>
            <ul class="hp-glossar-related__list">
                <?php while ( $hp_related->have_posts() ) : $hp_related->the_post(); ?>
                    <li class="hp-glossar-related__item">
                        <span class="hp-glossar-related__type"><?php echo 'essay' === get_post_type() ? 'Essay' : 'Notiz'; ?></span>
                        <a class="hp-glossar-related__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>
    <?php
        wp_reset_postdata();
    endif;
    ?>

    <?php
    // Quellen-Block
    if ( $hp_quellen ) :
        $hp_quellen_lines = array_filter( array_map( 'trim', explode( "\n", $hp_quellen ) ) );
        if ( $hp_quellen_lines ) : ?>
            <aside class="hp-begriff-section hp-begriff-quellen" aria-label="Quellen">
                <h2 class="hp-begriff-section__heading">Quellen</h2>
                <ol class="hp-begriff-quellen__list">
                    <?php foreach ( $hp_quellen_lines as $line ) : ?>
                        <li><?php echo wp_kses( make_clickable( $line ), [ 'a' => [ 'href' => [], 'rel' => [], 'target' => [] ] ] ); ?></li>
                    <?php endforeach; ?>
                </ol>
            </aside>
        <?php endif;
    endif;
    ?>

    <?php if ( $hp_version || $hp_stand ) : ?>
        <footer class="hp-begriff-stand" aria-label="Versionsinformation">
            <?php if ( $hp_version ) : ?>
                <span class="hp-begriff-stand__item"><span class="hp-begriff-stand__label">Version</span> <?php echo esc_html( $hp_version ); ?></span>
            <?php endif; ?>
            <?php if ( $hp_stand ) :
                $hp_stand_ts = strtotime( $hp_stand );
            ?>
                <span class="hp-begriff-stand__item"><span class="hp-begriff-stand__label">Stand</span>
                    <time datetime="<?php echo esc_attr( $hp_stand ); ?>"><?php echo esc_html( $hp_stand_ts ? date_i18n( 'j. F Y', $hp_stand_ts ) : $hp_stand ); ?></time>
                </span>
            <?php endif; ?>
        </footer>
    <?php endif; ?>

    <nav class="hp-glossar-backnav" aria-label="Glossar-Navigation">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'glossar' ) ); ?>">
            <span aria-hidden="true">&larr;</span> Alle Begriffe im Glossar
        </a>
    </nav>

</article>
</main>

<?php endwhile; ?>

<?php get_footer(); ?>
