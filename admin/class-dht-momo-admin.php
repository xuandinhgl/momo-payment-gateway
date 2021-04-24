<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       da-nang.top
 * @since      1.0.0
 *
 * @package    Dht_Momo
 * @subpackage Dht_Momo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Dht_Momo
 * @subpackage Dht_Momo/admin
 * @author     Xuan Dinh <xuandinhgl@gmail.com>
 */
class Dht_Momo_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


    /**
     * This function register new gateway
     *
     * @param $gateways
     * @return array
     */
	public function add_woo_payment_gateway($gateways) {
        $gateways[] = DHT_MOMO_GATEWAY_NAME;

        return $gateways;
    }

    public function add_woo_gateway_class() {

	    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-dht-momo-gateway.php';
    }

}
