/**
 * Hasimuener Journal — Votes Frontend JavaScript
 *
 * Handles Like/Dislike voting interactions via AJAX.
 * Provides smooth user experience with immediate feedback.
 *
 * @package Hasimuener_Journal
 * @version 1.0.0
 */

( function( $ ) {
	'use strict';

	const Votes = {
		/**
		 * Initialize voting system
		 */
		init() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			$( document ).on( 'click', '.hp-vote-btn', this.handleVote.bind( this ) );
		},

		/**
		 * Handle vote button click
		 */
		handleVote( event ) {
			event.preventDefault();

			const $button = $( event.currentTarget );
			const postId  = $button.data( 'post-id' );
			const voteType = $button.data( 'vote-type' );

			// Prevent multiple clicks
			if ( $button.hasClass( 'processing' ) ) {
				return;
			}

			this.setProcessingState( $button, true );

			// Send AJAX request
			$.ajax( {
				url: hasimOrgVotes.ajax_url,
				type: 'POST',
				data: {
					post_id: postId,
					vote_type: voteType,
					nonce: hasimOrgVotes.nonce
				},
				success: ( response ) => {
					this.handleSuccess( response, $button, postId );
				},
				error: ( xhr ) => {
					this.handleError( xhr, $button );
				},
				complete: () => {
					this.setProcessingState( $button, false );
				}
			} );
		},

		/**
		 * Handle successful vote response
		 */
		handleSuccess( response, $button, postId ) {
			if ( response.success ) {
				this.updateUI( postId, response );
				this.showFeedback( 'Vote erfolgreich!', 'success' );
			} else {
				this.showFeedback( response.message || 'Fehler beim Voting', 'error' );
			}
		},

		/**
		 * Handle AJAX error
		 */
		handleError( xhr, $button ) {
			let message = 'Netzwerkfehler';

			if ( xhr.responseJSON && xhr.responseJSON.message ) {
				message = xhr.responseJSON.message;
			} else if ( xhr.statusText ) {
				message = xhr.statusText;
			}

			this.showFeedback( message, 'error' );
		},

		/**
		 * Update UI with new vote data
		 */
		updateUI( postId, data ) {
			// Update counters
			$( `.hp-vote-likes[data-post-id="${postId}"]` ).text( data.likes );
			$( `.hp-vote-dislikes[data-post-id="${postId}"]` ).text( data.dislikes );

			// Update button states
			$( `.hp-vote-btn[data-post-id="${postId}"]` ).removeClass( 'active' );
			if ( data.user_vote ) {
				$( `.hp-vote-btn[data-post-id="${postId}"][data-vote-type="${data.user_vote}"]` ).addClass( 'active' );
			}
		},

		/**
		 * Set processing state for button
		 */
		setProcessingState( $button, processing ) {
			if ( processing ) {
				$button.addClass( 'processing' ).prop( 'disabled', true );
				$button.find( '.hp-vote-spinner' ).show();
			} else {
				$button.removeClass( 'processing' ).prop( 'disabled', false );
				$button.find( '.hp-vote-spinner' ).hide();
			}
		},

		/**
		 * Show feedback message
		 */
		showFeedback( message, type ) {
			// Create feedback element
			const $feedback = $( '<div class="hp-vote-feedback hp-vote-feedback--' + type + '"></div>' );
			$feedback.text( message );

			// Add to page
			$( 'body' ).append( $feedback );

			// Show with animation
			$feedback.addClass( 'show' );

			// Remove after delay
			setTimeout( () => {
				$feedback.removeClass( 'show' );
				setTimeout( () => {
					$feedback.remove();
				}, 300 );
			}, 3000 );
		}
	};

	// Initialize on document ready
	$( document ).ready( function() {
		Votes.init();
	} );

} )( jQuery );