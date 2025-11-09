<?php

/**
 * Directorist_Nearby_Amenities DRA_Custom_Field
 *
 * This class is for preparing the custom field for the google reviews
 *
 * @package     Directorist_Nearby_Amenities
 * @since       1.0
 */

// Exit if accessed directly.
defined('ABSPATH') || die('Direct access is not allowed.');

if (! class_exists('DRA_Custom_Field')):

    /**
     * Class Multi_Location_Custom_Field
     */
    class DRA_Custom_Field
    {

        /**
         * Multi_Location_Custom_Field Constructor
         */
        public function __construct()
        {
            //add_filter('atbdp_form_preset_widgets', [$this, 'register_custom_field']);
            add_filter('atbdp_single_listing_other_fields_widget', [$this, 'single_listing_content_widgets']);
            //add_filter('directorist_field_template', [$this, 'directorist_field_template'], 10, 2);
            add_filter('directorist_single_item_template', [$this, 'directorist_single_item_template'], 10, 2);
        }

        /**
         * Single listing content widget
         */
        public function single_listing_content_widgets($widgets)
        {
            $widgets['nearby_amenities'] = [
                'label'   => __('Nearby Amenities', 'directorist-nearby-amenities'),
                'icon' => 'la la-map',
                'options'       => [
                    'label'   => [
                        'type'  => 'text',
                        'label' => __('Label', 'directorist-nearby-amenities'),
                        'value' => 'Nearby Amenities',
                    ],
                    'icon'    => [
                        'type'  => 'icon',
                        'label' => __('Icon', 'directorist-nearby-amenities'),
                        'value' => 'la la-map',
                    ],
                    'by_distance_title' => [
                        'type'        => 'text',
                        'label'       => __('By Distance Title', 'directorist-nearby-amenities'),
                        'value'       => 'Distances',
                        'description' => __('You can type the amenity types', 'directorist-nearby-amenities'),
                    ],
                    'by_distance_amenities' => [
                        'type'        => 'textarea',
                        'label'       => __('By Distance Amenities', 'directorist-nearby-amenities'),
                        'value'       => '',
                        'description' => __('You can type the amenity types, separated by comma. e.g. hospital, doctor, pharmacy, dentist, veterinary_care', 'directorist-nearby-amenities'),
                    ],
                    'nearby_amenities_title' => [
                        'type'        => 'text',
                        'label'       => __('Nearby Amenities Title', 'directorist-nearby-amenities'),
                        'value'       => 'Nearby Amenities',
                        'description' => __('You can type the nearby amenities title', 'directorist-nearby-amenities'),
                    ],
                    'nearby_amenities_amenities' => [
                        'type'        => 'textarea',
                        'label'       => __('Nearby Amenities', 'directorist-nearby-amenities'),
                        'value'       => '',
                        'description' => __('You can type the nearby amenities, separated by comma. e.g. hospital, doctor, pharmacy, dentist, veterinary_care', 'directorist-nearby-amenities'),
                    ],
                    'nearby_amenities_radius' => [
                        'type'        => 'number',
                        'label'       => __('Nearby Amenities Radius in meters', 'directorist-nearby-amenities'),
                        'value'       => 500,
                        'description' => __('You can type the nearby amenities radius', 'directorist-nearby-amenities'),
                    ],
                    'nearby_amenities_mode' => [
                        'type'        => 'select',
                        'label'       => __('Nearby Amenities Mode', 'directorist-nearby-amenities'),
                        'value'       => 'walking',
                        'description' => __('You can select the nearby amenities mode', 'directorist-nearby-amenities'),
                        'options'     => [
                            [
                                'label' => __('Walking', 'directorist-nearby-amenities'),
                                'value' => 'walking',
                            ],
                            [
                                'label' => __('Driving', 'directorist-nearby-amenities'),
                                'value' => 'driving',
                            ],
                            [
                                'label' => __('Cycling', 'directorist-nearby-amenities'),
                                'value' => 'cycling',
                            ],
                            [
                                'label' => __('Transit', 'directorist-nearby-amenities'),
                                'value' => 'transit',
                            ],
                        ],
                    ],
                    'max_amenities' => [
                        'type'        => 'number',
                        'label'       => __('Max Amenities Per Amenity Type', 'directorist-nearby-amenities'),
                        'value'       => 3,
                        'description' => __('You can type the max amenities per amenity type', 'directorist-nearby-amenities'),
                    ],
                    'amenity_icon_colors' => [
                        'type'        => 'toggle',
                        'label'       => __('Enable Amenity Icon Colors', 'directorist-nearby-amenities'),
                        'value'       => false,
                        'description' => __('You can enable the amenity icon colors', 'directorist-nearby-amenities'),
                    ],
                ],
            ];
            return $widgets;
        }

        /**
         * Directorist Single Listing Template
         */
        public function directorist_single_item_template($template, $field_data)
        {
            if ($field_data['widget_name'] == 'nearby_amenities') {
                $address = get_post_meta( $field_data['listing_id'], '_address', true );
                $apiKey = get_directorist_option('map_api_key', false);
                if ( $address && $apiKey ) {
                    $template .= $this->load_template('templates/single-listing', ['data' => $field_data, 'address' => $address]);
                }
            }

            return $template;
        }

        /**
         * Load Template
         */
        public function load_template($template_file, $args = array())
        {
            if (is_array($args)) {
                extract($args);
            }

            $theme_template  = '/directorist-custom-code/' . $template_file . '.php';
            $plugin_template = DIRECTORIST_NEARBY_AMENITIES_DIR . $template_file . '.php';

            if (file_exists(get_stylesheet_directory() . $theme_template)) {
                $file = get_stylesheet_directory() . $theme_template;
            } elseif (file_exists(get_template_directory() . $theme_template)) {
                $file = get_template_directory() . $theme_template;
            } else {
                $file = $plugin_template;
            }

            if (file_exists($file)) {
                include $file;
            }
        }
    }

    new DRA_Custom_Field();

endif;
