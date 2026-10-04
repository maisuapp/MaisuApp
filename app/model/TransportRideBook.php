<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class TransportRideBook extends Model
{
    protected $table = 'user_ride_booking';

    public function generateRideNo()
    {
        $ride_no = random_int(1, 9) . date('sihYdm') . random_int(1, 9);
        return $ride_no;
    }
    public function generateOtp($digits = 4)
    {
        $otp = rand(pow(10, $digits-1), pow(10, $digits)-1);
        return $otp;
    }

    public function BookingCost($km, $booking_price, $get_tax, $admin_commission)
    {
        //$km = explode(' ', $km);
        ////  $km = filter_var($km, FILTER_SANITIZE_NUMBER_INT);
        //$this->base_fare = round(($km[0] * $booking_price), 2);
        //$this->save();
        //$this->tax = round((($this->base_fare * $get_tax) / 100), 2);
        //$this->save();
        //$this->total_pay = round(($this->base_fare + $this->tax), 2);
        //$this->save();
        //$this->driver_amount = round($this->total_pay - (($this->total_pay * $admin_commission) / 100), 2);
        //$this->save();
        //return "success";
    }

    public function BookingCountDistanceAmount($km, $cost_per_km, $base_fare, $time_fare, $eta)
    {
        try{
            $km = explode(' ', $km);
            $km_count = floatval($km[0]);
            $distance_km = $km_count;

            $eta = explode(' ', $eta);
            $eta_count = intval($eta[0]);

        } catch (\Exception $e) {
            $distance_km = 0;
            $eta_count = 0;
        }
        $this->base_fare = round(($base_fare), 2);
        $this->save();
        $this->total_distance_amount = round(($distance_km * $cost_per_km), 2);
        $this->save();

        $this->time_fare_amount = round(($eta_count * $time_fare), 2);
        $this->save();
    }

    public function BookingCostCount($base_fare, $total_distance_amount, $promo_code_discount, $get_tax, $admin_commission, $min_fare_amount,$time_fare_amount,$surcharge_cost = 0)
    {
        //client req

        $step_1 = $base_fare + $total_distance_amount + $time_fare_amount + $surcharge_cost; //ride amount
        $adjustment_amount = 0;
        if ($step_1 < $min_fare_amount) {
            $adjustment_amount = round(($min_fare_amount - $step_1), 2); //adjust amount
        }
        $step_2 = round(($step_1 + $adjustment_amount), 2); //add adjust amount
        $step_3 = $step_2 - $promo_code_discount; //promo code discount
        $step_4 = round((($step_3 * $get_tax) / 100), 2); //tax count
        $step_5 = $step_3 + $step_4 + $this->tip; //count ride amount

        //$step_1 = $base_fare + $total_distance_amount + $time_fare_amount; //ride amount
        //$step_2 = $step_1 - $promo_code_discount; //promo code discount
        //$step_3 = round((($step_2 * $get_tax) / 100), 2); //tax count
        //$step_4 = $step_2 + $step_3; //count ride amount
        //$adjustment_amount = 0;
        //if ($step_4 < $min_fare_amount) {
        //    $adjustment_amount = round(($min_fare_amount - $step_4), 2); //adjust amount
        //}
        //$step_5 = round(($step_4 + $adjustment_amount), 2); //add adjust amount

        $this->tax = $step_4;
        $this->save();
        $this->total_pay = $step_5;
        $this->save();
        $this->adjustment_amount = $adjustment_amount;
        $this->save();

        //commission calculation
        $driver_amount = $step_1 + $adjustment_amount;
        $commission = round((($driver_amount * $admin_commission) / 100), 2);
        $driver_new_amount = $driver_amount - $commission;
        $admin_commission = $commission - $promo_code_discount;

        $this->driver_amount = round($driver_new_amount + $this->tip, 2);
        $this->save();

        $this->admin_commission = round($admin_commission, 2);
        $this->save();

        ////original
        ////ride calculation
        ////$step_1 = $base_fare + $total_distance_amount; //ride amount
        //$step_1 = $base_fare + $total_distance_amount + $time_fare_amount; //ride amount
        //$step_2 = $step_1 - $promo_code_discount; //promo code discount
        //$step_3 = round((($step_2 * $get_tax) / 100), 2); //tax count
        //$step_4 = $step_2 + $step_3; //count ride amount
        //$adjustment_amount = 0;
        //if ($step_4 < $min_fare_amount) {
        //    $adjustment_amount = round(($min_fare_amount - $step_4), 2); //adjust amount
        //}
        //$step_5 = round(($step_4 + $adjustment_amount), 2); //add adjust amount

        //$this->tax = $step_3;
        //$this->save();
        //$this->total_pay = $step_5;
        //$this->save();
        //$this->adjustment_amount = $adjustment_amount;
        //$this->save();

        ////commission calculation
        //$driver_amount = $step_1 + $adjustment_amount;
        //$commission = round((($driver_amount * $admin_commission) / 100), 2);
        //$driver_new_amount = $driver_amount - $commission;
        //$admin_commission = $commission - $promo_code_discount;

        //$this->driver_amount = round($driver_new_amount, 2);
        //$this->save();

        //$this->admin_commission = round($admin_commission, 2);
        //$this->save();
    }
}
