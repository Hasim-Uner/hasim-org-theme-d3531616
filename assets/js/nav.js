/**
 * Hasimuener Journal — Navigation JS
 *
 * Globale Navigationslogik: Hamburger-Toggle, Suchfeld,
 * Header-Scroll-State. Auf ALLEN Seiten geladen.
 *
 * Kein Framework. Kein Build-Step.
 *
 * @package Hasimuener_Journal
 * @version 4.1.0
 */

/* =========================================
   MOBILE NAVIGATION — Hamburger Toggle
   =========================================
   Öffnet/schließt das Mobile-Menü.
*/
( function () {
    'use strict';

    function init() {
        var toggle = document.querySelector( '.hp-nav__toggle' );
        var mobile = document.getElementById( 'hp-nav-mobile' );
        var searchToggle = document.querySelector( '.hp-nav__search-toggle' );
        var searchPanel = document.getElementById( 'hp-nav-search' );
        var headerBar = document.querySelector( '.hp-header-bar' );
        var toggleLabel = toggle ? toggle.querySelector( '.hp-nav__toggle-label' ) : null;

        if ( ! toggle || ! mobile ) return;

        toggle.addEventListener( 'click', function () {
            var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
            toggle.setAttribute( 'aria-expanded', String( ! expanded ) );
            toggle.setAttribute( 'aria-label', expanded ? 'Menü öffnen' : 'Menü schließen' );
            if ( toggleLabel ) {
                toggleLabel.textContent = expanded ? 'Menü' : 'Schließen';
            }

            if ( expanded ) {
                // Schließen
                mobile.setAttribute( 'data-open', 'false' );
                if ( headerBar ) headerBar.classList.remove( 'hp-header-bar--menu-open' );
                setTimeout( function () {
                    mobile.setAttribute( 'hidden', '' );
                }, 300 );
            } else {
                if ( searchToggle && searchPanel && searchToggle.getAttribute( 'aria-expanded' ) === 'true' ) {
                    searchToggle.setAttribute( 'aria-expanded', 'false' );
                    searchToggle.setAttribute( 'aria-label', 'Suche öffnen' );
                    searchPanel.setAttribute( 'hidden', '' );
                    if ( headerBar ) headerBar.classList.remove( 'hp-header-bar--search-open' );
                }
                // Öffnen
                mobile.removeAttribute( 'hidden' );
                // Force reflow für Animation
                void mobile.offsetHeight;
                mobile.setAttribute( 'data-open', 'true' );
                if ( headerBar ) headerBar.classList.add( 'hp-header-bar--menu-open' );
            }
        } );

        // ESC schließt Menü
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
                toggle.click();
                toggle.focus();
            }
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();

/* =========================================
   NAVIGATION SEARCH TOGGLE
   =========================================
   Öffnet/schließt das Suchfeld in der Navigation.
*/
( function () {
    'use strict';

    function init() {
        var toggle = document.querySelector( '.hp-nav__search-toggle' );
        var panel  = document.getElementById( 'hp-nav-search' );
        var menuToggle = document.querySelector( '.hp-nav__toggle' );
        var menuPanel = document.getElementById( 'hp-nav-mobile' );
        var headerBar = document.querySelector( '.hp-header-bar' );

        if ( ! toggle || ! panel ) return;

        toggle.addEventListener( 'click', function () {
            var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
            toggle.setAttribute( 'aria-expanded', String( ! expanded ) );
            toggle.setAttribute( 'aria-label', expanded ? 'Suche öffnen' : 'Suche schließen' );

            if ( expanded ) {
                panel.setAttribute( 'hidden', '' );
                if ( headerBar ) headerBar.classList.remove( 'hp-header-bar--search-open' );
            } else {
                if ( menuToggle && menuPanel && menuToggle.getAttribute( 'aria-expanded' ) === 'true' ) {
                    menuToggle.setAttribute( 'aria-expanded', 'false' );
                    menuToggle.setAttribute( 'aria-label', 'Menü öffnen' );
                    menuPanel.setAttribute( 'data-open', 'false' );
                    menuPanel.setAttribute( 'hidden', '' );
                    if ( headerBar ) headerBar.classList.remove( 'hp-header-bar--menu-open' );
                }
                panel.removeAttribute( 'hidden' );
                if ( headerBar ) headerBar.classList.add( 'hp-header-bar--search-open' );
                var input = panel.querySelector( 'input[type="search"]' );
                if ( input ) input.focus();
            }
        } );

        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && toggle.getAttribute( 'aria-expanded' ) === 'true' ) {
                toggle.setAttribute( 'aria-expanded', 'false' );
                toggle.setAttribute( 'aria-label', 'Suche öffnen' );
                panel.setAttribute( 'hidden', '' );
                if ( headerBar ) headerBar.classList.remove( 'hp-header-bar--search-open' );
                toggle.focus();
            }
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();

/* =========================================
   HEADER SCROLL STATE
   =========================================
   Lässt die Navigationsleiste beim Scrollen als
   eigenständige schwebende Leiste auftreten.
*/
( function () {
    'use strict';

    function init() {
        var headerBar = document.querySelector( '.hp-header-bar' );
        var masthead = document.querySelector( '.hp-masthead' );
        var ticking = false;

        if ( ! headerBar || ! masthead ) return;

        function update() {
            var threshold = Math.max( 24, masthead.offsetHeight - 16 );
            var scrollY = window.pageYOffset || window.scrollY || 0;
            headerBar.classList.toggle( 'hp-header-bar--scrolled', scrollY > threshold );
            ticking = false;
        }

        function onScroll() {
            if ( ticking ) return;
            ticking = true;
            window.requestAnimationFrame( update );
        }

        update();
        window.addEventListener( 'scroll', onScroll, { passive: true } );
        window.addEventListener( 'resize', update );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }
} )();
