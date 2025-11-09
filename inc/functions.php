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
    <div class="nearby-widget">
        <?php if(!empty($distances)): ?>
        <h4 class="amenities-title"><?php echo $by_distance_title; ?></h4>
        <div class="distances">
            <?php foreach ($distances as $type): ?>
                <?php if( $amenity = dna_get_amenity_by_key($type) ): ?>
                    <?php
                        $places = dna_get_nearby_places($lat, $lng, $type, $radius, $apiKey);
                        $i = 0;
                        if (!empty($places)) {
                            foreach ($places as $place) {
                                if ($i >= $max_amenities) {
                                    break;
                                }
                                $time = dna_get_travel_time($lat, $lng, $place['geometry']['location']['lat'], $place['geometry']['location']['lng'], $mode, $apiKey);
                    ?>
                                <div class="distance-item dna-amenity-item <?php echo $amenity['key']; ?>">
                                    <?php directorist_icon( $amenity['icon'] ); ?>
                                    <span>
                                        <?php
                                            if ($time) {
                                                // Show label depending on mode
                                                if ($mode === 'walking') {
                                                    echo esc_html($time . " walk to " . $place['name']);
                                                } elseif ($mode === 'driving') {
                                                    echo esc_html($time . " drive to " . $place['name']);
                                                } elseif ($mode === 'cycling') {
                                                    echo esc_html($time . " bike ride to " . $place['name']);
                                                } elseif ($mode === 'transit') {
                                                    echo esc_html($time . " transit to " . $place['name']);
                                                } else {
                                                    echo esc_html($time . " to " . $place['name']);
                                                }
                                            } else {
                                                echo esc_html("Nearby " . ucfirst($type));
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
        </div>
        <?php endif; ?>
        <?php if(!empty($amenities)): ?>    
        <h4 class="amenities-title"><?php echo $nearby_amenities_title; ?></h4>
        <div class="amenities">
            <?php foreach ($amenities as $type): ?>
                <?php if( $amenity = dna_get_amenity_by_key($type) ): ?>
                    <?php
                        $places = dna_get_nearby_places($lat, $lng, $type, $radius, $apiKey);
                        $i = 0;
                        if (!empty($places)) :
                            foreach ($places as $place) {
                                if ($i >= $max_amenities) {
                                    break;
                                }
                                ?>
                                <div class="amenity-item dna-amenity-item <?php echo $amenity['key']; ?>">
                                    <?php directorist_icon( $amenity['icon'] ); ?>
                                    <span><?php echo $place['name']; ?></span>
                                </div>
                                <?php
                                $i++;
                            }
                        endif;
                    ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Function to fetch places from Google Places API
function dna_get_nearby_places($lat, $lng, $type, $radius = 1000, $apiKey) {
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
            'label' => 'Hospital',
            'icon' => 'fa fa-hospital',
            'color' => '#DC3545' // Red
        ],
        [
            'key' => 'doctor',
            'label' => 'Doctor / Clinic',
            'icon' => 'fa fa-user-md',
            'color' => '#007BFF' // Blue
        ],
        [
            'key' => 'pharmacy',
            'label' => 'Pharmacy',
            'icon' => 'fa fa-prescription-bottle-alt',
            'color' => '#28A745' // Green
        ],
        [
            'key' => 'dentist',
            'label' => 'Dentist',
            'icon' => 'fa fa-tooth',
            'color' => '#17A2B8' // Teal
        ],
        [
            'key' => 'veterinary_care',
            'label' => 'Veterinary Care',
            'icon' => 'fa fa-dog',
            'color' => '#6F42C1' // Purple
        ],
        // Transport
        [
            'key' => 'bus_station',
            'label' => 'Bus Stop',
            'icon' => 'fa fa-bus',
            'color' => '#FFC107' // Amber
        ],
        [
            'key' => 'subway_station',
            'label' => 'Metro / Subway Station',
            'icon' => 'fa fa-subway',
            'color' => '#6610f2' // Indigo
        ],
        [
            'key' => 'train_station',
            'label' => 'Train Station',
            'icon' => 'fa fa-train',
            'color' => '#FF851B' // Orange
        ],
        [
            'key' => 'transit_station',
            'label' => 'Transit Station',
            'icon' => 'fa fa-bus-alt',
            'color' => '#FFD600' // Yellow
        ],
        [
            'key' => 'airport',
            'label' => 'Airport',
            'icon' => 'fa fa-plane',
            'color' => '#0074D9' // Sky Blue
        ],
        [
            'key' => 'taxi_stand',
            'label' => 'Taxi Stand',
            'icon' => 'fa fa-taxi',
            'color' => '#FFDD57' // Gold-Yellow
        ],
        [
            'key' => 'parking',
            'label' => 'Parking',
            'icon' => 'fa fa-parking',
            'color' => '#6C757D' // Grey
        ],
        // Education
        [
            'key' => 'school',
            'label' => 'School',
            'icon' => 'fa fa-school',
            'color' => '#00B894' // Turquoise Green
        ],
        [
            'key' => 'university',
            'label' => 'University',
            'icon' => 'fa fa-university',
            'color' => '#3D5AFE' // Blue
        ],
        [
            'key' => 'library',
            'label' => 'Library',
            'icon' => 'fa fa-book',
            'color' => '#795548' // Brown
        ],
        // Shopping
        [
            'key' => 'shopping_mall',
            'label' => 'Shopping Mall',
            'icon' => 'fa fa-shopping-bag',
            'color' => '#FF69B4' // Pink
        ],
        [
            'key' => 'store',
            'label' => 'Store',
            'icon' => 'fa fa-store',
            'color' => '#43A047' // Dark Green
        ],
        [
            'key' => 'supermarket',
            'label' => 'Supermarket',
            'icon' => 'fa fa-shopping-cart',
            'color' => '#8D6E63' // Soft Brown
        ],
        [
            'key' => 'convenience_store',
            'label' => 'Convenience Store',
            'icon' => 'fa fa-store-alt',
            'color' => '#FFA000' // Deep Yellow
        ],
        [
            'key' => 'clothing_store',
            'label' => 'Clothing Store',
            'icon' => 'fa fa-tshirt',
            'color' => '#B388FF' // Light Purple
        ],
        [
            'key' => 'electronics_store',
            'label' => 'Electronics Store',
            'icon' => 'fa fa-plug',
            'color' => '#FF5722' // Deep Orange
        ],
        [
            'key' => 'furniture_store',
            'label' => 'Furniture Store',
            'icon' => 'fa fa-couch',
            'color' => '#A1887F' // Brown-Gray
        ],
        [
            'key' => 'hardware_store',
            'label' => 'Hardware Store',
            'icon' => 'fa fa-tools',
            'color' => '#616161' // Dark Gray
        ],
        [
            'key' => 'home_goods_store',
            'label' => 'Home Goods Store',
            'icon' => 'fa fa-home',
            'color' => '#009688' // Teal
        ],
        [
            'key' => 'jewelry_store',
            'label' => 'Jewelry Store',
            'icon' => 'fa fa-gem',
            'color' => '#FFD700' // Gold
        ],
        [
            'key' => 'pet_store',
            'label' => 'Pet Store',
            'icon' => 'fa fa-paw',
            'color' => '#FDD835' // Yellow
        ],
        [
            'key' => 'book_store',
            'label' => 'Bookstore',
            'icon' => 'fa fa-book-open',
            'color' => '#6D4C41' // Brown
        ],
        // Food & Drinks
        [
            'key' => 'restaurant',
            'label' => 'Restaurant',
            'icon' => 'fa fa-utensils',
            'color' => '#D84315' // Reddish Orange
        ],
        [
            'key' => 'cafe',
            'label' => 'Cafe',
            'icon' => 'fa fa-coffee',
            'color' => '#A0522D' // Coffee Brown
        ],
        [
            'key' => 'bakery',
            'label' => 'Bakery',
            'icon' => 'fa fa-bread-slice',
            'color' => '#FBC02D' // Light Yellow
        ],
        [
            'key' => 'bar',
            'label' => 'Bar',
            'icon' => 'fa fa-glass-martini-alt',
            'color' => '#6F42C1' // Purple
        ],
        [
            'key' => 'meal_takeaway',
            'label' => 'Takeaway',
            'icon' => 'fa fa-hamburger',
            'color' => '#8D6E63' // Soft Brown
        ],
        [
            'key' => 'meal_delivery',
            'label' => 'Food Delivery',
            'icon' => 'fa fa-motorcycle',
            'color' => '#0288D1' // Blue
        ],
        // Lifestyle & Leisure
        [
            'key' => 'gym',
            'label' => 'Fitness Center / Gym',
            'icon' => 'fa fa-dumbbell',
            'color' => '#388E3C' // Green
        ],
        [
            'key' => 'spa',
            'label' => 'Spa / Swimming Pool',
            'icon' => 'fa fa-spa',
            'color' => '#00B8D4' // Cyan
        ],
        [
            'key' => 'stadium',
            'label' => 'Stadium',
            'icon' => 'fa fa-football-ball',
            'color' => '#F44336' // Red
        ],
        [
            'key' => 'movie_theater',
            'label' => 'Movie Theater',
            'icon' => 'fa fa-film',
            'color' => '#E040FB' // Purple
        ],
        [
            'key' => 'museum',
            'label' => 'Museum',
            'icon' => 'fa fa-landmark',
            'color' => '#795548' // Brown
        ],
        [
            'key' => 'art_gallery',
            'label' => 'Art Gallery',
            'icon' => 'fa fa-palette',
            'color' => '#FF4081' // Pink
        ],
        [
            'key' => 'night_club',
            'label' => 'Night Club',
            'icon' => 'fa fa-music',
            'color' => '#C51162' // Deep Pink
        ],
        [
            'key' => 'zoo',
            'label' => 'Zoo',
            'icon' => 'fa fa-hippo',
            'color' => '#4CAF50' // Green
        ],
        [
            'key' => 'aquarium',
            'label' => 'Aquarium',
            'icon' => 'fa fa-fish',
            'color' => '#00B8D4' // Bright Blue
        ],
        [
            'key' => 'casino',
            'label' => 'Casino',
            'icon' => 'fa fa-dice',
            'color' => '#FFD600' // Yellow
        ],
        [
            'key' => 'bowling_alley',
            'label' => 'Bowling Alley',
            'icon' => 'fa fa-bowling-ball',
            'color' => '#607D8B' // Blue Grey
        ],
        // Outdoor & Nature
        [
            'key' => 'park',
            'label' => 'Park',
            'icon' => 'fa fa-tree',
            'color' => '#43A047' // Green
        ],
        [
            'key' => 'campground',
            'label' => 'Campground',
            'icon' => 'fa fa-campground',
            'color' => '#A1887F' // Brown-gray
        ],
        [
            'key' => 'tourist_attraction',
            'label' => 'Tourist Attraction',
            'icon' => 'fa fa-binoculars',
            'color' => '#E65100' // Deep Orange
        ],
        // Services
        [
            'key' => 'atm',
            'label' => 'ATM',
            'icon' => 'fa fa-credit-card',
            'color' => '#1976D2' // Blue
        ],
        [
            'key' => 'bank',
            'label' => 'Bank',
            'icon' => 'fa fa-university',
            'color' => '#283593' // Deep Blue
        ],
        [
            'key' => 'post_office',
            'label' => 'Post Office',
            'icon' => 'fa fa-envelope',
            'color' => '#EC407A' // Pink
        ],
        [
            'key' => 'police',
            'label' => 'Police Station',
            'icon' => 'fa fa-user-shield',
            'color' => '#2E3B55' // Navy
        ],
        [
            'key' => 'fire_station',
            'label' => 'Fire Station',
            'icon' => 'fa fa-fire-extinguisher',
            'color' => '#FF0000' // Fire Red
        ],
        [
            'key' => 'city_hall',
            'label' => 'City Hall',
            'icon' => 'fa fa-city',
            'color' => '#6C3483' // Purple
        ],
        [
            'key' => 'embassy',
            'label' => 'Embassy',
            'icon' => 'fa fa-flag',
            'color' => '#0097A7' // Teal Blue
        ],
        [
            'key' => 'lawyer',
            'label' => 'Lawyer',
            'icon' => 'fa fa-gavel',
            'color' => '#8D6E63' // Taupe
        ],
        [
            'key' => 'real_estate_agency',
            'label' => 'Real Estate Agency',
            'icon' => 'fa fa-building',
            'color' => '#607D8B' // Blue Grey
        ],
        // Lodging
        [
            'key' => 'lodging',
            'label' => 'Hotel / Lodging',
            'icon' => 'fa fa-hotel',
            'color' => '#FFA500' // Orange
        ],
        [
            'key' => 'rv_park',
            'label' => 'RV Park',
            'icon' => 'fa fa-caravan',
            'color' => '#A1887F' // Light Brown
        ],
        // Auto Services
        [
            'key' => 'car_rental',
            'label' => 'Car Rental',
            'icon' => 'fa fa-car',
            'color' => '#607D8B' // Gray Blue
        ],
        [
            'key' => 'car_dealer',
            'label' => 'Car Dealer',
            'icon' => 'fa fa-car-side',
            'color' => '#455A64' // Blue Grey
        ],
        [
            'key' => 'car_repair',
            'label' => 'Car Repair',
            'icon' => 'fa fa-tools',
            'color' => '#6D4C41' // Brown
        ],
        [
            'key' => 'gas_station',
            'label' => 'Gas Station',
            'icon' => 'fa fa-gas-pump',
            'color' => '#F4511E' // Orange-Red
        ],
        // Religion
        [
            'key' => 'church',
            'label' => 'Church',
            'icon' => 'fa fa-church',
            'color' => '#4527A0' // Deep Purple
        ],
        [
            'key' => 'hindu_temple',
            'label' => 'Hindu Temple',
            'icon' => 'fa fa-om',
            'color' => '#FF7043' // Orange
        ],
        [
            'key' => 'mosque',
            'label' => 'Mosque',
            'icon' => 'fa fa-mosque',
            'color' => '#388E3C' // Green
        ],
        [
            'key' => 'synagogue',
            'label' => 'Synagogue',
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