/**
 * Hasimuener Journal — Glossar Tooltip
 *
 * Schwebendes Tooltip für Auto-Linking von Glossar-Begriffen.
 * Nutzt die native Popover API (popover="manual") für
 * bessere Barrierefreiheit und reduzierten Code-Overhead.
 *
 * Geladen auf Posts mit Glossar-Verlinkung (essay, note, post).
 * Kein Framework. Kein Build-Step.
 *
 * @package Hasimuener_Journal
 * @version 4.1.0
 */

( function () {
    'use strict';

    var tooltip   = null;
    var activeEl  = null;
    var hideTimer = null;

    /* =========================================
       TOOLTIP ERSTELLEN (Popover API)
       ========================================= */

    function createTooltip() {
        tooltip = document.createElement( 'div' );
        tooltip.className = 'hp-gtt';
        tooltip.id        = 'hp-gtt';
        tooltip.setAttribute( 'popover', 'manual' );
        tooltip.setAttribute( 'role', 'tooltip' );
        tooltip.innerHTML =
            '<strong class="hp-gtt__term"></strong>' +
            '<p class="hp-gtt__def"></p>' +
            '<a class="hp-gtt__link" href="#">Im Glossar lesen \u2192</a>';
        document.body.appendChild( tooltip );

        tooltip.addEventListener( 'mouseenter', function () {
            clearTimeout( hideTimer );
        } );
        tooltip.addEventListener( 'mouseleave', function () {
            scheduleHide();
        } );
    }

    /* =========================================
       SHOW / HIDE / POSITION
       ========================================= */

    function show( el ) {
        clearTimeout( hideTimer );
        activeEl = el;

        tooltip.querySelector( '.hp-gtt__term' ).textContent = el.dataset.term || '';
        tooltip.querySelector( '.hp-gtt__def' ).textContent  = el.dataset.def  || '';
        tooltip.querySelector( '.hp-gtt__link' ).href        = el.dataset.url  || '#';

        // Popover API: sichtbar machen
        try {
            tooltip.showPopover();
        } catch ( e ) {
            // Fallback für ältere Browser ohne Popover-Support
            tooltip.classList.add( 'hp-gtt--visible' );
        }

        position( el );
    }

    function scheduleHide() {
        hideTimer = setTimeout( hide, 200 );
    }

    function hide() {
        try {
            tooltip.hidePopover();
        } catch ( e ) {
            tooltip.classList.remove( 'hp-gtt--visible' );
        }
        activeEl = null;
    }

    function position( el ) {
        var rect   = el.getBoundingClientRect();
        var tW     = tooltip.offsetWidth  || 280;
        var tH     = tooltip.offsetHeight || 120;
        var vW     = window.innerWidth;
        var scroll = window.scrollY || window.pageYOffset;

        // Versuche oberhalb zu platzieren
        var top  = rect.top + scroll - tH - 10;
        var left = rect.left + ( rect.width / 2 ) - ( tW / 2 );

        // Fällt oben raus → unterhalb
        if ( top < scroll + 8 ) {
            top = rect.bottom + scroll + 10;
            tooltip.classList.add( 'hp-gtt--below' );
        } else {
            tooltip.classList.remove( 'hp-gtt--below' );
        }

        // Rechts begrenzen
        if ( left + tW > vW - 12 ) { left = vW - tW - 12; }
        if ( left < 12 )           { left = 12; }

        tooltip.style.top  = top  + 'px';
        tooltip.style.left = left + 'px';
    }

    /* =========================================
       INIT
       ========================================= */

    function init() {
        var terms = document.querySelectorAll( '.hp-glossar-term' );
        if ( ! terms.length ) return;

        createTooltip();

        terms.forEach( function ( el ) {
            // Maus
            el.addEventListener( 'mouseenter', function () { show( el ); } );
            el.addEventListener( 'mouseleave', scheduleHide );
            // Tastatur
            el.addEventListener( 'focus', function () { show( el ); } );
            el.addEventListener( 'blur', scheduleHide );
            // Mobile: Toggle bei Tap
            el.addEventListener( 'click', function ( e ) {
                e.stopPropagation();
                if ( activeEl === el && tooltip.matches( ':popover-open' ) ) {
                    hide();
                } else {
                    show( el );
                }
            } );
        } );

        // Klick außerhalb schließt
        document.addEventListener( 'click', function () { hide(); } );

        // Repositionieren bei Scroll
        window.addEventListener( 'scroll', function () {
            if ( activeEl ) {
                try {
                    if ( tooltip.matches( ':popover-open' ) ) {
                        position( activeEl );
                    }
                } catch ( e ) {
                    if ( tooltip.classList.contains( 'hp-gtt--visible' ) ) {
                        position( activeEl );
                    }
                }
            }
        }, { passive: true } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
