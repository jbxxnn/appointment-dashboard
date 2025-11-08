<?php
/**
 * View class for the midwife's appointment dashboard
 */
if (!defined('ABSPATH')) {
    exit;
}

class Midwife_Dashboard_View extends Base_Dashboard_View {
    private $status;
    private $current_user_orders = array();

    public function __construct($status = 'processing') {
        parent::__construct();
        $this->status = $status;
    }

    protected function check_permissions() {
        return Appointment_Helper::verify_user_permission($this->user_id, 'vkpraktijk');
    }

    protected function load_appointments() {
        $this->appointments = Appointment_Helper::get_user_appointments(
            $this->user_id, 
            $this->status, 
            'vkpraktijk'
        );

        // Get orders made by current user for filtering
        $current_user_orders = wc_get_orders(array(
            'customer' => $this->user_id
        ));

        foreach ($current_user_orders as $order) {
            $this->current_user_orders[] = $order->get_id();
        }
    }

    protected function get_table_headers() {
        // Get base headers but don't merge yet
        $base_headers = parent::get_table_headers();
        
        // Create headers in correct order
        return array(
            'appointment-id' => esc_html__('Appointment ID', 'woocommerce-appointments'),
            'client-name' => esc_html__('Client Name', 'woocommerce-appointments'),
            'when' => $base_headers['when'],
            'type' => $base_headers['type'],
            'status' => $base_headers['status'],
            'location' => esc_html__('Locatie', 'woocommerce-appointments'),
            'actions' => esc_html__('Actie', 'woocommerce-appointments')
        );
    }

    protected function render() {
        ob_start();
        ?>
        <?php if (!empty($this->appointments)) : ?>
            <br>
            <input type="text" 
                   class="appointment-search" 
                   id="myInputText" 
                   onkeyup="mysearchFunction()" 
                   placeholder="<?php esc_attr_e('Zoek afspraak op naam cliënt...', 'woocommerce-appointments'); ?>" 
                   title="<?php esc_attr_e('Search Appointment...', 'woocommerce-appointments'); ?>">
            
            <select class="appointment-search-select" 
                    id="myInputSelect" 
                    name="filter" 
                    onclick="myselectFunction()">
                <option class="appt-value" value=""><?php esc_html_e('Filter Afspraken Op', 'woocommerce-appointments'); ?></option>
                <option value=""><?php esc_html_e('Alle Afspraken', 'woocommerce-appointments'); ?></option>
                <option value="my-appt"><?php esc_html_e('Afspraken Team Account', 'woocommerce-appointments'); ?></option>
            </select>

            <table id="myTable" class="shop_table shop_table_responsive my_account_orders my_account_appointments">
                <thead>
                    <tr>
                        <?php foreach ($this->get_table_headers() as $key => $label) : ?>
                            <th scope="col" class="<?php echo esc_attr($key); ?> appt-table-head">
                                <span class="nobr"><?php echo esc_html($label); ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->appointments as $order) : 
                        $appointment_details = Appointment_Helper::get_appointment_details($order);
                        foreach ($appointment_details as $appointment) :
                            $is_my_appointment = in_array($order->get_id(), $this->current_user_orders);
                    ?>
                        <tr>
                            <td class="appointment-id anowrap appt-table-body">
                                #<?php echo esc_html($appointment['id']); ?>
                                <?php if ($appointment['order_url']) : ?>
                                    <span class="adesc">
                                        <a href="<?php echo esc_url($appointment['order_url']); ?>" class="adesc">
                                            <?php esc_html_e('View Order', 'woocommerce-appointments'); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="client-name anowrap appt-table-body">
                                <?php echo esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()); ?>
                                <?php if ($is_my_appointment) : ?>
                                    <span class="my-appt"><?php esc_html_e('my-appt', 'woocommerce-appointments'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="when anowrap appt-table-body">
                                <?php echo esc_html($appointment['start_date']); ?>
                                <span class="adesc"><?php echo esc_html($appointment['duration']); ?></span>
                            </td>
                            <td class="type anowrap appt-table-body">
                                <span class="adesc"><?php echo esc_html($appointment['product_name']); ?></span>
                            </td>
                            <td class="status anowrap appt-table-body">
                                <?php echo esc_html(wc_appointments_get_status_label($appointment['status'])); ?>
                            </td>
                            <td class="location anowrap appt-table-body">
                                <?php echo esc_html($appointment['staff']); ?>
                            </td>
                            <td class="actions anowrap appt-table-body">
                                <?php if ($is_my_appointment && 'cancelled' !== $appointment['status'] && 'completed' !== $appointment['status']) : ?>
                                    <?php if (!$appointment['passed_cancel_day']) : ?>
                                        <a href="<?php echo esc_url($appointment['cancel_url']); ?>" 
                                           class="woocommerce-button button anowrap appt-cancel">
                                            <?php esc_html_e('Cancel', 'woocommerce-appointments'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$appointment['passed_reschedule_day']) : ?>
                                        <a href="<?php echo esc_url($appointment['reschedule_url']); ?>" 
                                           class="woocommerce-button button anowrap appt-reschedule">
                                            <?php esc_html_e('Reschedule', 'woocommerce-appointments'); ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
                <a class="woocommerce-Button button new-appt-button" href="<?php echo esc_url('/appointments/'); ?>">
                    <?php esc_html_e('Book An Appointment', 'woocommerce-appointments'); ?>
                </a>
                <p class="no-appointment-notice">
                    <?php esc_html_e('You Have No Appointments.', 'woocommerce-appointments'); ?>
                </p>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }
} 