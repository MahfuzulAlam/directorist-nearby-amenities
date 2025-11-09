# Directorist - Nearby Amenities

A powerful WordPress plugin extension for Directorist that displays nearby amenities and places on single listing pages using Google Places API. Show users hospitals, restaurants, schools, and more with travel time calculations and direct links to Google Maps.

## Description

Directorist - Nearby Amenities enhances your Directorist listings by automatically displaying nearby places and amenities based on the listing's location. The plugin integrates with Google Places API to fetch real-time data about nearby establishments and calculates travel times using different transportation modes.

## Features

### Core Features

- **70+ Amenity Types**: Support for a wide range of place types including:
  - Essentials (Hospitals, Clinics, Pharmacies, Dentists, Veterinary Care)
  - Transportation (Bus Stops, Train Stations, Airports, Parking)
  - Education (Schools, Universities, Libraries)
  - Shopping (Malls, Stores, Supermarkets, Convenience Stores)
  - Food & Drinks (Restaurants, Cafes, Bars, Bakeries)
  - Lifestyle & Leisure (Gyms, Spas, Movie Theaters, Museums)
  - Services (ATMs, Banks, Post Offices, Police Stations)
  - And many more...

- **Two Display Modes**:
  - **Distance Mode**: Shows amenities with travel time calculations (walking, driving, cycling, transit)
  - **Amenities Mode**: Displays a simple list of nearby places without travel time

- **Travel Time Calculation**: Automatically calculates and displays travel time using Google Distance Matrix API with support for:
  - Walking
  - Driving
  - Cycling
  - Transit

- **Ajax Loading**: Amenities load asynchronously via Ajax to improve page load performance

- **Google Maps Integration**: Click on any amenity to view its location on Google Maps in a new tab

- **Customizable Settings**:
  - Custom titles for distance and amenities sections
  - Configurable search radius (in meters)
  - Maximum number of amenities per type
  - Enable/disable icon colors
  - Select transportation mode for travel time

- **Responsive Design**: Fully responsive layout that works on all devices

- **Translation Ready**: Fully internationalized with POT file included

- **Icon Support**: Each amenity type has a dedicated Font Awesome icon

- **Color Customization**: Optional color-coded icons for better visual distinction

## Requirements

- WordPress 5.2 or higher
- Directorist plugin (active)
- Google Maps API Key with the following APIs enabled:
  - Places API
  - Distance Matrix API
- PHP 7.4 or higher

## Installation

1. Upload the plugin files to `/wp-content/plugins/directorist-nearby-amenities/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure Directorist plugin is installed and activated
4. Configure your Google Maps API Key in Directorist settings
5. Add the "Nearby Amenities" widget to your single listing page layout

## Configuration

### Setting Up Google Maps API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - Places API
   - Distance Matrix API
4. Create credentials (API Key)
5. Add the API Key in Directorist Settings → General → Map Settings

### Adding the Widget

1. Go to Directorist → Settings → Single Listing → Content Builder
2. Add the "Nearby Amenities" widget to your desired section
3. Configure the widget options:
   - **Label**: Custom label for the widget
   - **Icon**: Choose an icon for the widget
   - **By Distance Title**: Title for the distance section
   - **By Distance Amenities**: Comma-separated list of amenity types to show with travel time (e.g., `hospital, doctor, pharmacy`)
   - **Nearby Amenities Title**: Title for the amenities section
   - **Nearby Amenities**: Comma-separated list of amenity types to show (e.g., `restaurant, cafe, supermarket`)
   - **Radius**: Search radius in meters (default: 500)
   - **Mode**: Transportation mode for travel time (walking, driving, cycling, transit)
   - **Max Amenities**: Maximum number of amenities to show per type (default: 3)
   - **Enable Icon Colors**: Toggle to enable color-coded icons

## Usage

### Basic Usage

Once configured, the plugin automatically displays nearby amenities on single listing pages. The amenities are loaded via Ajax after the page loads, ensuring optimal performance.

### Available Amenity Types

The plugin supports the following amenity types (use the key in configuration):

**Essentials**: `hospital`, `doctor`, `pharmacy`, `dentist`, `veterinary_care`

**Transport**: `bus_station`, `subway_station`, `train_station`, `transit_station`, `airport`, `taxi_stand`, `parking`

**Education**: `school`, `university`, `library`

**Shopping**: `shopping_mall`, `store`, `supermarket`, `convenience_store`, `clothing_store`, `electronics_store`, `furniture_store`, `hardware_store`, `home_goods_store`, `jewelry_store`, `pet_store`, `book_store`

**Food & Drinks**: `restaurant`, `cafe`, `bakery`, `bar`, `meal_takeaway`, `meal_delivery`

**Lifestyle & Leisure**: `gym`, `spa`, `stadium`, `movie_theater`, `museum`, `art_gallery`, `night_club`, `zoo`, `aquarium`, `casino`, `bowling_alley`

**Outdoor & Nature**: `park`, `campground`, `tourist_attraction`

**Services**: `atm`, `bank`, `post_office`, `police`, `fire_station`, `city_hall`, `embassy`, `lawyer`, `real_estate_agency`

**Lodging**: `lodging`, `rv_park`

**Auto Services**: `car_rental`, `car_dealer`, `car_repair`, `gas_station`

**Religion**: `church`, `hindu_temple`, `mosque`, `synagogue`

## Template Override

You can override the plugin templates by placing them in your theme directory:

### Theme Override Path

```
wp-content/themes/your-theme/directorist-custom-code/templates/single-listing.php
```

The plugin will automatically use the theme template if it exists, otherwise it will use the default plugin template.

## Filter Hooks

The plugin provides several filter hooks for developers to customize functionality:

### `dna_get_amenity_types_list`

Filter to modify the list of available amenity types.

**Parameters:**
- `$amenity_types` (array): Array of amenity type definitions

**Example:**
```php
add_filter('dna_get_amenity_types_list', function($amenity_types) {
    // Add custom amenity type
    $amenity_types[] = [
        'key' => 'custom_place',
        'label' => __('Custom Place', 'directorist-nearby-amenities'),
        'icon' => 'fa fa-star',
        'color' => '#FF0000'
    ];
    
    // Modify existing amenity
    foreach ($amenity_types as &$amenity) {
        if ($amenity['key'] === 'hospital') {
            $amenity['color'] = '#00FF00';
        }
    }
    
    return $amenity_types;
});
```

### `atbdp_single_listing_other_fields_widget`

Filter to modify the widget configuration in Directorist single listing builder.

**Parameters:**
- `$widgets` (array): Array of available widgets

**Example:**
```php
add_filter('atbdp_single_listing_other_fields_widget', function($widgets) {
    // Modify nearby_amenities widget options
    if (isset($widgets['nearby_amenities'])) {
        $widgets['nearby_amenities']['options']['max_amenities']['value'] = 5;
    }
    return $widgets;
});
```

### `directorist_single_item_template`

Filter to modify the template output for single listing items.

**Parameters:**
- `$template` (string): Template HTML output
- `$field_data` (array): Field data including widget configuration

**Example:**
```php
add_filter('directorist_single_item_template', function($template, $field_data) {
    if ($field_data['widget_name'] === 'nearby_amenities') {
        // Add custom wrapper or modify output
        $template = '<div class="custom-wrapper">' . $template . '</div>';
    }
    return $template;
}, 10, 2);
```

### `directorist_template`

Filter to modify Directorist template output.

**Parameters:**
- `$template` (mixed): Template output
- `$field_data` (array): Field data

**Example:**
```php
add_filter('directorist_template', function($template, $field_data) {
    // Customize template output
    return $template;
}, 10, 2);
```

## Action Hooks

### `wp_ajax_dna_load_nearby_amenities`

Ajax action hook for loading amenities (logged-in users).

**Usage:**
This hook is automatically registered. You can hook into it to modify the Ajax response or add custom logic.

**Example:**
```php
add_action('wp_ajax_dna_load_nearby_amenities', function() {
    // Custom logic before or after the default handler
}, 9); // Priority 9 to run before default handler
```

### `wp_ajax_nopriv_dna_load_nearby_amenities`

Ajax action hook for loading amenities (non-logged-in users).

**Usage:**
Same as above but for non-authenticated users.

## Functions

### `dna_generate_nearby_amenities($amenity_args)`

Generate HTML for nearby amenities.

**Parameters:**
- `$amenity_args` (array): Array of arguments including lat, lng, radius, distances, amenities, mode, etc.

**Returns:** (string) HTML output

### `dna_get_nearby_places($lat, $lng, $type, $apiKey, $radius = 1000)`

Fetch nearby places from Google Places API.

**Parameters:**
- `$lat` (float): Latitude
- `$lng` (float): Longitude
- `$type` (string): Place type
- `$apiKey` (string): Google Maps API key
- `$radius` (int): Search radius in meters

**Returns:** (array) Array of place results

### `dna_get_travel_time($lat, $lng, $destLat, $destLng, $mode, $apiKey)`

Calculate travel time between two points.

**Parameters:**
- `$lat` (float): Origin latitude
- `$lng` (float): Origin longitude
- `$destLat` (float): Destination latitude
- `$destLng` (float): Destination longitude
- `$mode` (string): Transportation mode
- `$apiKey` (string): Google Maps API key

**Returns:** (string|null) Travel time text or null

### `dna_get_google_map_url($place)`

Generate Google Maps URL for a place.

**Parameters:**
- `$place` (array): Place data from Google Places API

**Returns:** (string) Google Maps URL

### `dna_get_amenity_types_list()`

Get list of all available amenity types.

**Returns:** (array) Array of amenity type definitions

### `dna_get_amenity_by_key($key)`

Get amenity type definition by key.

**Parameters:**
- `$key` (string): Amenity type key

**Returns:** (array|false) Amenity definition or false if not found

## Styling

The plugin includes comprehensive CSS classes for easy customization:

- `.dna-nearby-amenities-wrapper` - Main wrapper
- `.dna-amenities-container` - Container for content
- `.dna-loading-state` - Loading state container
- `.dna-amenities-content` - Content container
- `.dna-nearby-widget` - Widget container
- `.dna-section` - Section container
- `.dna-distance-item` - Distance item
- `.dna-amenity-item` - Amenity item
- `.dna-amenity-icon` - Icon wrapper
- `.dna-amenity-text` - Text wrapper

All styles can be overridden in your theme's stylesheet.

## JavaScript

The plugin includes JavaScript for Ajax loading and Google Maps integration. The script is localized with the following data:

- `dnaAmenities.ajaxUrl` - Ajax URL
- `dnaAmenities.nonce` - Security nonce

## Translation

The plugin is fully translation-ready. Translation files are located in:

```
wp-content/plugins/directorist-nearby-amenities/languages/
```

To translate:
1. Copy `directorist-nearby-amenities.pot` to your language
2. Translate using Poedit or similar tool
3. Save as `directorist-nearby-amenities-{locale}.po` and `.mo`
4. Place in the `languages` directory

## Changelog

### 3.0.0
- Initial release with Ajax loading
- 70+ amenity types support
- Travel time calculation
- Google Maps integration
- Responsive design
- Translation support

## Support

For support, feature requests, or bug reports, please visit:
- Plugin URI: https://wpxplore.com/tools/directorist-nearby-amenities
- Author URI: https://wpxplore.com

## License

GPL v2 or later

## Credits

Developed by wpXplore for the Directorist community.
