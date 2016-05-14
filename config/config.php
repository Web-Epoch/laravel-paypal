<?php
/**
 * PayPal Setting & API Credentials
 * Created by Saleem Beg <saleem@web-epoch.com>
 */

return [
    'mode' => 'sandbox', // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
    'sandbox' => [
        'username' => env('PAYPAL_LIVE_API_USERNAME', ''),
        'password' => env('PAYPAL_LIVE_API_PASSWORD', ''),
        'secret' => env('PAYPAL_LIVE_API_SECRET', ''),
        'certificate' => env('PAYPAL_LIVE_API_CERTIFICATE', ''),
        'app_id' => 'APP-80W284485P519543T',    // Used for testing Adaptive Payments API in sandbox mode
	    /*
		If using In-Context Checkout uncomment the following line. Otherwise,
		leave it commented and it will use the default gateway: https://www.sandbox.paypal.com
		*/
	    //'gateway_url' => 'https://www.sandbox.paypal.com/checkoutnow/',
    ],
    'live' => [
        'username' => env('PAYPAL_SANDBOX_API_USERNAME', ''),
        'password' => env('PAYPAL_SANDBOX_API_PASSWORD', ''),
        'secret' => env('PAYPAL_SANDBOX_API_SECRET', ''),
        'certificate' => env('PAYPAL_SANDBOX_API_CERTIFICATE', ''),
        'app_id' => '',         // Used for Adaptive Payments API 
	    /*
		If using In-Context Checkout uncomment the following line. Otherwise,
		leave it commented and it will use the default gateway: https://www.sandbox.paypal.com
		*/
	    //'gateway_url' => 'https://www.paypal.com/checkoutnow/',
    ],

    'payment_action' => 'Sale', // Can Only Be 'Sale', 'Authorization', 'Order'
    'currency' => 'USD',
    'notify_url' => '', // Change this accordingly for your application.
];
