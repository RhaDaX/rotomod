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
    public function custom_enqueue_scripts() {

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

       wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rotomod-admin.js', array(''), $this->version, true );
        wp_enqueue_script( 'flatpickr-locale-fr', 'https://npmcdn.com/flatpickr/dist/l10n/fr.js', array('carbon-fields-core'), $this->version, true);


    }

    /**
     * Création de la page de gestion des remises
     */
    public function options_initialize_admin_page() {

        $tabs = apply_filters( 'rtm_plugin_options_tabs', [] );

        if ( empty( $tabs ) ) {
            return;
        }

        $theme_options = Container::make( 'theme_options', __( 'Options du plugin', 'rtm-plugin' ) );
        $theme_options->set_page_file( 'plugin-rotomod' );
        $theme_options->set_page_menu_title( __( 'Remises et Livraison', 'rtm-plugin' ) );
        $theme_options->set_page_menu_position( 10 );
        $theme_options->set_icon( 'dashicons-shield' );

        foreach ( $tabs as $tab_slug => $tab_title ) {
            $theme_options->add_tab(
                esc_html( $tab_title ),
                apply_filters( "rtm_plugin_options_fields_tab_{$tab_slug}", [] )
            );
        }


    }

    public function crb_load() {
        $plugin_dir = ABSPATH . 'wp-content/plugins/rotomod/';
        require_once( $plugin_dir . 'vendor/autoload.php' );
        \Carbon_Fields\Carbon_Fields::boot();
    }

    function options_set_tabs( $tabs ) {
        return [
            'discountwithamount'  => __( 'Remises relatives au CA', 'rtm-plugin' ),
            'discountwithshippingdate' => __( 'Remises relatives aux dates', 'rtm-plugin' ),
        ];
    }

    /**
     * @return array
     * Remise en fonction du CA
     */
    function options_discountwithamount_tab_theme_fields() {
        $fields = [];
        $reduce_labels = array(
            'plural_name' => "Remises",
            'singular_name' => 'Remise',
        );

        $fields[] = Field::make('complex', 'rtm_discountwithamount', 'Liste des remises à effectuer en fonction du CA de la commande')
            ->set_layout('tabbed-horizontal')
            ->setup_labels( $reduce_labels )
            ->add_fields(array(
                Field::make('text', 'title', 'Titre')
                    ->set_width(25),
                Field::make('text', 'min_amount', 'Montant minimum')
                    ->set_required(true)
                    ->set_width(25)
                    ->set_attribute( 'type', 'number' ),
                Field::make('text', 'max_amount', 'Montant maximum')
                    ->set_required(true)
                    ->set_width(25)
                    ->set_attribute( 'type', 'number' ),
                Field::make('text', 'percentage_discount', 'Pourcentage de la remise')
                    ->set_required(true)
                    ->set_width(25)
                    ->set_attribute( 'type', 'number' )
                    ->set_attribute( 'max', "100" ),
            ));

        return $fields;
    }


    /**
     * @return array
     * Remise en fonction de la date de commande et de livraison
     */
    function options_discountwithshippingdate_tab_theme_fields() {
        $fields = [];
        $reduce_labels = array(
            'plural_name' => "Remises",
            'singular_name' => 'Remise',
        );

        $fields[] = Field::make('complex', 'rtm_discountwithshippingdate', 'Liste des remises à effectuer en fonction des dates de commandes & livraisons')
            ->set_layout('tabbed-horizontal')
            ->setup_labels( $reduce_labels )
            ->add_fields(array(
                Field::make('date', 'order_before', 'Commande validée avant le :')
                    ->set_required(true)
                    ->set_width(25)
                    ->set_storage_format('d-m-Y')
                    ->set_input_format('d-m-Y', 'd-m-Y')
                    ->set_picker_options( array(
                        'locale' => 'fr', // example with Spanish
                        'allowInput' => false,
                        'dateFormat' => 'd-m-Y'
                    )),
                Field::make('date', 'shipment_before', 'Livraison planifée avant le :')
                    ->set_required(true)
                    ->set_storage_format('d-m-Y')
                    ->set_picker_options( array(
                        'locale' => 'fr', // example with Spanish
                        'allowInput' => false,
                        'dateFormat' => 'd-m-Y'
                    ))
                    ->set_input_format('d-m-Y', 'd-m-Y')
                    ->set_width(25),
                Field::make('text', 'percentage_discount', 'Pourcentage de la remise')
                    ->set_required(true)
                    ->set_width(25)
                    ->set_attribute( 'type', 'number' )
                    ->set_attribute( 'max', "100" ),
            ));

        return $fields;

    }

    /**
     * Ajout du bloc de rappel des valeur pour la saisie des remises dans l'admin
     */
    function display_discountwithamount_field_after()
    {
        $recap = '';
        $recap .= '<section class="row">';

        //  Remise par CA
        $recap .= '<div class="col-sm-6">';
        $recap .= '<h1 style="margin-bottom: 20px; font">Remises en fonction du CA</h1><hr>';
        $recap .= '<table style="width: 200px"><tbody>';
        foreach (carbon_get_theme_option('rtm_discountwithamount') as $discount){
            $recap .= '<tr> <td style="font-size: 16px">'. $discount['title'] .' :</td> <td style="font-size: 16px"><strong>'. $discount['percentage_discount'] .'%</strong> </td></tr>';
        }
        $recap .= '</tbody></table>';
        $recap .= '</div>';


        //  Remise par Date
        $recap .= '<div class="col-sm-6">';
        $recap .= '<h1 style="margin-bottom: 00px;">Remises en fonction des dates de commandes et de livraisons</h1><hr>';
        /*$recap .= print_r(carbon_get_theme_option('rtm_discountwithamount'));*/
        $recap .= '<table style="width: 100%;" ><tbody>';
        $recap .= '<thead style="border-bottom: 1px solid black;">
                        <td style="font-size: 18px;line-height: 38px;">Commande avant le </td>
                        <td style="font-size: 18px;line-height: 38px;">Livraison avant le </td>
                        <td style="font-size: 18px;line-height: 38px;">Remise </td>
                    </thead>';
        foreach (carbon_get_theme_option('rtm_discountwithshippingdate') as $discount){
            $recap .=  '
                        <tr>  
                        <td style="font-size: 16px">'. $discount['order_before'] .' </td>
                        <td style="font-size: 16px">'. $discount['shipment_before'] .' </td>
                        <td style="font-size: 16px"><strong>'. $discount['percentage_discount'] .' %</strong></td>
                        </tr>';
        }
        $recap .= '</tbody></table>';
        $recap .= '</div>';


        $recap .= '</section>';
        echo $recap;
    }


    /**
     *Fonction d'ajout du champs de remise propre au client 10 % - 8 % - 6 % - 3 % - 0 %
     */


    public function create_discount_field_user_profile()
    {
        Container::make('user_meta', 'Remise 1')
            ->add_fields(array(
                Field::make( 'select', 'rtm_discount_user_meta', 'Remise 1 :' )
                    ->add_options( array(
                        '0' => '0%',
                        '3' => '3%',
                        '6' => '6%',
                        '8' => '8%',
                        '10' => '10%',
                    ) )
            ));
    }


    /**
     * Ajout du select pour les commerciaux sur le profil client.
     */
    public function add_commercial_field()
    {

        $user_query = new WP_User_Query(array( 'role' => 'commercial' ));
        // User Loop
        $AllCommerciaux = array();
        if ( ! empty( $user_query->get_results() ) ) {
            foreach ( $user_query->get_results() as $user ) {
                $AllCommerciaux[$user->ID] = $user->display_name ;
            }
        } else {
            echo 'No users found.';
        }

        Container::make('user_meta', 'Commercial Référent')
            ->add_fields(array(
                Field::make( 'select', 'rtm_commercial_user_meta', 'Commercial référent' )
                    ->add_options( $AllCommerciaux )
            ));
    }
}
