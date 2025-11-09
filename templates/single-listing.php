<?php
/**
 * @author  wpWax
 * @since   6.7
 * @version 7.3.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lat = get_post_meta( $data['listing_id'], '_manual_lat', true );
$lng = get_post_meta( $data['listing_id'], '_manual_lng', true );

$by_distance_title = isset($data['by_distance_title']) ? $data['by_distance_title'] : __('Distance', 'directorist-nearby-amenities');
$nearby_amenities_title = isset($data['nearby_amenities_title']) ? $data['nearby_amenities_title'] : __('Nearby Amenities', 'directorist-nearby-amenities');
$by_distance_amenities = isset($data['by_distance_amenities']) ? $data['by_distance_amenities'] : '';
$nearby_amenities = isset($data['nearby_amenities_amenities']) ? $data['nearby_amenities_amenities'] : '';
$nearby_amenities_radius = isset($data['nearby_amenities_radius']) ? $data['nearby_amenities_radius'] : 500;
$nearby_amenities_mode = isset($data['nearby_amenities_mode']) ? $data['nearby_amenities_mode'] : 'driving';
$max_amenities = isset($data['max_amenities']) ? $data['max_amenities'] : 3;
$amenity_icon_colors = isset($data['amenity_icon_colors']) ? $data['amenity_icon_colors'] : false;

// Prepare data attributes for Ajax
$amenity_data = [
    'listing_id' => $data['listing_id'],
    'lat' => $lat,
    'lng' => $lng,
    'radius' => $nearby_amenities_radius,
    'distances' => $by_distance_amenities,
    'amenities' => $nearby_amenities,
    'mode' => $nearby_amenities_mode,
    'by_distance_title' => $by_distance_title,
    'nearby_amenities_title' => $nearby_amenities_title,
    'max_amenities' => $max_amenities,
    'amenity_icon_colors' => $amenity_icon_colors ? 'true' : 'false',
];
?>

<div class="directorist-single-info directorist-single-info-nearby-amenities dna-nearby-amenities-wrapper" 
     data-amenity-args='<?php echo esc_attr(json_encode($amenity_data)); ?>'>
    <div class="directorist-single-info__value dna-amenities-container">
        <div class="dna-loading-state dna-loading-state--active">
            <span class="dna-spinner"></span>
            <span class="dna-loading-text"><?php esc_html_e('Loading nearby amenities...', 'directorist-nearby-amenities'); ?></span>
        </div>
        <div class="dna-amenities-content dna-amenities-content--hidden"></div>
        <div class="dna-error-message dna-error-message--hidden"></div>
    </div>
</div>