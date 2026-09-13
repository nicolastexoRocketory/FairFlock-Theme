<?php
/**
 * FairFlock theme bootstrap.
 *
 * @package FairFlock
 * @since 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FAIRFLOCK_VERSION', '0.9.168' );
define( 'FAIRFLOCK_DIR', get_template_directory() );
define( 'FAIRFLOCK_URI', get_template_directory_uri() );

require_once FAIRFLOCK_DIR . '/inc/setup.php';
require_once FAIRFLOCK_DIR . '/inc/legal-pages.php';
require_once FAIRFLOCK_DIR . '/inc/defaults.php';
require_once FAIRFLOCK_DIR . '/inc/patterns.php';
require_once FAIRFLOCK_DIR . '/inc/editor-faq.php';
require_once FAIRFLOCK_DIR . '/inc/editor-bento.php';
require_once FAIRFLOCK_DIR . '/inc/editor-product-collection.php';
require_once FAIRFLOCK_DIR . '/inc/performance.php';
require_once FAIRFLOCK_DIR . '/inc/woocommerce.php';
require_once FAIRFLOCK_DIR . '/inc/category-overview.php';
require_once FAIRFLOCK_DIR . '/inc/header-search.php';
require_once FAIRFLOCK_DIR . '/inc/submenu-styles.php';
require_once FAIRFLOCK_DIR . '/inc/product-modal.php';
require_once FAIRFLOCK_DIR . '/inc/account-popup.php';
require_once FAIRFLOCK_DIR . '/inc/product-personalization.php';
require_once FAIRFLOCK_DIR . '/inc/password-protection.php';
require_once FAIRFLOCK_DIR . '/inc/setup-wizard.php';
require_once FAIRFLOCK_DIR . '/inc/demo-content.php';
