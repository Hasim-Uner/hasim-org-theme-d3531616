/**
 * Wissensgraph — D3.js Force-Directed Graph
 *
 * Immersive, ganzseitige Visualisierung der Beziehungen zwischen
 * Essays, Notizen, Dossiers, Glossar-Einträgen und Themenfeldern.
 *
 * Abhängigkeit: D3.js v7 (lokal im Theme eingebunden).
 * Daten: REST-Endpoint /wp-json/hp/v1/graph
 *
 * @package Hasimuener_Journal
 * @since   6.1.0
 */

( function() {
	'use strict';

	/* =========================================
	   KONFIGURATION
	   ========================================= */

	var CONFIG = {
		colors: {
			essay:   '#e8574b',
			note:    '#8b95a5',
			dossier: '#44c28d',
			glossar: '#4da6e8',
			topic:   '#e8c94b',
		},
		glowColors: {
			essay:   'rgba(232,87,75,0.6)',
			note:    'rgba(139,149,165,0.5)',
			dossier: 'rgba(68,194,141,0.58)',
			glossar: 'rgba(77,166,232,0.6)',
			topic:   'rgba(232,201,75,0.6)',
		},
		edgeStyles: {
			topic_membership:      '8,4',   // dashed: content↔topic
			shared_topic:          '',       // solid: nodes sharing topic
			glossar_in_content:    '4,3',   // dotted: glossar in content
			dossier_has_part:      '10,3',  // curated reading path
			dossier_mentions_term: '2,4',   // dossier term apparatus
		},
		typeLabels: {
			essay:   'Essay',
			note:    'Notiz',
			dossier: 'Dossier',
			glossar: 'Glossar',
			topic:   'Themenfeld',
		},
		edgeLabels: {
			topic_membership:      'Themenfeld',
			shared_topic:          'gemeinsames Thema',
			glossar_in_content:    'Begriff im Text',
			dossier_has_part:      'Teil des Leseplans',
			dossier_mentions_term: 'Begriff im Dossier',
		},
		typeOrder: [ 'essay', 'note', 'dossier', 'glossar', 'topic' ],
		edgeColor:      'rgba(255,255,255,0.12)',
		edgeHighlight:  'rgba(255,255,255,0.55)',
		dimOpacity:     0.15,
		dimEdgeOpacity: 0.03,
		minRadius:      8,
		maxRadius:      32,
		labelOffset:    6,
		labelOpacity:   0.12,
		labelOpacityStrong: 0.28,
		labelMinLinks:  4,
		mobileBreak:    768,
		// Force-Simulation
		chargeStrength: -400,
		linkDistBase:   100,
		linkDistTopic:  140,
	};

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* =========================================
	   STATE
	   ========================================= */

	var state = {
		nodes:        [],
		edges:        [],
		activeTypes:  {},
		searchTerm:   '',
		selectedNode: null,
		simulation:   null,
		svg:          null,
		g:            null,
		zoom:         null,
		linkSel:      null,
		nodeSel:      null,
		labelSel:     null,
		glowSel:      null,
		width:        0,
		height:       0,
	};

	resetActiveTypes();

	/* =========================================
	   INITIALISIERUNG
	   ========================================= */

	function init() {
		var canvas  = document.getElementById( 'hp-graph-canvas' );
		var graphConfig = window.hpGraph || {};

		if ( ! canvas ) { return; }
		canvas.classList.add( 'is-loading' );
		canvas.classList.remove( 'is-error', 'is-ready', 'is-empty' );

		if ( typeof d3 === 'undefined' ) {
			showError( 'Die lokale D3-Datei konnte nicht geladen werden.' );
			return;
		}

		if ( graphConfig.data && Array.isArray( graphConfig.data.nodes ) && Array.isArray( graphConfig.data.edges ) ) {
			applyGraphData( graphConfig.data );
			return;
		}

		if ( ! graphConfig.restUrl ) {
			showError( 'Es wurde keine lokale Datenquelle fuer den Graph gefunden.' );
			return;
		}

		fetch( graphConfig.restUrl, { credentials: 'same-origin' } )
			.then( function( resp ) {
				if ( ! resp.ok ) {
					throw new Error( 'HTTP ' + resp.status );
				}
				return resp.json();
			} )
			.then( function( json ) {
				window.hpGraph = graphConfig;
				window.hpGraph.data = json;
				applyGraphData( json );
			} )
			.catch( function( err ) {
				console.error( '[hp-graph] REST fallback failed:', err );
				showError( 'Die Graph-Daten konnten lokal nicht geladen werden.' );
			} );
	}

	function applyGraphData( data ) {
		var canvas  = document.getElementById( 'hp-graph-canvas' );
		var loading = document.getElementById( 'hp-graph-loading' );
		var error   = document.getElementById( 'hp-graph-error' );

		if ( ! canvas ) { return; }

		bindControls();

		if ( loading ) { loading.hidden = true; }
		if ( error )   { error.hidden = true; }
		canvas.classList.remove( 'is-loading', 'is-error' );
		canvas.classList.add( 'is-ready' );

		state.nodes = data.nodes || [];
		state.edges = data.edges || [];

		if ( state.nodes.length === 0 ) {
			var empty = document.createElement( 'div' );
			var status = data.meta && data.meta.status ? data.meta.status : 'ready';
			var message = 'Noch keine Inhalte für den Wissensgraph vorhanden.';

			if ( status === 'pending' || status === 'stale' ) {
				message = 'Der Wissensgraph wird gerade vorbereitet. Bitte in Kürze erneut laden.';
			} else if ( status === 'error' ) {
				message = 'Der Wissensgraph konnte noch nicht vorbereitet werden.';
			}

			empty.className = 'hp-graph__loading hp-graph__loading--empty';
			empty.innerHTML = '<p>' + escHtml( message ) + '</p>';
			canvas.classList.add( 'is-empty' );
			canvas.appendChild( empty );
			updateSRSummary();
			return;
		}

		canvas.classList.remove( 'is-empty' );
		buildGraph();
		updateSRSummary();
	}

	function showError( message ) {
		var canvas  = document.getElementById( 'hp-graph-canvas' );
		var loading = document.getElementById( 'hp-graph-loading' );
		var error   = document.getElementById( 'hp-graph-error' );
		var text    = error ? error.querySelector( 'p' ) : null;
		var summaryEl = document.getElementById( 'hp-graph-summary' );
		var nodesEl   = document.getElementById( 'hp-graph-stat-nodes' );
		var edgesEl   = document.getElementById( 'hp-graph-stat-edges' );

		if ( canvas ) {
			canvas.classList.remove( 'is-loading', 'is-ready' );
			canvas.classList.add( 'is-error' );
		}
		if ( loading ) { loading.hidden = true; }
		if ( text && message ) { text.textContent = message; }
		if ( error ) { error.hidden = false; }
		if ( summaryEl && message ) { summaryEl.textContent = message; }
		if ( nodesEl ) { nodesEl.textContent = '0'; }
		if ( edgesEl ) { edgesEl.textContent = '0'; }
	}

	/* =========================================
	   GRAPH AUFBAUEN
	   ========================================= */

	function buildGraph() {
		var canvas = document.getElementById( 'hp-graph-canvas' );
		state.width  = canvas.clientWidth;
		state.height = canvas.clientHeight;

		// Node-Verbindungsanzahl berechnen
		var linkCount = {};
		state.edges.forEach( function( e ) {
			linkCount[ e.source ] = ( linkCount[ e.source ] || 0 ) + 1;
			linkCount[ e.target ] = ( linkCount[ e.target ] || 0 ) + 1;
		} );

		var maxLinks = 1;
		state.nodes.forEach( function( n ) {
			var c = linkCount[ n.id ] || 0;
			if ( c > maxLinks ) { maxLinks = c; }
		} );

		var radiusScale = d3.scaleSqrt()
			.domain( [ 0, maxLinks ] )
			.range( [ CONFIG.minRadius, CONFIG.maxRadius ] );

		state.nodes.forEach( function( n ) {
			n._linkCount = linkCount[ n.id ] || 0;
			n._radius    = radiusScale( n._linkCount );
		} );

		// SVG anlegen
		state.svg = d3.select( '#hp-graph-canvas' )
			.append( 'svg' )
			.attr( 'width', '100%' )
			.attr( 'height', '100%' )
			.attr( 'viewBox', '0 0 ' + state.width + ' ' + state.height )
			.attr( 'role', 'img' )
			.attr( 'aria-label', 'Wissensgraph: Interaktive Netzwerk-Visualisierung' );

		// SVG-Defs: Glow-Filter pro Typ
		var defs = state.svg.append( 'defs' );

		Object.keys( CONFIG.glowColors ).forEach( function( type ) {
			var filter = defs.append( 'filter' )
				.attr( 'id', 'glow-' + type )
				.attr( 'x', '-50%' )
				.attr( 'y', '-50%' )
				.attr( 'width', '200%' )
				.attr( 'height', '200%' );

			filter.append( 'feGaussianBlur' )
				.attr( 'stdDeviation', '4' )
				.attr( 'result', 'blur' );

			filter.append( 'feFlood' )
				.attr( 'flood-color', CONFIG.glowColors[ type ] )
				.attr( 'result', 'color' );

			filter.append( 'feComposite' )
				.attr( 'in', 'color' )
				.attr( 'in2', 'blur' )
				.attr( 'operator', 'in' )
				.attr( 'result', 'glow' );

			var merge = filter.append( 'feMerge' );
			merge.append( 'feMergeNode' ).attr( 'in', 'glow' );
			merge.append( 'feMergeNode' ).attr( 'in', 'SourceGraphic' );
		} );

		// Zoom-Verhalten
		state.zoom = d3.zoom()
			.scaleExtent( [ 0.3, 5 ] )
			.on( 'zoom', function( event ) {
				state.g.attr( 'transform', event.transform );
			} );

		state.svg.call( state.zoom );

		// Container-Gruppe
		state.g = state.svg.append( 'g' );

		// Edges
		state.linkSel = state.g.append( 'g' )
			.attr( 'class', 'hp-graph__edges' )
			.selectAll( 'line' )
			.data( state.edges )
			.enter()
			.append( 'line' )
			.attr( 'stroke', CONFIG.edgeColor )
			.attr( 'stroke-width', function( d ) { return Math.max( 1, d.weight || 1 ); } )
			.attr( 'stroke-opacity', 0.6 )
			.attr( 'stroke-dasharray', function( d ) {
				return CONFIG.edgeStyles[ d.type ] || '';
			} );

		// Node-Gruppen
		state.nodeSel = state.g.append( 'g' )
			.attr( 'class', 'hp-graph__nodes' )
			.selectAll( 'g' )
			.data( state.nodes )
			.enter()
			.append( 'g' )
			.attr( 'class', 'hp-graph__node' )
			.attr( 'tabindex', '0' )
			.attr( 'role', 'button' )
			.attr( 'aria-label', function( d ) {
				return getTypeLabel( d.type ) + ': ' + d.label +
					' (' + d._linkCount + ' Verbindung' + ( d._linkCount !== 1 ? 'en' : '' ) + ')';
			} );

		// Glow-Kreis (hinter dem eigentlichen Kreis)
		state.glowSel = state.nodeSel.append( 'circle' )
			.attr( 'r', function( d ) { return d._radius + 4; } )
			.attr( 'fill', function( d ) { return CONFIG.colors[ d.type ] || '#999'; } )
			.attr( 'opacity', 0.35 )
			.attr( 'filter', function( d ) { return 'url(#glow-' + d.type + ')'; } )
			.attr( 'class', 'hp-graph__glow' )
			.style( 'animation-delay', function( _, i ) {
				// Versetzt Pulse-Animation organisch, jeder Node leicht anders
				return ( ( i * 0.37 ) % 2.8 ).toFixed( 2 ) + 's';
			} );

		// Hauptkreis
		state.nodeSel.append( 'circle' )
			.attr( 'r', function( d ) { return d._radius; } )
			.attr( 'fill', function( d ) { return CONFIG.colors[ d.type ] || '#999'; } )
			.attr( 'stroke', 'rgba(255,255,255,0.25)' )
			.attr( 'stroke-width', 1.5 )
			.attr( 'class', 'hp-graph__circle' );

		// Labels — immer sichtbar
		state.labelSel = state.nodeSel.append( 'text' )
			.text( function( d ) { return d.label; } )
			.attr( 'class', 'hp-graph__label' )
			.attr( 'dy', function( d ) { return -d._radius - CONFIG.labelOffset; } )
			.attr( 'text-anchor', 'middle' )
			.attr( 'opacity', function( d ) { return getBaseLabelOpacity( d ); } )
			.attr( 'aria-hidden', 'true' );

		// Interaktionen
		state.nodeSel
			.on( 'mouseenter', handleNodeHover )
			.on( 'mousemove', function( event, d ) { moveTooltip( event ); } )
			.on( 'mouseleave', handleNodeUnhover )
			.on( 'click', handleNodeClick )
			.on( 'keydown', function( event, d ) {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					handleNodeClick( event, d );
				}
			} )
			.on( 'focus', handleNodeHover )
			.on( 'blur', handleNodeUnhover );

		// Drag
		var drag = d3.drag()
			.on( 'start', dragStart )
			.on( 'drag', dragging )
			.on( 'end', dragEnd );

		state.nodeSel.call( drag );

		// Force-Simulation
		state.simulation = d3.forceSimulation( state.nodes )
			.force( 'link', d3.forceLink( state.edges )
				.id( function( d ) { return d.id; } )
				.distance( function( d ) {
					var src = typeof d.source === 'object' ? d.source : findNode( d.source );
					var tgt = typeof d.target === 'object' ? d.target : findNode( d.target );
					if ( ( src && src.type === 'topic' ) || ( tgt && tgt.type === 'topic' ) ) {
						return CONFIG.linkDistTopic;
					}
					if ( ( src && src.type === 'dossier' ) || ( tgt && tgt.type === 'dossier' ) ) {
						return CONFIG.linkDistBase + 24;
					}
					return CONFIG.linkDistBase;
				} )
			)
			.force( 'charge', d3.forceManyBody().strength( CONFIG.chargeStrength ) )
			.force( 'center', d3.forceCenter( state.width / 2, state.height / 2 ) )
			.force( 'collision', d3.forceCollide().radius( function( d ) { return d._radius + 4; } ) )
			.force( 'x', d3.forceX( state.width / 2 ).strength( 0.04 ) )
			.force( 'y', d3.forceY( state.height / 2 ).strength( 0.04 ) )
			.on( 'tick', ticked );

		if ( prefersReducedMotion ) {
			state.simulation.alpha( 1 ).alphaDecay( 0.05 );
			for ( var i = 0; i < 300; i++ ) { state.simulation.tick(); }
			state.simulation.stop();
			ticked();
			zoomToFit();
		} else {
			state.simulation.on( 'end', function() {
				state.simulation.stop();
				zoomToFit();
			} );
		}

		// Zoom-Buttons
		bindZoomButtons();

		// Resize
		window.addEventListener( 'resize', debounce( handleResize, 250 ) );
	}

	/* =========================================
	   ZOOM TO FIT
	   ========================================= */

	function zoomToFit() {
		if ( ! state.svg || ! state.nodes.length ) { return; }

		var xMin = Infinity, xMax = -Infinity;
		var yMin = Infinity, yMax = -Infinity;

		state.nodes.forEach( function( n ) {
			var r = n._radius || 0;
			if ( n.x - r < xMin ) { xMin = n.x - r; }
			if ( n.x + r > xMax ) { xMax = n.x + r; }
			if ( n.y - r < yMin ) { yMin = n.y - r; }
			if ( n.y + r > yMax ) { yMax = n.y + r; }
		} );

		var pad = 60;
		var bw = xMax - xMin + pad * 2;
		var bh = yMax - yMin + pad * 2;
		var scale = Math.min( state.width / bw, state.height / bh, 1.5 );
		var tx = ( state.width - bw * scale ) / 2 - ( xMin - pad ) * scale;
		var ty = ( state.height - bh * scale ) / 2 - ( yMin - pad ) * scale;

		var t = d3.zoomIdentity.translate( tx, ty ).scale( scale );

		if ( prefersReducedMotion ) {
			state.svg.call( state.zoom.transform, t );
		} else {
			state.svg.transition().duration( 800 ).call( state.zoom.transform, t );
		}
	}

	/* =========================================
	   SIMULATION TICK
	   ========================================= */

	function ticked() {
		state.linkSel
			.attr( 'x1', function( d ) { return d.source.x; } )
			.attr( 'y1', function( d ) { return d.source.y; } )
			.attr( 'x2', function( d ) { return d.target.x; } )
			.attr( 'y2', function( d ) { return d.target.y; } );

		state.nodeSel
			.attr( 'transform', function( d ) {
				return 'translate(' + d.x + ',' + d.y + ')';
			} );
	}

	/* =========================================
	   HOVER / FOKUS
	   ========================================= */

	function handleNodeHover( event, d ) {
		var connected = getConnectedIds( d.id );

		// Nicht-verbundene dimmen (mit sanfter Transition)
		state.nodeSel
			.transition().duration( 180 )
			.attr( 'opacity', function( n ) {
				return ( n.id === d.id || connected[ n.id ] ) ? 1 : CONFIG.dimOpacity;
			} );

		// Glow bei Hover verstärken
		state.glowSel
			.transition().duration( 180 )
			.attr( 'opacity', function( n ) {
				return ( n.id === d.id || connected[ n.id ] ) ? 0.8 : 0.05;
			} );

		state.linkSel
			.transition().duration( 180 )
			.attr( 'stroke', function( e ) {
				var src = typeof e.source === 'object' ? e.source.id : e.source;
				var tgt = typeof e.target === 'object' ? e.target.id : e.target;
				return ( src === d.id || tgt === d.id ) ? CONFIG.edgeHighlight : CONFIG.edgeColor;
			} )
			.attr( 'stroke-opacity', function( e ) {
				var src = typeof e.source === 'object' ? e.source.id : e.source;
				var tgt = typeof e.target === 'object' ? e.target.id : e.target;
				return ( src === d.id || tgt === d.id ) ? 1 : CONFIG.dimEdgeOpacity;
			} )
			.attr( 'stroke-width', function( e ) {
				var src = typeof e.source === 'object' ? e.source.id : e.source;
				var tgt = typeof e.target === 'object' ? e.target.id : e.target;
				var base = Math.max( 1, e.weight || 1 );
				return ( src === d.id || tgt === d.id ) ? base + 1.5 : base;
			} );

		// Labels: connected voll, rest schwach
		state.labelSel
			.transition().duration( 180 )
			.attr( 'opacity', function( n ) {
				return ( n.id === d.id || connected[ n.id ] ) ? 1 : 0.1;
			} );

		// Tooltip anzeigen
		showTooltip( event, d );
	}

	function handleNodeUnhover() {
		state.nodeSel.transition().duration( 250 ).attr( 'opacity', 1 );
		state.glowSel.transition().duration( 250 ).attr( 'opacity', 0.35 );
		state.linkSel
			.transition().duration( 250 )
			.attr( 'stroke', CONFIG.edgeColor )
			.attr( 'stroke-opacity', 0.6 )
			.attr( 'stroke-width', function( d ) { return Math.max( 1, d.weight || 1 ); } );
		state.labelSel
			.transition().duration( 250 )
			.attr( 'opacity', function( d ) { return getBaseLabelOpacity( d ); } );
		hideTooltip();
	}

	/* =========================================
	   CLICK → DETAIL-PANEL
	   ========================================= */

	function handleNodeClick( event, d ) {
		event.stopPropagation();
		hideTooltip();
		state.selectedNode = d;
		showDetail( d );
	}

	function showDetail( d ) {
		var panel   = document.getElementById( 'hp-graph-detail' );
		var content = document.getElementById( 'hp-graph-detail-content' );
		var shell   = document.querySelector( '.hp-graph__canvas-shell' );
		if ( ! panel || ! content ) { return; }

		var html = '';

		html += '<span class="hp-graph__detail-type hp-graph__detail-type--' + escHtml( d.type ) + '">' + escHtml( getTypeLabel( d.type ) ) + '</span>';
		html += '<h2 class="hp-graph__detail-title">' + escHtml( d.label ) + '</h2>';

		if ( d.meta ) {
			if ( d.meta.reading_time ) {
				html += '<p class="hp-graph__detail-meta">' + escHtml( d.meta.reading_time );
				if ( d.meta.date ) { html += ' · ' + escHtml( d.meta.date ); }
				html += '</p>';
			}
			if ( d.meta.excerpt ) {
				html += '<p class="hp-graph__detail-excerpt">' + escHtml( d.meta.excerpt ) + '</p>';
			}
			if ( d.meta.kurz ) {
				html += '<p class="hp-graph__detail-excerpt">' + escHtml( d.meta.kurz ) + '</p>';
			}
			if ( d.meta.intro ) {
				html += '<p class="hp-graph__detail-excerpt">' + escHtml( d.meta.intro ) + '</p>';
			}
			if ( d.meta.description ) {
				html += '<p class="hp-graph__detail-excerpt">' + escHtml( d.meta.description ) + '</p>';
			}
			if ( d.meta.count !== undefined ) {
				html += '<p class="hp-graph__detail-meta">' + escHtml( d.meta.count ) + ' Beiträge</p>';
			}
			if ( d.type === 'dossier' ) {
				html += '<div class="hp-graph__detail-facts">';
				html += '<span><strong>' + escHtml( d.meta.entries_count || 0 ) + '</strong> Beiträge</span>';
				html += '<span><strong>' + escHtml( d.meta.terms_count || 0 ) + '</strong> Begriffe</span>';
				if ( d.meta.version ) {
					html += '<span><strong>v' + escHtml( d.meta.version ) + '</strong> Version</span>';
				}
				html += '</div>';
			}
		}

		var connected = getConnectedEntries( d.id );
		var visibleConnected = connected.slice( 0, 10 );
		if ( connected.length > 0 ) {
			html += '<h3 class="hp-graph__detail-subtitle">Verbindungen · ' + connected.length + '</h3>';
			html += '<ul class="hp-graph__detail-links">';
			visibleConnected.forEach( function( entry ) {
				var n = entry.node;
				html += '<li>';
				html += '<a href="' + escAttr( n.url ) + '" class="hp-graph__detail-link">';
				html += '<span class="hp-graph__detail-link-dot hp-graph__detail-link-dot--' + escHtml( n.type ) + '" aria-hidden="true"></span>';
				html += '<span class="hp-graph__detail-link-copy">';
				html += '<span class="hp-graph__detail-link-label">' + escHtml( n.label ) + '</span>';
				html += '<span class="hp-graph__detail-link-relation">' + escHtml( entry.relationship ) + '</span>';
				html += '</span>';
				html += '</a></li>';
			} );
			html += '</ul>';
			if ( connected.length > visibleConnected.length ) {
				html += '<p class="hp-graph__detail-more">+' + ( connected.length - visibleConnected.length ) + ' weitere Verbindungen im Netz</p>';
			}
		}

		if ( d.url ) {
			html += '<a href="' + escAttr( d.url ) + '" class="hp-graph__detail-cta">' + escHtml( getOpenLabel( d.type ) ) + ' →</a>';
		}

		content.innerHTML = html;
		panel.hidden = false;
		if ( shell ) { shell.classList.add( 'has-detail' ); }
		panel.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function hideDetail() {
		var panel = document.getElementById( 'hp-graph-detail' );
		var shell = document.querySelector( '.hp-graph__canvas-shell' );
		if ( panel ) { panel.hidden = true; }
		if ( shell ) { shell.classList.remove( 'has-detail' ); }
		state.selectedNode = null;
	}

	/* =========================================
	   FILTER
	   ========================================= */

	function applyFilter() {
		if ( ! state.nodeSel || ! state.linkSel ) { return; }

		var visibleCount = 0;

		state.nodeSel.each( function( d ) {
			var visible = isNodeVisible( d );
			if ( visible ) {
				visibleCount++;
			}
			d3.select( this )
				.attr( 'visibility', visible ? 'visible' : 'hidden' )
				.classed( 'hp-graph__node--match', Boolean( state.searchTerm && visible ) );
		} );

		state.linkSel.each( function( e ) {
			var src = typeof e.source === 'object' ? e.source : findNode( e.source );
			var tgt = typeof e.target === 'object' ? e.target : findNode( e.target );
			var visible = src && tgt && isNodeVisible( src ) && isNodeVisible( tgt );
			d3.select( this ).attr( 'visibility', visible ? 'visible' : 'hidden' );
		} );

		if ( state.selectedNode && ! isNodeVisible( state.selectedNode ) ) {
			hideDetail();
		}

		updateSearchControls();
		updateEmptyState( visibleCount );
		updateSRSummary();
	}

	/* =========================================
	   DRAG
	   ========================================= */

	function dragStart( event, d ) {
		if ( ! event.active ) { state.simulation.alphaTarget( 0.3 ).restart(); }
		d.fx = d.x;
		d.fy = d.y;
	}

	function dragging( event, d ) {
		d.fx = event.x;
		d.fy = event.y;
	}

	function dragEnd( event, d ) {
		if ( ! event.active ) { state.simulation.alphaTarget( 0 ); }
		d.fx = null;
		d.fy = null;
	}

	/* =========================================
	   ZOOM-BUTTONS
	   ========================================= */

	function bindZoomButtons() {
		var zoomIn    = document.getElementById( 'hp-graph-zoom-in' );
		var zoomOut   = document.getElementById( 'hp-graph-zoom-out' );
		var zoomReset = document.getElementById( 'hp-graph-zoom-reset' );

		if ( zoomIn ) {
			zoomIn.addEventListener( 'click', function() {
				state.svg.transition().duration( 300 ).call( state.zoom.scaleBy, 1.4 );
			} );
		}
		if ( zoomOut ) {
			zoomOut.addEventListener( 'click', function() {
				state.svg.transition().duration( 300 ).call( state.zoom.scaleBy, 0.7 );
			} );
		}
		if ( zoomReset ) {
			zoomReset.addEventListener( 'click', function() {
				zoomToFit();
			} );
		}
	}

	function resetGraphView() {
		var searchInput = document.getElementById( 'hp-graph-search' );
		var filters = document.querySelectorAll( '.hp-graph__filter' );

		state.searchTerm = '';
		resetActiveTypes();

		if ( searchInput ) {
			searchInput.value = '';
		}

		filters.forEach( function( btn ) {
			btn.setAttribute( 'aria-pressed', 'true' );
			btn.classList.add( 'hp-graph__filter--active' );
		} );

		hideDetail();
		applyFilter();
		zoomToFit();
	}

	/* =========================================
	   CONTROLS BINDEN
	   ========================================= */

	function bindControls() {
		var filters = document.querySelectorAll( '.hp-graph__filter' );
		filters.forEach( function( btn ) {
			btn.addEventListener( 'click', function() {
				var type   = btn.getAttribute( 'data-type' );
				var active = ! state.activeTypes[ type ];
				state.activeTypes[ type ] = active;
				btn.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
				btn.classList.toggle( 'hp-graph__filter--active', active );
				applyFilter();
			} );
		} );

		var searchInput = document.getElementById( 'hp-graph-search' );
		var searchClear = document.getElementById( 'hp-graph-search-clear' );
		var resetBtn = document.getElementById( 'hp-graph-reset' );
		var emptyReset = document.getElementById( 'hp-graph-empty-reset' );

		if ( searchInput ) {
			searchInput.addEventListener( 'input', debounce( function() {
				state.searchTerm = normalizeSearch( searchInput.value );
				applyFilter();
			}, 80 ) );

			searchInput.addEventListener( 'keydown', function( e ) {
				if ( e.key !== 'Enter' ) {
					return;
				}

				var first = getVisibleNodes()[0];
				if ( first ) {
					e.preventDefault();
					state.selectedNode = first;
					showDetail( first );
					focusNode( first.id );
				}
			} );
		}

		if ( searchClear ) {
			searchClear.addEventListener( 'click', function() {
				state.searchTerm = '';
				if ( searchInput ) {
					searchInput.value = '';
					searchInput.focus();
				}
				applyFilter();
			} );
		}

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', resetGraphView );
		}

		if ( emptyReset ) {
			emptyReset.addEventListener( 'click', resetGraphView );
		}

		var closeBtn = document.getElementById( 'hp-graph-detail-close' );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', hideDetail );
		}

		document.addEventListener( 'keydown', function( e ) {
			if ( e.key === 'Escape' ) { hideDetail(); }
		} );

		var canvas = document.getElementById( 'hp-graph-canvas' );
		if ( canvas ) {
			canvas.addEventListener( 'click', function( e ) {
				if ( e.target === canvas || e.target.tagName === 'svg' ) {
					hideDetail();
				}
			} );
		}
	}

	/* =========================================
	   RESIZE
	   ========================================= */

	function handleResize() {
		var canvas = document.getElementById( 'hp-graph-canvas' );
		if ( ! canvas || ! state.svg ) { return; }

		state.width  = canvas.clientWidth;
		state.height = canvas.clientHeight;

		state.svg.attr( 'viewBox', '0 0 ' + state.width + ' ' + state.height );

		if ( state.simulation ) {
			state.simulation
				.force( 'center', d3.forceCenter( state.width / 2, state.height / 2 ) )
				.force( 'x', d3.forceX( state.width / 2 ).strength( 0.04 ) )
				.force( 'y', d3.forceY( state.height / 2 ).strength( 0.04 ) );
			state.simulation.alpha( 0.3 ).restart();
		}
	}

	/* =========================================
	   TOOLTIP
	   ========================================= */

	function showTooltip( event, d ) {
		var tt = document.getElementById( 'hp-graph-tooltip' );
		if ( ! tt ) { return; }
		var label = getTypeLabel( d.type );
		tt.innerHTML =
			'<span class="hp-graph__tooltip-badge hp-graph__tooltip-badge--' + escHtml( d.type ) + '">' +
			escHtml( label ) + '</span>' +
			'<span class="hp-graph__tooltip-label">' + escHtml( d.label ) + '</span>';
		tt.hidden = false;
		moveTooltip( event );
	}

	function moveTooltip( event ) {
		var tt = document.getElementById( 'hp-graph-tooltip' );
		if ( ! tt || tt.hidden ) { return; }
		var canvas = document.getElementById( 'hp-graph-canvas' );
		if ( ! canvas ) { return; }
		var rect = canvas.getBoundingClientRect();
		var x = event.clientX - rect.left + 16;
		var y = event.clientY - rect.top - 38;
		// Rechts-Overflow verhindern
		if ( x + 200 > rect.width ) { x = event.clientX - rect.left - 220; }
		tt.style.left = x + 'px';
		tt.style.top  = y + 'px';
	}

	function hideTooltip() {
		var tt = document.getElementById( 'hp-graph-tooltip' );
		if ( tt ) { tt.hidden = true; }
	}

	/* =========================================
	   SR ZUSAMMENFASSUNG
	   ========================================= */

	function updateSRSummary() {
		var el        = document.getElementById( 'hp-graph-sr-summary' );
		var summaryEl = document.getElementById( 'hp-graph-summary' );
		var visibleSummaryEl = document.getElementById( 'hp-graph-visible-summary' );
		var nodesEl   = document.getElementById( 'hp-graph-stat-nodes' );
		var edgesEl   = document.getElementById( 'hp-graph-stat-edges' );
		var typesEl   = document.getElementById( 'hp-graph-stat-types' );

		var counts = {};
		CONFIG.typeOrder.forEach( function( type ) {
			counts[ type ] = 0;
		} );
		state.nodes.forEach( function( n ) {
			if ( isNodeVisible( n ) ) {
				counts[ n.type ] = ( counts[ n.type ] || 0 ) + 1;
			}
		} );

		var visibleEdges = 0;
		state.edges.forEach( function( e ) {
			var src = typeof e.source === 'object' ? e.source : findNode( e.source );
			var tgt = typeof e.target === 'object' ? e.target : findNode( e.target );
			if ( src && tgt && isNodeVisible( src ) && isNodeVisible( tgt ) ) {
				visibleEdges++;
			}
		} );

		var activeTypeCount = 0;
		Object.keys( state.activeTypes ).forEach( function( type ) {
			if ( state.activeTypes[ type ] ) {
				activeTypeCount++;
			}
		} );

		var total = 0;
		CONFIG.typeOrder.forEach( function( type ) {
			total += counts[ type ] || 0;
		} );

		if ( el ) {
			el.textContent = 'Wissensgraph: ' + total + ' Knoten sichtbar — ' +
				counts.essay + ' Essays, ' +
				counts.note + ' Notizen, ' +
				counts.dossier + ' Dossiers, ' +
				counts.glossar + ' Glossar-Einträge, ' +
				counts.topic + ' Themenfelder. ' +
				visibleEdges + ' Verbindungen sichtbar.';
		}

		if ( summaryEl ) {
			summaryEl.textContent = total + ' sichtbare Knoten und ' + visibleEdges + ' aktive Verbindungen.';
		}
		if ( visibleSummaryEl ) {
			visibleSummaryEl.textContent = state.searchTerm
				? total + ' Treffer im aktiven Graph.'
				: activeTypeCount === CONFIG.typeOrder.length ? 'Alle Knotentypen aktiv.' : activeTypeCount + ' Knotentypen aktiv.';
		}
		if ( nodesEl ) {
			nodesEl.textContent = String( total );
		}
		if ( edgesEl ) {
			edgesEl.textContent = String( visibleEdges );
		}
		if ( typesEl ) {
			typesEl.textContent = String( activeTypeCount );
		}
	}

	function getBaseLabelOpacity( d ) {
		if ( window.innerWidth < CONFIG.mobileBreak ) {
			return 0;
		}

		if ( d._linkCount >= CONFIG.labelMinLinks + 2 ) {
			return CONFIG.labelOpacityStrong;
		}

		if ( d._linkCount >= CONFIG.labelMinLinks ) {
			return CONFIG.labelOpacity;
		}

		return 0;
	}

	/* =========================================
	   HILFSFUNKTIONEN
	   ========================================= */

	function normalizeSearch( value ) {
		return String( value || '' )
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' )
			.trim();
	}

	function resetActiveTypes() {
		state.activeTypes = {};
		CONFIG.typeOrder.forEach( function( type ) {
			state.activeTypes[ type ] = true;
		} );
	}

	function getTypeLabel( type ) {
		return CONFIG.typeLabels[ type ] || type;
	}

	function getEdgeLabel( type ) {
		return CONFIG.edgeLabels[ type ] || 'verbunden';
	}

	function getOpenLabel( type ) {
		if ( type === 'topic' ) {
			return 'Thema öffnen';
		}
		if ( type === 'glossar' ) {
			return 'Begriff öffnen';
		}
		if ( type === 'dossier' ) {
			return 'Dossier öffnen';
		}
		return 'Beitrag öffnen';
	}

	function nodeSearchText( node ) {
		var parts = [ node.label, node.type ];

		if ( node.meta ) {
			parts.push( node.meta.excerpt, node.meta.kurz, node.meta.description, node.meta.date, node.meta.reading_time );
		}

		return normalizeSearch( parts.filter( Boolean ).join( ' ' ) );
	}

	function isNodeVisible( node ) {
		if ( ! node || ! state.activeTypes[ node.type ] ) {
			return false;
		}

		return ! state.searchTerm || nodeSearchText( node ).indexOf( state.searchTerm ) !== -1;
	}

	function getVisibleNodes() {
		return state.nodes.filter( isNodeVisible );
	}

	function updateSearchControls() {
		var count = document.getElementById( 'hp-graph-search-count' );
		var clear = document.getElementById( 'hp-graph-search-clear' );
		var total = getVisibleNodes().length;

		if ( count ) {
			count.textContent = state.searchTerm ? String( total ) : '';
		}

		if ( clear ) {
			clear.hidden = ! state.searchTerm;
		}
	}

	function updateEmptyState( visibleCount ) {
		var empty = document.getElementById( 'hp-graph-empty' );
		var canvas = document.getElementById( 'hp-graph-canvas' );
		var show = state.nodes.length > 0 && visibleCount === 0;

		if ( empty ) {
			empty.hidden = ! show;
		}

		if ( canvas ) {
			canvas.classList.toggle( 'has-no-visible-nodes', show );
		}
	}

	function focusNode( nodeId ) {
		if ( ! state.nodeSel ) { return; }

		var match = state.nodeSel.filter( function( n ) {
			return n.id === nodeId;
		} ).node();

		if ( match && typeof match.focus === 'function' ) {
			match.focus();
		}
	}

	function getConnectedIds( nodeId ) {
		var ids = {};
		state.edges.forEach( function( e ) {
			var src = typeof e.source === 'object' ? e.source.id : e.source;
			var tgt = typeof e.target === 'object' ? e.target.id : e.target;
			if ( src === nodeId ) { ids[ tgt ] = true; }
			if ( tgt === nodeId ) { ids[ src ] = true; }
		} );
		return ids;
	}

	function getConnectedNodes( nodeId ) {
		var ids = getConnectedIds( nodeId );
		return state.nodes.filter( function( n ) { return ids[ n.id ]; } );
	}

	function getConnectedEntries( nodeId ) {
		var entries = {};

		state.edges.forEach( function( e ) {
			var src = typeof e.source === 'object' ? e.source.id : e.source;
			var tgt = typeof e.target === 'object' ? e.target.id : e.target;
			var otherId = '';

			if ( src === nodeId ) {
				otherId = tgt;
			} else if ( tgt === nodeId ) {
				otherId = src;
			} else {
				return;
			}

			var otherNode = findNode( otherId );
			if ( ! otherNode ) {
				return;
			}

			var key = otherNode.id;
			var weight = e.weight || 1;
			var relationship = getEdgeLabel( e.type );

			if ( e.type === 'dossier_has_part' && e.meta && e.meta.order ) {
				relationship += ' #' + e.meta.order;
			}

			if ( ! entries[ key ] || weight > entries[ key ].weight ) {
				entries[ key ] = {
					node: otherNode,
					relationship: relationship,
					weight: weight,
					type: e.type,
				};
			}
		} );

		return Object.keys( entries ).map( function( key ) {
			return entries[ key ];
		} ).sort( function( a, b ) {
			if ( b.weight !== a.weight ) {
				return b.weight - a.weight;
			}
			return String( a.node.label ).localeCompare( String( b.node.label ) );
		} );
	}

	function findNode( id ) {
		for ( var i = 0; i < state.nodes.length; i++ ) {
			if ( state.nodes[ i ].id === id ) { return state.nodes[ i ]; }
		}
		return null;
	}

	function escHtml( str ) {
		if ( ! str ) { return ''; }
		var div = document.createElement( 'div' );
		div.appendChild( document.createTextNode( String( str ) ) );
		return div.innerHTML;
	}

	function escAttr( str ) {
		return escHtml( str ).replace( /"/g, '&quot;' );
	}

	function debounce( fn, ms ) {
		var timer;
		return function() {
			clearTimeout( timer );
			timer = setTimeout( fn, ms );
		};
	}

	/* =========================================
	   START
	   ========================================= */

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
