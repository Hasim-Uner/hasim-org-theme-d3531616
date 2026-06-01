/**
 * Hasimuener Journal — Votes Frontend JavaScript
 *
 * Vanilla REST client for like/dislike voting.
 *
 * @package Hasimuener_Journal
 * @version 1.1.0
 */

( function() {
	'use strict';

	function getConfig() {
		return window.hasimOrgVotes || {};
	}

	function setProcessingState( button, processing ) {
		button.classList.toggle( 'processing', processing );
		button.disabled = processing;

		const spinner = button.querySelector( '.hp-vote-spinner' );
		if ( spinner ) {
			spinner.hidden = ! processing;
			spinner.style.display = processing ? '' : 'none';
		}
	}

	function showFeedback( message, type ) {
		const feedback = document.createElement( 'div' );
		feedback.className = 'hp-vote-feedback hp-vote-feedback--' + type;
		feedback.textContent = message;
		document.body.appendChild( feedback );

		window.requestAnimationFrame( function() {
			feedback.classList.add( 'show' );
		} );

		window.setTimeout( function() {
			feedback.classList.remove( 'show' );
			window.setTimeout( function() {
				feedback.remove();
			}, 300 );
		}, 3000 );
	}

	function updateUI( postId, data ) {
		document.querySelectorAll( '.hp-vote-likes[data-post-id="' + postId + '"]' ).forEach( function( counter ) {
			counter.textContent = data.likes;
		} );

		document.querySelectorAll( '.hp-vote-dislikes[data-post-id="' + postId + '"]' ).forEach( function( counter ) {
			counter.textContent = data.dislikes;
		} );

		document.querySelectorAll( '.hp-vote-btn[data-post-id="' + postId + '"]' ).forEach( function( button ) {
			button.classList.remove( 'active' );
		} );

		if ( data.user_vote ) {
			const active = document.querySelector( '.hp-vote-btn[data-post-id="' + postId + '"][data-vote-type="' + data.user_vote + '"]' );
			if ( active ) {
				active.classList.add( 'active' );
			}
		}
	}

	function handleVote( event ) {
		const button = event.target.closest( '.hp-vote-btn' );
		if ( ! button ) {
			return;
		}

		event.preventDefault();

		if ( button.classList.contains( 'processing' ) ) {
			return;
		}

		const config = getConfig();
		const postId = button.dataset.postId;
		const voteType = button.dataset.voteType;

		if ( ! config.ajax_url || ! config.nonce || ! postId || ! voteType ) {
			showFeedback( 'Voting ist gerade nicht verfügbar.', 'error' );
			return;
		}

		setProcessingState( button, true );

		window.fetch( config.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			credentials: 'same-origin',
			body: JSON.stringify( {
				post_id: postId,
				vote_type: voteType,
				nonce: config.nonce,
			} ),
		} )
			.then( function( response ) {
				return response.json().then( function( data ) {
					if ( ! response.ok ) {
						throw data;
					}
					return data;
				} );
			} )
			.then( function( data ) {
				if ( data.success ) {
					updateUI( postId, data );
					showFeedback( 'Vote erfolgreich!', 'success' );
					return;
				}

				showFeedback( data.message || 'Fehler beim Voting', 'error' );
			} )
			.catch( function( error ) {
				showFeedback( error && error.message ? error.message : 'Netzwerkfehler', 'error' );
			} )
			.finally( function() {
				setProcessingState( button, false );
			} );
	}

	document.addEventListener( 'click', handleVote );
} )();
