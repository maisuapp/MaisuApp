<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class TransportRideRoute extends Model
{
    protected $table = 'transport_ride_route';

    public function get_coordinates($origin_waypoint, $destination)
    {
        $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin_waypoint."&destination=".$destination."&mode=DRIVING&key=AIzaSyBjA92EIcwcbIYVa78x-yJK9gQNnzF6rXI";
        $json = file_get_contents($details);
        return $json;
    }
}
