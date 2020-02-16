<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              da-nang.top
 * @since             1.0.0
 * @package           Dht_Momo
 *
 * @wordpress-plugin
 * Plugin Name:       DHT Momo
 * Plugin URI:        daihanhtrinh.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Xuan Dinh
 * Author URI:        da-nang.top
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dht-momo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DHT_MOMO_VERSION', '1.0.0' );

if(!defined('DHT_MOMO_GATEWAY_NAME')) {
    define('DHT_MOMO_GATEWAY_NAME', __('Dht_Momo_Gateway'));
}


if(!defined('DHT_MOMO_GATEWAY_ID')) {
    define('DHT_MOMO_GATEWAY_ID', __('dht_momo_gateway'));
}

if(!defined('DHT_MOMO_GATEWAY_TITLE')) {
    define('DHT_MOMO_GATEWAY_TITLE', __('Momo gateway'));
}

if(!defined('DHT_MOMO_GATEWAY_DESC')) {
    define('DHT_MOMO_GATEWAY_DESC', __('Momo gateway'));
}


if(!defined('DHT_MOMO_PAYMENT_ENDPOINT_DEV')) {
    define('DHT_MOMO_PAYMENT_ENDPOINT_DEV', __('https://test-payment.momo.vn/gw_payment/transactionProcessor'));
}

if(!defined('DHT_MOMO_PAYMENT_ENDPOINT_PROD')) {
    define('DHT_MOMO_PAYMENT_ENDPOINT_PROD', __('https://payment.momo.vn/gw_payment/transactionProcessor'));
}

if(!defined('DHT_MOMO_PAYMENT_LOGO')) {
    define('DHT_MOMO_PAYMENT_LOGO', __('https://developers.momo.vn/images/logo.svg'));
}

if(!defined('DHT_MOMO_WEBHOOK')) {
    define('DHT_MOMO_WEBHOOK', __('momo_ipn'));
}

if(!defined('DHT_MOMO_ERROR_RESPONSE')) {
    $arr = [
        0       => __('Payment Success via MOMO, transaction ID %s ', 'dht-momo'),
        17      => __('Số tiền không đủ để giao dịch','dht-momo'),
        46      => __('Số tiền hoàn tiền vượt quá phạm vi: Giới hạn 1','dht-momo'),
        68      => __('Không được hỗ trợ (refund)','dht-momo'),
        151     => __('Số tiền thanh toán phải nằm trong khoảng 1000 đến 20000000Đơn vị tiền tệ: VND','dht-momo'),
        153     => __('Giải mã hash thất bại\n- Không thể giải mã field hash bằng thuật toán RSA. Xem chi tiết\n- Dữ liệu được giải mã không phải JSON Object','dht-momo'),
        162     => __('Mã thanh toán (Payment Code) đã được sử dụng','dht-momo'),
        208     => __('Thông tin đối tác không tồn tại hoặc chưa kích hoạt','dht-momo'),
        403     => __('Truy cập bị từ chối','dht-momo'),
        404     => __('Dịch vụ không hỗ trợ yêu cầu của bạn:\n- Endpoint MoMo API không chính xác\n- requestType trong HTTP Body không được hỗ trợ','dht-momo'),
        2126    => __('Dữ liệu đối tác chưa được cấu hình','dht-momo'),
        2128    => __('Request Id đã tồn tại','dht-momo'),
        2129    => __('Chữ ký (signature) không chính xác','dht-momo'),
        2131    => __('Giao dịch chưa được khởi tạo hoặc đã quá hạn\n- API /pay/confirm: Giao dịch không tồn tại trong hệ thống\n- Mã QR đã hết hạn (Thời gian: 5 phút)','dht-momo'),
        2132    => __('Giao dịch đã tồn tại','dht-momo'),
    ];
    define('DHT_MOMO_ERROR_RESPONSE', $arr);
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dht-momo-activator.php
 */
function activate_dht_momo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dht-momo-activator.php';
	Dht_Momo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dht-momo-deactivator.php
 */
function deactivate_dht_momo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dht-momo-deactivator.php';
	Dht_Momo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dht_momo' );
register_deactivation_hook( __FILE__, 'deactivate_dht_momo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-dht-momo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dht_momo() {

	$plugin = new Dht_Momo();
	$plugin->run();

}
run_dht_momo();
