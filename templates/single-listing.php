<?php
/**
 * @author  wpWax
 * @since   6.7
 * @version 7.3.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$lat = get_post_meta( $data['listing_id'], '_manual_lat', true );
$lng = get_post_meta( $data['listing_id'], '_manual_lng', true );
?>

<div class="directorist-single-info directorist-single-info-nearby-amenities">
    <div class="directorist-single-info__value">
        <?php echo dna_generate_nearby_amenities(
            ['lat'=>$lat, 'lng'=>$lng],
            ['park', 'metro', 'bus_stop', 'hospital', 'mosque'],
            ['swimming_pool', 'school', 'restaurant', 'mosque']
            ); 
        ?>
    </div>
</div>