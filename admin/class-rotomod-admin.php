<?php
use Carbon_Fields\Block;
use Carbon_Fields\Field;
use Carbon_Fields\Container;
use Carbon_Fields\Field\Field as FieldField;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https//linkweb.fr
 * @since      1.0.0
 *
 * @package    Rotomod
 * @subpackage Rotomod/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rotomod
 * @subpackage Rotomod/admin
 * @author     Linkweb <technique@linkweb.fr>
 */
class Rotomod_Admin {

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
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rotomod_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rotomod_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rotomod-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rotomod_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rotomod_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rotomod-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function options_initialize_admin_page() {
	
		Container::make( 'theme_options', __( 'Options de livraisons & remises', 'rtm-plugin' ) )
			->add_fields(array(
				Field::make('complex', 'rtm_periods', 'Périodes de promos')
					->set_layout('tabbed-horizontal')
					->add_fields(array(
						Field::make('text', 'title', 'Titre'),
						Field::make('date', 'date_begin', 'Date de début'),
						Field::make('date', 'date_end', 'Date de fin')
					))
			))
			->set_page_file( 'plugin-rotomod' )
			->set_page_menu_title( __( 'Livraison & remises', 'rtm-plugin' ) )
			->set_page_menu_position( 31 )
			->set_icon( 'dashicons-shield' );
		
		

	}

	public function crb_load() {
	$plugin_dir = ABSPATH . 'wp-content/plugins/rotomod/';
    require_once( $plugin_dir . 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}


}
