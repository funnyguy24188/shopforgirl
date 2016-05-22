jQuery( document ).ready( function( $ ){
    var payment_profile,
        card_number,
        card_expiry,
        card_cvc,
        routing_number,
        account_number,
        payment_form = $( '#authorize_net_payment_form'),
        radio_change_handling = function(){
            var credit_card_form = $( '#yith_wcauthnet_credit_card_form'),
                new_form = credit_card_form.find( '.new-profile-form' );

            if( credit_card_form.length != 0 ){
                credit_card_form.find( '.payment-profile-radio' ).change( function(){
                    var t = $( this );

                    if( t.is( '#yith_wcauthnet_payment_profile_new:checked' ) ){
                        new_form.slideDown();
                    }
                    else{
                        new_form.slideUp();
                    }
                } );
            }
        },
        save_form_values = function(){
            var credit_card_form = $( '#yith_wcauthnet_credit_card_form'),
                echeck_form = $( 'div.payment_method_yith_wcauthnet_echeck_gateway');

            credit_card_form
                .on( 'change', '.payment-profile-radio', function(){ payment_profile = $(this).val() } )
                .on( 'change', '.wc-credit-card-form-card-number', function(){ card_number = $(this).val() } )
                .on( 'change', '.wc-credit-card-form-card-expiry', function(){ card_expiry = $(this).val() } )
                .on( 'change', '.wc-credit-card-form-card-cvc', function(){ card_cvc = $(this).val() } );

            echeck_form
                .on( 'change', '#yith_wcauthnet_echeck_gateway-routing-number', function(){ routing_number = $(this).val() } )
                .on( 'change', '#yith_wcauthnet_echeck_gateway-account-number', function(){ account_number = $(this).val() } );
        },
        restore_form_values = function(){
            var credit_card_form = $( '#yith_wcauthnet_credit_card_form'),
                echeck_form = $( 'div.payment_method_yith_wcauthnet_echeck_gateway');

            credit_card_form
                .find( '.payment-profile-radio[value="'+ payment_profile +'"]').prop( 'checked', true).change().end()
                .find( '.wc-credit-card-form-card-number').val( card_number ).end()
                .find( '.wc-credit-card-form-card-expiry').val( card_expiry ).end()
                .find( '.wc-credit-card-form-card-cvc').val( card_cvc );

            echeck_form
                .find( '#yith_wcauthnet_echeck_gateway-routing-number').val( routing_number).end()
                .find( '#yith_wcauthnet_echeck_gateway-account-number').val( account_number );
        };

    if( payment_form.length != 0 ){
        payment_form.find( 'input[type="submit"]').click();
    }

    $( 'body' ).on( 'updated_checkout', radio_change_handling );
    radio_change_handling();

    $( 'body' ).on( 'updated_checkout', restore_form_values );

    $( 'body' ).on( 'updated_checkout', save_form_values );
    save_form_values();
} );