/**
 * Hasimuener Journal — Link Preview Popover
 *
 * Zeigt für Glossar-Begriffe und interne redaktionelle Links eine kurze
 * Vorschau an. Auf Desktop wird die Vorschau am Auslöser positioniert, auf
 * Touch-/Mobil-Viewports als Bottom Sheet, damit sie nie außerhalb des
 * sichtbaren Bereichs liegt.
 *
 * Datenquelle: GET /wp-json/hp/v1/link-preview?url=<href>
 *
 * Skip-Regeln:
 *  - Externe URLs (anderes Origin)
 *  - Anchor-Only (#...), mailto:, tel:
 *  - Navigation/Footer/Breadcrumbs
 *  - Klasse .hp-no-preview oder data-no-preview="1"
 *
 * Kein Framework. Kein Build-Step.
 *
 * @package Hasimuener_Journal
 * @version 1.1.0
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

    var TOOLTIP_ID = 'hp-link-preview';
    var DESC_ID    = 'hp-link-preview-desc';

    var tooltip     = null;
    var elTerm      = null;
    var elDef       = null;
    var elMeta      = null;
    var elLink      = null;
    var elClose     = null;
    var activeEl    = null;
    var showTimer   = null;
    var hideTimer   = null;
    var managedFocus = false;
    var openedByActivation = false;

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
        '.main-navigation',
        '.site-header',
        '.site-footer',
        '.hp-breadcrumbs',
        '#hp-gtt',
        '#' + TOOLTIP_ID,
    ];

    function closestFrom( target, selector ) {
        if ( ! target ) return null;
        if ( target.nodeType !== 1 ) {
            target = target.parentElement;
        }
        return target && target.closest ? target.closest( selector ) : null;
    }

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
       POPOVER
       ========================================= */

    function createTooltip() {
        if ( tooltip ) return;

        tooltip = document.createElement( 'div' );
        tooltip.className = 'hp-gtt hp-gtt--link-preview';
        tooltip.id        = TOOLTIP_ID;
        tooltip.setAttribute( 'popover', 'manual' );
        tooltip.setAttribute( 'role', 'dialog' );
        tooltip.setAttribute( 'aria-modal', 'false' );
        tooltip.setAttribute( 'aria-describedby', DESC_ID );
        tooltip.setAttribute( 'tabindex', '-1' );
        tooltip.setAttribute( 'aria-hidden', 'true' );
        tooltip.hidden = true;
        tooltip.innerHTML =
            '<button class="hp-gtt__close" type="button" aria-label="Vorschau schließen">' +
                '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '<strong class="hp-gtt__term"></strong>' +
            '<p class="hp-gtt__def" id="' + DESC_ID + '"></p>' +
            '<span class="hp-gtt__meta"></span>' +
            '<a class="hp-gtt__link" href="#">Weiterlesen →</a>';

        document.body.appendChild( tooltip );

        elTerm  = tooltip.querySelector( '.hp-gtt__term' );
        elDef   = tooltip.querySelector( '.hp-gtt__def' );
        elMeta  = tooltip.querySelector( '.hp-gtt__meta' );
        elLink  = tooltip.querySelector( '.hp-gtt__link' );
        elClose = tooltip.querySelector( '.hp-gtt__close' );

        tooltip.addEventListener( 'mouseenter', function () {
            clearTimeout( hideTimer );
        } );
        tooltip.addEventListener( 'mouseleave', scheduleHide );
        tooltip.addEventListener( 'focusin', function () {
            clearTimeout( hideTimer );
        } );
        tooltip.addEventListener( 'focusout', function ( e ) {
            if ( e.relatedTarget === activeEl || isInsideTooltip( e.relatedTarget ) ) {
                return;
            }
            scheduleHide();
        } );
        tooltip.addEventListener( 'keydown', onTooltipKeyDown );

        elClose.addEventListener( 'click', function () {
            hide( { restoreFocus: true } );
        } );
    }

    function renderPayload( data ) {
        var label = data.type_label || 'Vorschau';
        if ( data.title ) {
            label += ': ' + data.title;
        }

        tooltip.setAttribute( 'aria-label', label );
        elTerm.textContent = data.type_label || '';
        elDef.textContent = '';
        elMeta.textContent = data.meta || '';
        elMeta.style.display = data.meta ? '' : 'none';
        elLink.href = data.url || '#';
        elLink.textContent = data.type === 'glossar' ? 'Im Glossar lesen →' : 'Weiterlesen →';

        // Titel als Sekundär-Info: wenn excerpt vorhanden, Titel als kleinen
        // Vorspann einsetzen — sonst nur excerpt oder Titel als Fallback.
        if ( data.excerpt && data.title ) {
            var titleLine = document.createElement( 'span' );
            var excerptLine = document.createElement( 'span' );

            titleLine.className = 'hp-gtt__title-line';
            excerptLine.className = 'hp-gtt__excerpt-line';
            titleLine.textContent = data.title;
            excerptLine.textContent = data.excerpt;

            elDef.appendChild( titleLine );
            elDef.appendChild( excerptLine );
        } else {
            elDef.textContent = data.excerpt || data.title || '';
        }
    }

    function showFor( el, data, options ) {
        if ( ! tooltip ) createTooltip();

        clearTimeout( hideTimer );
        clearTimeout( showTimer );

        if ( activeEl && activeEl !== el ) {
            setTriggerExpanded( activeEl, false );
        }

        activeEl = el;
        managedFocus = !! ( options && options.focusPreview );
        openedByActivation = !! ( options && options.activation );

        renderPayload( data );
        setTriggerExpanded( el, true );
        openTooltip();
        position( el );

        if ( managedFocus ) {
            window.setTimeout( function () {
                if ( tooltip && isOpen() ) {
                    focusNoScroll( tooltip );
                }
            }, 0 );
        }
    }

    function scheduleHide() {
        clearTimeout( hideTimer );
        hideTimer = setTimeout( hide, HIDE_DELAY );
    }

    function hide( options ) {
        if ( ! tooltip ) return;

        var trigger = activeEl;

        try {
            if ( tooltip.matches( ':popover-open' ) ) {
                tooltip.hidePopover();
            }
        } catch ( e ) {
            tooltip.classList.remove( 'hp-gtt--visible' );
        }

        tooltip.hidden = true;
        tooltip.setAttribute( 'aria-hidden', 'true' );

        tooltip.classList.remove( 'hp-gtt--above', 'hp-gtt--below', 'hp-gtt--sheet' );
        tooltip.style.removeProperty( '--hp-gtt-arrow-left' );
        tooltip.style.top = '';
        tooltip.style.left = '';
        tooltip.style.maxHeight = '';

        setTriggerExpanded( trigger, false );
        activeEl = null;
        managedFocus = false;
        openedByActivation = false;

        if ( options && options.restoreFocus && trigger && typeof trigger.focus === 'function' ) {
            focusNoScroll( trigger );
        }
    }

    function openTooltip() {
        tooltip.hidden = false;
        tooltip.removeAttribute( 'aria-hidden' );

        try {
            if ( ! tooltip.matches( ':popover-open' ) ) {
                tooltip.showPopover();
            }
        } catch ( e ) {
            tooltip.classList.add( 'hp-gtt--visible' );
        }
    }

    function isOpen() {
        if ( ! tooltip ) return false;
        try {
            return tooltip.matches( ':popover-open' ) || tooltip.classList.contains( 'hp-gtt--visible' );
        } catch ( e ) {
            return tooltip.classList.contains( 'hp-gtt--visible' );
        }
    }

    function position( el ) {
        var margin = 12;

        if ( useSheetLayout() ) {
            tooltip.classList.remove( 'hp-gtt--above', 'hp-gtt--below' );
            tooltip.classList.add( 'hp-gtt--sheet' );
            tooltip.style.top = '';
            tooltip.style.left = '';
            tooltip.style.maxHeight = Math.max( 240, Math.floor( window.innerHeight * 0.72 ) ) + 'px';
            tooltip.style.removeProperty( '--hp-gtt-arrow-left' );
            return;
        }

        tooltip.classList.remove( 'hp-gtt--sheet', 'hp-gtt--above', 'hp-gtt--below' );
        tooltip.style.maxHeight = Math.max( 160, window.innerHeight - ( margin * 2 ) ) + 'px';

        var rect       = el.getBoundingClientRect();
        var tW         = tooltip.offsetWidth || 320;
        var tH         = tooltip.offsetHeight || 160;
        var vW         = window.innerWidth;
        var vH         = window.innerHeight;
        var gap        = 12;
        var spaceAbove = rect.top - margin - gap;
        var spaceBelow = vH - rect.bottom - margin - gap;
        var placeBelow = spaceBelow >= tH || spaceBelow > spaceAbove;

        var top  = placeBelow ? rect.bottom + gap : rect.top - tH - gap;
        var left = rect.left + ( rect.width / 2 ) - ( tW / 2 );

        top  = clamp( top, margin, Math.max( margin, vH - tH - margin ) );
        left = clamp( left, margin, Math.max( margin, vW - tW - margin ) );

        var arrowLeft = clamp(
            rect.left + ( rect.width / 2 ) - left,
            18,
            Math.max( 18, tW - 18 )
        );

        tooltip.classList.add( placeBelow ? 'hp-gtt--below' : 'hp-gtt--above' );
        tooltip.style.top = Math.round( top ) + 'px';
        tooltip.style.left = Math.round( left ) + 'px';
        tooltip.style.setProperty( '--hp-gtt-arrow-left', Math.round( arrowLeft ) + 'px' );
    }

    function clamp( value, min, max ) {
        return Math.min( Math.max( value, min ), max );
    }

    function useSheetLayout() {
        if ( window.innerWidth <= 640 ) {
            return true;
        }
        if ( ! window.matchMedia ) {
            return false;
        }
        return window.matchMedia( '(hover: none), (pointer: coarse)' ).matches;
    }

    function isInsideTooltip( node ) {
        return !! ( tooltip && node && tooltip.contains( node ) );
    }

    function focusNoScroll( el ) {
        try {
            el.focus( { preventScroll: true } );
        } catch ( e ) {
            el.focus();
        }
    }

    function focusFirstControl() {
        if ( ! tooltip ) return;
        var control = tooltip.querySelector( 'button, a[href]' );
        if ( control && typeof control.focus === 'function' ) {
            focusNoScroll( control );
        }
    }

    function focusLastControl() {
        if ( ! tooltip ) return;
        var controls = tooltip.querySelectorAll( 'button, a[href]' );
        var control = controls.length ? controls[ controls.length - 1 ] : null;
        if ( control && typeof control.focus === 'function' ) {
            focusNoScroll( control );
        }
    }

    function setTriggerExpanded( el, expanded ) {
        if ( ! isPreviewChip( el ) ) {
            return;
        }

        el.setAttribute( 'aria-haspopup', 'dialog' );
        el.setAttribute( 'aria-controls', TOOLTIP_ID );
        el.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );

        if ( expanded ) {
            el.setAttribute( 'aria-describedby', DESC_ID );
        } else {
            el.removeAttribute( 'aria-describedby' );
        }
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
       TARGET-RESOLUTION (Link ODER Glossar-Chip)
       ========================================= */

    function isPreviewChip( chip ) {
        return !! (
            chip &&
            chip.classList &&
            chip.classList.contains( 'hp-glossar-term' ) &&
            ( chip.dataset.term || chip.dataset.def || chip.dataset.url )
        );
    }

    /**
     * Liefert das hover-fähige Element (a[href] ODER .hp-glossar-term)
     * oder null, wenn nichts Passendes vorliegt.
     */
    function resolveTarget( eventTarget ) {
        var chip = closestFrom( eventTarget, '.hp-glossar-term' );
        if ( isPreviewChip( chip ) ) {
            return chip;
        }

        var a = closestFrom( eventTarget, 'a[href]' );
        if ( ! a ) return null;
        if ( ! isInternal( a ) ) return null;
        if ( shouldSkip( a ) ) return null;
        if ( ! inScope( a ) ) return null;
        return a;
    }

    /**
     * Liefert Preview-Payload für einen Glossar-Chip aus seinen
     * Inline-Data-Attributen — ohne Netzwerk-Roundtrip.
     */
    function payloadFromChip( chip ) {
        return {
            id:         0,
            type:       'glossar',
            type_label: 'Glossar',
            title:      chip.dataset.term || chip.textContent.trim(),
            excerpt:    chip.dataset.def || '',
            url:        chip.dataset.url || chip.href || '#',
            meta:       '',
        };
    }

    /* =========================================
       EVENT-HANDLING (delegation)
       ========================================= */

    function onMouseOver( e ) {
        if ( useSheetLayout() ) return;

        var target = resolveTarget( e.target );
        if ( ! target ) return;

        clearTimeout( showTimer );
        clearTimeout( hideTimer );

        // Glossar-Chip: sofort verfügbare Inline-Daten
        if ( isPreviewChip( target ) ) {
            var chipData = payloadFromChip( target );
            showTimer = setTimeout( function () {
                if ( target.matches( ':hover' ) || document.activeElement === target ) {
                    showFor( target, chipData, { focusPreview: false, activation: false } );
                }
            }, SHOW_DELAY );
            return;
        }

        // Regulärer Link: REST-Fetch
        var href = target.href;
        showTimer = setTimeout( function () {
            fetchPreview( href ).then( function ( data ) {
                if ( ! data ) return;
                if ( target.matches( ':hover' ) || document.activeElement === target ) {
                    showFor( target, data, { focusPreview: false, activation: false } );
                }
            } );
        }, SHOW_DELAY );
    }

    function onMouseOut( e ) {
        var target = closestFrom( e.target, 'a[href], .hp-glossar-term' );
        if ( ! target ) return;
        if ( e.relatedTarget && ( target.contains( e.relatedTarget ) || isInsideTooltip( e.relatedTarget ) ) ) {
            return;
        }

        clearTimeout( showTimer );
        if ( activeEl === target ) {
            scheduleHide();
        }
    }

    function onFocusIn( e ) {
        if ( isInsideTooltip( e.target ) ) {
            clearTimeout( hideTimer );
            return;
        }

        var target = resolveTarget( e.target );
        if ( ! target ) return;

        if ( isPreviewChip( target ) ) {
            if ( document.activeElement === target && ! useSheetLayout() ) {
                showFor( target, payloadFromChip( target ), { focusPreview: false, activation: false } );
            }
            return;
        }

        fetchPreview( target.href ).then( function ( data ) {
            if ( ! data ) return;
            if ( document.activeElement === target ) {
                showFor( target, data, { focusPreview: false, activation: false } );
            }
        } );
    }

    function onFocusOut( e ) {
        if ( e.relatedTarget === activeEl || isInsideTooltip( e.relatedTarget ) ) {
            return;
        }
        scheduleHide();
    }

    function onClick( e ) {
        if ( isInsideTooltip( e.target ) ) {
            return;
        }

        var target = resolveTarget( e.target );
        if ( ! target ) {
            if ( isOpen() ) {
                hide();
            }
            return;
        }

        if ( ! isPreviewChip( target ) ) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        if ( activeEl === target && isOpen() && openedByActivation ) {
            hide( { restoreFocus: false } );
            return;
        }

        showFor( target, payloadFromChip( target ), { focusPreview: true, activation: true } );
    }

    function onKeyDown( e ) {
        if ( e.key === 'Escape' ) {
            hide( { restoreFocus: true } );
            return;
        }

        if (
            e.key === 'Tab' &&
            activeEl &&
            isPreviewChip( activeEl ) &&
            isOpen() &&
            document.activeElement === activeEl &&
            ! e.shiftKey
        ) {
            e.preventDefault();
            focusFirstControl();
            return;
        }

        var target = resolveTarget( e.target );
        if ( ! isPreviewChip( target ) ) {
            return;
        }

        if ( e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar' ) {
            e.preventDefault();
            showFor( target, payloadFromChip( target ), { focusPreview: true, activation: true } );
        }
    }

    function onTooltipKeyDown( e ) {
        if ( e.key === 'Escape' ) {
            hide( { restoreFocus: true } );
            return;
        }

        if ( ! managedFocus || e.key !== 'Tab' ) {
            return;
        }

        var controls = tooltip.querySelectorAll( 'button, a[href]' );
        if ( ! controls.length ) {
            e.preventDefault();
            return;
        }

        var first = controls[ 0 ];
        var last = controls[ controls.length - 1 ];

        if ( e.shiftKey && document.activeElement === first ) {
            e.preventDefault();
            focusLastControl();
        } else if ( ! e.shiftKey && document.activeElement === last ) {
            e.preventDefault();
            focusFirstControl();
        }
    }

    function prepareGlossaryTriggers() {
        var terms = document.querySelectorAll( '.hp-glossar-term' );

        terms.forEach( function ( el ) {
            if ( ! isPreviewChip( el ) ) {
                return;
            }

            if ( ! el.matches( 'a[href]' ) ) {
                el.setAttribute( 'role', 'button' );
                if ( ! el.hasAttribute( 'tabindex' ) ) {
                    el.setAttribute( 'tabindex', '0' );
                }
            }

            el.setAttribute( 'aria-haspopup', 'dialog' );
            el.setAttribute( 'aria-expanded', 'false' );
            el.setAttribute( 'aria-controls', TOOLTIP_ID );
            el.removeAttribute( 'aria-describedby' );
        } );
    }

    function repositionActive() {
        if ( activeEl && tooltip && isOpen() ) {
            position( activeEl );
        }
    }

    /* =========================================
       INIT
       ========================================= */

    function init() {
        createTooltip();
        prepareGlossaryTriggers();

        document.addEventListener( 'mouseover', onMouseOver );
        document.addEventListener( 'mouseout',  onMouseOut );
        document.addEventListener( 'focusin',   onFocusIn );
        document.addEventListener( 'focusout',  onFocusOut );
        document.addEventListener( 'click',     onClick );
        document.addEventListener( 'keydown',   onKeyDown );

        window.addEventListener( 'scroll', repositionActive, { passive: true } );
        window.addEventListener( 'resize', repositionActive, { passive: true } );

        if ( window.visualViewport ) {
            window.visualViewport.addEventListener( 'resize', repositionActive, { passive: true } );
            window.visualViewport.addEventListener( 'scroll', repositionActive, { passive: true } );
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
