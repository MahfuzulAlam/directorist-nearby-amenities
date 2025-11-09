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
        $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
        return;
      }
      
      var amenityData;
      try {
        amenityData = JSON.parse(amenityDataAttr);
      } catch (e) {
        console.error('Error parsing amenity data:', e);
        $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
        return;
      }
      
      if (!amenityData || !amenityData.listing_id) {
        $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
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
            $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
            
            // Hide error message if visible
            $container.find('.dna-error-message').removeClass('dna-error-message--active').addClass('dna-error-message--hidden');
            
            // Insert amenities HTML
            if (response.data.html) {
              $content.html(response.data.html).removeClass('dna-amenities-content--hidden').addClass('dna-amenities-content--visible');
            }
            
            // Insert icon colors if available
            if (response.data.icon_colors) {
              $('head').append(response.data.icon_colors);
            }
          } else {
            // Handle error
            $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
            var errorMessage = response.data && response.data.message ? response.data.message : 'Failed to load amenities';
            $container.find('.dna-error-message').html('<span class="dna-error-text">' + errorMessage + '</span>').removeClass('dna-error-message--hidden').addClass('dna-error-message--active');
          }
        },
        error: function(xhr, status, error) {
          // Handle error
          $loadingState.removeClass('dna-loading-state--active').addClass('dna-loading-state--hidden');
          $container.find('.dna-error-message').html('<span class="dna-error-text">Error loading amenities. Please try again later.</span>').removeClass('dna-error-message--hidden').addClass('dna-error-message--active');
          console.error('Ajax error:', error);
        }
      });
    });
  }

  // Load amenities when page is ready
  if ($('.directorist-single-info-nearby-amenities').length > 0) {
    loadNearbyAmenities();
  }

  // Handle click on amenity items to redirect to Google Maps
  $(document).on('click', '.dna-amenity-item[data-google-map-url]', function(e) {
    e.preventDefault();
    var mapUrl = $(this).attr('data-google-map-url');
    if (mapUrl) {
      window.open(mapUrl, '_blank');
    }
  });
});
