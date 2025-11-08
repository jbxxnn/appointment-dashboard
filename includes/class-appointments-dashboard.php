<?php
/**
 * Main plugin class for handling appointments dashboard functionality
 */
if (!defined('ABSPATH')) {
    exit;
}

class Appointments_Dashboard {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function register_shortcodes() {
        add_shortcode('appointment_dashboard', array($this, 'render_appointment_dashboard'));
        add_shortcode('past_appointment_dashboard', array($this, 'render_past_appointment_dashboard'));
    }

    public function render_appointment_dashboard() {
        if (!class_exists('Appointment_Helper')) {
            return '';
        }

        if (Appointment_Helper::verify_user_permission(get_current_user_id(), 'vkpraktijk')) {
            $dashboard = new Midwife_Dashboard_View('processing');
        } else {
            $dashboard = new Client_Dashboard_View('processing');
        }
        return $dashboard->init();
    }

    public function render_past_appointment_dashboard() {
        if (!class_exists('Appointment_Helper')) {
            return '';
        }

        if (Appointment_Helper::verify_user_permission(get_current_user_id(), 'vkpraktijk')) {
            $dashboard = new Midwife_Dashboard_View('completed');
        } else {
            $dashboard = new Client_Dashboard_View('completed');
        }
        return $dashboard->init();
    }

    public function enqueue_scripts() {
        if (!$this->should_enqueue_assets()) {
            return;
        }

        wp_enqueue_style(
            'appointments-dashboard', 
            APPT_DASHBOARD_PLUGIN_URL . 'assets/css/appointments-dashboard.css',
            array(),
            APPT_DASHBOARD_VERSION
        );

        wp_enqueue_script(
            'appointments-dashboard',
            APPT_DASHBOARD_PLUGIN_URL . 'assets/js/appointments-dashboard.js',
            array('jquery'),
            APPT_DASHBOARD_VERSION,
            true
        );
    }

    private function should_enqueue_assets() {
        if (is_admin()) {
            return false;
        }

        global $post;

        if ($post instanceof \WP_Post) {
            if (has_shortcode($post->post_content, 'appointment_dashboard') ||
                has_shortcode($post->post_content, 'past_appointment_dashboard')) {
                return true;
            }
        }

        /**
         * Allow third-parties to opt-in to enqueueing the dashboard assets manually.
         */
        return apply_filters('appointments_dashboard_enqueue_assets', false);
    }
} 