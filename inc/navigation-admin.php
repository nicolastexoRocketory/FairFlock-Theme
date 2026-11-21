<?php
/**
 * FairFlock → Navigation: Hauptmenü-Punkte verwalten (inkl. individuelle Links).
 *
 * @package FairFlock
 * @since 0.9.480
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers FairFlock → Navigation.
 *
 * @since 0.9.480
 *
 * @return void
 */
function fairflock_navigation_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Navigation', 'fairflock' ),
		__( 'Navigation', 'fairflock' ),
		'edit_theme_options',
		'fairflock-navigation',
		'fairflock_navigation_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_navigation_register_admin_page', 26 );

/**
 * Resolves the primary wp_navigation post (header menu).
 *
 * @since 0.9.480
 *
 * @return WP_Post|null
 */
function fairflock_navigation_get_primary_post() {
	$posts = get_posts(
		array(
			'post_type'              => 'wp_navigation',
			'post_status'            => array( 'publish', 'draft' ),
			'posts_per_page'         => 20,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $posts ) ) {
		return null;
	}

	foreach ( $posts as $post ) {
		if ( $post instanceof WP_Post && 'navigation' === $post->post_name ) {
			return $post;
		}
	}

	foreach ( $posts as $post ) {
		if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
			return $post;
		}
	}

	return $posts[0] instanceof WP_Post ? $posts[0] : null;
}

/**
 * Creates an empty primary navigation when none exists.
 *
 * @since 0.9.480
 *
 * @return WP_Post|null
 */
function fairflock_navigation_ensure_primary_post() {
	$existing = fairflock_navigation_get_primary_post();
	if ( $existing instanceof WP_Post ) {
		return $existing;
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_title'   => __( 'Navigation', 'fairflock' ),
			'post_name'    => 'navigation',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return null;
	}

	$post = get_post( (int) $id );
	return $post instanceof WP_Post ? $post : null;
}

/**
 * Top-level navigation blocks from post content.
 *
 * @since 0.9.480
 *
 * @param string $content Block markup.
 * @return array<int, array<string, mixed>>
 */
function fairflock_navigation_extract_blocks( $content ) {
	$out = array();

	foreach ( parse_blocks( (string) $content ) as $block ) {
		$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
		if ( 'core/navigation-link' === $name || 'core/navigation-submenu' === $name ) {
			$out[] = $block;
		}
	}

	return $out;
}

/**
 * Serializes navigation blocks back to post content.
 *
 * @since 0.9.480
 *
 * @param array<int, array<string, mixed>> $blocks Blocks.
 * @return string
 */
function fairflock_navigation_serialize_blocks( array $blocks ) {
	$parts = array();
	foreach ( $blocks as $block ) {
		$parts[] = serialize_block( $block );
	}

	return implode( "\n\n", $parts );
}

/**
 * Whether a navigation block is a custom (title + URL) link.
 *
 * @since 0.9.480
 *
 * @param array<string, mixed> $block Block.
 * @return bool
 */
function fairflock_navigation_block_is_custom( array $block ) {
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$kind  = isset( $attrs['kind'] ) ? (string) $attrs['kind'] : '';
	$type  = isset( $attrs['type'] ) ? (string) $attrs['type'] : '';

	return 'custom' === $kind || 'custom' === $type;
}

/**
 * Human-readable type label for a nav item.
 *
 * @since 0.9.480
 *
 * @param array<string, mixed> $block Block.
 * @return string
 */
function fairflock_navigation_block_type_label( array $block ) {
	if ( 'core/navigation-submenu' === ( $block['blockName'] ?? '' ) ) {
		return __( 'Untermenü', 'fairflock' );
	}

	if ( fairflock_navigation_block_is_custom( $block ) ) {
		return __( 'Individuell', 'fairflock' );
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$type  = isset( $attrs['type'] ) ? (string) $attrs['type'] : '';

	$map = array(
		'page'     => __( 'Seite', 'fairflock' ),
		'post'     => __( 'Beitrag', 'fairflock' ),
		'category' => __( 'Kategorie', 'fairflock' ),
		'product'  => __( 'Produkt', 'fairflock' ),
	);

	return isset( $map[ $type ] ) ? $map[ $type ] : __( 'Link', 'fairflock' );
}

/**
 * Builds a custom navigation-link block.
 *
 * @since 0.9.480
 *
 * @param string $label Display title.
 * @param string $url   Target URL.
 * @return array<string, mixed>
 */
function fairflock_navigation_make_custom_block( $label, $url ) {
	return array(
		'blockName'    => 'core/navigation-link',
		'attrs'        => array(
			'label'         => $label,
			'type'          => 'custom',
			'url'           => $url,
			'kind'          => 'custom',
			'isTopLevelLink'=> true,
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
}

/**
 * Normalizes a menu URL (absolute or site-relative).
 *
 * @since 0.9.480
 *
 * @param string $url Raw URL.
 * @return string Empty if invalid.
 */
function fairflock_navigation_sanitize_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}

	// Allow relative paths like /shop/ or #anker.
	if ( '#' === $url[0] ) {
		return sanitize_text_field( $url );
	}

	if ( '/' === $url[0] ) {
		$path = esc_url_raw( home_url( $url ) );
		$home = untrailingslashit( home_url() );
		if ( 0 === strpos( $path, $home ) ) {
			$rel = substr( $path, strlen( $home ) );
			return '' !== $rel ? $rel : '/';
		}
		return esc_url_raw( $url );
	}

	$clean = esc_url_raw( $url );
	return is_string( $clean ) ? $clean : '';
}

/**
 * Saves navigation mutations (add / update / delete / move).
 *
 * @since 0.9.480
 *
 * @return void
 */
function fairflock_navigation_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_navigation_action'] ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_navigation_manage' );

	$action = sanitize_key( wp_unslash( (string) $_POST['fairflock_navigation_action'] ) );
	$post   = fairflock_navigation_ensure_primary_post();

	if ( ! $post instanceof WP_Post ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'  => 'fairflock-navigation',
					'error' => 'missing',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	$blocks = fairflock_navigation_extract_blocks( (string) $post->post_content );
	$notice = 'updated';
	$index  = isset( $_POST['fairflock_navigation_index'] )
		? (int) wp_unslash( $_POST['fairflock_navigation_index'] )
		: -1;

	switch ( $action ) {
		case 'add':
			$label = isset( $_POST['fairflock_navigation_label'] )
				? sanitize_text_field( wp_unslash( (string) $_POST['fairflock_navigation_label'] ) )
				: '';
			$url   = isset( $_POST['fairflock_navigation_url'] )
				? fairflock_navigation_sanitize_url( wp_unslash( (string) $_POST['fairflock_navigation_url'] ) )
				: '';

			if ( '' === $label || '' === $url ) {
				$notice = 'invalid';
				break;
			}

			$blocks[] = fairflock_navigation_make_custom_block( $label, $url );
			$notice   = 'added';
			break;

		case 'update':
			if ( $index < 0 || ! isset( $blocks[ $index ] ) ) {
				$notice = 'invalid';
				break;
			}

			$label = isset( $_POST['fairflock_navigation_label'] )
				? sanitize_text_field( wp_unslash( (string) $_POST['fairflock_navigation_label'] ) )
				: '';
			$url   = isset( $_POST['fairflock_navigation_url'] )
				? fairflock_navigation_sanitize_url( wp_unslash( (string) $_POST['fairflock_navigation_url'] ) )
				: '';

			if ( '' === $label ) {
				$notice = 'invalid';
				break;
			}

			if ( ! isset( $blocks[ $index ]['attrs'] ) || ! is_array( $blocks[ $index ]['attrs'] ) ) {
				$blocks[ $index ]['attrs'] = array();
			}

			$blocks[ $index ]['attrs']['label'] = $label;

			if ( fairflock_navigation_block_is_custom( $blocks[ $index ] ) ) {
				if ( '' === $url ) {
					$notice = 'invalid';
					break;
				}
				$blocks[ $index ]['attrs']['url']  = $url;
				$blocks[ $index ]['attrs']['type'] = 'custom';
				$blocks[ $index ]['attrs']['kind'] = 'custom';
			}

			$notice = 'saved';
			break;

		case 'delete':
			if ( $index < 0 || ! isset( $blocks[ $index ] ) ) {
				$notice = 'invalid';
				break;
			}
			array_splice( $blocks, $index, 1 );
			$notice = 'deleted';
			break;

		case 'move_up':
			if ( $index > 0 && isset( $blocks[ $index ] ) ) {
				$tmp                  = $blocks[ $index - 1 ];
				$blocks[ $index - 1 ] = $blocks[ $index ];
				$blocks[ $index ]     = $tmp;
				$notice               = 'moved';
			}
			break;

		case 'move_down':
			if ( $index >= 0 && isset( $blocks[ $index ], $blocks[ $index + 1 ] ) ) {
				$tmp                  = $blocks[ $index + 1 ];
				$blocks[ $index + 1 ] = $blocks[ $index ];
				$blocks[ $index ]     = $tmp;
				$notice               = 'moved';
			}
			break;

		default:
			$notice = 'invalid';
			break;
	}

	if ( in_array( $notice, array( 'added', 'saved', 'deleted', 'moved', 'updated' ), true ) ) {
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => fairflock_navigation_serialize_blocks( $blocks ),
				'post_status'  => 'publish',
			)
		);
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'   => 'fairflock-navigation',
				'notice' => $notice,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_navigation_handle_admin_save' );

/**
 * Admin notice message for redirect notices.
 *
 * @since 0.9.480
 *
 * @param string $code Notice code.
 * @return string
 */
function fairflock_navigation_notice_message( $code ) {
	$map = array(
		'added'   => __( 'Menüpunkt hinzugefügt.', 'fairflock' ),
		'saved'   => __( 'Menüpunkt gespeichert.', 'fairflock' ),
		'deleted' => __( 'Menüpunkt entfernt.', 'fairflock' ),
		'moved'   => __( 'Reihenfolge aktualisiert.', 'fairflock' ),
		'updated' => __( 'Navigation gespeichert.', 'fairflock' ),
		'invalid' => __( 'Bitte Titel und gültigen Link angeben.', 'fairflock' ),
		'missing' => __( 'Navigation konnte nicht angelegt werden.', 'fairflock' ),
	);

	return isset( $map[ $code ] ) ? $map[ $code ] : '';
}

/**
 * Renders FairFlock → Navigation.
 *
 * @since 0.9.480
 *
 * @return void
 */
function fairflock_navigation_render_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$post   = fairflock_navigation_ensure_primary_post();
	$blocks = $post instanceof WP_Post
		? fairflock_navigation_extract_blocks( (string) $post->post_content )
		: array();

	$notice_code = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$error_code  = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( (string) $_GET['error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$msg_code    = '' !== $notice_code ? $notice_code : $error_code;
	$message     = '' !== $msg_code ? fairflock_navigation_notice_message( $msg_code ) : '';
	$is_error    = in_array( $msg_code, array( 'invalid', 'missing' ), true );

	$site_editor = admin_url( 'site-editor.php?p=%2Fnavigation' );
	$darstellung = admin_url( 'admin.php?page=fairflock-header-style' );
	$count       = count( $blocks );
	?>
	<div class="wrap fairflock-navigation-admin">
		<h1><?php esc_html_e( 'Navigation', 'fairflock' ); ?></h1>

		<?php if ( '' !== $message ) : ?>
			<div class="notice notice-<?php echo $is_error ? 'error' : 'success'; ?> is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php esc_html_e( 'Hauptmenü der Website. Individuelle Punkte mit eigenem Titel und Link anlegen — oder bestehende Einträge umbenennen und sortieren.', 'fairflock' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $darstellung ); ?>"><?php esc_html_e( 'Darstellung (Nav-Design)', 'fairflock' ); ?></a>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( $site_editor ); ?>"><?php esc_html_e( 'Site Editor (erweitert)', 'fairflock' ); ?></a>
		</p>

		<h2><?php esc_html_e( 'Individuellen Menüpunkt erstellen', 'fairflock' ); ?></h2>
		<form method="post" action="" class="fairflock-navigation-add">
			<?php wp_nonce_field( 'fairflock_navigation_manage' ); ?>
			<input type="hidden" name="fairflock_navigation_action" value="add" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="fairflock_navigation_label"><?php esc_html_e( 'Titel (Anzeige)', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="text"
							class="regular-text"
							id="fairflock_navigation_label"
							name="fairflock_navigation_label"
							value=""
							required
							placeholder="<?php esc_attr_e( 'z. B. Blog', 'fairflock' ); ?>"
							autocomplete="off"
						/>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fairflock_navigation_url"><?php esc_html_e( 'Link', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="text"
							class="regular-text"
							id="fairflock_navigation_url"
							name="fairflock_navigation_url"
							value=""
							required
							placeholder="<?php esc_attr_e( 'https://… oder /pfad/', 'fairflock' ); ?>"
							autocomplete="off"
						/>
						<p class="description">
							<?php esc_html_e( 'Volle URL oder relativer Pfad (z. B. /kontakt/).', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Menüpunkt hinzufügen', 'fairflock' ), 'primary', 'submit', false ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Aktuelle Menüpunkte', 'fairflock' ); ?></h2>
		<?php if ( empty( $blocks ) ) : ?>
			<p><?php esc_html_e( 'Noch keine Menüpunkte. Lege oben einen individuellen Link an oder nutze den Site Editor.', 'fairflock' ); ?></p>
		<?php else : ?>
			<table class="widefat striped fairflock-navigation-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Titel', 'fairflock' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Link', 'fairflock' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Typ', 'fairflock' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Aktionen', 'fairflock' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $blocks as $i => $block ) : ?>
						<?php
						$attrs     = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
						$label     = isset( $attrs['label'] ) ? (string) $attrs['label'] : '';
						$url       = isset( $attrs['url'] ) ? (string) $attrs['url'] : '';
						$is_custom = fairflock_navigation_block_is_custom( $block );
						$type_lbl  = fairflock_navigation_block_type_label( $block );
						?>
						<tr>
							<td colspan="4" style="padding:0;border:none;">
								<form method="post" action="" class="fairflock-navigation-row">
									<?php wp_nonce_field( 'fairflock_navigation_manage' ); ?>
									<input type="hidden" name="fairflock_navigation_index" value="<?php echo esc_attr( (string) $i ); ?>" />
									<table class="fairflock-navigation-inner" style="width:100%;border-collapse:collapse;">
										<tr>
											<td style="width:22%;vertical-align:middle;">
												<input
													type="text"
													class="regular-text"
													name="fairflock_navigation_label"
													value="<?php echo esc_attr( $label ); ?>"
													required
													aria-label="<?php esc_attr_e( 'Titel', 'fairflock' ); ?>"
												/>
											</td>
											<td style="width:38%;vertical-align:middle;">
												<?php if ( $is_custom ) : ?>
													<input
														type="text"
														class="regular-text"
														name="fairflock_navigation_url"
														value="<?php echo esc_attr( $url ); ?>"
														required
														aria-label="<?php esc_attr_e( 'Link', 'fairflock' ); ?>"
														style="width:100%;max-width:28rem;"
													/>
												<?php else : ?>
													<code style="word-break:break-all;"><?php echo esc_html( $url ); ?></code>
													<input type="hidden" name="fairflock_navigation_url" value="<?php echo esc_attr( $url ); ?>" />
												<?php endif; ?>
											</td>
											<td style="width:12%;vertical-align:middle;">
												<?php echo esc_html( $type_lbl ); ?>
											</td>
											<td style="width:28%;vertical-align:middle;white-space:nowrap;">
												<button type="submit" class="button button-small" name="fairflock_navigation_action" value="update">
													<?php esc_html_e( 'Speichern', 'fairflock' ); ?>
												</button>
												<?php if ( $i > 0 ) : ?>
													<button type="submit" class="button button-small" name="fairflock_navigation_action" value="move_up" title="<?php esc_attr_e( 'Nach oben', 'fairflock' ); ?>">↑</button>
												<?php endif; ?>
												<?php if ( $i < $count - 1 ) : ?>
													<button type="submit" class="button button-small" name="fairflock_navigation_action" value="move_down" title="<?php esc_attr_e( 'Nach unten', 'fairflock' ); ?>">↓</button>
												<?php endif; ?>
												<button type="submit" class="button button-small button-link-delete" name="fairflock_navigation_action" value="delete" onclick="return confirm('<?php echo esc_js( __( 'Diesen Menüpunkt entfernen?', 'fairflock' ) ); ?>');">
													<?php esc_html_e( 'Entfernen', 'fairflock' ); ?>
												</button>
											</td>
										</tr>
									</table>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description" style="margin-top:0.75rem;">
				<?php esc_html_e( 'Seiten-Links: Titel änderbar, Ziel-URL bleibt die der Seite. Individuelle Links: Titel und URL frei editierbar. Mega-Stil und Untermenüs im Site Editor.', 'fairflock' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<style>
		.fairflock-navigation-admin .fairflock-navigation-add { max-width: 42rem; margin-bottom: 1.5rem; }
		.fairflock-navigation-admin .fairflock-navigation-table { max-width: 960px; }
		.fairflock-navigation-admin .fairflock-navigation-row { margin: 0; }
		.fairflock-navigation-admin .fairflock-navigation-inner td { padding: 10px 12px; border-bottom: 1px solid #f0f0f1; }
		.fairflock-navigation-admin .button-link-delete { color: #b32d2e; }
	</style>
	<?php
}
