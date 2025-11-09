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
                                <div class="distance-item">
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
                                <div class="amenity-item">
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
            'icon' => 'fa fa-hospital'
        ],
        [
            'key' => 'doctor',
            'label' => 'Doctor / Clinic',
            'icon' => 'fa fa-user-md'
        ],
        [
            'key' => 'pharmacy',
            'label' => 'Pharmacy',
            'icon' => 'fa fa-prescription-bottle-alt'
        ],
        [
            'key' => 'dentist',
            'label' => 'Dentist',
            'icon' => 'fa fa-tooth'
        ],
        [
            'key' => 'veterinary_care',
            'label' => 'Veterinary Care',
            'icon' => 'fa fa-dog'
        ],
        // Transport
        [
            'key' => 'bus_station',
            'label' => 'Bus Stop',
            'icon' => 'fa fa-bus'
        ],
        [
            'key' => 'subway_station',
            'label' => 'Metro / Subway Station',
            'icon' => 'fa fa-subway'
        ],
        [
            'key' => 'train_station',
            'label' => 'Train Station',
            'icon' => 'fa fa-train'
        ],
        [
            'key' => 'transit_station',
            'label' => 'Transit Station',
            'icon' => 'fa fa-bus-alt'
        ],
        [
            'key' => 'airport',
            'label' => 'Airport',
            'icon' => 'fa fa-plane'
        ],
        [
            'key' => 'taxi_stand',
            'label' => 'Taxi Stand',
            'icon' => 'fa fa-taxi'
        ],
        [
            'key' => 'parking',
            'label' => 'Parking',
            'icon' => 'fa fa-parking'
        ],
        // Education
        [
            'key' => 'school',
            'label' => 'School',
            'icon' => 'fa fa-school'
        ],
        [
            'key' => 'university',
            'label' => 'University',
            'icon' => 'fa fa-university'
        ],
        [
            'key' => 'library',
            'label' => 'Library',
            'icon' => 'fa fa-book'
        ],
        // Shopping
        [
            'key' => 'shopping_mall',
            'label' => 'Shopping Mall',
            'icon' => 'fa fa-shopping-bag'
        ],
        [
            'key' => 'store',
            'label' => 'Store',
            'icon' => 'fa fa-store'
        ],
        [
            'key' => 'supermarket',
            'label' => 'Supermarket',
            'icon' => 'fa fa-shopping-cart'
        ],
        [
            'key' => 'convenience_store',
            'label' => 'Convenience Store',
            'icon' => 'fa fa-store-alt'
        ],
        [
            'key' => 'clothing_store',
            'label' => 'Clothing Store',
            'icon' => 'fa fa-tshirt'
        ],
        [
            'key' => 'electronics_store',
            'label' => 'Electronics Store',
            'icon' => 'fa fa-plug'
        ],
        [
            'key' => 'furniture_store',
            'label' => 'Furniture Store',
            'icon' => 'fa fa-couch'
        ],
        [
            'key' => 'hardware_store',
            'label' => 'Hardware Store',
            'icon' => 'fa fa-tools'
        ],
        [
            'key' => 'home_goods_store',
            'label' => 'Home Goods Store',
            'icon' => 'fa fa-home'
        ],
        [
            'key' => 'jewelry_store',
            'label' => 'Jewelry Store',
            'icon' => 'fa fa-gem'
        ],
        [
            'key' => 'pet_store',
            'label' => 'Pet Store',
            'icon' => 'fa fa-paw'
        ],
        [
            'key' => 'book_store',
            'label' => 'Bookstore',
            'icon' => 'fa fa-book-open'
        ],
        // Food & Drinks
        [
            'key' => 'restaurant',
            'label' => 'Restaurant',
            'icon' => 'fa fa-utensils'
        ],
        [
            'key' => 'cafe',
            'label' => 'Cafe',
            'icon' => 'fa fa-coffee'
        ],
        [
            'key' => 'bakery',
            'label' => 'Bakery',
            'icon' => 'fa fa-bread-slice'
        ],
        [
            'key' => 'bar',
            'label' => 'Bar',
            'icon' => 'fa fa-glass-martini-alt'
        ],
        [
            'key' => 'meal_takeaway',
            'label' => 'Takeaway',
            'icon' => 'fa fa-hamburger'
        ],
        [
            'key' => 'meal_delivery',
            'label' => 'Food Delivery',
            'icon' => 'fa fa-motorcycle'
        ],
        // Lifestyle & Leisure
        [
            'key' => 'gym',
            'label' => 'Fitness Center / Gym',
            'icon' => 'fa fa-dumbbell'
        ],
        [
            'key' => 'spa',
            'label' => 'Spa / Swimming Pool',
            'icon' => 'fa fa-spa'
        ],
        [
            'key' => 'stadium',
            'label' => 'Stadium',
            'icon' => 'fa fa-football-ball'
        ],
        [
            'key' => 'movie_theater',
            'label' => 'Movie Theater',
            'icon' => 'fa fa-film'
        ],
        [
            'key' => 'museum',
            'label' => 'Museum',
            'icon' => 'fa fa-landmark'
        ],
        [
            'key' => 'art_gallery',
            'label' => 'Art Gallery',
            'icon' => 'fa fa-palette'
        ],
        [
            'key' => 'night_club',
            'label' => 'Night Club',
            'icon' => 'fa fa-music'
        ],
        [
            'key' => 'zoo',
            'label' => 'Zoo',
            'icon' => 'fa fa-hippo'
        ],
        [
            'key' => 'aquarium',
            'label' => 'Aquarium',
            'icon' => 'fa fa-fish'
        ],
        [
            'key' => 'casino',
            'label' => 'Casino',
            'icon' => 'fa fa-dice'
        ],
        [
            'key' => 'bowling_alley',
            'label' => 'Bowling Alley',
            'icon' => 'fa fa-bowling-ball'
        ],
        // Outdoor & Nature
        [
            'key' => 'park',
            'label' => 'Park',
            'icon' => 'fa fa-tree'
        ],
        [
            'key' => 'campground',
            'label' => 'Campground',
            'icon' => 'fa fa-campground'
        ],
        [
            'key' => 'tourist_attraction',
            'label' => 'Tourist Attraction',
            'icon' => 'fa fa-binoculars'
        ],
        // Services
        [
            'key' => 'atm',
            'label' => 'ATM',
            'icon' => 'fa fa-credit-card'
        ],
        [
            'key' => 'bank',
            'label' => 'Bank',
            'icon' => 'fa fa-university'
        ],
        [
            'key' => 'post_office',
            'label' => 'Post Office',
            'icon' => 'fa fa-envelope'
        ],
        [
            'key' => 'police',
            'label' => 'Police Station',
            'icon' => 'fa fa-user-shield'
        ],
        [
            'key' => 'fire_station',
            'label' => 'Fire Station',
            'icon' => 'fa fa-fire-extinguisher'
        ],
        [
            'key' => 'city_hall',
            'label' => 'City Hall',
            'icon' => 'fa fa-city'
        ],
        [
            'key' => 'embassy',
            'label' => 'Embassy',
            'icon' => 'fa fa-flag'
        ],
        [
            'key' => 'lawyer',
            'label' => 'Lawyer',
            'icon' => 'fa fa-gavel'
        ],
        [
            'key' => 'real_estate_agency',
            'label' => 'Real Estate Agency',
            'icon' => 'fa fa-building'
        ],
        // Lodging
        [
            'key' => 'lodging',
            'label' => 'Hotel / Lodging',
            'icon' => 'fa fa-hotel'
        ],
        [
            'key' => 'rv_park',
            'label' => 'RV Park',
            'icon' => 'fa fa-caravan'
        ],
        // Auto Services
        [
            'key' => 'car_rental',
            'label' => 'Car Rental',
            'icon' => 'fa fa-car'
        ],
        [
            'key' => 'car_dealer',
            'label' => 'Car Dealer',
            'icon' => 'fa fa-car-side'
        ],
        [
            'key' => 'car_repair',
            'label' => 'Car Repair',
            'icon' => 'fa fa-tools'
        ],
        [
            'key' => 'gas_station',
            'label' => 'Gas Station',
            'icon' => 'fa fa-gas-pump'
        ],
        // Religion
        [
            'key' => 'church',
            'label' => 'Church',
            'icon' => 'fa fa-church'
        ],
        [
            'key' => 'hindu_temple',
            'label' => 'Hindu Temple',
            'icon' => 'fa fa-om'
        ],
        [
            'key' => 'mosque',
            'label' => 'Mosque',
            'icon' => 'fa fa-mosque'
        ],
        [
            'key' => 'synagogue',
            'label' => 'Synagogue',
            'icon' => 'fa fa-synagogue'
        ]
    ];
    return apply_filters('dna_get_amenity_types_list', $amenity_types);
}

/**
 * Check if the amenity type is valid
 * Check if the 'key' is in the amenity types list
 */
function dna_is_valid_amenity_type($type) {
    $amenity_types = dna_get_amenity_types_list();
    return in_array($type, array_column($amenity_types, 'key'));
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