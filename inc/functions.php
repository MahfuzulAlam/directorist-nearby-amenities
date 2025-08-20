<?php

/**
 * Add your custom php code here
 */


function dna_generate_nearby_amenities($location = ['lat' => 0, 'lng' => 0], $distances = [], $amenities = []) {
    $lat = $location['lat'];
    $lng = $location['lng'];
    $apiKey = "AIzaSyBJUNxPs3yobDNC4HCNDQJ7QeeZ8d-4e_M";

    $places_map_new = [
    // Essentials
    'hospital'          => 'Hospital',
    'doctor'            => 'Doctor / Clinic',
    'pharmacy'          => 'Pharmacy',
    'dentist'           => 'Dentist',
    'veterinary_care'   => 'Veterinary Care',

    // Transport
    'bus_station'       => 'Bus Stop',
    'subway_station'    => 'Metro / Subway Station',
    'train_station'     => 'Train Station',
    'transit_station'   => 'Transit Station',
    'airport'           => 'Airport',
    'taxi_stand'        => 'Taxi Stand',
    'parking'           => 'Parking',

    // Education
    'school'            => 'School',
    'university'        => 'University',
    'library'           => 'Library',

    // Shopping
    'shopping_mall'     => 'Shopping Mall',
    'store'             => 'Store',
    'supermarket'       => 'Supermarket',
    'convenience_store' => 'Convenience Store',
    'clothing_store'    => 'Clothing Store',
    'electronics_store' => 'Electronics Store',
    'furniture_store'   => 'Furniture Store',
    'hardware_store'    => 'Hardware Store',
    'home_goods_store'  => 'Home Goods Store',
    'jewelry_store'     => 'Jewelry Store',
    'pet_store'         => 'Pet Store',
    'book_store'        => 'Bookstore',

    // Food & Drinks
    'restaurant'        => 'Restaurant',
    'cafe'              => 'Cafe',
    'bakery'            => 'Bakery',
    'bar'               => 'Bar',
    'meal_takeaway'     => 'Takeaway',
    'meal_delivery'     => 'Food Delivery',

    // Lifestyle & Leisure
    'gym'               => 'Fitness Center / Gym',
    'spa'               => 'Spa / Swimming Pool',
    'stadium'           => 'Stadium',
    'movie_theater'     => 'Movie Theater',
    'museum'            => 'Museum',
    'art_gallery'       => 'Art Gallery',
    'night_club'        => 'Night Club',
    'zoo'               => 'Zoo',
    'aquarium'          => 'Aquarium',
    'casino'            => 'Casino',
    'bowling_alley'     => 'Bowling Alley',

    // Outdoor & Nature
    'park'              => 'Park',
    'campground'        => 'Campground',
    'tourist_attraction'=> 'Tourist Attraction',

    // Services
    'atm'               => 'ATM',
    'bank'              => 'Bank',
    'post_office'       => 'Post Office',
    'police'            => 'Police Station',
    'fire_station'      => 'Fire Station',
    'city_hall'         => 'City Hall',
    'embassy'           => 'Embassy',
    'lawyer'            => 'Lawyer',
    'real_estate_agency'=> 'Real Estate Agency',

    // Lodging
    'lodging'           => 'Hotel / Lodging',
    'campground'        => 'Campground',
    'rv_park'           => 'RV Park',

    // Auto Services
    'car_rental'        => 'Car Rental',
    'car_dealer'        => 'Car Dealer',
    'car_repair'        => 'Car Repair',
    'gas_station'       => 'Gas Station',
    'parking'           => 'Parking Lot',

    // Religion
    'church'            => 'Church',
    'hindu_temple'      => 'Hindu Temple',
    'mosque'            => 'Mosque',
    'synagogue'         => 'Synagogue',
    ];


    // Define place types for Google API
    $places_map = [
        'hospital'       => 'hospital',
        'bus_stop'       => 'bus_station',
        'metro'          => 'subway_station',
        'school'         => 'school',
        'restaurant'     => 'restaurant',
        'fitness_center' => 'gym',
        'park'           => 'park',
        'mosque'        => 'mosque',
        'swimming_pool'  => 'spa' // Google doesn't always return "swimming_pool" directly
    ];

    // Function to fetch places from Google Places API
    function dna_get_nearby_places($lat, $lng, $type, $apiKey) {
        $radius = 2000; // 2km radius
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lat},{$lng}&radius={$radius}&type={$type}&key={$apiKey}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        return $data['results'] ?? [];
    }

    // Function to calculate travel time with Distance Matrix
    function dna_get_travel_time($lat, $lng, $destLat, $destLng, $apiKey) {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$lat},{$lng}&destinations={$destLat},{$destLng}&mode=walking&key={$apiKey}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        return $data['rows'][0]['elements'][0]['duration']['text'] ?? null;
    }

    ob_start();
    ?>
    <div class="nearby-widget">
        <h4 class="amenities-title">Distance</h4>
        <div class="distances">
            <?php foreach ($distances as $type): ?>
                <?php if(isset($places_map[$type])): ?>
                    <?php
                        $places = dna_get_nearby_places($lat, $lng, $places_map[$type], $apiKey);
                        if (!empty($places)) {
                            $place = $places[0]; // Closest
                            $time = dna_get_travel_time($lat, $lng, $place['geometry']['location']['lat'], $place['geometry']['location']['lng'], $apiKey);
                        }
                    ?>
                    <?php if(!empty($place)): ?>
                        <div class="distance-item">
                            <span class="icon">📍</span>
                            <span><?php echo $time ? $time . " walk to " . $place['name'] : "Nearby " . ucfirst($type); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <h4 class="amenities-title">Nearby Amenities</h4>
        <div class="amenities">
            <?php foreach ($amenities as $type): ?>
                <?php if(isset($places_map[$type])): ?>
                    <?php
                        $places = dna_get_nearby_places($lat, $lng, $places_map[$type], $apiKey);
                        if (!empty($places)) {
                            $place = $places[0];
                        }
                    ?>
                    <?php if(!empty($place)): ?>
                        <div class="amenity-item">
                            <span class="icon">✔️</span>
                            <span><?php echo $place['name']; ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
