<?php
/**
 * Darstellung: Nav-Design, Inhaltsbreite, Formen, Hero-Höhe.
 *
 * @package FairFlock
 * @since 0.9.173
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed header style keys.
 *
 * @since 0.9.173
 *
 * @return array<string, string> style => label
 */
function fairflock_header_style_choices() {
	return array(
		'floating' => __( 'Schwebend (abgerundet)', 'fairflock' ),
		'edge'     => __( 'Randlos (bis zum Rand, ohne Rundungen)', 'fairflock' ),
	);
}

/**
 * Content width choices.
 *
 * @since 0.9.186
 *
 * @return array<string, string>
 */
function fairflock_content_width_choices() {
	return array(
		'shell' => __( 'Inhaltsbreite (max. 1240px)', 'fairflock' ),
		'full'  => __( 'Volle Breite (nur Seitenabstand)', 'fairflock' ),
	);
}

/**
 * Shape / radius choices.
 *
 * @since 0.9.186
 *
 * @return array<string, string>
 */
function fairflock_shape_style_choices() {
	return array(
		'rounded' => __( 'Abgerundet', 'fairflock' ),
		'sharp'   => __( 'Eckig', 'fairflock' ),
	);
}

/**
 * Mega menu width choices.
 *
 * @since 0.9.205
 *
 * @return array<string, string>
 */
function fairflock_mega_width_choices() {
	return array(
		'bar'   => __( 'Wie Header-Leiste', 'fairflock' ),
		'shell' => __( 'Inhaltsbreite (--ff-shell)', 'fairflock' ),
		'full'  => __( 'Volle Breite (mit Seitenabstand)', 'fairflock' ),
	);
}

/**
 * Hero slider navigation layout choices.
 *
 * @since 0.9.235
 *
 * @return array<string, string>
 */
function fairflock_hero_nav_layout_choices() {
	return array(
		'edges'   => __( 'Pfeile außen, Punkte mittig', 'fairflock' ),
		'cluster' => __( 'Pfeile neben den Punkten (Gruppe mittig)', 'fairflock' ),
	);
}

/**
 * Collections grid column choices (desktop).
 *
 * @since 0.9.236
 *
 * @return array<int, string>
 */
function fairflock_collections_cols_choices() {
	return array(
		2 => __( '2 Spalten', 'fairflock' ),
		3 => __( '3 Spalten', 'fairflock' ),
		4 => __( '4 Spalten', 'fairflock' ),
	);
}

/**
 * Current header style.
 *
 * @since 0.9.173
 *
 * @return string floating|edge
 */
function fairflock_get_header_style() {
	$style = (string) get_option( 'fairflock_header_style', 'floating' );
	$ok    = array_keys( fairflock_header_style_choices() );

	return in_array( $style, $ok, true ) ? $style : 'floating';
}

/**
 * Current content width mode.
 *
 * @since 0.9.186
 *
 * @return string shell|full
 */
function fairflock_get_content_width() {
	$width = (string) get_option( 'fairflock_content_width', 'shell' );
	$ok    = array_keys( fairflock_content_width_choices() );

	return in_array( $width, $ok, true ) ? $width : 'shell';
}

/**
 * Current shape style.
 *
 * @since 0.9.186
 *
 * @return string rounded|sharp
 */
function fairflock_get_shape_style() {
	$shape = (string) get_option( 'fairflock_shape_style', 'rounded' );
	$ok    = array_keys( fairflock_shape_style_choices() );

	return in_array( $shape, $ok, true ) ? $shape : 'rounded';
}

/**
 * Current mega menu width mode.
 *
 * @since 0.9.205
 *
 * @return string bar|shell|full
 */
function fairflock_get_mega_width() {
	$width = (string) get_option( 'fairflock_mega_width', 'bar' );
	$ok    = array_keys( fairflock_mega_width_choices() );

	return in_array( $width, $ok, true ) ? $width : 'bar';
}

/**
 * Current hero nav layout.
 *
 * @since 0.9.235
 *
 * @return string edges|cluster
 */
function fairflock_get_hero_nav_layout() {
	$layout = (string) get_option( 'fairflock_hero_nav_layout', 'edges' );
	$ok     = array_keys( fairflock_hero_nav_layout_choices() );

	return in_array( $layout, $ok, true ) ? $layout : 'edges';
}

/**
 * Desktop column count for the collections grid.
 *
 * @since 0.9.236
 *
 * @return int 2–4
 */
function fairflock_get_collections_cols() {
	$cols = (int) get_option( 'fairflock_collections_cols', 3 );
	$ok   = array_keys( fairflock_collections_cols_choices() );

	return in_array( $cols, $ok, true ) ? $cols : 3;
}

/**
 * Hero slideshow height in vh (all devices).
 *
 * @since 0.9.216
 *
 * @return int 40–100
 */
function fairflock_get_hero_height_vh() {
	$vh = (int) get_option( 'fairflock_hero_height_vh', 80 );

	return max( 40, min( 100, $vh ) );
}

/**
 * Prints CSS variable for hero height.
 *
 * @since 0.9.216
 *
 * @return void
 */
function fairflock_print_hero_height_css() {
	$vh = fairflock_get_hero_height_vh();
	/*
	 * Desktop: fixed vh. Mobile: min-height only so long slide copy
	 * (centered hero + CTA) is not clipped by overflow:hidden.
	 */
	$css = sprintf(
		':root{--ff-hero-vh:%1$dvh;}'
		. 'body:not(.block-editor-iframe__body) .fs-slider{height:var(--ff-hero-vh);min-height:var(--ff-hero-vh);}'
		. '@media (max-width:960px){body:not(.block-editor-iframe__body) .fs-slider{height:auto;min-height:var(--ff-hero-vh);}}',
		$vh
	);

	if ( wp_style_is( 'fairflock-hero-slider', 'enqueued' ) || wp_style_is( 'fairflock-hero-slider', 'registered' ) ) {
		wp_add_inline_style( 'fairflock-hero-slider', $css );
		return;
	}

	printf(
		'<style id="fairflock-hero-height">%s</style>' . "\n",
		$css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- numeric CSS only
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_print_hero_height_css', 25 );

/**
 * Prints CSS variable for collections column count.
 *
 * @since 0.9.236
 *
 * @return void
 */
function fairflock_print_collections_cols_css() {
	$cols = fairflock_get_collections_cols();
	$css  = sprintf( ':root{--ff-collections-cols:%d;}', $cols );

	if ( wp_style_is( 'fairflock-components', 'enqueued' ) || wp_style_is( 'fairflock-components', 'registered' ) ) {
		wp_add_inline_style( 'fairflock-components', $css );
		return;
	}

	printf(
		'<style id="fairflock-collections-cols">%s</style>' . "\n",
		$css // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- numeric CSS only
	);
}
add_action( 'wp_enqueue_scripts', 'fairflock_print_collections_cols_css', 25 );

/**
 * Hero height also in the block editor canvas.
 *
 * @since 0.9.216
 *
 * @return void
 */
function fairflock_print_hero_height_css_editor() {
	$vh  = fairflock_get_hero_height_vh();
	$css = sprintf(
		'.editor-styles-wrapper .fs-slider,.block-editor-iframe__body .fs-slider{--ff-hero-vh:%1$dvh;}',
		$vh
	);
	wp_add_inline_style( 'wp-edit-blocks', $css );
}
add_action( 'enqueue_block_editor_assets', 'fairflock_print_hero_height_css_editor', 25 );

/**
 * Collections columns also in the block editor canvas.
 *
 * @since 0.9.236
 *
 * @return void
 */
function fairflock_print_collections_cols_css_editor() {
	$cols = fairflock_get_collections_cols();
	$css  = sprintf(
		'.editor-styles-wrapper,.block-editor-iframe__body{--ff-collections-cols:%d;}',
		$cols
	);
	wp_add_inline_style( 'wp-edit-blocks', $css );
}
add_action( 'enqueue_block_editor_assets', 'fairflock_print_collections_cols_css_editor', 25 );

/**
 * Adds body classes for CSS clearance / layout / shapes.
 *
 * @since 0.9.173
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fairflock_header_style_body_class( $classes ) {
	$classes[] = 'ff-header-style-' . fairflock_get_header_style();
	$classes[] = 'ff-content-width-' . fairflock_get_content_width();
	$classes[] = 'ff-shape-' . fairflock_get_shape_style();
	$classes[] = 'ff-mega-width-' . fairflock_get_mega_width();
	$classes[] = 'ff-hero-nav-' . fairflock_get_hero_nav_layout();
	$classes[] = 'ff-collections-cols-' . fairflock_get_collections_cols();

	return $classes;
}
add_filter( 'body_class', 'fairflock_header_style_body_class' );

/**
 * Mirrors Darstellung body/header classes into the Site Editor canvas.
 *
 * @since 0.9.225
 *
 * @return void
 */
function fairflock_enqueue_darstellung_editor_bridge() {
	$path = FAIRFLOCK_DIR . '/assets/js/editor-darstellung.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-editor-darstellung',
		FAIRFLOCK_URI . '/assets/js/editor-darstellung.js',
		array( 'wp-dom-ready' ),
		FAIRFLOCK_VERSION,
		true
	);

	wp_localize_script(
		'fairflock-editor-darstellung',
		'fairflockDarstellung',
		array(
			'bodyClasses' => array(
				'ff-header-style-' . fairflock_get_header_style(),
				'ff-content-width-' . fairflock_get_content_width(),
				'ff-shape-' . fairflock_get_shape_style(),
				'ff-mega-width-' . fairflock_get_mega_width(),
				'ff-hero-nav-' . fairflock_get_hero_nav_layout(),
				'ff-collections-cols-' . fairflock_get_collections_cols(),
			),
			'headerMod'   => 'ff-header--' . fairflock_get_header_style(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'fairflock_enqueue_darstellung_editor_bridge', 20 );

/**
 * Adds style modifier on the root .ff-header group (frontend + editor render).
 *
 * @since 0.9.173
 *
 * @param string               $block_content Rendered HTML.
 * @param array<string, mixed> $block         Parsed block.
 * @return string
 */
function fairflock_header_style_render_block( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'core/group' !== $block['blockName'] ) {
		return $block_content;
	}

	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( ! str_contains( $class, 'ff-header' ) || str_contains( $class, 'ff-header__' ) ) {
		return $block_content;
	}

	// Only the root shell (class "ff-header"), not nested ff-header__*.
	if ( ! preg_match( '/(^|\s)ff-header(\s|$)/', $class ) ) {
		return $block_content;
	}

	$style = fairflock_get_header_style();
	$mod   = 'ff-header--' . $style;

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $processor->next_tag( array( 'class_name' => 'ff-header' ) ) ) {
		$processor->add_class( $mod );
		return $processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/group', 'fairflock_header_style_render_block', 10, 2 );

/**
 * Registers admin submenu.
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_register_admin_page() {
	add_submenu_page(
		'fairflock-setup',
		__( 'Darstellung', 'fairflock' ),
		__( 'Darstellung', 'fairflock' ),
		'manage_options',
		'fairflock-header-style',
		'fairflock_header_style_render_admin_page'
	);
}
add_action( 'admin_menu', 'fairflock_header_style_register_admin_page', 25 );

/**
 * Saves Darstellung settings (nav, width, shapes).
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_handle_admin_save() {
	if ( ! isset( $_POST['fairflock_header_style_save'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'fairflock_header_style_settings' );

	$header_raw = isset( $_POST['fairflock_header_style'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_header_style'] ) ) : 'floating';
	$header_ok  = array_keys( fairflock_header_style_choices() );
	$header     = in_array( $header_raw, $header_ok, true ) ? $header_raw : 'floating';

	$width_raw = isset( $_POST['fairflock_content_width'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_content_width'] ) ) : 'shell';
	$width_ok  = array_keys( fairflock_content_width_choices() );
	$width     = in_array( $width_raw, $width_ok, true ) ? $width_raw : 'shell';

	$shape_raw = isset( $_POST['fairflock_shape_style'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_shape_style'] ) ) : 'rounded';
	$shape_ok  = array_keys( fairflock_shape_style_choices() );
	$shape     = in_array( $shape_raw, $shape_ok, true ) ? $shape_raw : 'rounded';

	$mega_raw = isset( $_POST['fairflock_mega_width'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_mega_width'] ) ) : 'bar';
	$mega_ok  = array_keys( fairflock_mega_width_choices() );
	$mega     = in_array( $mega_raw, $mega_ok, true ) ? $mega_raw : 'bar';

	$hero_nav_raw = isset( $_POST['fairflock_hero_nav_layout'] ) ? sanitize_key( wp_unslash( (string) $_POST['fairflock_hero_nav_layout'] ) ) : 'edges';
	$hero_nav_ok  = array_keys( fairflock_hero_nav_layout_choices() );
	$hero_nav     = in_array( $hero_nav_raw, $hero_nav_ok, true ) ? $hero_nav_raw : 'edges';

	$hero_vh = isset( $_POST['fairflock_hero_height_vh'] ) ? (int) wp_unslash( $_POST['fairflock_hero_height_vh'] ) : 80;
	$hero_vh = max( 40, min( 100, $hero_vh ) );

	$cols_raw = isset( $_POST['fairflock_collections_cols'] ) ? (int) wp_unslash( $_POST['fairflock_collections_cols'] ) : 3;
	$cols_ok  = array_keys( fairflock_collections_cols_choices() );
	$cols     = in_array( $cols_raw, $cols_ok, true ) ? $cols_raw : 3;

	update_option( 'fairflock_header_style', $header, false );
	update_option( 'fairflock_content_width', $width, false );
	update_option( 'fairflock_shape_style', $shape, false );
	update_option( 'fairflock_mega_width', $mega, false );
	update_option( 'fairflock_hero_nav_layout', $hero_nav, false );
	update_option( 'fairflock_hero_height_vh', $hero_vh, false );
	update_option( 'fairflock_collections_cols', $cols, false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'fairflock-header-style',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'fairflock_header_style_handle_admin_save' );

/**
 * Renders Darstellung admin screen.
 *
 * @since 0.9.173
 *
 * @return void
 */
function fairflock_header_style_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$header_current = fairflock_get_header_style();
	$header_choices = fairflock_header_style_choices();
	$width_current  = fairflock_get_content_width();
	$width_choices  = fairflock_content_width_choices();
	$shape_current  = fairflock_get_shape_style();
	$shape_choices  = fairflock_shape_style_choices();
	$mega_current   = fairflock_get_mega_width();
	$mega_choices   = fairflock_mega_width_choices();
	$hero_nav_cur   = fairflock_get_hero_nav_layout();
	$hero_nav_opts  = fairflock_hero_nav_layout_choices();
	$hero_vh        = fairflock_get_hero_height_vh();
	$cols_current   = fairflock_get_collections_cols();
	$cols_choices   = fairflock_collections_cols_choices();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Darstellung', 'fairflock' ); ?></h1>
		<?php if ( ! empty( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', 'fairflock' ); ?></p></div>
		<?php endif; ?>

		<p><?php esc_html_e( 'Globale Optik für Navigation, Inhaltsbreite, Formen, Mega-Menü, Hero und Kollektionen — gilt für den gesamten Shop.', 'fairflock' ); ?></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'fairflock_header_style_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Nav-Design', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $header_choices as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_header_style" value="<?php echo esc_attr( $key ); ?>" <?php checked( $header_current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Schwebend: abgerundete Leiste mit Abstand zum Rand (Standard). Randlos: volle Breite, oben bündig, ohne Rundungen.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Volle Breite', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $width_choices as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_content_width" value="<?php echo esc_attr( $key ); ?>" <?php checked( $width_current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Inhaltsbreite: max. 1240px zentriert (Standard). Volle Breite: Inhalte nutzen die Viewport-Breite mit dem üblichen Seitenabstand.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Formen', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $shape_choices as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_shape_style" value="<?php echo esc_attr( $key ); ?>" <?php checked( $shape_current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Abgerundet: weiche Ecken an Karten, Modals und Buttons (Standard). Eckig: alle Design-Radien auf 0.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Mega-Menü Breite', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $mega_choices as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_mega_width" value="<?php echo esc_attr( $key ); ?>" <?php checked( $mega_current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Breite des Panels unter der Navigation. Bei Nav-Design „Randlos“ spannt das Mega-Menü immer über die volle Viewport-Breite. Inhalt und Spalten: Site Editor → Template-Teil „Mega-Menü“.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fairflock_hero_height_vh"><?php esc_html_e( 'Hero-Höhe', 'fairflock' ); ?></label></th>
					<td>
						<input
							type="number"
							name="fairflock_hero_height_vh"
							id="fairflock_hero_height_vh"
							value="<?php echo esc_attr( (string) $hero_vh ); ?>"
							min="40"
							max="100"
							step="1"
							class="small-text"
						/>
						<span>vh</span>
						<p class="description">
							<?php esc_html_e( 'Höhe der Hero-Slideshow in Viewport-Höhe (vh), gilt für alle Geräte. Standard: 80. Erlaubt: 40–100.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Hero-Navigation', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $hero_nav_opts as $key => $label ) : ?>
								<label style="display:block;margin:0 0 10px;">
									<input type="radio" name="fairflock_hero_nav_layout" value="<?php echo esc_attr( $key ); ?>" <?php checked( $hero_nav_cur, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Pfeile und Punkte am unteren Hero-Rand — shopweit, unabhängig vom Block-Stil „Hero: Inhalt mittig“. „Neben den Punkten“ fasst alles zu einer mittigen Gruppe zusammen.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Kollektionen-Spalten', 'fairflock' ); ?></th>
					<td>
						<fieldset>
							<?php foreach ( $cols_choices as $key => $label ) : ?>
								<label style="display:inline-block;margin:0 16px 10px 0;">
									<input type="radio" name="fairflock_collections_cols" value="<?php echo esc_attr( (string) $key ); ?>" <?php checked( $cols_current, $key ); ?> />
									<strong><?php echo esc_html( $label ); ?></strong>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description">
							<?php esc_html_e( 'Desktop-Raster der Kollektionen-Karten (Startseite und /kategorien/). Auf dem Handy bleibt der horizontale Slider. „Mehr anzeigen“ zeigt weiterhin 3 Reihen.', 'fairflock' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Speichern', 'fairflock' ), 'primary', 'fairflock_header_style_save' ); ?>
		</form>
	</div>
	<?php
}
