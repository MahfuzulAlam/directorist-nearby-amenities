/**
 *   Add your custom JS here
 * */

jQuery(document).ready(function ($) {
  // Load nearby amenities via Ajax on single listing page
  function loadNearbyAmenities() {
    $('.directorist-single-info-nearby-amenities').each(function() {
      var $container = $(this);
      var $loadingState = $container.find('.dna-loading-state');
      var $content = $container.find('.dna-amenities-content');
      
      // Get data attribute and parse JSON
      var amenityDataAttr = $container.attr('data-amenity-args');
      if (!amenityDataAttr) {
        $loadingState.hide();
        return;
      }
      
      var amenityData;
      try {
        amenityData = JSON.parse(amenityDataAttr);
      } catch (e) {
        console.error('Error parsing amenity data:', e);
        $loadingState.hide();
        return;
      }
      
      if (!amenityData || !amenityData.listing_id) {
        $loadingState.hide();
        return;
      }

      // Make Ajax request
      $.ajax({
        url: dnaAmenities.ajaxUrl,
        type: 'POST',
        data: {
          action: 'dna_load_nearby_amenities',
          nonce: dnaAmenities.nonce,
          listing_id: amenityData.listing_id,
          lat: amenityData.lat,
          lng: amenityData.lng,
          radius: amenityData.radius,
          distances: amenityData.distances,
          amenities: amenityData.amenities,
          mode: amenityData.mode,
          by_distance_title: amenityData.by_distance_title,
          nearby_amenities_title: amenityData.nearby_amenities_title,
          max_amenities: amenityData.max_amenities,
          amenity_icon_colors: amenityData.amenity_icon_colors
        },
        success: function(response) {
          if (response.success && response.data) {
            // Hide loading state
            $loadingState.hide();
            
            // Insert amenities HTML
            if (response.data.html) {
              $content.html(response.data.html).show();
            }
            
            // Insert icon colors if available
            if (response.data.icon_colors) {
              $('head').append(response.data.icon_colors);
            }
          } else {
            // Handle error
            $loadingState.html('<span style="color: #dc3545;">' + (response.data && response.data.message ? response.data.message : 'Failed to load amenities') + '</span>');
          }
        },
        error: function(xhr, status, error) {
          // Handle error
          $loadingState.html('<span style="color: #dc3545;">Error loading amenities. Please try again later.</span>');
          console.error('Ajax error:', error);
        }
      });
    });
  }

  // Load amenities when page is ready
  if ($('.directorist-single-info-nearby-amenities').length > 0) {
    loadNearbyAmenities();
  }
});
