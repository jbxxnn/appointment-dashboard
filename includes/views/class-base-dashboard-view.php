<?php
if (!defined('ABSPATH')) {
    exit;
}

abstract class Base_Dashboard_View {
    protected $user_id;
    protected $appointments;

    public function __construct() {
        $this->user_id = get_current_user_id();
    }

    /**
     * Initialize the dashboard
     */
    public function init() {
        if (!$this->check_permissions()) {
            return '';
        }

        $this->load_appointments();
        return $this->render();
    }

    /**
     * Check if user has required permissions
     */
    abstract protected function check_permissions();

    /**
     * Load appointments for the view
     */
    abstract protected function load_appointments();

    /**
     * Render the dashboard
     */
    abstract protected function render();

    /**
     * Get common table headers
     */
    protected function get_table_headers() {
        return array(
            'appointment-id' => esc_html__('Appointment ID', 'woocommerce-appointments'),
            'when' => esc_html__('When', 'woocommerce-appointments'),
            'type' => esc_html__('Type Echo', 'woocommerce-appointments'),
            'status' => esc_html__('Status', 'woocommerce-appointments')
        );
    }
} 