<?php
/**
 * WooCommerce CSV import: allow product CSVs that PHP/WordPress misdetects.
 *
 * WordPress rejects uploads when finfo reports application/octet-stream (or
 * text/html for CSVs with HTML in descriptions). WooCommerce only patches
 * text/html; Playground / some hosts need the broader fix for admins.
 *
 * @package FairFlock
 * @since 0.9.176
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current user may run WooCommerce product CSV imports.
 *
 * @since 0.9.176
 *
 * @return bool
 */
function fairflock_user_can_import_products_csv() {
	return is_admin()
		&& (
			current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_products' )
			|| current_user_can( 'import' )
		);
}

/**
 * Ensures csv / txt stay in the site MIME allow-list for shop editors.
 *
 * @since 0.9.176
 *
 * @param array<string, string> $mimes Mime map.
 * @return array<string, string>
 */
function fairflock_upload_mimes_allow_csv( $mimes ) {
	if ( ! fairflock_user_can_import_products_csv() ) {
		return $mimes;
	}

	$mimes['csv'] = 'text/csv';
	$mimes['txt'] = 'text/plain';

	return $mimes;
}
add_filter( 'upload_mimes', 'fairflock_upload_mimes_allow_csv', 20 );

/**
 * Restores CSV/TXT type when WordPress clears it after a finfo mismatch.
 *
 * Runs after WooCommerce's text/html patch (priority 10).
 *
 * @since 0.9.176
 *
 * @param array{ext?:string|false,type?:string|false,proper_filename?:string|false} $data      Checked file data.
 * @param string                                                                     $file      Full path.
 * @param string                                                                     $filename  Original filename.
 * @param array<string, string>|null                                                 $mimes     Context mimes.
 * @param string                                                                     $real_mime Detected mime.
 * @return array{ext?:string|false,type?:string|false,proper_filename?:string|false}
 */
function fairflock_check_filetype_allow_csv( $data, $file, $filename, $mimes, $real_mime ) {
	if ( ! fairflock_user_can_import_products_csv() ) {
		return $data;
	}

	$csv_mimes = array(
		'csv' => 'text/csv',
		'txt' => 'text/plain',
	);

	$from_name = wp_check_filetype( $filename, $csv_mimes );
	if ( empty( $from_name['ext'] ) || empty( $from_name['type'] ) ) {
		return $data;
	}

	$ambiguous = array(
		'',
		'text/html',
		'text/plain',
		'text/x-csv',
		'application/csv',
		'application/octet-stream',
		'application/vnd.ms-excel',
		'application/xhtml+xml',
		'inode/x-empty',
	);

	$ext_ok  = ! empty( $data['ext'] );
	$type_ok = ! empty( $data['type'] );

	if ( ( $ext_ok && $type_ok ) && ! in_array( (string) $real_mime, $ambiguous, true ) ) {
		return $data;
	}

	$data['ext']  = $from_name['ext'];
	$data['type'] = $from_name['type'];

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fairflock_check_filetype_allow_csv', 20, 5 );
