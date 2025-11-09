<?php

/**
 * Add your custom php code here
 */

function dna_generate_nearby_amenities( $amenity_args = [] ) {
    $apiKey = get_directorist_option('map_api_key', '');
    $lat = $amenity_args['lat'];
    $lng = $amenity_args['lng'];
    $radius = $amenity_args['radius'];
    $distances = $amenity_args['distances'];
    $amenities = $amenity_args['amenities'];
    $mode = $amenity_args['mode'];
    $by_distance_title = $amenity_args['by_distance_title'];
    $nearby_amenities_title = $amenity_args['nearby_amenities_title'];
    $max_amenities = $amenity_args['max_amenities'];

    $distance_found = 0;
    $amenity_found = 0;

    // Explode the distances and amenities by comma, check if the array is not empty
    if (!empty($distances)) {
        $distances = explode(',', $distances);
        $distances = array_map('trim', $distances);
    }
    if (!empty($amenities)) {
        $amenities = explode(',', $amenities);
        $amenities = array_map('trim', $amenities);
    }

    // Define place types for Google API
    $places_map = dna_get_amenity_types_list();

    ob_start();
    ?>
    <div class="dna-nearby-widget nearby-widget">
        <?php if(!empty($distances)): ?>
        <div class="dna-section dna-section--distances">
            <h4 class="dna-section-title amenities-title"><?php echo esc_html($by_distance_title); ?></h4>
            <div class="dna-distances-list distances">
                <?php foreach ($distances as $type): ?>
                    <?php if( $amenity = dna_get_amenity_by_key($type) ): ?>
                        <?php
                            $places = dna_get_nearby_places($lat, $lng, $type, $apiKey, $radius);
                            $i = 0;
                            if (!empty($places)) {
                                $distance_found ++;
                                foreach ($places as $place) {
                                    if ($i >= $max_amenities) {
                                        break;
                                    }
                                    $time = dna_get_travel_time($lat, $lng, $place['geometry']['location']['lat'], $place['geometry']['location']['lng'], $mode, $apiKey);
                                    $google_map_url = dna_get_google_map_url($place);
                        ?>
                                    <div class="dna-distance-item distance-item dna-amenity-item dna-amenity-item--<?php echo esc_attr($amenity['key']); ?>" 
                                         <?php if (!empty($google_map_url)): ?>data-google-map-url="<?php echo esc_attr($google_map_url); ?>"<?php endif; ?>>
                                        <span class="dna-amenity-icon"><?php directorist_icon( $amenity['icon'] ); ?></span>
                                        <span class="dna-amenity-text">
                                            <?php
                                                if ($time) {
                                                    // Show label depending on mode
                                                    if ($mode === 'walking') {
                                                        echo esc_html($time . " " . __('walk to', 'directorist-nearby-amenities') . " " . $place['name']);
                                                    } elseif ($mode === 'driving') {
                                                        echo esc_html($time . " " . __('drive to', 'directorist-nearby-amenities') . " " . $place['name']);
                                                    } elseif ($mode === 'cycling') {
                                                        echo esc_html($time . " " . __('bike ride to', 'directorist-nearby-amenities') . " " . $place['name']);
                                                    } elseif ($mode === 'transit') {
                                                        echo esc_html($time . " " . __('transit to', 'directorist-nearby-amenities') . " " . $place['name']);
                                                    } else {
                                                        echo esc_html($time . " " . __('to', 'directorist-nearby-amenities') . " " . $place['name']);
                                                    }
                                                } else {
                                                    echo esc_html(__('Nearby', 'directorist-nearby-amenities') . " " . ucfirst($type));
                                                }
                                            ?>
                                        </span>
                                    </div>
                        <?php
                                    $i++;
                                }
                            }
                        ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if($distance_found == 0): ?>
                    <div class="dna-no-results-found">
                        <span class="dna-amenity-text"><?php echo esc_html__('No nearby places found', 'directorist-nearby-amenities'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($amenities)): ?>    
        <div class="dna-section dna-section--amenities">
            <h4 class="dna-section-title amenities-title"><?php echo esc_html($nearby_amenities_title); ?></h4>
            <div class="dna-amenities-list amenities">
                <?php foreach ($amenities as $type): ?>
                    <?php if( $amenity = dna_get_amenity_by_key($type) ): ?>
                        <?php
                            $places = dna_get_nearby_places($lat, $lng, $type, $apiKey, $radius);
                            $i = 0;
                            if (!empty($places)) {
                                $amenity_found ++;
                                foreach ($places as $place) {
                                    if ($i >= $max_amenities) {
                                        break;
                                    }
                                    $google_map_url = dna_get_google_map_url($place);
                                    ?>
                                    <div class="dna-amenity-item amenity-item dna-amenity-item--<?php echo esc_attr($amenity['key']); ?>" 
                                         <?php if (!empty($google_map_url)): ?>data-google-map-url="<?php echo esc_attr($google_map_url); ?>"<?php endif; ?>>
                                        <span class="dna-amenity-icon"><?php directorist_icon( $amenity['icon'] ); ?></span>
                                        <span class="dna-amenity-text"><?php echo esc_html($place['name']); ?></span>
                                    </div>
                                    <?php
                                    $i++;
                                }
                            }
                        ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if($amenity_found == 0): ?>
                    <div class="dna-no-results-found">
                        <span class="dna-amenity-text"><?php echo esc_html__('No nearby amenities found', 'directorist-nearby-amenities'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Function to generate Google Maps URL from place data
function dna_get_google_map_url($place) {
    if (isset($place['geometry']['location']['lat']) && isset($place['geometry']['location']['lng'])) {
        $lat = $place['geometry']['location']['lat'];
        $lng = $place['geometry']['location']['lng'];
        
        // Use Google Maps search URL with coordinates
        // If place_id is available, use it for better accuracy
        if (isset($place['place_id']) && !empty($place['place_id'])) {
            return "https://www.google.com/maps/place/?q=place_id:" . urlencode($place['place_id']);
        }
        
        // Fallback to coordinates-based URL
        return "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
    }
    return '';
}

// Function to fetch places from Google Places API
function dna_get_nearby_places($lat, $lng, $type, $apiKey, $radius = 1000) {
    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lat},{$lng}&radius={$radius}&type={$type}&key={$apiKey}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['results'] ?? [];
}

// Function to calculate travel time with Distance Matrix
function dna_get_travel_time($lat, $lng, $destLat, $destLng, $mode, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$lat},{$lng}&destinations={$destLat},{$destLng}&mode={$mode}&key={$apiKey}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['rows'][0]['elements'][0]['duration']['text'] ?? null;
}


/**
 * Amenity Types List with appropiate icons - Google Places
 * 
 */
function dna_get_amenity_types_list() {
    $amenity_types = [
        // Essentials
        [
            'key' => 'hospital',
            'label' => __('Hospital', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-hospital',
            'color' => '#DC3545' // Red
        ],
        [
            'key' => 'doctor',
            'label' => __('Doctor / Clinic', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-user-md',
            'color' => '#007BFF' // Blue
        ],
        [
            'key' => 'pharmacy',
            'label' => __('Pharmacy', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-prescription-bottle-alt',
            'color' => '#28A745' // Green
        ],
        [
            'key' => 'dentist',
            'label' => __('Dentist', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-tooth',
            'color' => '#17A2B8' // Teal
        ],
        [
            'key' => 'veterinary_care',
            'label' => __('Veterinary Care', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-dog',
            'color' => '#6F42C1' // Purple
        ],
        // Transport
        [
            'key' => 'bus_station',
            'label' => __('Bus Stop', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-bus',
            'color' => '#FFC107' // Amber
        ],
        [
            'key' => 'subway_station',
            'label' => __('Metro / Subway Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-subway',
            'color' => '#6610f2' // Indigo
        ],
        [
            'key' => 'train_station',
            'label' => __('Train Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-train',
            'color' => '#FF851B' // Orange
        ],
        [
            'key' => 'transit_station',
            'label' => __('Transit Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-bus-alt',
            'color' => '#FFD600' // Yellow
        ],
        [
            'key' => 'airport',
            'label' => __('Airport', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-plane',
            'color' => '#0074D9' // Sky Blue
        ],
        [
            'key' => 'taxi_stand',
            'label' => __('Taxi Stand', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-taxi',
            'color' => '#FFDD57' // Gold-Yellow
        ],
        [
            'key' => 'parking',
            'label' => __('Parking', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-parking',
            'color' => '#6C757D' // Grey
        ],
        // Education
        [
            'key' => 'school',
            'label' => __('School', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-school',
            'color' => '#00B894' // Turquoise Green
        ],
        [
            'key' => 'university',
            'label' => __('University', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-university',
            'color' => '#3D5AFE' // Blue
        ],
        [
            'key' => 'library',
            'label' => __('Library', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-book',
            'color' => '#795548' // Brown
        ],
        // Shopping
        [
            'key' => 'shopping_mall',
            'label' => __('Shopping Mall', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-shopping-bag',
            'color' => '#FF69B4' // Pink
        ],
        [
            'key' => 'store',
            'label' => __('Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-store',
            'color' => '#43A047' // Dark Green
        ],
        [
            'key' => 'supermarket',
            'label' => __('Supermarket', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-shopping-cart',
            'color' => '#8D6E63' // Soft Brown
        ],
        [
            'key' => 'convenience_store',
            'label' => __('Convenience Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-store-alt',
            'color' => '#FFA000' // Deep Yellow
        ],
        [
            'key' => 'clothing_store',
            'label' => __('Clothing Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-tshirt',
            'color' => '#B388FF' // Light Purple
        ],
        [
            'key' => 'electronics_store',
            'label' => __('Electronics Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-plug',
            'color' => '#FF5722' // Deep Orange
        ],
        [
            'key' => 'furniture_store',
            'label' => __('Furniture Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-couch',
            'color' => '#A1887F' // Brown-Gray
        ],
        [
            'key' => 'hardware_store',
            'label' => __('Hardware Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-tools',
            'color' => '#616161' // Dark Gray
        ],
        [
            'key' => 'home_goods_store',
            'label' => __('Home Goods Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-home',
            'color' => '#009688' // Teal
        ],
        [
            'key' => 'jewelry_store',
            'label' => __('Jewelry Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-gem',
            'color' => '#FFD700' // Gold
        ],
        [
            'key' => 'pet_store',
            'label' => __('Pet Store', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-paw',
            'color' => '#FDD835' // Yellow
        ],
        [
            'key' => 'book_store',
            'label' => __('Bookstore', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-book-open',
            'color' => '#6D4C41' // Brown
        ],
        // Food & Drinks
        [
            'key' => 'restaurant',
            'label' => __('Restaurant', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-utensils',
            'color' => '#D84315' // Reddish Orange
        ],
        [
            'key' => 'cafe',
            'label' => __('Cafe', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-coffee',
            'color' => '#A0522D' // Coffee Brown
        ],
        [
            'key' => 'bakery',
            'label' => __('Bakery', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-bread-slice',
            'color' => '#FBC02D' // Light Yellow
        ],
        [
            'key' => 'bar',
            'label' => __('Bar', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-glass-martini-alt',
            'color' => '#6F42C1' // Purple
        ],
        [
            'key' => 'meal_takeaway',
            'label' => __('Takeaway', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-hamburger',
            'color' => '#8D6E63' // Soft Brown
        ],
        [
            'key' => 'meal_delivery',
            'label' => __('Food Delivery', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-motorcycle',
            'color' => '#0288D1' // Blue
        ],
        // Lifestyle & Leisure
        [
            'key' => 'gym',
            'label' => __('Fitness Center / Gym', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-dumbbell',
            'color' => '#388E3C' // Green
        ],
        [
            'key' => 'spa',
            'label' => __('Spa / Swimming Pool', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-spa',
            'color' => '#00B8D4' // Cyan
        ],
        [
            'key' => 'stadium',
            'label' => __('Stadium', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-football-ball',
            'color' => '#F44336' // Red
        ],
        [
            'key' => 'movie_theater',
            'label' => __('Movie Theater', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-film',
            'color' => '#E040FB' // Purple
        ],
        [
            'key' => 'museum',
            'label' => __('Museum', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-landmark',
            'color' => '#795548' // Brown
        ],
        [
            'key' => 'art_gallery',
            'label' => __('Art Gallery', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-palette',
            'color' => '#FF4081' // Pink
        ],
        [
            'key' => 'night_club',
            'label' => __('Night Club', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-music',
            'color' => '#C51162' // Deep Pink
        ],
        [
            'key' => 'zoo',
            'label' => __('Zoo', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-hippo',
            'color' => '#4CAF50' // Green
        ],
        [
            'key' => 'aquarium',
            'label' => __('Aquarium', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-fish',
            'color' => '#00B8D4' // Bright Blue
        ],
        [
            'key' => 'casino',
            'label' => __('Casino', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-dice',
            'color' => '#FFD600' // Yellow
        ],
        [
            'key' => 'bowling_alley',
            'label' => __('Bowling Alley', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-bowling-ball',
            'color' => '#607D8B' // Blue Grey
        ],
        // Outdoor & Nature
        [
            'key' => 'park',
            'label' => __('Park', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-tree',
            'color' => '#43A047' // Green
        ],
        [
            'key' => 'campground',
            'label' => __('Campground', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-campground',
            'color' => '#A1887F' // Brown-gray
        ],
        [
            'key' => 'tourist_attraction',
            'label' => __('Tourist Attraction', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-binoculars',
            'color' => '#E65100' // Deep Orange
        ],
        // Services
        [
            'key' => 'atm',
            'label' => __('ATM', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-credit-card',
            'color' => '#1976D2' // Blue
        ],
        [
            'key' => 'bank',
            'label' => __('Bank', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-university',
            'color' => '#283593' // Deep Blue
        ],
        [
            'key' => 'post_office',
            'label' => __('Post Office', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-envelope',
            'color' => '#EC407A' // Pink
        ],
        [
            'key' => 'police',
            'label' => __('Police Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-user-shield',
            'color' => '#2E3B55' // Navy
        ],
        [
            'key' => 'fire_station',
            'label' => __('Fire Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-fire-extinguisher',
            'color' => '#FF0000' // Fire Red
        ],
        [
            'key' => 'city_hall',
            'label' => __('City Hall', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-city',
            'color' => '#6C3483' // Purple
        ],
        [
            'key' => 'embassy',
            'label' => __('Embassy', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-flag',
            'color' => '#0097A7' // Teal Blue
        ],
        [
            'key' => 'lawyer',
            'label' => __('Lawyer', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-gavel',
            'color' => '#8D6E63' // Taupe
        ],
        [
            'key' => 'real_estate_agency',
            'label' => __('Real Estate Agency', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-building',
            'color' => '#607D8B' // Blue Grey
        ],
        // Lodging
        [
            'key' => 'lodging',
            'label' => __('Hotel / Lodging', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-hotel',
            'color' => '#FFA500' // Orange
        ],
        [
            'key' => 'rv_park',
            'label' => __('RV Park', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-caravan',
            'color' => '#A1887F' // Light Brown
        ],
        // Auto Services
        [
            'key' => 'car_rental',
            'label' => __('Car Rental', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-car',
            'color' => '#607D8B' // Gray Blue
        ],
        [
            'key' => 'car_dealer',
            'label' => __('Car Dealer', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-car-side',
            'color' => '#455A64' // Blue Grey
        ],
        [
            'key' => 'car_repair',
            'label' => __('Car Repair', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-tools',
            'color' => '#6D4C41' // Brown
        ],
        [
            'key' => 'gas_station',
            'label' => __('Gas Station', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-gas-pump',
            'color' => '#F4511E' // Orange-Red
        ],
        // Religion
        [
            'key' => 'church',
            'label' => __('Church', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-church',
            'color' => '#4527A0' // Deep Purple
        ],
        [
            'key' => 'hindu_temple',
            'label' => __('Hindu Temple', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-om',
            'color' => '#FF7043' // Orange
        ],
        [
            'key' => 'mosque',
            'label' => __('Mosque', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-mosque',
            'color' => '#388E3C' // Green
        ],
        [
            'key' => 'synagogue',
            'label' => __('Synagogue', 'directorist-nearby-amenities'),
            'icon' => 'fa fa-synagogue',
            'color' => '#FBC02D' // Yellow
        ]
    ];
    return apply_filters('dna_get_amenity_types_list', $amenity_types);
}

function dna_get_amenity_by_key($key) {
    $amenity_types = dna_get_amenity_types_list();
    foreach ($amenity_types as $amenity) {
        if ($amenity['key'] === $key) {
            return $amenity;
        }
    }
    return false;
}

/**
 * Ajax handler to load nearby amenities
 */
function dna_ajax_load_nearby_amenities() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dna_load_amenities_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed', 'directorist-nearby-amenities')));
        return;
    }

    // Get and sanitize parameters
    $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
    
    if (!$listing_id) {
        wp_send_json_error(array('message' => __('Invalid listing ID', 'directorist-nearby-amenities')));
        return;
    }

    // Get amenity arguments from POST data
    $amenity_args = array(
        'lat' => isset($_POST['lat']) ? sanitize_text_field($_POST['lat']) : '',
        'lng' => isset($_POST['lng']) ? sanitize_text_field($_POST['lng']) : '',
        'radius' => isset($_POST['radius']) ? intval($_POST['radius']) : 500,
        'distances' => isset($_POST['distances']) ? sanitize_text_field($_POST['distances']) : '',
        'amenities' => isset($_POST['amenities']) ? sanitize_text_field($_POST['amenities']) : '',
        'mode' => isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'driving',
        'by_distance_title' => isset($_POST['by_distance_title']) ? sanitize_text_field($_POST['by_distance_title']) : __('Distance', 'directorist-nearby-amenities'),
        'nearby_amenities_title' => isset($_POST['nearby_amenities_title']) ? sanitize_text_field($_POST['nearby_amenities_title']) : __('Nearby Amenities', 'directorist-nearby-amenities'),
        'max_amenities' => isset($_POST['max_amenities']) ? intval($_POST['max_amenities']) : 3,
    );

    // Generate amenities HTML
    $amenities_html = dna_generate_nearby_amenities($amenity_args);

    // Get amenity icon colors if enabled
    $icon_colors_html = '';
    if (isset($_POST['amenity_icon_colors']) && $_POST['amenity_icon_colors'] == 'true') {
        $amenity_types = dna_get_amenity_types_list();
        if (!empty($amenity_types)) {
            $icon_colors_html = '<style class="dna-amenity-icon-colors">';
            foreach ($amenity_types as $amenity) {
                $icon_colors_html .= '.dna-amenity-item--' . esc_attr($amenity['key']) . ' .dna-amenity-icon .directorist-icon-mask::after { background-color: ' . esc_attr($amenity['color']) . ' !important; }';
            }
            $icon_colors_html .= '</style>';
        }
    }

    wp_send_json_success(array(
        'html' => $amenities_html,
        'icon_colors' => $icon_colors_html
    ));
}
add_action('wp_ajax_dna_load_nearby_amenities', 'dna_ajax_load_nearby_amenities');
add_action('wp_ajax_nopriv_dna_load_nearby_amenities', 'dna_ajax_load_nearby_amenities');