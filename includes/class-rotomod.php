<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https//linkweb.fr
 * @since      1.0.0
 *
 * @package    Rotomod
 * @subpackage Rotomod/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Rotomod
 * @subpackage Rotomod/includes
 * @author     Linkweb <technique@linkweb.fr>
 */
class Rotomod {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Rotomod_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ROTOMOD_VERSION' ) ) {
			$this->version = ROTOMOD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'rotomod';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rotomod_Loader. Orchestrates the hooks of the plugin.
	 * - Rotomod_i18n. Defines internationalization functionality.
	 * - Rotomod_Admin. Defines all hooks for the admin area.
	 * - Rotomod_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rotomod-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rotomod-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rotomod-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rotomod-public.php';

        /**
         * Déclaration de la classe UserSwitching
         */
        require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/class-rotomod-userswitching.php';

		$this->loader = new Rotomod_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rotomod_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
	    $plugin_i18n = new Rotomod_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {



		$plugin_admin = new Rotomod_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'after_setup_theme', $plugin_admin,'crb_load' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, wp_enqueue_script( 'flatpickr-locale-fr', 'https://npmcdn.com/flatpickr/dist/l10n/fr.js', array('carbon-fields-core'), $this->version, true) );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'custom_enqueue_scripts' );
		$this->loader->add_action( 'carbon_fields_register_fields', $plugin_admin ,'options_initialize_admin_page' );
		$this->loader->add_action( 'carbon_fields_register_fields', $plugin_admin, 'create_discount_field_user_profile' );
		$this->loader->add_filter( 'rtm_plugin_options_tabs', $plugin_admin ,'options_set_tabs' );
		$this->loader->add_action( 'carbon_fields_container_options_du_plugin_after_fields', $plugin_admin, 'display_discountwithamount_field_after');
		$this->loader->add_filter( 'rtm_plugin_options_fields_tab_discountwithamount', $plugin_admin ,'options_discountwithamount_tab_theme_fields', 10 );
		$this->loader->add_filter( 'rtm_plugin_options_fields_tab_discountwithshippingdate', $plugin_admin ,'options_discountwithshippingdate_tab_theme_fields', 10 );

        $plugin_userswitching = new Rotomod_userswitching();

        // Required functionality:
        $this->loader->add_filter( 'user_has_cap',                    $plugin_userswitching, 'filter_user_has_cap' , 10, 4 );
        $this->loader->add_filter( 'map_meta_cap',                    $plugin_userswitching, 'filter_map_meta_cap' , 10, 4 );
        $this->loader->add_filter( 'user_row_actions',                $plugin_userswitching, 'filter_user_row_actions' , 10, 2 );
        $this->loader->add_action( 'plugins_loaded',                  $plugin_userswitching, 'action_plugins_loaded' , 1 );
        $this->loader->add_action( 'init',                            $plugin_userswitching, 'action_init'  );
        $this->loader->add_action( 'all_admin_notices',               $plugin_userswitching, 'action_admin_notices' , 1 );
        $this->loader->add_action( 'wp_logout',                       $plugin_userswitching, 'user_switching_clear_olduser_cookie' );
        $this->loader->add_action( 'wp_login',                        $plugin_userswitching, 'user_switching_clear_olduser_cookie' );

        // Nice-to-haves:
        $this->loader->add_filter( 'ms_user_row_actions',             $plugin_userswitching, 'filter_user_row_actions' , 10, 2 );
        $this->loader->add_filter( 'login_message',                   $plugin_userswitching, 'filter_login_message' , 1 );
        $this->loader->add_filter( 'removable_query_args',            $plugin_userswitching, 'filter_removable_query_args'  );
        $this->loader->add_action( 'wp_meta',                         $plugin_userswitching, 'action_wp_meta'  );
        $this->loader->add_action( 'wp_footer',                       $plugin_userswitching, 'action_wp_footer'  );
        $this->loader->add_action( 'personal_options',                $plugin_userswitching, 'action_personal_options'  );
        $this->loader->add_action( 'admin_bar_menu',                  $plugin_userswitching, 'action_admin_bar_menu' , 11 );
        $this->loader->add_action( 'bp_member_header_actions',        $plugin_userswitching, 'action_bp_button' , 11 );
        $this->loader->add_action( 'bp_directory_members_actions',    $plugin_userswitching, 'action_bp_button' , 11 );
        $this->loader->add_action( 'bbp_template_after_user_details', $plugin_userswitching, 'action_bbpress_button'  );
        $this->loader->add_action( 'switch_to_user',                  $plugin_userswitching, 'forget_woocommerce_session'  );
        $this->loader->add_action( 'switch_back_user',                $plugin_userswitching, 'forget_woocommerce_session'  );

        /**
         * Custom hooks pour partie commerciale
         */

        $this->loader->add_action( 'wp_nav_menu',    $plugin_userswitching, 'display_link_user_switching', 99, 2  );
        $this->loader->add_action( 'page_template',  $plugin_userswitching, 'choose_commercial_template');

        $this->loader->add_action( 'carbon_fields_register_fields', $plugin_admin ,'add_commercial_field' );

        $this->loader->add_action('admin_init', $plugin_userswitching ,'add_custom_cap');

        /**
         * Redirection
         */



	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rotomod_Public( $this->get_plugin_name(), $this->get_version() );
        $plugin_userswitching = new Rotomod_userswitching();
		$this->loader->add_action( 'init', $plugin_public, 'enqueue_styles', 99);
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts', 19 );

		// Hook pour l'ajout des différentes remise
        // $this->loader->add_action('woocommerce_cart_calculate_fees',$plugin_public , 'display_discount_with_profile', 92);
        $this->loader->add_action('woocommerce_cart_calculate_fees',$plugin_public , 'rtm_add_discount_custom', 1);

        $this->loader->add_action('action_woocommerce_after_cart_item_quantity_update',$plugin_public , 'rtm_add_discount_custom');


        // Hook pour l'ajout des remises en rapport avec les dates de commandes et livraison sur la page Checkout
        $this->loader->add_action( 'woocommerce_review_order_after_shipping', $plugin_public , 'select_last_day_of_shipment', 99 );
        //$this->loader->add_action( 'display_shipping_date_max', $plugin_public , 'test', 99 );

        //Vérification du choix de la date de livraison
        //$this->loader->add_action( 'woocommerce_checkout_process' , $plugin_public, 'check_shipping_date');

        // Ajout de la date de livraison sur le mail de notif de commande
        $this->loader->add_filter( 'woocommerce_email_order_fields' , $plugin_public, 'add_delivery_date_to_emails', 10, 3);
        $this->loader->add_filter( 'woocommerce_order_details_after_order_table' , $plugin_public, 'add_delivery_date_to_order_received_page', 10, 1);
        $this->loader->add_action( 'woocommerce_admin_order_data_after_order_details' , $plugin_public, 'display_order_data_in_admin', 99);

        $this->loader->add_action( 'woocommerce_checkout_update_order_meta' , $plugin_public, 'shipping_date_order_meta', 10,2);

        //Trigger le changement de valeur du champs DATE dans la page Checkout pour le calcul de remise
        $this->loader->add_filter( 'woocommerce_review_order_before_payment',$plugin_public, 'refresh_checkout_on_payment_methods_change' );






        $this->loader->add_filter( 'woocommerce_sort_fees_callback', $plugin_public, 'rtm_change_order_fees', 99, 3 );

        /**
         * Filter permattant l'ajout des champs remise dans l'export CSV
         */
        $this->loader->add_filter( 'woe_get_order_product_fields', $plugin_public, 'add_field_remise1_csv', 10, 2 );
        $this->loader->add_filter( 'woe_get_order_product_fields', $plugin_public, 'add_field_remise2_csv', 10, 2 );
        $this->loader->add_filter( 'woe_get_order_product_value_remise1', $plugin_public, 'display_fee_csv1', 10, 5 );
        $this->loader->add_filter( 'woe_get_order_product_value_remise2', $plugin_public, 'display_fee_csv2', 10, 5 );
        $this->loader->add_filter( 'woe_fetch_order_products', $plugin_public, 'display_fees_as_row', 10, 5 );


        /**
         * User Switching
         */
        // Required functionality:
        $this->loader->add_filter( 'user_has_cap',                    $plugin_userswitching, 'filter_user_has_cap' , 10, 4 );
        $this->loader->add_filter( 'map_meta_cap',                    $plugin_userswitching, 'filter_map_meta_cap' , 10, 4 );
        $this->loader->add_filter( 'user_row_actions',                $plugin_userswitching, 'filter_user_row_actions' , 10, 2 );
        $this->loader->add_action( 'plugins_loaded',                  $plugin_userswitching, 'action_plugins_loaded' , 1 );
        $this->loader->add_action( 'init',                            $plugin_userswitching, 'action_init'  );
        $this->loader->add_action( 'all_admin_notices',               $plugin_userswitching, 'action_admin_notices' , 1 );
        $this->loader->add_action( 'wp_logout',                       $plugin_userswitching, 'user_switching_clear_olduser_cookie' );
        $this->loader->add_action( 'wp_login',                        $plugin_userswitching, 'user_switching_clear_olduser_cookie' );

        // Nice-to-haves:
        $this->loader->add_filter( 'ms_user_row_actions',             $plugin_userswitching, 'filter_user_row_actions' , 10, 2 );
        $this->loader->add_filter( 'login_message',                   $plugin_userswitching, 'filter_login_message' , 1 );
        $this->loader->add_filter( 'removable_query_args',            $plugin_userswitching, 'filter_removable_query_args'  );
        $this->loader->add_action( 'wp_meta',                         $plugin_userswitching, 'action_wp_meta'  );
        $this->loader->add_action( 'wp_footer',                       $plugin_userswitching, 'action_wp_footer'  );
        $this->loader->add_action( 'personal_options',                $plugin_userswitching, 'action_personal_options'  );
        $this->loader->add_action( 'admin_bar_menu',                  $plugin_userswitching, 'action_admin_bar_menu' , 11 );
        $this->loader->add_action( 'bp_member_header_actions',        $plugin_userswitching, 'action_bp_button' , 11 );
        $this->loader->add_action( 'bp_directory_members_actions',    $plugin_userswitching, 'action_bp_button' , 11 );
        $this->loader->add_action( 'bbp_template_after_user_details', $plugin_userswitching, 'action_bbpress_button'  );
        $this->loader->add_action( 'switch_to_user',                  $plugin_userswitching, 'forget_woocommerce_session'  );
        $this->loader->add_action( 'switch_back_user',                $plugin_userswitching, 'forget_woocommerce_session'  );

        $this->loader->add_action('init', $plugin_userswitching ,'add_custom_cap');





       // $this->loader->add_filter( 'woocommerce_add_order_item_meta',$plugin_public, 'test', 1,2 );




        //$this->loader->add_filter( 'woocommerce_checkout_update_order_review',$plugin_public, 'test' );
        //$this->loader->add_filter( 'template_redirect',$plugin_public, 'my_template_redirect' );
        //$this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'rtm_addon_plugin_template', 1, 3 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Rotomod_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
