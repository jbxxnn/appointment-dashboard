<?php
/**
 * Plugin Name: Appointments Dashboard
 * Description: Custom appointments dashboard for midwives and clients
 * Version: 1.0.0
 * Author: Jbxxnn
 * Text Domain: woocommerce-appointments
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('APPT_DASHBOARD_VERSION', '1.0.0');
define('APPT_DASHBOARD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APPT_DASHBOARD_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    // Convert class name to file path
    $file = str_replace('_', '-', strtolower($class));
    
    // Check if it's a view class
    if (strpos($class, '_View') !== false) {
        $path = APPT_DASHBOARD_PLUGIN_DIR . 'includes/views/class-' . $file . '.php';
    } else {
        $path = APPT_DASHBOARD_PLUGIN_DIR . 'includes/class-' . $file . '.php';
    }

    // If the file exists, require it
    if (file_exists($path)) {
        require_once $path;
    }
});

// Initialize plugin
function init_appointments_dashboard() {
    if (class_exists('Appointments_Dashboard')) {
        return Appointments_Dashboard::get_instance();
    }
    
    // Log error if class is not found
    error_log('Appointments Dashboard class not found');
    return null;
}

add_action('plugins_loaded', 'init_appointments_dashboard'); 