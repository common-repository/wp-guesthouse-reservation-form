jQuery(document).ready(function ($) {
    // Datepicker input box
    $('input.datepicker').daterangepicker({
        singleDatePicker: true 
    });

    // Parsley
    $.listen('parsley:field:validate', function() {
        validateFront();
    });
    $('#reservation-form .btn').on('click', function() {
        $('#reservation-form').parsley().validate();
        validateFront();
    });
    var validateFront = function() {
        if (true === $('#reservation-form').parsley().isValid()) {
            $('.bs-callout-info').removeClass('hidden');
            $('.bs-callout-warning').addClass('hidden');
        } else {
            $('.bs-callout-info').addClass('hidden');
            $('.bs-callout-warning').removeClass('hidden');
        }
    };

});