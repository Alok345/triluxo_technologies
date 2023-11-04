<?php
/*
Plugin Name: Weather Display
Description: Display the weather of the user's location and a specified location.
Version: 1.0
Author: Your Name
*/

// Include the CSS to center the output
function weather_display_enqueue_styles() {
    wp_enqueue_style('weather-display-styles', plugins_url('css/weather-display.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'weather_display_enqueue_styles');

// Weather API logic
function get_weather_data($location) {
    $api_key = '0468b2922060cf618b421c10e1a2fe1b'; // Replace with your OpenWeatherMap API key
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$location&appid=$api_key";
    
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return "Error fetching weather data.";
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    return $data;
}

// Shortcode to display weather for the user's location and a specified location
function display_weather_locations_shortcode($atts) {
    $user_location = '';
    $specified_location = isset($atts['location']) ? $atts['location'] : 'Delhi';

    if (isset($_COOKIE['user_location'])) {
        $user_location = $_COOKIE['user_location'];
    }

    $user_location_script = '';

    // Inside your geolocation script
if (empty($user_location)) {
    $user_location_script = "
        <script>
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var user_location = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };
                    document.cookie = 'user_location=' + JSON.stringify(user_location);
                    location.reload();
                }, function(error) {
                    console.error('Geolocation error:', error);
                    alert('Geolocation failed. Using default location.');
                    var default_location = 'Delhi';
                    document.cookie = 'user_location=' + JSON.stringify(default_location);
                    location.reload();
                });
            } else {
                console.error('Geolocation not supported.');
                alert('Geolocation not supported. Using default location.');
                var default_location = 'Delhi';
                document.cookie = 'user_location=' + JSON.stringify(default_location);
                location.reload();
            }
        </script>
    ";
}

    

    $user_weather_data = get_weather_data(json_decode($user_location, true));
    $specified_weather_data = get_weather_data($specified_location);

    $output = '';

    if (isset($user_weather_data['weather'][0]['description'])) {
        $user_weather = $user_weather_data['weather'][0]['description'];
        $user_temperature = round($user_weather_data['main']['temp'] - 273.15, 1); // Convert Kelvin to Celsius

        $output .= "Your Location: " . json_encode($user_location) . "<br>";
        $output .= "Weather: $user_weather<br>";
        $output .= "Temperature: $user_temperature\°C<br><br>";
    } else {
        $output .= "Weather data not available for your location.<br><br>";
    }

    if (isset($specified_weather_data['weather'][0]['description'])) {
        $specified_weather = $specified_weather_data['weather'][0]['description'];
        $specified_temperature = round($specified_weather_data['main']['temp'] - 273.15, 1); // Convert Kelvin to Celsius

        $output .= "Specified Location: $specified_location<br>";
        $output .= "Weather: $specified_weather<br>";
        $output .= "Temperature: $specified_temperature °C";
    } else {
        $output .= "Weather data not available for the specified location.";
    }

    return '<div class="weather-display">' . $output . '</div>' . $user_location_script;
}
add_shortcode('weather_display', 'display_weather_locations_shortcode');

// Shortcode to display weather for the user's location

