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
   NOTIFICATION BELL — Newsletter Modal
   =========================================
   Toggle für das Newsletter-Modal in der Kopfzeile.
   Inklusive ESC-Schließen, Backdrop-Klick, Body-Lock
   und Auto-Open nach Submission (data-open-on-load).
*/
( function () {
    'use strict';

    function init() {
        var toggle  = document.querySelector( '.hp-nav__bell-toggle' );
        var modal   = document.getElementById( 'hp-nav-bell-modal' );

        if ( ! toggle || ! modal ) return;

        var dot           = toggle.querySelector( '.hp-nav__bell-dot' );
        var card          = modal.querySelector( '.hp-nav-bell-modal__card' );
        var firstField    = modal.querySelector( 'input[type="email"]' );
        var emailInput    = firstField;
        var lastFocused   = null;
        var STORAGE_KEY   = 'hp_newsletter_subscribed';

        function isSubscribed() {
            try { return window.localStorage && localStorage.getItem( STORAGE_KEY ) === '1'; }
            catch ( e ) { return false; }
        }

        function markSubscribed() {
            try { if ( window.localStorage ) localStorage.setItem( STORAGE_KEY, '1' ); }
            catch ( e ) {}
        }

        function isOpen() { return ! modal.hasAttribute( 'hidden' ); }

        function open() {
            if ( isOpen() ) return;
            lastFocused = document.activeElement;
            modal.removeAttribute( 'hidden' );
            // Reflow für Animation
            void modal.offsetHeight;
            modal.classList.add( 'hp-nav-bell-modal--open' );
            toggle.setAttribute( 'aria-expanded', 'true' );
            document.body.classList.add( 'hp-no-scroll' );
            if ( dot ) dot.classList.add( 'hp-nav__bell-dot--hidden' );
            // Fokus erst auf Schließen-Button, dann E-Mail-Feld (UX-Best-Practice: Dialog Discovery)
            window.setTimeout( function () {
                if ( emailInput && ! isSubscribed() ) {
                    try { emailInput.focus( { preventScroll: true } ); }
                    catch ( e ) { emailInput.focus(); }
                } else {
                    var closeBtn = modal.querySelector( '.hp-nav-bell-modal__close' );
                    if ( closeBtn ) closeBtn.focus();
                }
            }, 40 );
        }

        function close() {
            if ( ! isOpen() ) return;
            modal.classList.remove( 'hp-nav-bell-modal--open' );
            toggle.setAttribute( 'aria-expanded', 'false' );
            document.body.classList.remove( 'hp-no-scroll' );
            window.setTimeout( function () {
                modal.setAttribute( 'hidden', '' );
                if ( lastFocused && typeof lastFocused.focus === 'function' ) {
                    lastFocused.focus();
                }
            }, 200 );
        }

        toggle.addEventListener( 'click', function () {
            if ( isOpen() ) {
                close();
            } else {
                open();
            }
        } );

        // Backdrop + Close-Button (alle Elemente mit data-bell-close)
        modal.addEventListener( 'click', function ( e ) {
            var target = e.target;
            if ( target && target.closest && target.closest( '[data-bell-close="1"]' ) ) {
                close();
            }
        } );

        // ESC schließt
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && isOpen() ) {
                close();
            }
        } );

        // Einfacher Focus-Trap innerhalb des Modals
        modal.addEventListener( 'keydown', function ( e ) {
            if ( e.key !== 'Tab' || ! isOpen() ) return;
            var focusables = card.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), [tabindex]:not([tabindex="-1"])'
            );
            if ( ! focusables.length ) return;
            var first = focusables[0];
            var last  = focusables[focusables.length - 1];
            if ( e.shiftKey && document.activeElement === first ) {
                e.preventDefault(); last.focus();
            } else if ( ! e.shiftKey && document.activeElement === last ) {
                e.preventDefault(); first.focus();
            }
        } );

        // Auto-Open nach erfolgreichem POST (Flash-Notice gesetzt)
        if ( modal.getAttribute( 'data-open-on-load' ) === '1' ) {
            // Wenn Submission erfolgreich war → Cookie/LocalStorage setzen
            var notice = modal.querySelector( '.hp-newsletter__notice--success' );
            if ( notice ) {
                markSubscribed();
            }
            // URL-Param ?newsletter=... entfernen (kosmetisch)
            if ( window.history && window.history.replaceState ) {
                try {
                    var url = new URL( window.location.href );
                    url.searchParams.delete( 'newsletter' );
                    window.history.replaceState( {}, document.title, url.toString() );
                } catch ( err ) {}
            }
            open();
        } else if ( isSubscribed() && dot ) {
            // Wenn bereits abonniert → kein „Neu"-Dot
            dot.classList.add( 'hp-nav__bell-dot--hidden' );
        }
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
