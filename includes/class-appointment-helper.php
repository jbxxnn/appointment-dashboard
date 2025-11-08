<?php
/**
 * Prevent direct access to this file
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper class for managing appointments
 * 
 * This class provides utility methods for fetching and managing appointments
 * for both midwives and clients.
 */
class Appointment_Helper {
    /**
     * Get appointments for a user based on their role and status
     * 
     * For midwives (vkpraktijk), returns all appointments assigned to their practice
     * For clients, returns only their own appointments
     * 
     * @param int    $user_id The ID of the user
     * @param string $status  The appointment status to filter by (default: 'processing')
     * @param string $role    The user role to check for ('vkpraktijk' for midwives, empty for clients)
     * @return array Array of WC_Order objects containing appointments
     */
    public static function get_user_appointments($user_id, $status = 'processing', $role = '') {
        if ($role === 'vkpraktijk') {
            // For midwives, get their practice ID
            $user = get_user_by('id', $user_id);
            $current_user_midwife = get_user_meta($user_id, 'user_registration_select_1644061762', true);
            
            // Get all orders assigned to this practice
            $args = array(
                'status' => $status,
                'meta_key' => '_billing_midwife',
                'meta_value' => $current_user_midwife,
                'compare' => '='
            );
        } else {
            // For clients, get only their orders
            $args = array(
                'status' => $status,
                'customer' => $user_id
            );
        }
        
        return wc_get_orders($args);
    }

    /**
     * Get detailed appointment information from an order
     */
    public static function get_appointment_details($order) {
        $details = array();
        $appointments_ids = WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order->get_id());

        foreach ($appointments_ids as $appointment_id) {
            $appointment = get_wc_appointment($appointment_id);
            if (!$appointment) {
                continue;
            }

            // Check if the appointment can be cancelled or rescheduled
            $can_cancel = !$appointment->passed_cancel_day() && 
                         'cancelled' !== $appointment->get_status() && 
                         'completed' !== $appointment->get_status();
                         
            $can_reschedule = !$appointment->passed_reschedule_day() && 
                             'cancelled' !== $appointment->get_status() && 
                             'completed' !== $appointment->get_status();

            $details[] = array(
                'id' => $appointment->get_id(),
                'start_date' => $appointment->get_start_date(),
                'duration' => $appointment->get_duration(),
                'status' => $appointment->get_status(),
                'product_name' => $appointment->get_product() && $appointment->get_product()->is_type('appointment') 
                    ? $appointment->get_product_name() 
                    : '',
                'cancel_url' => $appointment->get_cancel_url(),
                'reschedule_url' => $appointment->get_reschedule_url(),
                'order_url' => $appointment->get_order() ? $appointment->get_order()->get_view_order_url() : '',
                'staff' => $appointment->get_staff_members(true),
                'passed_cancel_day' => $appointment->passed_cancel_day(),
                'passed_reschedule_day' => $appointment->passed_reschedule_day(),
                'can_cancel' => $can_cancel,
                'can_reschedule' => $can_reschedule
            );
        }

        return $details;
    }

    /**
     * Verify if a user has permission to view/manage appointments
     * 
     * @param int    $user_id       The ID of the user to check
     * @param string $required_role The role to check for ('vkpraktijk' for midwives, empty for clients)
     * @return bool True if user has permission, false otherwise
     */
    public static function verify_user_permission($user_id, $required_role = '') {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        // Check if user has the required role
        return $required_role === 'vkpraktijk' 
            ? in_array('vkpraktijk', (array) $user->roles)
            : !in_array('vkpraktijk', (array) $user->roles);
    }
} 