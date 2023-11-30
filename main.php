<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Duplicator Pro Activ@tor
 * Plugin URI:        https://bit.ly/dup-act
 * Description:       Duplicator Pro Plugin Activ@tor
 * Version:           1.4.0
 * Requires at least: 5.9.0
 * Requires PHP:      7.2
 * Author:            moh@medhk2
 * Author URI:        https://bit.ly/medhk2
 **/

defined( 'ABSPATH' ) || exit;
$PLUGIN_NAME   = 'Duplicator Pro Activ@tor';
$PLUGIN_DOMAIN = 'duplicator-pro-activ@tor';
extract( require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php' );
if (
	$admin_notice_ignored()
	|| $admin_notice_plugin_install( 'duplicator-pro/duplicator-pro.php', null, 'Duplicator Pro', $PLUGIN_NAME, $PLUGIN_DOMAIN )
	|| $admin_notice_plugin_activate( 'duplicator-pro/duplicator-pro.php', $PLUGIN_NAME, $PLUGIN_DOMAIN )
) {
	return;
}

if ( ! defined( 'DUPLICATOR_USTATS_DISALLOW' ) ) {
	define( 'DUPLICATOR_USTATS_DISALLOW', true );
}

use Duplicator\Addons\ProBase\License\License;
use \Duplicator\Utils\UsageStatistics\CommStats;

add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) use ( $json_response ) {
	$STORE_URL = 'https://duplicator.com';
	if ( class_exists( License::class ) ) {
		$STORE_URL = License::EDD_DUPPRO_STORE_URL;
	}
	if ( str_starts_with( $url, $STORE_URL ) !== false ) {
		if ( ( $parsed_args['body']['edd_action'] ?? false ) == 'check_license' ) {
			$data                 = new stdClass;
			$data->license        = 'valid';
			$data->expires        = 'lifetime';
			$data->site_count     = 1;
			$data->price_id       = License::TYPE_GOLD;
			$data->license_limit  = $data->activations_left = 500;
			$data->license_status = License::STATUS_VALID;

			return $json_response( $data );
		}
	} elseif ( str_starts_with( $url, CommStats::getRemoteHost() . CommStats::END_POINT_PLUGIN_STATS ) !== false ) {
		return $json_response( [] );
	}


	return $pre;
}, 99, 3 );
add_action( 'plugins_loaded', function () {
	update_option( License::LICENSE_KEY_OPTION_NAME, 'l6mzt8WCoqiLpanLpsbfo%2BC%2FlKmtysnFpbDfvoessdXaxuCc1LWQZaOfnZqslqeoV2N2mQ%3D%3D' );
	$global                 = \DUP_PRO_Global_Entity::getInstance();
	$global->license_type   = License::TYPE_GOLD;
	$global->license_status = License::STATUS_VALID;
	$global->license_limit  = 500;
	$global->save();
} );
