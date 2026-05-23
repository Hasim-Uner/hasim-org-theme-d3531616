/**
 * Hasimuener Journal — Link Preview Tooltip
 *
 * Zeigt beim Hover über interne Links (Essays, Notizen, Dossiers,
 * Glossar, Seiten) einen kompakten Preview-Tooltip mit Titel,
 * Typ-Label und Kurzbeschreibung an. Identisches visuelles Vokabular
 * wie der bestehende Glossar-Tooltip (.hp-gtt).
 *
 * Datenquelle: GET /wp-json/hp/v1/link-preview?url=<href>
 *
 * Skip-Regeln:
 *  - Externe URLs (anderes Origin)
 *  - Anchor-Only (#…), mailto:, tel:
 *  - Links innerhalb .hp-glossar-term (eigener Tooltip)
 *  - Links innerhalb Navigation/Footer/Breadcrumbs
 *  - Klasse .hp-no-preview oder data-no-preview="1"
 *
 * Kein Framework. Kein Build-Step.
 *
 * @package Hasimuener_Journal
 * @version 1.0.0
 */

( function () {
    'use strict';

    if ( ! window.hpLinkPreview || ! window.hpLinkPreview.restUrl ) {
        return;
    }

    var REST_URL    = window.hpLinkPreview.restUrl;
    var SHOW_DELAY  = 280;
    var HIDE_DELAY  = 180;
    var FETCH_CACHE = Object.create( null );
    var INFLIGHT    = Object.create( null );

    var tooltip   = null;
    var elTerm    = null;
    var elDef     = null;
    var elMeta    = null;
    var elLink    = null;
    var activeEl  = null;
    var showTimer = null;
    var hideTimer = null;

    /* =========================================
       SCOPE & FILTER
       ========================================= */

    var CONTENT_SCOPES = [
        '.essay-article',
        '.single-body',
        '.hp-glossar-body__content',
        '.hp-dossier-body__content',
        '.entry-content',
        '.prose',
        '.hp-essay-sog',
        '.hp-related',
        '.hp-einstieg',
        '.hp-begriff-verwandt',
        '.hp-dossier-begriffe',
    ];

    var SKIP_ANCESTORS = [
        '.hp-glossar-term',
        '.main-navigation',
        '.site-header',
        '.site-footer',
        '.hp-breadcrumbs',
        '#hp-gtt',
        '#hp-link-preview',
    ];

    function isInternal( a ) {
        if ( ! a.href ) return false;
        if ( a.target && a.target === '_blank' ) return false;
        if ( a.hostname !== window.location.hostname ) return false;
        if ( a.protocol !== 'http:' && a.protocol !== 'https:' ) return false;

        // Anchor-Only auf gleicher Seite
        if ( a.pathname === window.location.pathname && a.hash && ! a.search ) return false;

        // Asset-URLs (Bilder, PDFs, Downloads)
        if ( /\.(?:png|jpe?g|gif|webp|svg|pdf|zip|mp4|mp3|webm)(?:$|\?)/i.test( a.pathname ) ) {
            return false;
        }

        return true;
    }

    function shouldSkip( a ) {
        if ( a.classList.contains( 'hp-no-preview' ) ) return true;
        if ( a.dataset && a.dataset.noPreview === '1' ) return true;

        for ( var i = 0; i < SKIP_ANCESTORS.length; i++ ) {
            if ( a.closest( SKIP_ANCESTORS[ i ] ) ) return true;
        }
        return false;
    }

    function inScope( a ) {
        for ( var i = 0; i < CONTENT_SCOPES.length; i++ ) {
            if ( a.closest( CONTENT_SCOPES[ i ] ) ) return true;
        }
        return false;
    }

    /* =========================================
       TOOLTIP
       ========================================= */

    function createTooltip() {
        tooltip = document.createElement( 'div' );
        tooltip.className = 'hp-gtt hp-gtt--link-preview';
        tooltip.id        = 'hp-link-preview';
        tooltip.setAttribute( 'popover', 'manual' );
        tooltip.setAttribute( 'role', 'tooltip' );
        tooltip.innerHTML =
            '<strong class="hp-gtt__term"></strong>' +
            '<p class="hp-gtt__def"></p>' +
            '<span class="hp-gtt__meta"></span>' +
            '<a class="hp-gtt__link" href="#">Weiterlesen →</a>';

        document.body.appendChild( tooltip );

        elTerm = tooltip.querySelector( '.hp-gtt__term' );
        elDef  = tooltip.querySelector( '.hp-gtt__def' );
        elMeta = tooltip.querySelector( '.hp-gtt__meta' );
        elLink = tooltip.querySelector( '.hp-gtt__link' );

        tooltip.addEventListener( 'mouseenter', function () {
            clearTimeout( hideTimer );
        } );
        tooltip.addEventListener( 'mouseleave', scheduleHide );
    }

    function renderPayload( data ) {
        elTerm.textContent = data.type_label || '';
        elDef.textContent  = data.excerpt || data.title || '';
        elMeta.textContent = data.meta || '';
        elMeta.style.display = data.meta ? '' : 'none';
        elLink.href        = data.url || '#';

        // Titel als Sekundär-Info: wenn excerpt vorhanden, Titel als kleinen Vorspann
        // einsetzen — sonst nur excerpt (oder Titel als Fallback).
        if ( data.excerpt && data.title ) {
            elDef.innerHTML = '<span class="hp-gtt__title-line"></span>' +
                              '<span class="hp-gtt__excerpt-line"></span>';
            elDef.querySelector( '.hp-gtt__title-line' ).textContent   = data.title;
            elDef.querySelector( '.hp-gtt__excerpt-line' ).textContent = data.excerpt;
        }
    }

    function showFor( el, data ) {
        if ( ! tooltip ) createTooltip();

        clearTimeout( hideTimer );
        activeEl = el;

        renderPayload( data );

        try {
            tooltip.showPopover();
        } catch ( e ) {
            tooltip.classList.add( 'hp-gtt--visible' );
        }

        position( el );
    }

    function scheduleHide() {
        clearTimeout( hideTimer );
        hideTimer = setTimeout( hide, HIDE_DELAY );
    }

    function hide() {
        if ( ! tooltip ) return;
        try {
            tooltip.hidePopover();
        } catch ( e ) {
            tooltip.classList.remove( 'hp-gtt--visible' );
        }
        activeEl = null;
    }

    function position( el ) {
        var rect   = el.getBoundingClientRect();
        var tW     = tooltip.offsetWidth  || 320;
        var tH     = tooltip.offsetHeight || 140;
        var vW     = window.innerWidth;
        var scroll = window.scrollY || window.pageYOffset;

        var top  = rect.top + scroll - tH - 12;
        var left = rect.left + ( rect.width / 2 ) - ( tW / 2 );

        if ( top < scroll + 8 ) {
            top = rect.bottom + scroll + 12;
            tooltip.classList.add( 'hp-gtt--below' );
        } else {
            tooltip.classList.remove( 'hp-gtt--below' );
        }

        if ( left + tW > vW - 12 ) { left = vW - tW - 12; }
        if ( left < 12 )           { left = 12; }

        tooltip.style.top  = top  + 'px';
        tooltip.style.left = left + 'px';
    }

    /* =========================================
       FETCH (mit Cache + Inflight-Dedup)
       ========================================= */

    function fetchPreview( href ) {
        if ( FETCH_CACHE[ href ] !== undefined ) {
            return Promise.resolve( FETCH_CACHE[ href ] );
        }
        if ( INFLIGHT[ href ] ) {
            return INFLIGHT[ href ];
        }

        var url = REST_URL + ( REST_URL.indexOf( '?' ) === -1 ? '?' : '&' ) +
                  'url=' + encodeURIComponent( href );

        INFLIGHT[ href ] = fetch( url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        } )
            .then( function ( r ) { return r.ok ? r.json() : null; } )
            .then( function ( data ) {
                FETCH_CACHE[ href ] = ( data && ! data.error ) ? data : null;
                delete INFLIGHT[ href ];
                return FETCH_CACHE[ href ];
            } )
            .catch( function () {
                FETCH_CACHE[ href ] = null;
                delete INFLIGHT[ href ];
                return null;
            } );

        return INFLIGHT[ href ];
    }

    /* =========================================
       EVENT-HANDLING (delegation)
       ========================================= */

    function onMouseOver( e ) {
        var a = e.target.closest( 'a[href]' );
        if ( ! a ) return;
        if ( ! isInternal( a ) ) return;
        if ( shouldSkip( a ) ) return;
        if ( ! inScope( a ) ) return;

        clearTimeout( showTimer );
        clearTimeout( hideTimer );

        var href = a.href;
        showTimer = setTimeout( function () {
            fetchPreview( href ).then( function ( data ) {
                if ( ! data ) return;
                // Nur zeigen, wenn die Maus noch auf dem Link ist
                if ( a.matches( ':hover' ) || document.activeElement === a ) {
                    showFor( a, data );
                }
            } );
        }, SHOW_DELAY );
    }

    function onMouseOut( e ) {
        var a = e.target.closest( 'a[href]' );
        if ( ! a ) return;

        clearTimeout( showTimer );
        if ( activeEl === a ) {
            scheduleHide();
        }
    }

    function onFocusIn( e ) {
        var a = e.target.closest( 'a[href]' );
        if ( ! a ) return;
        if ( ! isInternal( a ) ) return;
        if ( shouldSkip( a ) ) return;
        if ( ! inScope( a ) ) return;

        fetchPreview( a.href ).then( function ( data ) {
            if ( ! data ) return;
            if ( document.activeElement === a ) {
                showFor( a, data );
            }
        } );
    }

    function onFocusOut() {
        scheduleHide();
    }

    /* =========================================
       INIT
       ========================================= */

    function init() {
        document.addEventListener( 'mouseover', onMouseOver );
        document.addEventListener( 'mouseout',  onMouseOut );
        document.addEventListener( 'focusin',   onFocusIn );
        document.addEventListener( 'focusout',  onFocusOut );

        window.addEventListener( 'scroll', function () {
            if ( ! activeEl || ! tooltip ) return;
            try {
                if ( tooltip.matches( ':popover-open' ) ) position( activeEl );
            } catch ( e ) {
                if ( tooltip.classList.contains( 'hp-gtt--visible' ) ) position( activeEl );
            }
        }, { passive: true } );

        // ESC schließt
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' ) hide();
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
