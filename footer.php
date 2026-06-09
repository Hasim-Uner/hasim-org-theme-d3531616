<?php
/**
 * Footer Template — Kolophon
 *
 * Dreispaltiges Kolophon: Herausgeber · Themenindex · Meta.
 * Ersetzt den GeneratePress-Standard-Footer.
 *
 * @package Hasimuener_Journal
 * @version 3.1.0
 */

defined( 'ABSPATH' ) || exit;

$hp_year    = gmdate( 'Y' );
?>

        </div><!-- .site-content (GeneratePress) -->
    </div><!-- .inside-site-main (GeneratePress) -->

    <footer class="hp-colophon" role="contentinfo" aria-label="Kolophon">
        <div class="hp-colophon__inner">

            <!-- Spalte 1: Herausgeber -->
            <div class="hp-colophon__col hp-colophon__mission">
                <span class="hp-colophon__label">Herausgeber</span>
                <p>Haşim Üner — Medienwissenschaftler. Publizist.</p>
                <div class="hp-colophon__links">
                    <a class="hp-colophon__mission-link" href="<?php echo esc_url( home_url( '/mission/' ) ); ?>">Mission lesen &rarr;</a>
                </div>
                <a class="hp-colophon__social" href="https://x.com/_0239983326111" target="_blank" rel="noopener noreferrer me" aria-label="Haşim Üner auf X">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    <span>Auf X folgen</span>
                </a>
                <a class="hp-colophon__social" href="https://www.youtube.com/@Hasimuener" target="_blank" rel="noopener noreferrer me" aria-label="Haşim Üner auf YouTube">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.19a3.02 3.02 0 00-2.12-2.12C19.5 3.56 12 3.56 12 3.56s-7.5 0-9.38.51A3.02 3.02 0 00.5 6.19 31.5 31.5 0 000 12a31.5 31.5 0 00.5 5.81 3.02 3.02 0 002.12 2.12c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 002.12-2.12A31.5 31.5 0 0024 12a31.5 31.5 0 00-.5-5.81zM9.55 15.57V8.43L15.82 12z"/></svg>
                    <span>YouTube</span>
                </a>
                <a class="hp-colophon__social" href="https://orcid.org/0009-0008-7500-2015" target="_blank" rel="noopener noreferrer me" aria-label="Haşim Üner auf ORCID">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#A6CE39" d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0z"/><path fill="#fff" d="M7.37 17.5H5.9V7.375h1.47V17.5zM6.635 5.957a.905.905 0 01-.904-.905c0-.497.406-.905.904-.905.497 0 .904.408.904.905 0 .5-.407.905-.904.905zM10.12 7.375h3.932c3.74 0 5.39 2.673 5.39 5.063 0 2.597-2.03 5.063-5.368 5.063H10.12V7.375zm1.47 8.806h2.29c3.268 0 4.015-2.48 4.015-3.743 0-2.058-1.31-3.743-4.09-3.743h-2.215v7.486z"/></svg>
                    <span>ORCID iD</span>
                </a>
            </div>

            <!-- Spalte 2: Themenindex -->
            <nav class="hp-colophon__col hp-colophon__index" aria-label="Themenindex">
                <span class="hp-colophon__label">Themenfelder</span>
                <?php
                $hp_footer_topics = hp_get_curated_topics();

                if ( $hp_footer_topics ) : ?>
                    <ul class="hp-colophon__topic-list">
                        <?php foreach ( $hp_footer_topics as $ft ) : ?>
                            <li><a href="<?php echo esc_url( get_term_link( $ft ) ); ?>"><?php echo esc_html( $ft->name ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="hp-colophon__empty">Keine Themen angelegt.</p>
                <?php endif; ?>
            </nav>

            <!-- Spalte 3: Meta / Legal -->
            <div class="hp-colophon__col hp-colophon__meta">
                <span class="hp-colophon__label">Meta</span>
                <ul class="hp-colophon__legal">
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'glossar' ) ); ?>">Glossar</a></li>
                    <li><a href="<?php echo esc_url( hp_get_contact_page_url() ); ?>">Anfragen</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a></li>
                </ul>
            </div>

        </div><!-- .hp-colophon__inner -->
        <div class="hp-colophon__transparency">
            <p>Dieses Journal ist ein eigenständiger publizistischer Ort. Meine berufliche Arbeit im Bereich digitaler Strategie und Webentwicklung findet getrennt davon auf <a href="https://hasimuener.de/">hasimuener.de</a> statt.</p>
        </div>
        <p class="hp-colophon__closing">Zwischen Sprachen und Perspektiven beginnt Verständigung.</p>
    </footer>

</div><!-- .site (GeneratePress) -->

<?php wp_footer(); ?>
</body>
</html>
