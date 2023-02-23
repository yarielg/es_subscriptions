jQuery( function( $ ) {

    const es_subscription = {
        $signup_btn: $('#es_submit_service'),
        $btn_send_business_info: $('#btn_send_business_info'),
        $btn_send_business_selection: $('#btn_send_business_selection'),
        init: function () {

            this.$signup_btn.on('click', this.addService);
            this.$btn_send_business_info.on('click', this.send_business_info);
            this.$btn_send_business_selection.on('click', this.send_business_selection);
            $('#collect_inside_yes').attr('checked', true);

        },
        addService: function(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_add_service',
                    'id' : $("input.service_choice:checked").val(),
                    'interval': $("input.interval_choice:checked").val()
                },
                dataType: "json",
                beforeSend: function () {},
                complete: function () {},
                success: function (response) {

                    console.log(response);
                    if(response.success){
                        window.location = '/checkout';
                    }else{
                        alert("There was an error, please try again!");
                    }
                    
                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        send_business_info: function(e){

            e.preventDefault();

            if(es_subscription.check_form_fields(e)){
                $.ajax( {
                    type: 'POST',
                    url:  parameters.ajax_url,
                    data:{
                        'action':'es_add_business',
                        'first' : $('#es_first').val(),
                        'last' : $('#es_last').val(),
                        'company' : $('#es_company').val(),
                        'business_type' : $('#es_business_type').val(),
                        'email' : $('#es_email').val(),
                        'phone' : $('#es_phone').val(),
                        'address' : $('#es_address').val(),
                        'es_number_of_employees' : $('#es_number_of_employees').val(),
                        'es_referred' : $('#es_referred').val(),
                        'description' : $('#es_description').val()
                    },
                    dataType: "json",
                    beforeSend: function () {
                        $('#es_business_form .mask').css('display','flex');
                    },
                    complete: function () {
                        $('#es_business_form .mask').css('display','none');
                    },
                    success: function (response) {
                        if(response.success){
                            $('#es_business_form').empty();
                            $('#es_business_form').append('<h3>Your request was sent, we will contact you soon</h3>');

                        }else{
                            toastr.warning(response.msg);
                        }
                    },
                    error : function(jqXHR, exception){
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }
                        console.log(msg);
                    }

                } );
            }

        },
        send_business_selection(){

            $.ajax( {
                type: 'POST',
                url:  parameters.ajax_url,
                data:{
                    'action':'es_business_selection',
                    'collect_inside' : $('#collect_inside_yes').is(":checked") ? 'yes' : 'no'
                },
                dataType: "json",
                beforeSend: function () {
                    //$('#es_business_form .mask').css('display','flex');
                },
                complete: function () {
                    //$('#es_business_form .mask').css('display','none');
                },
                success: function (response) {
                    if(response.success){
                        window.location = '/checkout';

                    }else{
                        toastr.warning(response.msg);
                    }
                },
                error : function(jqXHR, exception){
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = 'Not connect.\n Verify Network.';
                    } else if (jqXHR.status == 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (jqXHR.status == 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (exception === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (exception === 'timeout') {
                        msg = 'Time out error.';
                    } else if (exception === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + jqXHR.responseText;
                    }
                    console.log(msg);
                }

            } );
        },
        check_form_fields: function(e){
            var $first = $('#es_first');
            var $last = $('#es_last');
            var $company = $('#es_company');
            var $business_type = $('#es_business_type');
            var $email = $('#es_email');
            var $phone = $('#es_phone');
            var $address = $('#es_address');
            var $number_of_employees = $('#es_number_of_employees');
            var $es_referred = $('#es_referred');
            var $description = $('#es_description');
            var $terms = $('#es_terms');

            console.log($terms.prop('checked'))

            if($first.val() === '' || $last.val() === ''){
                toastr.warning('Check your name please');
                return false;
            }

            var filter_email = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if($email.val() === '' || !filter_email.test($email.val())){
                toastr.warning('Check your name email please');
                return false;
            }

            var filter_phone = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;
            if($phone.val() === '' || !filter_phone.test($phone.val())){
                toastr.warning('Check your phone please');
                return false;
            }

            if(! $terms.prop('checked')){
                toastr.warning('You need to check the terms to proceed');
                return false;
            }

            if(! $number_of_employees.val()){
                toastr.warning('Enter your number of employee');
                return false;
            }

            if(! $address.val()){
                toastr.warning('Check your address please');
                return false;
            }

            if(! $description.val()){
                toastr.warning('Enter a description please');
                return false;
            }

            /*if(!es_subscription.pristine.pristine.validate()){
                toastr.warning('Please check the field, you have some errors');
                return false;
            }*/

            return true;

        }
    }

    $(window).load(function(){

        es_subscription.init();

    });

});


