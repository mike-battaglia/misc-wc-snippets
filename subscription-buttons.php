/*
 * Easy shortcodes for placing important WooCommerce Subscription Actions Buttons anywhere on the site.
 * EXAMPLES:
 * [wcs_user_action_button button="cancel" title="Cancel my Subscription"]
 * [wcs_user_action_button status="on-hold " button="pay" title="Yes I want to keep my Subscription"]
 * 
 * BUTTON TYPES:
 * cancel
 * resubscribe
 * reactivate
 * suspend
 * pay 
 */

// // Shortcode For User Subscription Actions
function cd_wcs_user_action_button( $atts ) {
	
	// Attributes
	$atts = shortcode_atts(
		array(
			'user' => '',
			'status' => 'active',
			'button' => 'id',
			'title' => '',
		),
		$atts
	);
	
	$status = explode(",", $atts['status']);

	if( '' == $atts['user'] && is_user_logged_in() ) {
	    $atts['user'] = get_current_user_id();
	}
	// User not logged in we return false
	if( $atts['user'] == 0 ){
	    return false;
	}
	
	//check if user has a subscription matching status
	if( wcs_user_has_subscription( $atts['user'], '', $status ) ){
	    $btn_code = '';
	    //Get User Subscriptions
	    $users_subscriptions = wcs_get_users_subscriptions($user_id);
	    foreach ($users_subscriptions as $subscription){
	        if ($subscription->has_status($status)) {
	            
	            $current_status = $subscription->get_status();
	            
	            //Cancel Button
	            if( $atts['button'] == 'cancel'){
	            $next_payment = $subscription->get_time( 'next_payment' );
					if ( $subscription->can_be_updated_to( 'cancelled' ) && ( ! $subscription->is_one_payment() && ( $subscription->has_status( 'on-hold' ) && empty( $next_payment ) ) || $next_payment > 0 ) ) {

						$btn_title = ('' == $atts['title']) ? _x( 'Cancel', 'an action on a subscription', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wcs_get_users_change_status_link( $subscription->get_id(), 'cancelled', $current_status )."' class='button cancel'>". $btn_title ."</a>";
					}
	            }
	            
	            //Resubscribe Button
	            if( $atts['button'] == 'resubscribe'){
					if ( wcs_can_user_resubscribe_to( $subscription, $atts['user'] ) && false == $subscription->can_be_updated_to( 'active' ) ) {

						$btn_title = ('' == $atts['title']) ? __( 'Resubscribe', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wcs_get_users_resubscribe_link( $subscription ) ."' class='button resubscribe'>". $btn_title  ."</a>";
					}
	            }
	            
	            //Reactive Button
	            if( $atts['button'] == 'reactivate'){
					if ( $subscription->can_be_updated_to( 'active' ) && ! $subscription->needs_payment() ) {

						$btn_title = ('' == $atts['title']) ? __( 'Reactivate', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wcs_get_users_change_status_link( $subscription->get_id(), 'active', $current_status ) ."' class='button reactivate'>". $btn_title  ."</a>";
					}
	            }
	            
	            //Suspend Button
	            if( $atts['button'] == 'suspend'){
					if ( $subscription->can_be_updated_to( 'on-hold' ) && wcs_can_user_put_subscription_on_hold( $subscription, $atts['user'] ) ) {

						$btn_title = ('' == $atts['title']) ? __( 'Suspend', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wcs_get_users_change_status_link( $subscription->get_id(), 'on-hold', $current_status ) ."' class='button suspend'>". $btn_title  ."</a>";
					}     
	            }
	            
	            //Pay Renewal OR Change Payment Button
	            if( $atts['button'] == 'pay'){
	            $order = $subscription->get_last_order( 'all', array( 'renewal', 'switch' ) );

					if ( $order && ( $order->needs_payment() || $order->has_status( array( 'on-hold', 'failed', 'cancelled' ) ) ) ) {

						$btn_title = ('' == $atts['title']) ? esc_html_x( 'Pay', 'pay for a subscription', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". $order->get_checkout_payment_url() ."' class='button pay'>". $btn_title  ."</a>";

					} elseif ( $subscription->can_be_updated_to( 'new-payment-method' ) ) {

						$btn_title = ('' == $atts['title']) ? _x( 'Change payment', 'label on button, imperative', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wp_nonce_url( add_query_arg( array( 'change_payment_method' => $subscription->get_id() ), $subscription->get_checkout_payment_url() ) ) ."' class='button pay'>". $btn_title  ."</a>";

					}
	            }
	            
	            //ID ONLY
	            if( $atts['button'] == 'id'){
	                $btn_code = $subscription->get_id();
	            }
				
				//Requires Manual Renewal
				if( $atts['button'] == 'manual'){
					$manual_check_id = $subscription->get_id();
					$manual_check_key = '_requires_manual_renewal';
					$manual_check_value = 'true';
					$manual_meta = get_post_meta($manual_check_id, $manual_check_key, true);
					
					if ($manual_meta === $manual_check_value){
						$btn_title = ('' == $atts['title']) ? _x( 'Add Payment Method', 'label on button, imperative', 'woocommerce-subscriptions' ) : $atts['title'];

						$btn_code = "<a href='". wp_nonce_url( add_query_arg( array( 'change_payment_method' => $subscription->get_id() ), $subscription->get_checkout_payment_url() ) ) ."' class='button pay'>". $btn_title  ."</a>";
						
						return "<div class='manual-renewal-notice'><h4><b>Important Notice:</b> Your subscription does not have a valid payment method on file.</h4><p>Add a credit or debit card now to prevent losing your notes and favorites.</p><div class='woocommerce wcs-user-action-button'>".$btn_code."</div></div>";
					} else {
						return 'Does not require manual renewal';
					}
				}
	            
	            return "<div class='woocommerce wcs-user-action-button'>".$btn_code."</div>";
	            exit;
	        }
	    }
	} else {
	   return false;
	}
	

}

add_shortcode( 'wcs_user_action_button', 'cd_wcs_user_action_button' );
