<?php
/**
 * Uninstall routine: removes the plugin settings.
 *
 * @package ER_Payment_Method_Order_Column
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'er_pmoc_settings' );
