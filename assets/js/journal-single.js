/**
 * Hasimuener Journal — Single-View JS
 *
 * 1. Sticky TOC mit Active-Section-Tracking (IntersectionObserver)
 * 2. Lesefortschritts-Balken
 * 3. Footnote Smooth Scroll
 * 4. Share — Link kopieren
 *
 * Geladen nur auf Singles (essay, note, post).
 * Kein Framework. Kein Build-Step.
 *
 * @package Hasimuener_Journal
 * @version 4.1.0
 */

( function () {
    'use strict';

    var headingsArray = [];
    var tocItems      = [];

    /* =========================================
       1. TABLE OF CONTENTS + ACTIVE TRACKING
       ========================================= */

    function buildTOC() {
        var tocNav  = document.getElementById( 'js-toc' );
        var tocList = document.getElementById( 'js-toc-list' );

        if ( ! tocNav || ! tocList ) return;

        var prose = document.querySelector( '.prose' );
        if ( ! prose ) return;

        var headings = prose.querySelectorAll( 'h2, h3' );
        if ( headings.length < 2 ) return;

        headings.forEach( function ( heading, index ) {
            if ( ! heading.id ) {
                heading.id = 'section-' + ( index + 1 );
            }

            var li = document.createElement( 'li' );
            li.setAttribute( 'data-target', heading.id );

            if ( heading.tagName === 'H3' ) {
                li.classList.add( 'toc-h3' );
            }

            var a = document.createElement( 'a' );
            a.href = '#' + heading.id;
            a.textContent = heading.textContent;

            a.addEventListener( 'click', function ( e ) {
                e.preventDefault();
                smoothScrollTo( heading );
                history.pushState( null, '', '#' + heading.id );
            } );

            li.appendChild( a );
            tocList.appendChild( li );

            headingsArray.push( heading );
            tocItems.push( li );
        } );

        // TOC sichtbar machen
        tocNav.removeAttribute( 'hidden' );
        tocNav.setAttribute( 'data-visible', 'true' );

        // Active Section Tracking starten
        initActiveTracking();
    }

    function initActiveTracking() {
        // IntersectionObserver: beobachtet Überschriften
        if ( ! ( 'IntersectionObserver' in window ) ) return;

        var currentIndex = -1;

        var observer = new IntersectionObserver( function ( entries ) {
            entries.forEach( function ( entry ) {
                if ( entry.isIntersecting ) {
                    var idx = headingsArray.indexOf( entry.target );
                    if ( idx !== -1 ) {
                        setActive( idx );
                    }
                }
            } );
        }, {
            rootMargin: '-10% 0px -75% 0px',
            threshold: 0
        } );

        headingsArray.forEach( function ( heading ) {
            observer.observe( heading );
        } );

        // Fallback: Scroll-basiert für präzisere Erkennung
        var ticking = false;
        window.addEventListener( 'scroll', function () {
            if ( ! ticking ) {
                requestAnimationFrame( function () {
                    updateActiveOnScroll();
                    ticking = false;
                } );
                ticking = true;
            }
        }, { passive: true } );
    }

    function updateActiveOnScroll() {
        var scrollPos = window.pageYOffset + 120;
        var active    = -1;

        for ( var i = 0; i < headingsArray.length; i++ ) {
            if ( headingsArray[ i ].offsetTop <= scrollPos ) {
                active = i;
            }
        }

        if ( active >= 0 ) {
            setActive( active );
        }
    }

    function setActive( index ) {
        tocItems.forEach( function ( item, i ) {
            if ( i === index ) {
                item.classList.add( 'is-active' );
            } else {
                item.classList.remove( 'is-active' );
            }
        } );
    }

    /* =========================================
       2. READING PROGRESS BAR
       ========================================= */

    function initReadingProgress() {
        var bar   = document.getElementById( 'js-reading-bar' );
        var prose = document.querySelector( '.prose' );

        if ( ! bar || ! prose ) return;

        var ticking = false;

        window.addEventListener( 'scroll', function () {
            if ( ! ticking ) {
                requestAnimationFrame( function () {
                    updateProgress( bar, prose );
                    ticking = false;
                } );
                ticking = true;
            }
        }, { passive: true } );
    }

    function updateProgress( bar, prose ) {
        var proseTop    = prose.offsetTop;
        var proseHeight = prose.offsetHeight;
        var scrollPos   = window.pageYOffset;
        var windowH     = window.innerHeight;

        var start    = proseTop;
        var end      = proseTop + proseHeight - windowH;
        var progress = ( scrollPos - start ) / ( end - start );

        progress = Math.max( 0, Math.min( 1, progress ) );
        bar.style.width = ( progress * 100 ) + '%';
    }

    /* =========================================
       3. FOOTNOTE SMOOTH SCROLL
       ========================================= */

    function initFootnoteScroll() {
        document.addEventListener( 'click', function ( e ) {
            var link = e.target.closest( 'a[href^="#fn"], a[href^="#fnref"], a.footnote-backref, sup.footnote-ref a' );
            if ( ! link ) return;

            var targetId = link.getAttribute( 'href' );
            if ( ! targetId || targetId.charAt( 0 ) !== '#' ) return;

            var target = document.getElementById( targetId.substring( 1 ) );
            if ( ! target ) return;

            e.preventDefault();
            smoothScrollTo( target );
            history.pushState( null, '', targetId );
        } );
    }

    /* =========================================
       HELPER: Smooth Scroll
       ========================================= */

    function smoothScrollTo( element ) {
        var offset = 24;

        if ( 'scrollBehavior' in document.documentElement.style ) {
            window.scrollTo( {
                top: element.getBoundingClientRect().top + window.pageYOffset - offset,
                behavior: 'smooth'
            } );
        } else {
            window.scrollTo( 0, element.getBoundingClientRect().top + window.pageYOffset - offset );
        }

        element.setAttribute( 'tabindex', '-1' );
        element.focus( { preventScroll: true } );
    }

    /* =========================================
       4. SHARE — Link kopieren
       ========================================= */

    function initShareCopyLink() {
        var buttons = document.querySelectorAll( '.hp-share__link--copy' );

        buttons.forEach( function( btn ) {
            btn.addEventListener( 'click', function() {
                var url = btn.getAttribute( 'data-url' );
                if ( ! url ) return;

                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    navigator.clipboard.writeText( url ).then( function() {
                        showCopied( btn );
                    } );
                } else {
                    // Fallback
                    var input = document.createElement( 'input' );
                    input.value = url;
                    document.body.appendChild( input );
                    input.select();
                    document.execCommand( 'copy' );
                    document.body.removeChild( input );
                    showCopied( btn );
                }
            } );
        } );
    }

    function showCopied( btn ) {
        var original = btn.innerHTML;
        btn.innerHTML = '✓';
        btn.classList.add( 'is-copied' );

        setTimeout( function() {
            btn.innerHTML = original;
            btn.classList.remove( 'is-copied' );
        }, 2000 );
    }

    /* =========================================
       INIT
       ========================================= */

    function init() {
        buildTOC();
        initReadingProgress();
        initFootnoteScroll();
        initShareCopyLink();
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
