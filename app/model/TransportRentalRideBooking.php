<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class TransportRentalRideBooking extends Model
{
    protected $table = 'user_rental_ride_booking';

    public function generateRideNo()
    {
        $ride_no = random_int(1, 9) . date('sihYdm') . random_int(1, 9);
        return $ride_no;
    }
}
