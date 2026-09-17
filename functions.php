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

define( 'FAIRFLOCK_DIR', get_template_directory() );
define( 'FAIRFLOCK_URI', get_template_directory_uri() );

// Single source of truth: style.css Version header (packaging + cache bust).
$_fairflock_theme = wp_get_theme( get_template() );
define( 'FAIRFLOCK_VERSION', (string) ( $_fairflock_theme->get( 'Version' ) ?: '0.0.0' ) );
unset( $_fairflock_theme );

require_once FAIRFLOCK_DIR . '/inc/tenant-safety.php';
require_once FAIRFLOCK_DIR . '/inc/setup.php';
require_once FAIRFLOCK_DIR . '/inc/legal-pages.php';
require_once FAIRFLOCK_DIR . '/inc/defaults.php';
require_once FAIRFLOCK_DIR . '/inc/patterns.php';
require_once FAIRFLOCK_DIR . '/inc/editor-faq.php';
require_once FAIRFLOCK_DIR . '/inc/editor-bento.php';
require_once FAIRFLOCK_DIR . '/inc/editor-mega-list.php';
require_once FAIRFLOCK_DIR . '/inc/editor-product-collection.php';
require_once FAIRFLOCK_DIR . '/inc/performance.php';
require_once FAIRFLOCK_DIR . '/inc/woocommerce.php';
require_once FAIRFLOCK_DIR . '/inc/category-overview.php';
require_once FAIRFLOCK_DIR . '/inc/header-search.php';
require_once FAIRFLOCK_DIR . '/inc/submenu-styles.php';
require_once FAIRFLOCK_DIR . '/inc/hero-heading-styles.php';
require_once FAIRFLOCK_DIR . '/inc/navigation-mega.php';
require_once FAIRFLOCK_DIR . '/inc/header-style.php';
require_once FAIRFLOCK_DIR . '/inc/csv-import.php';
require_once FAIRFLOCK_DIR . '/inc/product-modal.php';
require_once FAIRFLOCK_DIR . '/inc/account-popup.php';
require_once FAIRFLOCK_DIR . '/inc/product-personalization.php';
require_once FAIRFLOCK_DIR . '/inc/password-protection.php';
require_once FAIRFLOCK_DIR . '/inc/unsplash-credit.php';
require_once FAIRFLOCK_DIR . '/inc/theme-info.php';
require_once FAIRFLOCK_DIR . '/inc/setup-wizard.php';
require_once FAIRFLOCK_DIR . '/inc/demo-content.php';
