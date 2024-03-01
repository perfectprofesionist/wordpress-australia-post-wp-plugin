<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           Wc_Ap_Addon
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Australia Post Add-on
 * Plugin URI:        
 * Description:       Enhance your WooCommerce store with the Australia Post Handling Fees plugin. Seamlessly integrate Australia Post shipping and effortlessly implement handling fees for streamlined cost management. Flexible calculations, transparent checkout, and efficient operations await. Boost profitability while ensuring a smooth customer experience.
 * Version:           1.0.0
 * Author:            H Singh
 * Author URI:        
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-ap-addon
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
// Define the version of the WC_AP_ADDON plugin
define( 'WC_AP_ADDON_VERSION', '1.0.0' );

// Include the main WooCommerce plugin file
include_once( ABSPATH . 'wp-content/plugins/woocommerce/woocommerce.php' );

// Include the abstract class for WooCommerce shipping methods
include_once( ABSPATH . 'wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-shipping-method.php' );






// Add a custom tab to WooCommerce settings
function add_custom_woocommerce_settings_tab($tabs) {
    // Define a custom tab named 'Handling Fees' with the translation text domain 'wc_addon_handling_fees'
    $tabs['handling_fees'] = __('Handling Fees', 'wc_addon_handling_fees');
    return $tabs;
}

// Hook into the 'woocommerce_settings_tabs_array' filter with a priority of 50, and call the function to add the custom tab
add_filter('woocommerce_settings_tabs_array', 'add_custom_woocommerce_settings_tab', 50);


// Custom WooCommerce Settings Tab
function custom_woocommerce_settings_tab() {
    // Retrieve the current value and calculation type options from the plugin's settings
    $current_value = get_option('wc_handling_fees');
    $calculation_type = get_option('wc_handling_fees_calculation_type', 'products');

    ?>
    <!-- HTML markup for the custom tab -->
    <div id="handling_fees" class="woocommerce-Settings-panel">
        <table class="form-table">
            <!-- Handling Fees input field -->
            <tr>
                <th scope="row" class="titledesc">Handling Fees</th>
                <td class="forminp">
                    <input type="text" name="wc_handling_fees" id="wc_handling_fees" value="<?php echo esc_attr($current_value); ?>">
                </td>
            </tr>
            <!-- Calculation Type radio buttons -->
            <tr>
                <th scope="row" class="titledesc">Calculation Type</th>
                <td class="forminp">
                    <fieldset>
                        <!-- Radio button for calculating based on the total of products -->
                        <label for="calculation_type_products">
                            <input type="radio" name="wc_handling_fees_calculation_type" value="products" <?php checked($calculation_type, 'products'); ?>>
                            Calculate on total of products
                        </label>
                        <br>
                        <!-- Radio button for calculating based on the total of products plus total shipping -->
                        <label for="calculation_type_products_shipping">
                            <input type="radio" name="wc_handling_fees_calculation_type" value="products_shipping" <?php checked($calculation_type, 'products_shipping'); ?>>
                            Calculate on total of products plus total shipping
                        </label>
                        <br>
                        <!-- Radio button for calculating based on the total shipping -->
                        <label for="calculation_type_shipping">
                            <input type="radio" name="wc_handling_fees_calculation_type" value="shipping" <?php checked($calculation_type, 'shipping'); ?>>
                            Calculate on total shipping
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php do_action('woocommerce_settings_handling_fees'); ?>
        <!-- Additional action hook for handling fees settings -->
        <p><em>Note: "Calculation Type" will only work for percentage values in the "Handling Fees" field.</em></p>
    </div>
    <?php
}

// Hook into the 'woocommerce_settings_tabs_handling_fees' action and call the function to display the custom tab content
add_action('woocommerce_settings_tabs_handling_fees', 'custom_woocommerce_settings_tab');


// Update the custom setting with validation
function save_custom_woocommerce_setting() {
    $custom_field_value = sanitize_text_field($_POST['wc_handling_fees']);
    $calculation_type = sanitize_text_field($_POST['wc_handling_fees_calculation_type']);

    // Use a regular expression to validate the input
    if (preg_match('/^(\d+(\.\d{1,2})?)%?$/', $custom_field_value)) {
        update_option('wc_handling_fees', $custom_field_value);
        update_option('wc_handling_fees_calculation_type', $calculation_type);
    } else {
        // If the input doesn't match the desired format, handle the error.
        add_settings_error(
            'wc_handling_fees',
            'invalid_handling_fees',
            'Invalid handling fee format. Use numbers or numbers with % symbol (e.g., 10, 10.5, 10%, 10.5%).',
            'error'
        );
    }
}

add_action('woocommerce_update_options_handling_fees', 'save_custom_woocommerce_setting');




// Display settings errors
function display_settings_errors() {
    settings_errors('wc_handling_fees');
}
add_action('woocommerce_settings_tabs_handling_fees', 'display_settings_errors');






// Add a custom tab to the WooCommerce settings
function add_australia_post_settings_tab($settings_tabs) {
    $settings_tabs['australia_post'] = __('Australia Post Settings', 'woocommerce');
    return $settings_tabs;
}
add_filter('woocommerce_settings_tabs_array', 'add_australia_post_settings_tab', 50);

// Add settings fields to the Australia Post tab
function australia_post_settings_fields() {
    woocommerce_admin_fields(array(
        'section_title' => array(
            'name'     => __('Australia Post Settings', 'woocommerce'),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'australia_post_settings_section_title'
        ),
        'api_key' => array(
            'name'     => __('Australia Post API Key', 'woocommerce'),
            'type'     => 'text',
            'desc'     => __('Enter your Australia Post API Key.', 'woocommerce'),
            'id'       => 'australia_post_api_key'
        ),
        'from_zipcode' => array(
            'name'     => __('Australia From Zipcode', 'woocommerce'),
            'type'     => 'text',
            'desc'     => __('Enter your Australia Post "From" Zipcode.', 'woocommerce'),
            'id'       => 'australia_post_from_zipcode'
        ),
        'section_end' => array(
            'type'     => 'sectionend',
            'id'       => 'australia_post_settings_section_end'
        )
    ));
}
add_action('woocommerce_settings_australia_post', 'australia_post_settings_fields');

// Save the custom settings
function save_australia_post_settings() {
    $settings = array(
        'australia_post_api_key',
        'australia_post_from_zipcode'
    );

    foreach ($settings as $setting) {
        if (isset($_POST[$setting])) {
            update_option($setting, sanitize_text_field($_POST[$setting]));
        }
    }
}
add_action('woocommerce_update_options_australia_post', 'save_australia_post_settings');

// Display the saved values in the fields
function display_australia_post_settings() {
    ?>
    <div id="australia_post_settings">
        <?php woocommerce_admin_fields(australia_post_settings_fields()); ?>
    </div>
    <?php
}
add_action('woocommerce_admin_field_australia_post_api_key', 'display_australia_post_settings');
add_action('woocommerce_admin_field_australia_post_from_zipcode', 'display_australia_post_settings');

// Add a custom tab to the WooCommerce settings
function add_my_post_settings_tab($settings_tabs) {
    $settings_tabs['my_post'] = __('my Post Settings', 'woocommerce');
    return $settings_tabs;
}
add_filter('woocommerce_settings_tabs_array', 'add_my_post_settings_tab', 50);

// Add settings fields to the my Post tab
function my_post_settings_fields() {
    woocommerce_admin_fields(array(
        'my_post_section_title' => array(
            'name' => __('Connect MyPost Business via ReachShip', 'woocommerce'),
            'type' => 'title',
            'desc' => '',
            'id' => 'my_post_settings_section_title'
        ),
        'my_post_client_id' => array(
            'name' => __('ReachShip Client Id', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Enter your ReachShip Client ID.', 'woocommerce'),
            'id' => 'my_post_client_id'
        ),
        'my_post_client_secret' => array(
            'name' => __('ReachShip Client Secret', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Enter your ReachShip Client Secret.', 'woocommerce'),
            'id' => 'my_post_client_secret'
        ),
        'my_post_from_city' => array(
            'name' => __('From City', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Enter your  "From" City.', 'woocommerce'),
            'id' => 'my_post_from_city'
        ),
        'my_post_from_state' => array(
            'name' => __('From State', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Enter your "From" State.', 'woocommerce'),
            'id' => 'my_post_from_state'
        ),
        'my_post_from_zipcode' => array(
            'name' => __('From Zipcode', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Enter your "From" Zipcode.', 'woocommerce'),
            'id' => 'my_post_from_zipcode'
        ),
        'my_post_access_token' => array(
            'name' => __('Access token', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Auto-populated Auth Code', 'woocommerce'),
            'id' => 'my_post_access_token',
            'custom_attributes' => array(
                'readonly' => 'readonly',
            ),
        ),

        'my_post_date_updated' => array(
            'name' => __('Date updated', 'woocommerce'),
            'type' => 'text',
            'desc' => __('Auto-populated Date updated', 'woocommerce'),
            'id' => 'my_post_date_updated',
            'custom_attributes' => array(
                'readonly' => 'readonly',
            ),
        ),
        'my_post_section_end' => array(
            'type' => 'sectionend',
            'id' => 'my_post_settings_section_end'
        )
    ));
}
add_action('woocommerce_settings_my_post', 'my_post_settings_fields');


// Save the custom settings
function save_my_post_settings() {
    $settings = array(
        'my_post_client_id',
        'my_post_client_secret',
        'my_post_from_city',
        'my_post_from_state',
        'my_post_from_zipcode'
    );

    foreach ($settings as $setting) {
        if (isset($_POST[$setting])) {
            update_option($setting, sanitize_text_field($_POST[$setting]));
        }
    }
}
add_action('woocommerce_update_options_my_post', 'save_my_post_settings');

// Display the saved values in the fields
function display_my_post_settings() {
    ?>
    <div id="my_post_settings">
        <?php woocommerce_admin_fields(my_post_settings_fields()); ?>
    </div>
    <?php
}
add_action('woocommerce_admin_field_my_post_api_key', 'display_my_post_settings');
add_action('woocommerce_admin_field_my_post_from_zipcode', 'display_my_post_settings');









/**
 * Hook into WooCommerce to add custom shipping methods.
 */
function add_custom_shipping_methods($methods) {
    /*$methods['AUS_PARCEL_EXPRESS'] = 'Australia_Post_HP_Shipping_Method';
    $methods['another_shipping_method'] = 'Another_Shipping_Method';*/
    // Add more shipping methods as needed
    return $methods;
}
add_filter('woocommerce_shipping_methods', 'add_custom_shipping_methods');

/**
 * Define the Australia_Post_HP_Shipping_Method class with a unique name.
 */
class Australia_Post_HP_Shipping_Method extends WC_Shipping_Method {
    
    public function __construct() {
        /*$this->id = 'AUS_PARCEL_EXPRESS';
        $this->method_title = 'Australia Post HP Shipping';
        $this->method_description = 'Australia Post HP shipping method based on product attributes';
        $this->enabled = 'yes';*/
        
        
        if ( empty( $this->instance_settings ) ) {
			$this->init_instance_settings();
		}
    }

    public function update_mypost_token() {
        $ch = curl_init('https://api.reachship.com/production/v1/oauth/token?grant_type=client_credentials&client_id='.get_option('my_post_client_id').'&client_secret='.get_option('my_post_client_secret'));

        // Set the cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

        // Set any headers you need (e.g., for authorization)

        // Execute the cURL request
        $response = curl_exec($ch);

        $responseArray = json_decode($response,true);
        if(isset($responseArray['access_token'])) {
            update_option('my_post_access_token', sanitize_text_field($responseArray['access_token']));
            update_option('my_post_date_updated', sanitize_text_field(  time()));
        }
    }

    public function add_fee_to_all_shipping_methods($price) {
        // Get the value of wc_handling_fees
         $handling_fees = get_option('wc_handling_fees');
     
         $calculation_type = get_option('wc_handling_fees_calculation_type');
     
         // Check if the value ends with a percent symbol
         if (substr($handling_fees, -1) === '%') {
             // Remove the percent symbol and convert to a float
             $percentage = (float) rtrim($handling_fees, '%');
             $products_total = WC()->cart->get_cart_contents_total();
             //$shipping_total = WC()->cart->get_shipping_total() +  WC()->cart->get_shipping_tax();
     
             $shipping_total = $price;
     
             $products_total = WC()->cart->subtotal;
     
             
             // Divide by 100
             $percentage /= 100;
             
     
             if($calculation_type == "products") {
                 $handling_fees = ( $products_total ) * $percentage;
                
             } else if ( $calculation_type == "products_shipping") {
                 $handling_fees = ($products_total  + $shipping_total) * $percentage;
     
             } else if ( $calculation_type == "shipping") {
     
                 $handling_fees = ( $shipping_total) * $percentage;
             } else {
                 //WC()->cart->add_fee(__('Handling Fees', 'txtdomain'), 0.00);
                 $handling_fees = 0.00;
             }
             
             //WC()->cart->add_fee(__('Handling Fees', 'txtdomain'), $handling_fees);
     
         } else {
             if(is_numeric($handling_fees) || is_float($handling_fees)) {
                 //WC()->cart->add_fee(__('Handling Fees', 'txtdomain'), $handling_fees);
                 
             } else {
                 //WC()->cart->add_fee(__('Handling Fees', 'txtdomain'), 0.00);
                 $handling_fees = 0.00;
             }
             
         }
         return $handling_fees + $price;
    }

    public function calculate_shipping($package = []) {

        $timestamp = (int) get_option('my_post_date_updated');

        $daysAgo = 29; // Number of days to compare against

        // Calculate the timestamp from 29 days ago (24 hours * 60 minutes * 60 seconds * $daysAgo)
        $timestamp29DaysAgo = $timestamp - (24 * 60 * 60 * $daysAgo);

        if ($timestamp < $timestamp29DaysAgo) {

            $this->update_mypost_token();
            
        } else {

        }

       

        $length = 0;
        $width = 0;
        $weight = 0;
        $height = 0;
        $non_bundle = false;


        foreach(WC()->cart->get_cart() as $cart_item) {
        
            $quantity = 0;
           
            //$this->method_title .= $cart_item['product_id']. print_r($cart_item,true)." : ".$shipping_cost." , ";

            if(wc_pb_is_bundle_container_cart_item($cart_item)){
                // if its a bundle container product
                //$this->method_title .= $cart_item['product_id']. "(bundle) : ".$shipping_cost." , ";
                
            } else if(wc_pb_get_bundled_cart_item_container($cart_item)) {
                //$this->method_title .= $cart_item['product_id']. "(in bundle -  ".wc_pb_get_bundled_cart_item_container($cart_item).") : ".$shipping_cost." , ";
            } else {
                $non_bundle = true;
                $product = wc_get_product($cart_item["product_id"]);
                /*$length += $cart_item["quantity"] * ($product->get_length()/10);
                $width += ($product->get_width()/10);
               
                $height += ($product->get_height()/10);*/
                $weight += $cart_item["quantity"] *($product->get_weight()/1000);

                $area_coverd += ($product->get_length()/10) * ($product->get_width()/10) * ($product->get_height()/10) * $cart_item["quantity"];

                /*pow($number, 1/3)*/

                
                $products_added_to_shipping .= " :::: ".$cart_item['product_id']. "(not in bundle), ";
            }

        }
        if($non_bundle) {
            
            $postal_code = $package[ 'destination' ][ 'postcode' ];
            $city = $package['destination']['city'];
            $state = $package['destination']['state'];

            
            $height = $width=  $length = pow($area_coverd, 1/3);
            
            /*$product = wc_get_product($cart_item["product_id"]);
            $length = $cart_item["quantity"] * ($product->get_length()/10);
            $width = ($product->get_width()/10);
            $weight = $cart_item["quantity"] *($product->get_weight()/1000);
            $height = ($product->get_height()/10);*/
            



            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://digitalapi.auspost.com.au/postage/parcel/domestic/calculate.json?service_code=AUS_PARCEL_REGULAR&from_postcode='.get_option('australia_post_from_zipcode').'&to_postcode='.$postal_code.'&length='.$length.'&width='.$width.'&height='.$height.'&weight='.$weight.'',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'auth-key: '. get_option('australia_post_api_key')
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            //echo $response;
            $response_array = json_decode($response,true);
            if(isset($response_array["postage_result"]["service"])) {
                $rate = array(
                    'id' => 'AUS_PARCEL_REGULAR',
                    'label' => "Australia Post Regular Post",
                    'cost' => $this->add_fee_to_all_shipping_methods((float)$response_array["postage_result"]["total_cost"]),
                    'taxes' => false
                );
                $this->add_rate($rate);

            } 



            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://digitalapi.auspost.com.au/postage/parcel/domestic/calculate.json?service_code=AUS_PARCEL_EXPRESS&from_postcode='.get_option('australia_post_from_zipcode').'&to_postcode='.$postal_code.'&length='.$length.'&width='.$width.'&height='.$height.'&weight='.$weight.'',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'auth-key: '. get_option('australia_post_api_key')
            ),
            ));

            $response3 = curl_exec($curl);

            curl_close($curl);
            //echo $response;
            $response_array = json_decode($response3,true);
            if(isset($response_array["postage_result"]["service"])) {

                $rate = array(
                    'id' => 'AU_POST_AUS_PARCEL_EXPRESS',
                    'label' => "Australia Post Express Post",
                    'cost' => $this->add_fee_to_all_shipping_methods((float)$response_array["postage_result"]["total_cost"])
                );
                $this->add_rate($rate);
                /*$rate = array(
                    'id' => 'AUS_PARCEL_EXPRESS123',
                    'label' => "Australia Post Express 12345",
                    //'cost' => $this->add_fee_to_all_shipping_methods((float)$response_array["postage_result"]["total_cost"])
                    'cost' => 10.5
                );
                $this->add_rate($rate);*/

            } 


            

            $curl1 = curl_init();

            $this->update_mypost_token();
          
            curl_setopt_array($curl1, array(
            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)',
            CURLOPT_URL => 'https://api.reachship.com/production/v1/rates',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "shipment": {
                    "ship_to": {
                        "city_locality": "'.$city.'",
                        "state_province": "'.$state.'",
                        "postal_code": "'.$postal_code.'",
                        "country_code": "AU"
                    },
                    "ship_from": {
                        "city_locality": "Mansfield",
                        "state_province": "Victoria",
                        "postal_code": "3722",
                        "country_code": "AU"
                    },
                    "packages": [
                        {
                            "weight": {
                                "value": '.$weight.',
                                "unit": "KG"
                            },
                            "length": {
                                "value": '.$length.',
                                "unit": "CM"
                            },
                            "width": {
                                "value": '.$width.',
                                "unit": "CM"
                            },
                            "height": {
                                "value": '.$height.',
                                "unit": "CM"
                            }
                        }
                    ]
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '. get_option('my_post_access_token'),
                'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl1);
            $fp = fopen('reachship_response.txt', 'w');
            fwrite($fp, $response);
            fclose($fp);
            curl_close($curl1);

            $least_rate_key = 0;
            $response_array_ready_ship = json_decode($response,true);
            /*if(is_array($response_array_ready_ship)) {*/
                foreach($response_array_ready_ship as $key => $r_shippings) {
                    if (strpos($r_shippings["serviceName"], "Satchel") !== false || strpos($r_shippings["serviceName"], "Parcel") !== false ) {
                        continue;
                    } 
                    if($r_shippings["totalChargeWithTaxes"] < $response_array_ready_ship[$least_rate_key]["totalChargeWithTaxes"]) {
                        $least_rate_key = $key;
                    }
                    
                    
                }

                $rate = array(
                    'id' => 'custom_AUPOST_MY_POST_Business_'.$response_array_ready_ship[$least_rate_key]["serviceCode"],
                    'label' => "My Post Business"/*. $response_array_ready_ship[$least_rate_key]['serviceName']*/,
                    'cost' => $this->add_fee_to_all_shipping_methods((float)$response_array_ready_ship[$least_rate_key]["totalChargeWithTaxes"])
                );
                $this->add_rate($rate);
            /*} */

            /*$rate = array(
                'id' => 'custom_1',
                'label' => "custom_alter 1.5946 ".print_r($response_array_ready_ship,true),
                'cost' => 1.5
            );
            $this->add_rate($rate);*/

        } else {

            $rate = array(
                'id' => 'custom_free_shipping',
                'label' => "Free Shipping",
                'cost' => 0.00
            );
            $this->add_rate($rate);

        }

            

        /*$shipping_cost = $this->calculate_australia_post_hp_shipping_cost($package);

        $rate = array(
            'id' => $this->id,
            'label' => $this->method_title,
            'description'=> $this->method_description,
            'cost' => $shipping_cost,
        );

        if(is_numeric($shipping_cost) || is_float($shipping_cost)) {
            $this->add_rate($rate);
        }*/
        
    }


    /**
     * Calculate shipping costs within a WooCommerce plugin with unique function names.
     */
    function calculate_australia_post_hp_shipping_cost($package) {
        // Initialize the shipping cost.
        $shipping_cost = 0;

        // Iterate through the items in the cart.
        /*foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $quantity = $values['quantity'];

            // Calculate the shipping cost based on the product and quantity.
            $shipping_cost += $quantity * $this->calculate_australia_post_hp_shipping_cost_for_product($product);
            if(wc_pb_is_bundled_cart_item( $values, true )) {
                $this->method_title .= $item_id. "(bundle) : ".$shipping_cost." , ";
            } else {
                $this->method_title .= $item_id. "(not bundle) : ".$shipping_cost." , ";
            }

            $this->method_title .= json_encode($product);
            
        }*/

        
            $length = 0;
            $width = 0;
            $weight = 0;
            $height = 0;

        foreach(WC()->cart->get_cart() as $cart_item) {
           
            $quantity = 0;
            
            //$this->method_title .= $cart_item['product_id']. print_r($cart_item,true)." : ".$shipping_cost." , ";

            if(wc_pb_is_bundle_container_cart_item($cart_item)){
	        	// if its a bundle container product
	        	//$this->method_title .= $cart_item['product_id']. "(bundle) : ".$shipping_cost." , ";
	        } else if(wc_pb_get_bundled_cart_item_container($cart_item)) {
                //$this->method_title .= $cart_item['product_id']. "(in bundle -  ".wc_pb_get_bundled_cart_item_container($cart_item).") : ".$shipping_cost." , ";
            } else {

                $product = wc_get_product($cart_item["product_id"]);
                $length += $cart_item["quantity"] * ($product->get_length()/10);
                $width += ($product->get_width()/10);
                $weight += $cart_item["quantity"] *($product->get_weight()/1000);
                $height += ($product->get_height()/10);

                
                $products_added_to_shipping .= " :::: ".$cart_item['product_id']. "(not in bundle), ";
            }



            
           
        }

        $shipping_cost +=  $this->calculate_australia_post_hp_shipping_cost_for_product($length, $width, $weight, $height);
        $this->method_title .= $products_added_to_shipping;
        return $shipping_cost;
    }

    /**
     * Define your custom shipping cost calculation logic for each product with a unique function name.
     */
    function calculate_australia_post_hp_shipping_cost_for_product($length, $width, $weight, $height) {
       
        
            $curl = curl_init();

          

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://digitalapi.auspost.com.au/postage/parcel/domestic/calculate.json?service_code=AUS_PARCEL_EXPRESS&from_postcode=3722&to_postcode=3722&length='.$length.'&width='.$width.'&height='.$height.'&weight='.$weight.'',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'auth-key: 3c6f131f-ac54-4ecb-80a5-f47c3526b796'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            //echo $response;
            $response_array = json_decode($response,true);
            $this->method_title = $response_array["postage_result"]["service"];
            $this->method_description= $response_array["postage_result"]["delivery_time"];
        return $response_array["postage_result"]["total_cost"]; // Example: $5 per product.
    }
}


// Hook into WooCommerce to register the custom shipping method classes.
function add_custom_shipping_method_classes($methods) {
    $methods[] = 'Australia_Post_HP_Shipping_Method';
    /*$methods[] = 'Another_Shipping_Method';*/
    // Add more shipping methods as needed
    return $methods;
}
add_filter('woocommerce_shipping_methods', 'add_custom_shipping_method_classes');
