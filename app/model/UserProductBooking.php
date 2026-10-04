<?php

namespace App\model;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class UserProductBooking extends Model
{
    protected $table = 'user_store_product_booking';

    public function generateBookingNo()
    {
        $this->order_no = random_int(1, 9) . date('siHYdm') . random_int(1, 9);
        $this->save();
        return $this->order_no;
    }
    public function generateOtp($digits = 4)
    {
        $otp = rand(pow(10, $digits-1), pow(10, $digits)-1);
        return $otp;
    }

    public function totalPay($item_cost, $discount, $promocode_discount, $get_tax, $delivery_cost, $packaging_cost, $admin_commission, $admin_delivery_commission,$tip=0)
    {
        $step_1 = $item_cost - $discount;
        $step_2 = $step_1;
        //$step_2 = $step_1 + $packaging_cost;
        $step_3 = $step_2 - $promocode_discount;
        $step_4 = $step_3 + $delivery_cost + $packaging_cost;
        $step_5 = (($step_4 * $get_tax) / 100);
        $step_6 = $step_4 + $step_5 + $tip;

        $this->tip = round($tip, 2);
        $this->save();
        $this->total_item_cost = round($item_cost, 2);
        $this->save();
        $this->discount_cost = round($discount, 2);
        $this->save();
        $this->tax_cost = round($step_5, 2);
        $this->save();
        $this->total_pay = round($step_6, 2);
        $this->save();
        $store_amount = $step_2 + $discount + $packaging_cost;
        $commission = (($store_amount * $admin_commission) / 100);
        $this->store_amount = round(($store_amount - ($commission) - $this->discount_cost), 2);
        //$this->admin_commission = round(($commission - $promocode_discount), 2);

        $get_commission_delivery_person = (($delivery_cost * $admin_delivery_commission) / 100);
//        $this->delivery_cost = round(($delivery_cost - $get_commission_delivery_person),2);
        $this->delivery_cost = round(($delivery_cost ),2);
        $this->delivery_admin_commission = round($get_commission_delivery_person,2);
        $commission = $commission + $get_commission_delivery_person;

//      $this->admin_commission = round((($commission - $promocode_discount) + $step_5), 2);
        $this->admin_commission = round((($commission - $promocode_discount) ), 2);
        $this->save();
        return "success";
    }

    public static function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}
