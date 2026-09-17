<?php
/**
 * Multi-tenant safety: theme file deploys must not wipe shop customizations.
 *
 * WordPress FSE stores Site Editor edits as wp_template / wp_template_part posts.
 * Those override theme files. Deploy replaces only files under themes/fairflock —
 * DB content stays. Automatic init migrators that call wp_update_post on those
 * posts are the real wipe risk and must stay additive or admin-triggered.
 *
 * @package FairFlock
 * @since 0.9.223
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether automatic migrators may rewrite customized DB content.
 *
 * Default: false. Theme pushes must preserve Site Editor / page edits.
 * Opt-in only via filter (e.g. Playground repair) or explicit admin tools
 * such as Demo Content with overwrite checked.
 *
 * @since 0.9.223
 *
 * @param string $context Migrator id (for logging / selective allow).
 * @return bool
 */
function fairflock_may_migrate_db_content( $context = '' ) {
	/**
	 * Filters whether a DB content migrator may run.
	 *
	 * @since 0.9.223
	 *
	 * @param bool   $allow   Default false.
	 * @param string $context Migrator identifier.
	 */
	return (bool) apply_filters( 'fairflock_may_migrate_db_content', false, (string) $context );
}
