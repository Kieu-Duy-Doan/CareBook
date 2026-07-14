<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    |
    */

    'spam_threshold' => env('BOOKING_SPAM_THRESHOLD', 3),
    'admin_phone' => env('BOOKING_ADMIN_PHONE', '0987.654.321'), // Update this in .env
];
