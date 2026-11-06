<?php
/**
 * Per-product spec table, shown under the product description.
 *
 * @package FairFlock
 * @since 0.9.278
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Post meta: JSON table (title, columns, rows). */
const FAIRFLOCK_META_PRODUCT_TABLE = '_fairflock_product_table';

/** Max columns a shop can add. */
const FAIRFLOCK_PRODUCT_TABLE_MAX_COLUMNS = 16;

/** Max data rows a shop can add. */
const FAIRFLOCK_PRODUCT_TABLE_MAX_ROWS = 24;

/** Product meta: id of a shared table template, empty for a custom table. */
const FAIRFLOCK_META_PRODUCT_TABLE_TEMPLATE = '_fairflock_product_table_template';

/** Option: list of table templates. */
const FAIRFLOCK_PRODUCT_TABLE_TEMPLATES_OPTION = 'fairflock_product_table_templates';

/** Max templates a shop can store. */
const FAIRFLOCK_PRODUCT_TABLE_MAX_TEMPLATES = 40;

/** Max length of a header or cell. */
const FAIRFLOCK_PRODUCT_TABLE_CELL_LENGTH = 180;

/**
 * Product id that owns the table (variations use the parent).
 *
 * @since 0.9.278
 *
 * @param int|WC_Product $product Product ID or object.
 * @return int
 */
function fairflock_product_table_product_id( $product ) {
	if ( $product instanceof WC_Product ) {
		if ( $product->is_type( 'variation' ) ) {
			return (int) $product->get_parent_id();
		}
		return (int) $product->get_id();
	}
	return absint( $product );
}

/**
 * Table saved on the product itself.
 *
 * @since 0.9.285
 *
 * @param int|WC_Product $product Product ID or object.
 * @return array{title:string,columns:array<int,string>,rows:array<int,array<int,string>>}
 */
function fairflock_product_table_stored( $product ) {
	$empty = array(
		'title'   => '',
		'columns' => array(),
		'rows'    => array(),
	);
	$id    = fairflock_product_table_product_id( $product );
	if ( ! $id ) {
		return $empty;
	}

	$raw = get_post_meta( $id, FAIRFLOCK_META_PRODUCT_TABLE, true );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return $empty;
	}

	$data = json_decode( $raw, true );
	if ( ! is_array( $data ) ) {
		return $empty;
	}

	return fairflock_product_table_normalize( $data );
}

/**
 * Table shown for the product: a shared template, or the product's own table.
 *
 * @since 0.9.278
 *
 * @param int|WC_Product $product Product ID or object.
 * @return array{title:string,columns:array<int,string>,rows:array<int,array<int,string>>}
 */
function fairflock_product_table_get( $product ) {
	$id          = fairflock_product_table_product_id( $product );
	$template_id = $id ? (string) get_post_meta( $id, FAIRFLOCK_META_PRODUCT_TABLE_TEMPLATE, true ) : '';
	if ( '' !== $template_id && function_exists( 'fairflock_product_table_template_find' ) ) {
		$template = fairflock_product_table_template_find( $template_id );
		if ( is_array( $template ) && ! empty( $template['rows'] ) ) {
			return array(
				'title'   => $template['title'],
				'columns' => $template['columns'],
				'rows'    => $template['rows'],
			);
		}
	}

	return fairflock_product_table_stored( $product );
}

/**
 * Drops empty rows and clamps size.
 *
 * @since 0.9.278
 *
 * @param array $data Raw table.
 * @return array{title:string,columns:array<int,string>,rows:array<int,array<int,string>>}
 */
function fairflock_product_table_normalize( $data ) {
	$title   = isset( $data['title'] ) ? sanitize_text_field( (string) $data['title'] ) : '';
	$title   = fairflock_product_table_repair_text( $title );
	$title   = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 80 ) : substr( $title, 0, 80 );
	$columns = array();

	if ( isset( $data['columns'] ) && is_array( $data['columns'] ) ) {
		foreach ( $data['columns'] as $column ) {
			if ( count( $columns ) >= FAIRFLOCK_PRODUCT_TABLE_MAX_COLUMNS ) {
				break;
			}
			$columns[] = fairflock_product_table_cell( $column );
		}
	}

	if ( empty( $columns ) ) {
		$columns = array( '', '' );
	}

	$count = count( $columns );
	$rows  = array();
	if ( isset( $data['rows'] ) && is_array( $data['rows'] ) ) {
		foreach ( $data['rows'] as $row ) {
			if ( count( $rows ) >= FAIRFLOCK_PRODUCT_TABLE_MAX_ROWS ) {
				break;
			}
			if ( ! is_array( $row ) ) {
				continue;
			}
			$cells  = array();
			$filled = false;
			for ( $i = 0; $i < $count; $i++ ) {
				$cell     = fairflock_product_table_cell( isset( $row[ $i ] ) ? $row[ $i ] : '' );
				$cells[]  = $cell;
				$filled   = $filled || '' !== $cell;
			}
			if ( $filled ) {
				$rows[] = $cells;
			}
		}
	}

	return array(
		'title'   => $title,
		'columns' => $columns,
		'rows'    => $rows,
	);
}

/**
 * One plain-text cell.
 *
 * @since 0.9.278
 *
 * @param mixed $value Raw value.
 * @return string
 */
function fairflock_product_table_cell( $value ) {
	$text = sanitize_text_field( is_string( $value ) ? $value : '' );
	$text = fairflock_product_table_repair_text( $text );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, FAIRFLOCK_PRODUCT_TABLE_CELL_LENGTH );
	}
	return substr( $text, 0, FAIRFLOCK_PRODUCT_TABLE_CELL_LENGTH );
}

/**
 * Restores letters whose JSON escapes lost their backslash when WordPress saved the table.
 *
 * "Größe" was stored as "Gru00f6u00dfe".
 *
 * @since 0.9.286
 *
 * @param string $text Cell or title text.
 * @return string
 */
function fairflock_product_table_repair_text( $text ) {
	if ( ! is_string( $text ) || false === strpos( $text, 'u00' ) ) {
		return $text;
	}
	$repaired = preg_replace_callback(
		'/u00([0-9a-fA-F]{2})/',
		static function ( $matches ) {
			$code = hexdec( $matches[1] );
			if ( $code < 128 ) {
				return $matches[0];
			}
			return html_entity_decode( '&#' . $code . ';', ENT_QUOTES, 'UTF-8' );
		},
		$text
	);
	return is_string( $repaired ) ? $repaired : $text;
}

/**
 * Frontend table HTML, empty when nothing was filled in.
 *
 * @since 0.9.278
 *
 * @param int|WC_Product $product Product ID or object.
 * @return string
 */
function fairflock_product_table_markup( $product ) {
	$data = fairflock_product_table_get( $product );
	if ( empty( $data['rows'] ) ) {
		return '';
	}

	$columns = $data['columns'];
	$title   = $data['title'];
	$uid     = 'ff-ptable-title-' . fairflock_product_table_product_id( $product ) . '-' . wp_unique_id();
	$label   = '' !== $title ? $title : __( 'Tabelle', 'fairflock' );
	$html    = '<section class="ff-product-table" data-cols="' . esc_attr( (string) count( $columns ) ) . '">';
	$html   .= '<button type="button" class="ff-product-table__open" data-ff-ptable-open>';
	$html   .= '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M4 5.5h16M4 12h16M4 18.5h16M9 4v16M15 4v16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>';
	$html   .= esc_html__( 'Tabelle öffnen', 'fairflock' );
	$html   .= '</button>';
	$html   .= '<div class="ff-product-table__dialog" hidden role="dialog" aria-modal="true" aria-labelledby="' . esc_attr( $uid ) . '">';
	$html   .= '<div class="ff-product-table__bar">';
	$html   .= '<h3 class="ff-product-table__title" id="' . esc_attr( $uid ) . '">' . esc_html( $label ) . '</h3>';
	$html   .= '<button type="button" class="ff-product-table__close" data-ff-ptable-close>';
	$html   .= '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
	$html   .= esc_html__( 'Schließen', 'fairflock' );
	$html   .= '</button></div>';
	$html   .= '<div class="ff-product-table__gallery" data-ff-ptable-gallery hidden></div>';
	$html   .= '<div class="ff-product-table__sheet">';
	$html   .= '<button type="button" class="ff-product-table__arrow is-prev" data-ff-ptable-scroll="-1" aria-label="' . esc_attr__( 'Nach links', 'fairflock' ) . '" hidden>';
	$html   .= '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M14.5 6 8.5 12l6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$html   .= '</button>';
	$html   .= '<div class="ff-product-table__wrap"><table>';
	$html .= '<thead><tr>';
	foreach ( $columns as $index => $column ) {
		$label = '' !== $column ? $column : sprintf(
			/* translators: %d: column number */
			__( 'Spalte %d', 'fairflock' ),
			$index + 1
		);
		$html .= '<th scope="col">' . esc_html( $label ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';

	foreach ( $data['rows'] as $row ) {
		$html .= '<tr>';
		foreach ( $columns as $index => $column ) {
			$label = '' !== $column ? $column : sprintf(
				/* translators: %d: column number */
				__( 'Spalte %d', 'fairflock' ),
				$index + 1
			);
			$cell  = isset( $row[ $index ] ) ? $row[ $index ] : '';
			$attr  = ' data-label="' . esc_attr( $label ) . '"';
			if ( 0 === $index && count( $columns ) > 1 ) {
				$html .= '<th scope="row"' . $attr . '>' . esc_html( $cell ) . '</th>';
			} else {
				$html .= '<td' . $attr . '>' . esc_html( $cell ) . '</td>';
			}
		}
		$html .= '</tr>';
	}

	$html .= '</tbody></table></div>';
	$html .= '<button type="button" class="ff-product-table__arrow is-next" data-ff-ptable-scroll="1" aria-label="' . esc_attr__( 'Nach rechts', 'fairflock' ) . '" hidden>';
	$html .= '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M9.5 6 15.5 12l-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$html .= '</button></div></div></section>';
	return $html;
}

/**
 * Product data tab.
 *
 * @since 0.9.278
 *
 * @param array $tabs Tabs.
 * @return array
 */
function fairflock_product_table_admin_tab( $tabs ) {
	$tabs['fairflock_table'] = array(
		'label'    => __( 'Tabelle', 'fairflock' ),
		'target'   => 'fairflock_product_table',
		'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
		'priority' => 65,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'fairflock_product_table_admin_tab' );

/**
 * Editor panel.
 *
 * @since 0.9.278
 *
 * @return void
 */
function fairflock_product_table_admin_editor( $data ) {
	$columns = isset( $data['columns'] ) && is_array( $data['columns'] ) ? $data['columns'] : array();
	$rows    = isset( $data['rows'] ) && is_array( $data['rows'] ) ? $data['rows'] : array();
	$title   = isset( $data['title'] ) ? (string) $data['title'] : '';
	if ( count( $columns ) < 2 && empty( $rows ) ) {
		$columns = array( '', '' );
	}

	echo '<div class="ff-ptable-admin__title">';
	echo '<label for="ff_ptable_title">' . esc_html__( 'Überschrift', 'fairflock' ) . '</label>';
	printf(
		'<input type="text" id="ff_ptable_title" name="ff_ptable_title" value="%s" maxlength="80" placeholder="%s" />',
		esc_attr( $title ),
		esc_attr__( 'z. B. Material & Pflege', 'fairflock' )
	);
	echo '</div>';

	echo '<div class="ff-ptable-admin__tools">';
	echo '<button type="button" class="button button-small" data-ff-ptable-add-col>' . esc_html__( 'Spalte', 'fairflock' ) . '</button>';
	echo '<button type="button" class="button button-small button-primary" data-ff-ptable-add-row>' . esc_html__( 'Zeile', 'fairflock' ) . '</button>';
	echo '</div>';

	echo '<div class="ff-ptable-admin__sheet">';
	echo '<table class="ff-ptable-admin__table">';
	echo '<thead><tr data-ff-ptable-columns>';
	foreach ( $columns as $index => $column ) {
		fairflock_product_table_admin_column_field( $column, $index );
	}
	echo '<th class="ff-ptable-admin__actions" scope="col"></th>';
	echo '</tr></thead>';
	echo '<tbody data-ff-ptable-rows>';
	foreach ( $rows as $row_index => $row ) {
		fairflock_product_table_admin_row( $row, $row_index, count( $columns ) );
	}
	echo '</tbody></table></div>';
	echo '<p class="ff-ptable-admin__empty" data-ff-ptable-empty' . ( empty( $rows ) ? '' : ' hidden' ) . '>' . esc_html__( 'Noch keine Zeile. Mit „Zeile“ füllst du die Tabelle.', 'fairflock' ) . '</p>';
}

/**
 * Editor panel.
 *
 * @since 0.9.278
 *
 * @return void
 */
function fairflock_product_table_admin_panel() {
	global $post;

	$data      = ( $post instanceof WP_Post ) ? fairflock_product_table_stored( $post->ID ) : fairflock_product_table_normalize( array() );
	$selected  = ( $post instanceof WP_Post ) ? (string) get_post_meta( $post->ID, FAIRFLOCK_META_PRODUCT_TABLE_TEMPLATE, true ) : '';
	$templates = function_exists( 'fairflock_product_table_templates' ) ? fairflock_product_table_templates() : array();
	$using     = '';
	foreach ( $templates as $template ) {
		if ( isset( $template['id'] ) && $template['id'] === $selected ) {
			$using = $selected;
			break;
		}
	}

	echo '<div id="fairflock_product_table" class="panel woocommerce_options_panel hidden">';
	echo '<div class="ff-ptable-admin' . ( '' !== $using ? ' is-from-template' : '' ) . '" data-ff-ptable-admin data-max-columns="' . esc_attr( (string) FAIRFLOCK_PRODUCT_TABLE_MAX_COLUMNS ) . '" data-max-rows="' . esc_attr( (string) FAIRFLOCK_PRODUCT_TABLE_MAX_ROWS ) . '">';
	echo '<input type="hidden" name="ff_ptable_present" value="1" />';
	echo '<p class="ff-ptable-admin__lead">' . esc_html__( 'Diese Tabelle steht auf der Produktseite direkt unter der Beschreibung. Für gleiche Produkte kannst du eine Vorlage wählen. Leere Zeilen werden nicht angezeigt.', 'fairflock' ) . '</p>';

	echo '<div class="ff-ptable-admin__source">';
	echo '<label for="ff_ptable_template">' . esc_html__( 'Vorlage', 'fairflock' ) . '</label>';
	echo '<select id="ff_ptable_template" name="ff_ptable_template" data-ff-ptable-template>';
	echo '<option value="">' . esc_html__( 'Eigene Tabelle', 'fairflock' ) . '</option>';
	foreach ( $templates as $template ) {
		printf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $template['id'] ),
			selected( $using, $template['id'], false ),
			esc_html( $template['name'] )
		);
	}
	echo '</select>';
	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<a class="ff-ptable-admin__source-link" href="' . esc_url( admin_url( 'admin.php?page=fairflock-produkte' ) ) . '">' . esc_html__( 'Vorlagen', 'fairflock' ) . '</a>';
	}
	echo '</div>';
	echo '<p class="ff-ptable-admin__source-note">' . esc_html__( 'Diese Tabelle kommt aus der Vorlage. Änderungen unter FairFlock → Produkte gelten für alle Produkte, die sie verwenden.', 'fairflock' ) . '</p>';

	if ( ! empty( $templates ) ) {
		echo '<div class="ff-ptable-admin__previews">';
		foreach ( $templates as $template ) {
			echo '<div class="ff-ptable-admin__preview" data-ff-ptable-preview="' . esc_attr( $template['id'] ) . '"' . ( $template['id'] === $using ? '' : ' hidden' ) . '>';
			if ( function_exists( 'fairflock_product_table_template_preview' ) ) {
				fairflock_product_table_template_preview( $template );
			}
			echo '</div>';
		}
		echo '</div>';
	}

	echo '<div class="ff-ptable-admin__editor">';
	fairflock_product_table_admin_editor( $data );
	echo '</div>';

	echo '</div></div>';
}
add_action( 'woocommerce_product_data_panels', 'fairflock_product_table_admin_panel' );

/**
 * One column-header field.
 *
 * @since 0.9.278
 *
 * @param string $value Header text.
 * @param int    $index Column index.
 * @return void
 */
function fairflock_product_table_admin_column_field( $value, $index ) {
	$placeholders = array(
		__( 'z. B. Merkmal', 'fairflock' ),
		__( 'z. B. Angabe', 'fairflock' ),
	);
	$placeholder  = isset( $placeholders[ $index ] ) ? $placeholders[ $index ] : __( 'Spaltenname', 'fairflock' );

	echo '<th class="ff-ptable-admin__col" data-ff-ptable-column scope="col">';
	echo '<div class="ff-ptable-admin__head">';
	printf(
		'<input type="text" name="ff_ptable_columns[]" value="%s" maxlength="%d" placeholder="%s" aria-label="%s" />',
		esc_attr( $value ),
		FAIRFLOCK_PRODUCT_TABLE_CELL_LENGTH,
		esc_attr( $placeholder ),
		esc_attr(
			sprintf(
				/* translators: %d: column number */
				__( 'Spalte %d', 'fairflock' ),
				$index + 1
			)
		)
	);
	echo '<button type="button" class="ff-ptable-admin__icon" data-ff-ptable-remove-col aria-label="' . esc_attr__( 'Spalte entfernen', 'fairflock' ) . '">&times;</button>';
	echo '</div></th>';
}

/**
 * One data row.
 *
 * @since 0.9.278
 *
 * @param array $row          Cell values.
 * @param int   $row_index    Row index.
 * @param int   $column_count Column count.
 * @return void
 */
function fairflock_product_table_admin_row( $row, $row_index, $column_count ) {
	echo '<tr data-ff-ptable-row>';
	for ( $i = 0; $i < $column_count; $i++ ) {
		$cell = isset( $row[ $i ] ) ? $row[ $i ] : '';
		echo '<td>';
		printf(
			'<input type="text" name="ff_ptable_rows[%1$d][%2$d]" value="%3$s" maxlength="%4$d" aria-label="%5$s" />',
			(int) $row_index,
			(int) $i,
			esc_attr( $cell ),
			FAIRFLOCK_PRODUCT_TABLE_CELL_LENGTH,
			esc_attr(
				sprintf(
					/* translators: 1: row number, 2: column number */
					__( 'Zeile %1$d, Spalte %2$d', 'fairflock' ),
					$row_index + 1,
					$i + 1
				)
			)
		);
		echo '</td>';
	}
	echo '<td class="ff-ptable-admin__actions"><button type="button" class="ff-ptable-admin__icon" data-ff-ptable-remove-row aria-label="' . esc_attr__( 'Zeile entfernen', 'fairflock' ) . '">&times;</button></td>';
	echo '</tr>';
}

/**
 * Persist the table on product save.
 *
 * @since 0.9.278
 *
 * @param int $post_id Product ID.
 * @return void
 */
function fairflock_product_table_save( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the product save nonce.
	if ( ! isset( $_POST['ff_ptable_present'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$columns = isset( $_POST['ff_ptable_columns'] ) && is_array( $_POST['ff_ptable_columns'] ) ? wp_unslash( $_POST['ff_ptable_columns'] ) : array();
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$rows = isset( $_POST['ff_ptable_rows'] ) && is_array( $_POST['ff_ptable_rows'] ) ? wp_unslash( $_POST['ff_ptable_rows'] ) : array();
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$title       = isset( $_POST['ff_ptable_title'] ) ? wp_unslash( $_POST['ff_ptable_title'] ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$template_id = isset( $_POST['ff_ptable_template'] ) ? sanitize_key( wp_unslash( $_POST['ff_ptable_template'] ) ) : '';
	$template    = ( '' !== $template_id && function_exists( 'fairflock_product_table_template_find' ) ) ? fairflock_product_table_template_find( $template_id ) : null;
	if ( is_array( $template ) ) {
		update_post_meta( $post_id, FAIRFLOCK_META_PRODUCT_TABLE_TEMPLATE, $template_id );
	} else {
		delete_post_meta( $post_id, FAIRFLOCK_META_PRODUCT_TABLE_TEMPLATE );
	}

	$data = fairflock_product_table_normalize(
		array(
			'title'   => $title,
			'columns' => $columns,
			'rows'    => $rows,
		)
	);

	if ( empty( $data['rows'] ) ) {
		delete_post_meta( $post_id, FAIRFLOCK_META_PRODUCT_TABLE );
		return;
	}

	$encoded = wp_json_encode( $data, JSON_UNESCAPED_UNICODE );
	if ( ! is_string( $encoded ) ) {
		return;
	}
	update_post_meta( $post_id, FAIRFLOCK_META_PRODUCT_TABLE, wp_slash( $encoded ) );
}
add_action( 'woocommerce_process_product_meta', 'fairflock_product_table_save' );

/**
 * Keep the description tab when a product has a table but no description text.
 *
 * @since 0.9.278
 *
 * @param array $tabs Product tabs.
 * @return array
 */
function fairflock_product_table_ensure_description_tab( $tabs ) {
	if ( isset( $tabs['description'] ) ) {
		return $tabs;
	}
	$product_id = get_the_ID();
	if ( ! $product_id || '' === fairflock_product_table_markup( $product_id ) ) {
		return $tabs;
	}
	$tabs['description'] = array(
		'title'    => __( 'Beschreibung', 'woocommerce' ),
		'priority' => 10,
		'callback' => 'woocommerce_product_description_tab',
	);
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'fairflock_product_table_ensure_description_tab', 20 );

/**
 * Append the table to the product description on the product page.
 *
 * @since 0.9.278
 *
 * @param string $content Post content.
 * @return string
 */
function fairflock_product_table_append_content( $content ) {
	if ( ! is_string( $content ) || is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}
	if ( ! is_singular( 'product' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( str_contains( $content, 'ff-product-table' ) ) {
		return $content;
	}

	$markup = fairflock_product_table_markup( get_the_ID() );
	if ( '' === $markup ) {
		return $content;
	}
	return $content . $markup;
}
add_filter( 'the_content', 'fairflock_product_table_append_content', 20 );

/**
 * Admin styles and the row editor script.
 *
 * @since 0.9.278
 *
 * @param string $hook Current admin page.
 * @return void
 */
function fairflock_product_table_admin_assets( $hook ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only page check for asset enqueue.
	$page         = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	$is_templates = 'fairflock-produkte' === $page;
	$is_product   = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
	if ( $is_product ) {
		$screen     = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$is_product = $screen && 'product' === $screen->post_type;
	}
	if ( ! $is_templates && ! $is_product ) {
		return;
	}

	wp_enqueue_script(
		'fairflock-product-table-admin',
		FAIRFLOCK_URI . '/assets/js/product-table-admin.js',
		array(),
		FAIRFLOCK_VERSION,
		true
	);

	wp_register_style( 'fairflock-product-table-admin', false, array(), FAIRFLOCK_VERSION );
	wp_enqueue_style( 'fairflock-product-table-admin' );
	wp_add_inline_style( 'fairflock-product-table-admin', fairflock_product_table_admin_css() );
}
add_action( 'admin_enqueue_scripts', 'fairflock_product_table_admin_assets' );

/**
 * Product-data tab icon plus the editor layout.
 *
 * @since 0.9.278
 *
 * @return string
 */
function fairflock_product_table_admin_css() {
	return '
	#woocommerce-product-data ul.product_data_tabs li.fairflock_table_options a::before {
		font-family: Dashicons;
		content: "\\f163";
	}
	#woocommerce-product-data #fairflock_product_table.woocommerce_options_panel {
		padding: 10px 12px 14px !important;
	}
	#woocommerce-product-data #fairflock_product_table p,
	#woocommerce-product-data #fairflock_product_table .ff-ptable-admin__title,
	#woocommerce-product-data #fairflock_product_table .ff-ptable-admin__tools {
		float: none !important;
		width: auto !important;
		margin: 0 0 8px !important;
		padding: 0 !important;
	}
	#woocommerce-product-data #fairflock_product_table label {
		float: none !important;
		width: auto !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	#fairflock_product_table .ff-ptable-admin,
	.ff-ptable-screen .ff-ptable-admin {
		clear: both;
		max-width: 920px;
	}
	.ff-ptable-admin__source {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin: 0 0 8px;
	}
	.ff-ptable-admin__source label {
		flex: none;
		font-weight: 600;
		font-size: 12px;
		color: #1d2327;
	}
	#woocommerce-product-data #fairflock_product_table .ff-ptable-admin__source select,
	.ff-ptable-admin__source select {
		width: auto !important;
		max-width: 280px;
		height: 28px;
		margin: 0;
		float: none !important;
	}
	.ff-ptable-admin__source-link {
		font-size: 12px;
	}
	.ff-ptable-admin__source-note {
		display: none;
		margin: 0 0 8px !important;
		color: #646970;
		font-size: 12px;
		line-height: 1.4;
	}
	.ff-ptable-admin.is-from-template .ff-ptable-admin__source-note {
		display: block;
	}
	.ff-ptable-admin.is-from-template .ff-ptable-admin__editor {
		display: none;
	}
	.ff-ptable-admin__preview {
		margin: 0 0 8px;
		overflow-x: auto;
	}
	.ff-ptable-admin__preview[hidden] {
		display: none !important;
	}
	.ff-ptable-admin__preview table {
		width: 100%;
		border-collapse: collapse;
		background: #fff;
	}
	.ff-ptable-admin__preview th,
	.ff-ptable-admin__preview td {
		border: 1px solid #dcdcde;
		padding: 6px 8px;
		font-size: 13px;
		line-height: 1.4;
		text-align: left;
		vertical-align: top;
	}
	.ff-ptable-admin__preview th {
		background: #f6f7f7;
		font-weight: 600;
	}
	.ff-ptable-screen .ff-ptable-admin__name {
		margin-bottom: 12px;
	}
	.ff-ptable-admin__lead {
		color: #646970;
		font-size: 12px;
		line-height: 1.4;
	}
	.ff-ptable-admin__title {
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.ff-ptable-admin__title label {
		flex: none;
		font-weight: 600;
		font-size: 12px;
		color: #1d2327;
	}
	.ff-ptable-admin__title input {
		width: 280px;
		max-width: 100%;
		height: 28px;
		margin: 0;
		padding: 0 8px;
	}
	.ff-ptable-admin__tools {
		display: flex;
		gap: 6px;
	}
	.ff-ptable-admin__tools .button {
		min-height: 26px;
		line-height: 24px;
		padding: 0 8px;
	}
	.ff-ptable-admin__sheet {
		overflow-x: auto;
		border: 1px solid #c3c4c7;
		background: #fff;
	}
	.ff-ptable-admin__table {
		width: 100%;
		min-width: 420px;
		border-collapse: collapse;
		border-spacing: 0;
		background: #fff;
	}
	.ff-ptable-admin__table th,
	.ff-ptable-admin__table td {
		border: 1px solid #dcdcde;
		padding: 0;
		vertical-align: middle;
		background: #fff;
		text-align: left;
	}
	.ff-ptable-admin__table thead th {
		background: #f6f7f7;
	}
	.ff-ptable-admin__table tbody tr:nth-child(even) td {
		background: #fbfbfc;
	}
	.ff-ptable-admin__col {
		min-width: 160px;
	}
	.ff-ptable-admin__actions {
		width: 28px;
		min-width: 28px;
		background: #f6f7f7 !important;
	}
	.ff-ptable-admin__head {
		display: flex;
		align-items: stretch;
		min-width: 0;
	}
	.ff-ptable-admin__table input[type="text"] {
		display: block;
		width: 100% !important;
		height: 28px;
		min-height: 0;
		margin: 0;
		padding: 0 8px;
		border: 0;
		border-radius: 0;
		box-shadow: none;
		background: transparent;
		font-size: 13px;
		line-height: 28px;
	}
	.ff-ptable-admin__head input {
		flex: 1;
		min-width: 0;
		font-weight: 600;
	}
	.ff-ptable-admin__table input[type="text"]:focus {
		outline: 2px solid #2271b1;
		outline-offset: -2px;
		background: #fff;
	}
	.ff-ptable-admin__icon {
		flex: none;
		width: 28px;
		height: 28px;
		margin: 0;
		padding: 0;
		border: 0;
		border-left: 1px solid #dcdcde;
		border-radius: 0;
		background: transparent;
		color: #646970;
		font-size: 16px;
		line-height: 26px;
		cursor: pointer;
	}
	.ff-ptable-admin__actions .ff-ptable-admin__icon {
		width: 100%;
		border-left: 0;
	}
	.ff-ptable-admin__icon:hover {
		background: #f0f0f1;
		color: #b32d2e;
	}
	.ff-ptable-admin__empty {
		margin: 6px 0 0 !important;
		color: #646970;
		font-size: 12px;
	}
	';
}

require_once __DIR__ . '/product-table-templates.php';
