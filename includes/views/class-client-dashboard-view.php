<?php
if (!defined('ABSPATH')) {
    exit;
}

class Client_Dashboard_View extends Base_Dashboard_View {
    private $status;

    public function __construct($status = 'processing') {
        parent::__construct();
        $this->status = $status;
    }

    protected function check_permissions() {
        return Appointment_Helper::verify_user_permission($this->user_id, '');
    }

    protected function load_appointments() {
        $this->appointments = Appointment_Helper::get_user_appointments(
            $this->user_id,
            $this->status
        );
    }

    protected function get_table_headers() {
        $headers = parent::get_table_headers();
        
        // Add actions column for client view
        if ($this->status === 'processing') {
            $headers['actions'] = esc_html__('Actie', 'woocommerce-appointments');
        }
        
        return $headers;
    }

    protected function render() {
        ob_start();
        ?>
        <?php if (!empty($this->appointments)) : ?>
            <br>
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
                    ?>
                        <tr>
                            <td class="appointment-id anowrap appt-table-body">
                                #<?php echo esc_html($appointment['id']); ?>
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
                            <?php if ($this->status === 'processing') : ?>
                                <td class="actions anowrap appt-table-body">
                                    <?php if ($appointment['can_cancel']) : ?>
                                        <a href="<?php echo esc_url($appointment['cancel_url']); ?>" 
                                           class="woocommerce-button button anowrap appt-cancel">
                                            <?php esc_html_e('Cancel', 'woocommerce-appointments'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($appointment['can_reschedule']) : ?>
                                        <a href="<?php echo esc_url($appointment['reschedule_url']); ?>" 
                                           class="woocommerce-button button anowrap appt-reschedule">
                                            <?php esc_html_e('Reschedule', 'woocommerce-appointments'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        endforeach;
                    endforeach; 
                    ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
                <a class="woocommerce-Button button new-appt-button" href="<?php echo esc_url('/afspraak/'); ?>">
                    <?php esc_html_e('Maak Afspraak', 'woocommerce-appointments'); ?>
                </a>
                <p class="no-appointment-notice">
                    <?php esc_html_e('Je hebt nog geen afspraken gemaakt.', 'woocommerce-appointments'); ?>
                </p>
            </div>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }
} 