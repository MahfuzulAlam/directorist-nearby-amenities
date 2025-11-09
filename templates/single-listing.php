<?php
/**
 * @author  wpWax
 * @since   6.7
 * @version 7.3.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lat = get_post_meta( $data['listing_id'], '_manual_lat', true );
$lng = get_post_meta( $data['listing_id'], '_manual_lng', true );

$by_distance_title = isset($data['by_distance_title']) ? $data['by_distance_title'] : 'Distance';
$nearby_amenities_title = isset($data['nearby_amenities_title']) ? $data['nearby_amenities_title'] : 'Nearby Amenities';
$by_distance_amenities = isset($data['by_distance_amenities']) ? $data['by_distance_amenities'] : '';
$nearby_amenities = isset($data['nearby_amenities_amenities']) ? $data['nearby_amenities_amenities'] : '';
$nearby_amenities_radius = isset($data['nearby_amenities_radius']) ? $data['nearby_amenities_radius'] : 500;
$nearby_amenities_mode = isset($data['nearby_amenities_mode']) ? $data['nearby_amenities_mode'] : 'driving';
$max_amenities = isset($data['max_amenities']) ? $data['max_amenities'] : 3;

$amenity_args = [
    'lat' => $lat,
    'lng' => $lng,
    'radius' => $nearby_amenities_radius,
    'distances' => $by_distance_amenities,
    'amenities' => $nearby_amenities,
    'mode' => $nearby_amenities_mode,
    'by_distance_title' => $by_distance_title,
    'nearby_amenities_title' => $nearby_amenities_title,
    'max_amenities' => $max_amenities,
];
?>

<div class="directorist-single-info directorist-single-info-nearby-amenities">
    <div class="directorist-single-info__value">
        <?php echo dna_generate_nearby_amenities( $amenity_args ); ?>
    </div>
</div>

<?php
    if (isset($data['amenity_icon_colors']) && $data['amenity_icon_colors'] == true) {
        $amenity_types = dna_get_amenity_types_list();
        if (!empty($amenity_types)) {
            echo '<style>';
            foreach ($amenity_types as $amenity) {
                ?>
                    .dna-amenity-item.<?php echo $amenity['key']; ?> .directorist-icon-mask::after {
                        background-color: <?php echo $amenity['color']; ?> !important;
                    }
                <?php
            }
            echo '</style>';
        }
    }