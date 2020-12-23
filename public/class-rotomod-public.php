<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https//linkweb.fr
 * @since      1.0.0
 *
 * @package    Rotomod
 * @subpackage Rotomod/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rotomod
 * @subpackage Rotomod/public
 * @author     Linkweb <technique@linkweb.fr>
 */
class Rotomod_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rotomod-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rotomod-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Affiche la remise 1 en fonction de la remise saisie sur le profil client
     * @paramCarbon :: rtm_discount_user_meta
	 */
	private function display_discount_with_profile()
    {
        global $woocommerce;
        $current_user = wp_get_current_user();
        $userID = $current_user->id;
        //$discount = ($woocommerce->cart->get_cart_contents_total() - $woocommerce->cart->get_shipping_total()) * ((carbon_get_user_meta( $userID, 'rtm_discount_user_meta' ) / 100)) * -1;
        $discount = ($woocommerce->cart->get_cart_contents_total()) * ((carbon_get_user_meta( $userID, 'rtm_discount_user_meta' ) / 100)) * -1;
        //$woocommerce->cart->add_fee(carbon_get_user_meta( $userID, 'rtm_discount_user_meta' ) ,$discount , true);
        //dump($woocommerce->cart->get_shipping_total());
        return [
                'amount'                => $discount,
                'discountPercentage'    => carbon_get_user_meta( $userID, 'rtm_discount_user_meta' )
        ];
    }

    /**
     * @param $posted_data
     * @return mixed
     * Calcul de la remise 2 en fonction de la date de commande
     */
    private function calcul_discount_with_date($posted_data)
    {
            global $woocommerce;

            // Parsing posted data on checkout
            $post = array();
            if($posted_data['post_data']) {
                $vars = explode('&', $posted_data['post_data']);
                foreach ($vars as $k => $value){
                    $v = explode('=', urldecode($value));
                    $post[$v[0]] = $v[1];
                }
            } else {
                $post['shipping_date'] = $_POST['shipping_date'];
            }


            $shipping_date_selected = $post['shipping_date'];

            if($shipping_date_selected != 0) {
                $dateOption = carbon_get_theme_option('rtm_discountwithshippingdate');
                $shippingDates = ['Non renseigné'];
                $today = date("d-m-Y");
                //dump($today);

                for ($i = 0; $i < count($dateOption); $i++){
                    //dump($dateOption[$i]['shipment_before']);
                    if(strtotime($dateOption[$i]['shipment_before']) > strtotime($today)){
                        $shippingDates[] = $dateOption[$i];
                    }
                }

                $dateInfos = $shippingDates[$shipping_date_selected];

                // Retourne le % de remise
                return $dateInfos['percentage_discount'];

            }


    }

    /**
     * Ajout sur le panier des différentes remises relatives au dates ou au CA de la commande
     */
	public function rtm_add_discount_custom($post_data)
    {
        global $woocommerce;

        $total = round($woocommerce->cart->get_cart_contents_total(), 0);
        $evaluations = carbon_get_theme_option('rtm_discountwithamount');
        $discount2 = '';

        for ($i = 0; $i < count($evaluations); $i++){
            if($total <= $evaluations[$i]['max_amount'] && $discount2 === ''){
                $discount2 = $evaluations[$i]['percentage_discount'];

            }
        }

        /**
         * Calcul de la première remise
         */
        $discountWithProfile = $this->display_discount_with_profile();
        $discount1Content = 'Remise 1 ('. $discountWithProfile['discountPercentage'] .'%)';
        $woocommerce->cart->add_fee($discount1Content, $discountWithProfile['amount'], true);

        /**
         * Calcul de la deuxième remise en focntion de la première
         */
        $discountWithDate = $this->calcul_discount_with_date($_POST);
        $totalPercentageDiscount = $discountWithDate + $discount2;
        $discount = ($woocommerce->cart->get_cart_contents_total() + $discountWithProfile['amount']  ) * (($totalPercentageDiscount / 100)) * -1;
        $discountContent = "Remise 2 (" . $totalPercentageDiscount . "%)";
        $woocommerce->cart->add_fee($discountContent, round($discount, 2), true);

        //dump($post_data);
    }



    /**
     * Récuration de toutes les dates butoires de livraison présentes dans les options du plugin
     */
    private function getShippingDateInOptions() {
	    $dateOption = carbon_get_theme_option('rtm_discountwithshippingdate');
	    $shippingDates = ['Non renseigné'];
        $today = date("d-m-Y");
        //dump($today);

        for ($i = 0; $i < count($dateOption); $i++){
            //dump($dateOption[$i]['shipment_before']);
            if(strtotime($dateOption[$i]['shipment_before']) > strtotime($today)){
                $shippingDates[] = $dateOption[$i]['shipment_before'];
            }
        }
	    return $shippingDates;
    }


    /**
     * Récuration de toutes les dates limites de commande présentes dans les options du plugin
     */
    private function getOrderDateInOptions(){
        $dateOption = carbon_get_theme_option('rtm_discountwithshippingdate');
    }


    /**
     * @param $method
     * @param $index
     * Ajout du champs de selection des date butoires de livraison
     */
    public function select_last_day_of_shipment( )
    {

        //if( ! is_checkout()) return; // Only on checkout page

        //$shipping_method_wanted = "flexible_shipping_5_1";

        //if( $method->id != $shipping_method_wanted ) return;

        //$chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];

        $shippingDates = $this->getShippingDateInOptions();


        //if($chosen_method_id === $shipping_method_wanted){

            echo '<tr class="dateSelector"><td colspan="2">';
            woocommerce_form_field( 'shipping_date' , array(
                'type'          => 'select',
                'class'         => array('form-row-wide carrier-name'),
                'label'         => 'A livrer avant le :',
                'required'      => false,
                'placeholder'   => 'Date butoir livraison',
                'options'       => $shippingDates
            ), 'Non renseigné' );

            echo '</td></tr>';


        //}

    }


    /**
     * Vérification de la variable Shipping_date
     */
    public function check_shipping_date()
    {

        if( isset( $_POST['shipping_date'] ) && empty( $_POST['shipping_date'] ) )
            wc_add_notice( ( "Veuillez saisir une date limite de livraison." ), "error" );

    }

    /**
     * @param $order_id
     * Création de la meta Shipping_date sur la commande
     */
    public function shipping_date_order_meta( $order_id, $posted ) {
        $value = sanitize_text_field($_POST['shipping_date']);
        $dateoptions =  carbon_get_theme_option('rtm_discountwithshippingdate');
        if( isset( $_POST['shipping_date'] ))
            //dd($_POST['shipping_date']);

            if($value === 0 || empty($value) || $value === '') {
                $dateOption = 'Non renseigné';
            } else {
                $dateOption = $dateoptions[$value - 1]['shipment_before'];
            }
            update_post_meta( $order_id, '_shipping_date', $dateOption );

    }

    /**
     * Ajout de la meta delivery_date au email
     */
    public function add_delivery_date_to_emails($fields, $sent_to_admin, $order){
        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
            $order_id = $order->get_id();
        } else {
            $order_id = $order->id;
        }

        $delivery_date = get_post_meta( $order_id, '_shipping_date', true );

        if ( '' != $delivery_date ) {
            $fields[ 'shipping_date' ] = array(
                'label' => __( 'Date limite de livraison', 'add_extra_fields' ),
                'value' => $delivery_date,
            );
        }
        return $fields;
    }

    /**
     * Ajout de la date limite de livraison sur la page de remerciement après commande
     *
     */
    function add_delivery_date_to_order_received_page ( $order ) {
        if( version_compare( get_option( 'woocommerce_version' ), '3.0.0', ">=" ) ) {
            $order_id = $order->get_id();
        } else {
            $order_id = $order->id;
        }

        $delivery_date = get_post_meta( $order_id, '_shipping_date', true );

        if ( '' != $delivery_date ) {
            echo '<p><strong>' . __( 'Date limite de livraison', 'add_extra_fields' ) . ':</strong> ' . $delivery_date;
        }
    }

    /**
     * Ajout de la date de livraison sur le résumé de commande dans l'admin
     */
    function display_order_data_in_admin( $order ){  ?>
        <div class="order_data_column" style="width: 100%;">
            <h3><?php _e( 'Informations supplémentaires' ); ?></h3>
            <?php
       /*     dump($order);*/
            echo '<p><strong>' . __( 'Date limite de livraison' ) . ':</strong>' . get_post_meta( $order->id, '_shipping_date', true ) . '</p>'; ?>
        </div>
    <?php }

    public function refresh_checkout_on_payment_methods_change()
    {
        ?>
        <script type="text/javascript">
            jQuery(function(jQuery){
                // On 'select#propina' change (live event)

                jQuery('body').on( 'change', 'select#shipping_date', function(){
                    // Set the select value in a variable
                    console.log('Action changement ...');
                    var a = jQuery(this).val();

                    // Update checkout event
                    jQuery('body').trigger('update_checkout');

                    // Restoring the chosen option value
                    jQuery('select#shipping_date option[value='+a+']').prop('selected', true);

                    // Just for testing (To be removed)
                    console.log('trigger "update_checkout"');

                    // Once checkout has been updated
                    jQuery('body').on('updated_checkout', function(){
                        // Restoring the chosen option value
                        jQuery('select#shipping_date option[value='+a+']').prop('selected', true);

                        // Just for testing (To be removed)
                        console.log('"updated_checkout" event, restore selected option value: '+a);
                    });
                });
            })
        </script>
        <?php
    }


    /**
     * @param $order
     * @param $a
     * @param $b
     * @return int
     */
    public function rtm_change_order_fees( $order , $a, $b)
    {

        $order = ($a->id > $b->id) ? 1 : -1;
        return $order;
    }


    public function test($order_id, $posted)
    {
        global $woocommerce;
        $order = new WC_Order( $order_id );
        //dd($_POST['post_data']);
        dd($posted);
    }



    /**
     * Création du champs remise 1 dans l'export CSV
     */
    public function add_field_remise1_csv($fields,$format) {
        $fields['remise1'] = array( 'label' => 'Remise 1', 'colname' => 'REM1', 'checked' => 1 );
        return $fields;
    }

    /**
     * Création du champs remise 2 dans l'export CSV
     */
    public function add_field_remise2_csv($fields,$format) {
        $fields['remise2'] = array( 'label' => 'Remise 2', 'colname' => 'REM2', 'checked' => 1 );
        return $fields;
    }

    /**
     * @param $value
     * @param $order
     * @param $item
     * @param $product
     * @param $item_meta
     * @return array|int
     */
    public function display_fee_csv1($value,$order, $item, $product,$item_meta)
    {
        $feeValue = array();
        $i = 0;
        $value = array();
        foreach ($order->get_items('fee') as $item_id => $item_fee) {

            // The fee name
            $feeValue[$i]['fee_name'] = $item_fee->get_name();

            // The fee total amount
            $feeValue[$i]['fee_total'] = $item_fee->get_total();

            // The fee total tax amount
            $feeValue[$i]['fee_total_tax'] = $item_fee->get_total_tax();
            $i++;

        }

        $currentFee = $feeValue[0];
        $currentFeeName = $currentFee['fee_name'];
        $iteration = 0;
        $chain = substr(strstr($currentFeeName, '('), 1);
        $chain = strstr($chain, '%', true);

        //dump($item);

        $value = intval($chain) * $item['total'] / 100;

        return round($value, 2);
    }

    public function display_fee_csv2($value,$order, $item, $product,$item_meta)
    {
        $feeValue = array();
        $i = 0;
        $value = array();
        foreach ($order->get_items('fee') as $item_id => $item_fee) {

            // The fee name
            $feeValue[$i]['fee_name'] = $item_fee->get_name();

            // The fee total amount
            $feeValue[$i]['fee_total'] = $item_fee->get_total();

            // The fee total tax amount
            $feeValue[$i]['fee_total_tax'] = $item_fee->get_total_tax();
            $i++;

        }
        $currentFeeOld = $feeValue[0];
        $currentFeeNameOld = $currentFeeOld['fee_name'];
        $iteration = 0;
        $chainOld = substr(strstr($currentFeeNameOld, '('), 1);
        $chainOld = strstr($chainOld, '%', true);
        $valueOld = intval($chainOld) * $item['total'] / 100;

        $currentFee = $feeValue[1];
        $currentFeeName = $currentFee['fee_name'];
        $iteration = 0;
        $chain = substr(strstr($currentFeeName, '('), 1);
        $chain = strstr($chain, '%', true);
        $value = intval($chain)  * ($item['total'] - $valueOld ) / 100;
        return round($value, 2);
    }


    public function display_fees_as_row($products, $order, $labels, $format, $static_vals) {
        $i = count($products);
        foreach($order-> get_items('fee') as $item_id => $item) {
            $item_meta = $order->get_item_meta( $item_id );
            $fee_amount = $item_meta['_fee_amount'][0];
            $tax_amount = $item_meta['_line_tax'][0];
            $row = array();
            $i++;
            if(strpos($item->get_name(), 'Remise 1') !== false){
                $row['sku'] = 'REM1';
            } else {
                $row['sku'] = 'REM2';
            }
            $row['remise1'] = $item->get_total();
            $products[] = $row;
        }
        return $products;
    }


}
