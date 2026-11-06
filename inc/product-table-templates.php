<?php
/**
 * Shared product-table templates under FairFlock → Produkte.
 *
 * @package FairFlock
 * @since 0.9.285
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All saved templates.
 *
 * @since 0.9.285
 *
 * @return array<int,array{id:string,name:string,title:string,columns:array<int,string>,rows:array<int,array<int,string>>}>
 */
function fairflock_product_table_templates() {
	$stored = get_option( FAIRFLOCK_PRODUCT_TABLE_TEMPLATES_OPTION, array() );
	if ( ! is_array( $stored ) ) {
		return array();
	}

	$templates = array();
	foreach ( $stored as $item ) {
		if ( ! is_array( $item ) || empty( $item['id'] ) ) {
			continue;
		}
		$id = sanitize_key( (string) $item['id'] );
		if ( ! preg_match( '/^ffpt_[a-z0-9]+$/', $id ) ) {
			continue;
		}
		$data = fairflock_product_table_normalize( $item );
		$name = isset( $item['name'] ) ? sanitize_text_field( (string) $item['name'] ) : '';
		$name = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 80 ) : substr( $name, 0, 80 );
		if ( '' === $name ) {
			$name = '' !== $data['title'] ? $data['title'] : __( 'Vorlage', 'fairflock' );
		}
		$templates[] = array(
			'id'      => $id,
			'name'    => $name,
			'title'   => $data['title'],
			'columns' => $data['columns'],
			'rows'    => $data['rows'],
		);
	}

	return $templates;
}

/**
 * One template, or null.
 *
 * @since 0.9.285
 *
 * @param string $id Template id.
 * @return array{id:string,name:string,title:string,columns:array<int,string>,rows:array<int,array<int,string>>}|null
 */
function fairflock_product_table_template_find( $id ) {
	$id = sanitize_key( (string) $id );
	foreach ( fairflock_product_table_templates() as $template ) {
		if ( $template['id'] === $id ) {
			return $template;
		}
	}
	return null;
}

/**
 * Replace the stored template list.
 *
 * @since 0.9.285
 *
 * @param array $templates Templates.
 * @return void
 */
function fairflock_product_table_templates_update( $templates ) {
	update_option( FAIRFLOCK_PRODUCT_TABLE_TEMPLATES_OPTION, array_values( $templates ), true );
}

/**
 * New template id.
 *
 * @since 0.9.285
 *
 * @return string
 */
function fairflock_product_table_template_new_id() {
	return 'ffpt_' . strtolower( wp_generate_password( 8, false, false ) );
}

/**
 * Readonly preview of a template.
 *
 * @since 0.9.285
 *
 * @param array $template Template.
 * @return void
 */
function fairflock_product_table_template_preview( $template ) {
	$columns = isset( $template['columns'] ) && is_array( $template['columns'] ) ? $template['columns'] : array();
	$rows    = isset( $template['rows'] ) && is_array( $template['rows'] ) ? $template['rows'] : array();
	if ( empty( $rows ) ) {
		echo '<p class="ff-ptable-admin__empty">' . esc_html__( 'Diese Vorlage hat noch keine Zeilen.', 'fairflock' ) . '</p>';
		return;
	}

	echo '<table><thead><tr>';
	foreach ( $columns as $index => $column ) {
		$label = '' !== $column ? $column : sprintf(
			/* translators: %d: column number */
			__( 'Spalte %d', 'fairflock' ),
			$index + 1
		);
		echo '<th>' . esc_html( $label ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		echo '<tr>';
		foreach ( $columns as $index => $column ) {
			$cell = ( is_array( $row ) && isset( $row[ $index ] ) ) ? $row[ $index ] : '';
			echo '<td>' . esc_html( (string) $cell ) . '</td>';
		}
		echo '</tr>';
	}
	echo '</tbody></table>';
}

/**
 * Draft kept when saving a template fails validation.
 *
 * @since 0.9.285
 *
 * @param array|null $draft Draft to store, or null to read.
 * @return array|false|null
 */
function fairflock_product_table_template_draft( $draft = null ) {
	static $stored = false;
	if ( null !== $draft ) {
		$stored = $draft;
	}
	return $stored;
}

/**
 * Notice for the templates screen.
 *
 * @since 0.9.285
 *
 * @param array|null $notice Notice to store, or null to read.
 * @return array|null
 */
function fairflock_product_table_template_notice( $notice = null ) {
	static $stored = null;
	if ( null !== $notice ) {
		$stored = $notice;
	}
	return $stored;
}

/**
 * Registers FairFlock → Produkte.
 *
 * @since 0.9.285
 *
 * @return void
 */
function fairflock_product_table_templates_menu() {
	$hook = add_submenu_page(
		'fairflock-setup',
		__( 'Produkte', 'fairflock' ),
		__( 'Produkte', 'fairflock' ),
		'edit_theme_options',
		'fairflock-produkte',
		'fairflock_product_table_templates_page'
	);
	if ( is_string( $hook ) && '' !== $hook ) {
		add_action( 'load-' . $hook, 'fairflock_product_table_templates_handle' );
	}
}
add_action( 'admin_menu', 'fairflock_product_table_templates_menu' );

/**
 * Saves or deletes a template before the screen renders.
 *
 * @since 0.9.285
 *
 * @return void
 */
function fairflock_product_table_templates_handle() {
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Du darfst Vorlagen nicht ändern.', 'fairflock' ) );
	}

	$action = isset( $_POST['ff_ptable_template_action'] ) ? sanitize_key( wp_unslash( $_POST['ff_ptable_template_action'] ) ) : '';
	if ( 'delete' === $action ) {
		check_admin_referer( 'fairflock_ptable_template_delete' );
		$id        = isset( $_POST['ff_ptable_template_id'] ) ? sanitize_key( wp_unslash( $_POST['ff_ptable_template_id'] ) ) : '';
		$templates = array();
		foreach ( fairflock_product_table_templates() as $template ) {
			if ( $template['id'] !== $id ) {
				$templates[] = $template;
			}
		}
		fairflock_product_table_templates_update( $templates );
		wp_safe_redirect( admin_url( 'admin.php?page=fairflock-produkte&deleted=1' ) );
		exit;
	}

	if ( 'save' !== $action ) {
		return;
	}

	check_admin_referer( 'fairflock_ptable_template_save' );

	$id   = isset( $_POST['ff_ptable_template_id'] ) ? sanitize_key( wp_unslash( $_POST['ff_ptable_template_id'] ) ) : '';
	$name = isset( $_POST['ff_ptable_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ff_ptable_name'] ) ) : '';
	$name = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 80 ) : substr( $name, 0, 80 );
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cells are sanitized in fairflock_product_table_normalize().
	$columns = isset( $_POST['ff_ptable_columns'] ) && is_array( $_POST['ff_ptable_columns'] ) ? wp_unslash( $_POST['ff_ptable_columns'] ) : array();
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cells are sanitized in fairflock_product_table_normalize().
	$rows  = isset( $_POST['ff_ptable_rows'] ) && is_array( $_POST['ff_ptable_rows'] ) ? wp_unslash( $_POST['ff_ptable_rows'] ) : array();
	$title = isset( $_POST['ff_ptable_title'] ) ? sanitize_text_field( wp_unslash( $_POST['ff_ptable_title'] ) ) : '';
	$data  = fairflock_product_table_normalize(
		array(
			'title'   => $title,
			'columns' => $columns,
			'rows'    => $rows,
		)
	);

	$draft = array(
		'id'      => $id,
		'name'    => $name,
		'title'   => $data['title'],
		'columns' => $data['columns'],
		'rows'    => $data['rows'],
	);

	if ( '' === $name ) {
		fairflock_product_table_template_draft( $draft );
		fairflock_product_table_template_notice(
			array(
				'type' => 'error',
				'text' => __( 'Bitte einen Namen angeben.', 'fairflock' ),
			)
		);
		return;
	}
	if ( empty( $data['rows'] ) ) {
		fairflock_product_table_template_draft( $draft );
		fairflock_product_table_template_notice(
			array(
				'type' => 'error',
				'text' => __( 'Die Vorlage braucht mindestens eine ausgefüllte Zeile.', 'fairflock' ),
			)
		);
		return;
	}

	$templates = fairflock_product_table_templates();
	$found     = false;
	if ( '' !== $id ) {
		foreach ( $templates as $index => $template ) {
			if ( $template['id'] === $id ) {
				$templates[ $index ] = $draft;
				$found               = true;
				break;
			}
		}
		if ( ! $found ) {
			fairflock_product_table_template_draft( $draft );
			fairflock_product_table_template_notice(
				array(
					'type' => 'error',
					'text' => __( 'Diese Vorlage gibt es nicht mehr.', 'fairflock' ),
				)
			);
			return;
		}
	} else {
		if ( count( $templates ) >= FAIRFLOCK_PRODUCT_TABLE_MAX_TEMPLATES ) {
			fairflock_product_table_template_draft( $draft );
			fairflock_product_table_template_notice(
				array(
					'type' => 'error',
					'text' => __( 'Es sind bereits 40 Vorlagen angelegt.', 'fairflock' ),
				)
			);
			return;
		}
		$id           = fairflock_product_table_template_new_id();
		$draft['id']  = $id;
		$templates[]  = $draft;
	}

	fairflock_product_table_templates_update( $templates );
	wp_safe_redirect( admin_url( 'admin.php?page=fairflock-produkte&action=edit&template=' . rawurlencode( $id ) . '&updated=1' ) );
	exit;
}

/**
 * Renders FairFlock → Produkte.
 *
 * @since 0.9.285
 *
 * @return void
 */
function fairflock_product_table_templates_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Du darfst Vorlagen nicht ändern.', 'fairflock' ) );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
	if ( in_array( $action, array( 'new', 'edit' ), true ) ) {
		fairflock_product_table_template_edit_page( $action );
		return;
	}

	$templates = fairflock_product_table_templates();
	echo '<div class="wrap ff-ptable-screen">';
	echo '<h1 class="wp-heading-inline">' . esc_html__( 'Produkte', 'fairflock' ) . '</h1>';
	echo ' <a class="page-title-action" href="' . esc_url( admin_url( 'admin.php?page=fairflock-produkte&action=new' ) ) . '">' . esc_html__( 'Vorlage anlegen', 'fairflock' ) . '</a>';
	echo '<hr class="wp-header-end" />';
	fairflock_product_table_template_admin_notice();
	echo '<p class="ff-ptable-admin__lead">' . esc_html__( 'Tabellenvorlagen für gleiche Produkte. Eine Vorlage legst du einmal an und wählst sie danach am Produkt im Tab Tabelle.', 'fairflock' ) . '</p>';

	if ( empty( $templates ) ) {
		echo '<p>' . esc_html__( 'Noch keine Vorlage.', 'fairflock' ) . '</p>';
		echo '</div>';
		return;
	}

	echo '<table class="widefat striped" style="max-width:920px;margin-top:12px">';
	echo '<thead><tr>';
	echo '<th>' . esc_html__( 'Name', 'fairflock' ) . '</th>';
	echo '<th>' . esc_html__( 'Überschrift', 'fairflock' ) . '</th>';
	echo '<th>' . esc_html__( 'Spalten', 'fairflock' ) . '</th>';
	echo '<th>' . esc_html__( 'Zeilen', 'fairflock' ) . '</th>';
	echo '<th></th>';
	echo '</tr></thead><tbody>';
	foreach ( $templates as $template ) {
		$edit = admin_url( 'admin.php?page=fairflock-produkte&action=edit&template=' . rawurlencode( $template['id'] ) );
		echo '<tr>';
		echo '<td><a href="' . esc_url( $edit ) . '"><strong>' . esc_html( $template['name'] ) . '</strong></a></td>';
		echo '<td>' . esc_html( $template['title'] ) . '</td>';
		echo '<td>' . esc_html( (string) count( $template['columns'] ) ) . '</td>';
		echo '<td>' . esc_html( (string) count( $template['rows'] ) ) . '</td>';
		echo '<td>';
		echo '<a href="' . esc_url( $edit ) . '">' . esc_html__( 'Bearbeiten', 'fairflock' ) . '</a>';
		echo ' · ';
		fairflock_product_table_template_delete_form( $template['id'] );
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table></div>';
}

/**
 * Create or edit one template.
 *
 * @since 0.9.285
 *
 * @param string $action new|edit.
 * @return void
 */
function fairflock_product_table_template_edit_page( $action ) {
	$draft = fairflock_product_table_template_draft();
	if ( is_array( $draft ) ) {
		$template = $draft;
	} elseif ( 'edit' === $action ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only id for the edit screen.
		$id       = isset( $_GET['template'] ) ? sanitize_key( wp_unslash( $_GET['template'] ) ) : '';
		$template = fairflock_product_table_template_find( $id );
		if ( ! is_array( $template ) ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Vorlage', 'fairflock' ) . '</h1>';
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Diese Vorlage gibt es nicht mehr.', 'fairflock' ) . '</p></div>';
			echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=fairflock-produkte' ) ) . '">' . esc_html__( 'Zurück zur Übersicht', 'fairflock' ) . '</a></p></div>';
			return;
		}
	} else {
		$template = array(
			'id'      => '',
			'name'    => '',
			'title'   => '',
			'columns' => array( '', '' ),
			'rows'    => array(),
		);
	}

	$list_url = admin_url( 'admin.php?page=fairflock-produkte' );
	$form_url = '' !== $template['id']
		? admin_url( 'admin.php?page=fairflock-produkte&action=edit&template=' . rawurlencode( $template['id'] ) )
		: admin_url( 'admin.php?page=fairflock-produkte&action=new' );

	echo '<div class="wrap ff-ptable-screen">';
	echo '<h1>' . esc_html( '' !== $template['id'] ? __( 'Vorlage bearbeiten', 'fairflock' ) : __( 'Neue Vorlage', 'fairflock' ) ) . '</h1>';
	echo '<p><a href="' . esc_url( $list_url ) . '">' . esc_html__( 'Zurück zur Übersicht', 'fairflock' ) . '</a></p>';
	fairflock_product_table_template_admin_notice();

	echo '<form method="post" action="' . esc_url( $form_url ) . '">';
	wp_nonce_field( 'fairflock_ptable_template_save' );
	echo '<input type="hidden" name="ff_ptable_template_action" value="save" />';
	echo '<input type="hidden" name="ff_ptable_template_id" value="' . esc_attr( $template['id'] ) . '" />';
	echo '<div class="ff-ptable-admin" data-ff-ptable-admin data-max-columns="' . esc_attr( (string) FAIRFLOCK_PRODUCT_TABLE_MAX_COLUMNS ) . '" data-max-rows="' . esc_attr( (string) FAIRFLOCK_PRODUCT_TABLE_MAX_ROWS ) . '">';
	echo '<div class="ff-ptable-admin__title ff-ptable-admin__name">';
	echo '<label for="ff_ptable_name">' . esc_html__( 'Name', 'fairflock' ) . '</label>';
	printf(
		'<input type="text" id="ff_ptable_name" name="ff_ptable_name" value="%s" maxlength="80" required placeholder="%s" />',
		esc_attr( $template['name'] ),
		esc_attr__( 'z. B. Hoodie Maße', 'fairflock' )
	);
	echo '</div>';
	echo '<p class="ff-ptable-admin__lead">' . esc_html__( 'Der Name erscheint in der Auswahl am Produkt. Die Überschrift steht im Shop über der Tabelle.', 'fairflock' ) . '</p>';
	fairflock_product_table_admin_editor( $template );
	echo '</div>';
	submit_button( __( 'Speichern', 'fairflock' ) );
	echo '</form>';

	if ( '' !== $template['id'] ) {
		echo '<form method="post" action="' . esc_url( $list_url ) . '" data-ff-ptable-delete>';
		wp_nonce_field( 'fairflock_ptable_template_delete' );
		echo '<input type="hidden" name="ff_ptable_template_action" value="delete" />';
		echo '<input type="hidden" name="ff_ptable_template_id" value="' . esc_attr( $template['id'] ) . '" />';
		submit_button( __( 'Vorlage löschen', 'fairflock' ), 'delete' );
		echo '</form>';
	}
	echo '</div>';
}

/**
 * Inline delete control for one template.
 *
 * @since 0.9.285
 *
 * @param string $id Template id.
 * @return void
 */
function fairflock_product_table_template_delete_form( $id ) {
	echo '<form method="post" action="' . esc_url( admin_url( 'admin.php?page=fairflock-produkte' ) ) . '" data-ff-ptable-delete style="display:inline">';
	wp_nonce_field( 'fairflock_ptable_template_delete' );
	echo '<input type="hidden" name="ff_ptable_template_action" value="delete" />';
	echo '<input type="hidden" name="ff_ptable_template_id" value="' . esc_attr( $id ) . '" />';
	echo '<button type="submit" class="button-link-delete">' . esc_html__( 'Löschen', 'fairflock' ) . '</button>';
	echo '</form>';
}

/**
 * Success or error notice on the templates screen.
 *
 * @since 0.9.285
 *
 * @return void
 */
function fairflock_product_table_template_admin_notice() {
	$notice = fairflock_product_table_template_notice();
	if ( ! is_array( $notice ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag after a redirect.
		if ( isset( $_GET['updated'] ) ) {
			$notice = array(
				'type' => 'success',
				'text' => __( 'Vorlage gespeichert.', 'fairflock' ),
			);
		} elseif ( isset( $_GET['deleted'] ) ) {
			$notice = array(
				'type' => 'success',
				'text' => __( 'Vorlage gelöscht.', 'fairflock' ),
			);
		}
	}
	if ( ! is_array( $notice ) || empty( $notice['text'] ) ) {
		return;
	}
	$type = isset( $notice['type'] ) && in_array( $notice['type'], array( 'success', 'error' ), true ) ? $notice['type'] : 'info';
	echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . esc_html( $notice['text'] ) . '</p></div>';
}
