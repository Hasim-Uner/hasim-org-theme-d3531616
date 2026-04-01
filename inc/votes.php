<?php
/**
 * Vote-Management — Like/Dislike System
 *
 * Verwaltet Votes für Essays und Notizen mit Duplikat-Prüfung
 * und Rate-Limiting. Integriert mit WordPress REST API.
 *
 * @package Hasimuener_Journal
 * @since   7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Erstellt die Vote-Tabelle bei Theme-Aktivierung.
 */
function hp_votes_create_table(): void {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'hp_votes';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table_name} (
		vote_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		post_id BIGINT UNSIGNED NOT NULL,
		user_id BIGINT UNSIGNED DEFAULT NULL,
		user_ip VARCHAR(45) NOT NULL,
		vote_type ENUM('like', 'dislike') NOT NULL,
		vote_date DATETIME DEFAULT CURRENT_TIMESTAMP,
		UNIQUE KEY unique_vote (post_id, user_id, user_ip)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'hp_votes_create_table' );

// Prüft bei jedem Seitenaufruf, ob die Tabelle existiert
add_action( 'init', function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'hp_votes';
	
	// Prüfe ob Tabelle existiert
	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
		hp_votes_create_table();
	}
} );

/**
 * Verarbeitet einen Vote und gibt das Ergebnis zurück.
 *
 * @param int    $post_id  Post ID
 * @param string $vote_type 'like' oder 'dislike'
 * @return array|WP_Error Ergebnis oder Fehler
 */
function hp_process_vote( int $post_id, string $vote_type ): array {
	global $wpdb;

	// Validierung
	if ( ! in_array( $vote_type, [ 'like', 'dislike' ], true ) ) {
		return new WP_Error( 'invalid_vote_type', 'Ungültiger Vote-Typ' );
	}

	if ( ! get_post( $post_id ) || ! in_array( get_post_type( $post_id ), [ 'essay', 'note' ], true ) ) {
		return new WP_Error( 'invalid_post', 'Ungültiger Post oder Post-Type' );
	}

	// Rate Limiting prüfen
	$can_vote = hp_can_user_vote( $post_id );
	if ( is_wp_error( $can_vote ) ) {
		return $can_vote;
	}

	// Vorherigen Vote prüfen
	$existing_vote = hp_get_user_vote( $post_id );
	
	$table_name = $wpdb->prefix . 'hp_votes';
	$user_id = get_current_user_id();
	$user_ip = hp_get_user_ip();

	if ( $existing_vote ) {
		// Vote aktualisieren wenn Typ unterschiedlich
		if ( $existing_vote->vote_type !== $vote_type ) {
			$wpdb->update(
				$table_name,
				[ 'vote_type' => $vote_type ],
				[ 
					'post_id' => $post_id,
					'user_id' => $user_id ?: 0,
					'user_ip' => $user_ip
				],
				[ '%s' ],
				[ '%d', '%d', '%s' ]
			);
		}
		// Wenn gleicher Typ, nichts tun (Duplikat)
	} else {
		// Neuen Vote einfügen
		$wpdb->insert(
			$table_name,
			[
				'post_id' => $post_id,
				'user_id' => $user_id ?: null,
				'user_ip' => $user_ip,
				'vote_type' => $vote_type
			],
			[ '%d', '%d', '%s', '%s' ]
		);
	}

	// Aktualisierte Vote-Zahlen zurückgeben
	return hp_get_vote_counts( $post_id );
}

/**
 * Prüft ob User für einen Post voten kann.
 *
 * @param int $post_id Post ID
 * @return bool|WP_Error True wenn möglich, sonst Error
 */
function hp_can_user_vote( int $post_id ) {
	global $wpdb;

	$user_id = get_current_user_id();
	$user_ip = hp_get_user_ip();
	$table_name = $wpdb->prefix . 'hp_votes';

	// Letzten Vote des Users prüfen
	$last_vote = $wpdb->get_row( $wpdb->prepare(
		"SELECT vote_date FROM {$table_name} 
		WHERE post_id = %d AND (user_id = %d OR user_ip = %s) 
		ORDER BY vote_date DESC LIMIT 1",
		$post_id, $user_id ?: 0, $user_ip
	) );

	if ( $last_vote ) {
		$last_vote_time = strtotime( $last_vote->vote_date );
		$current_time = time();
		$time_diff = $current_time - $last_vote_time;

		// 1 Stunde Cooldown
		if ( $time_diff < 3600 ) {
			$remaining = 3600 - $time_diff;
			return new WP_Error( 'rate_limit', sprintf( 'Bitte warte %d Minuten bevor du erneut votest.', ceil( $remaining / 60 ) ) );
		}
	}

	return true;
}

/**
 * Holt den aktuellen Vote des Users für einen Post.
 *
 * @param int $post_id Post ID
 * @return object|null Vote-Objekt oder null
 */
function hp_get_user_vote( int $post_id ): ?object {
	global $wpdb;

	$user_id = get_current_user_id();
	$user_ip = hp_get_user_ip();
	$table_name = $wpdb->prefix . 'hp_votes';

	return $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table_name} 
		WHERE post_id = %d AND (user_id = %d OR user_ip = %s) 
		ORDER BY vote_date DESC LIMIT 1",
		$post_id, $user_id ?: 0, $user_ip
	) );
}

/**
 * Holt die Vote-Zahlen für einen Post.
 *
 * @param int $post_id Post ID
 * @return array Vote statistics
 */
function hp_get_vote_counts( int $post_id ): array {
	global $wpdb;

	$table_name = $wpdb->prefix . 'hp_votes';

	$counts = $wpdb->get_row( $wpdb->prepare(
		"SELECT 
			SUM(CASE WHEN vote_type = 'like' THEN 1 ELSE 0 END) as likes,
			SUM(CASE WHEN vote_type = 'dislike' THEN 1 ELSE 0 END) as dislikes
		FROM {$table_name} WHERE post_id = %d",
		$post_id
	) );

	$user_vote = hp_get_user_vote( $post_id );

	return [
		'likes' => (int) ( $counts->likes ?? 0 ),
		'dislikes' => (int) ( $counts->dislikes ?? 0 ),
		'user_vote' => $user_vote ? $user_vote->vote_type : null,
		'can_vote' => ! is_wp_error( hp_can_user_vote( $post_id ) )
	];
}

/**
 * Holt die User-IP-Adresse.
 *
 * @return string IP address
 */
function hp_get_user_ip(): string {
	$ip = '';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
	}

	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Gibt HTML für Vote-Buttons zurück.
 *
 * @param int $post_id Post ID
 * @return string HTML output
 */
function hp_get_vote_buttons( int $post_id ): string {
	$vote_data = hp_get_vote_counts( $post_id );
	$nonce = wp_create_nonce( 'hp_vote_' . $post_id );

	ob_start();
	?>
	<div class="hp-vote" data-post-id="<?php echo esc_attr( $post_id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<button class="hp-vote-btn hp-vote-like <?php echo $vote_data['user_vote'] === 'like' ? 'hp-vote-active' : ''; ?>" 
				data-vote-type="like" <?php echo ! $vote_data['can_vote'] ? 'disabled' : ''; ?>>
			<span class="hp-vote-icon">👍</span>
			<span class="hp-vote-count"><?php echo esc_html( $vote_data['likes'] ); ?></span>
		</button>
		
		<button class="hp-vote-btn hp-vote-dislike <?php echo $vote_data['user_vote'] === 'dislike' ? 'hp-vote-active' : ''; ?>" 
				data-vote-type="dislike" <?php echo ! $vote_data['can_vote'] ? 'disabled' : ''; ?>>
			<span class="hp-vote-icon">👎</span>
			<span class="hp-vote-count"><?php echo esc_html( $vote_data['dislikes'] ); ?></span>
		</button>
		
		<?php if ( ! $vote_data['can_vote'] ) : ?>
			<div class="hp-vote-message">Du hast bereits gevotet</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}