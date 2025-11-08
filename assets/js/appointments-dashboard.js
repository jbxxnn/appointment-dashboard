(function($) {
    'use strict';

    // Search functionality
    function mysearchFunction() {
        var input = document.getElementById("myInputText");
        var filter = input.value.toLowerCase();
        var table = document.getElementById("myTable");
        var tr = table.getElementsByTagName("tr");

        for (var i = 0; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName("td");
            var found = false;
            
            for (var j = 0; j < td.length; j++) {
                if (td[j]) {
                    var txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            if (found) {
                tr[i].style.display = "";
            } else {
                if (i === 0) continue; // Skip header row
                tr[i].style.display = "none";
            }
        }
    }

    // Filter functionality
    function myselectFunction() {
        var select = document.getElementById("myInputSelect");
        var filter = select.value;
        var table = document.getElementById("myTable");
        var tr = table.getElementsByTagName("tr");

        for (var i = 0; i < tr.length; i++) {
            var td = tr[i].getElementsByClassName("my-appt");
            
            if (filter === "my-appt") {
                if (td.length > 0) {
                    tr[i].style.display = "";
                } else {
                    if (i === 0) continue; // Skip header row
                    tr[i].style.display = "none";
                }
            } else {
                tr[i].style.display = "";
            }
        }
    }

    // Make functions globally available
    window.mysearchFunction = mysearchFunction;
    window.myselectFunction = myselectFunction;

    // Initialize tooltips and other UI elements
    $(document).ready(function() {
        // Add loading state to buttons
        $('.my_account_appointments').on('click', '.woocommerce-button', function() {
            $(this).addClass('loading').prop('disabled', true);
        });

        // Make tables responsive
        $('.shop_table').each(function() {
            var $table = $(this);
            var $tbody = $table.find('tbody');
            
            if ($tbody.length && window.innerWidth < 768) {
                $tbody.find('td').each(function() {
                    var $td = $(this);
                    var title = $td.data('title');
                    if (title) {
                        $td.prepend('<span class="mobile-label">' + title + ': </span>');
                    }
                });
            }
        });
    });
})(jQuery); 