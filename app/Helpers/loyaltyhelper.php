<?php

namespace App\Helpers;
use App\Models\Loyalty;
use App\Models\LoyaltyHistory;

class loyaltyhelper
{
    public static function getloyaltydata($vendor_id){
        $data=Loyalty::where('vendor_id',$vendor_id)->first();
        return $data;
    }

    public static function savepoints($vendor_id,$user_id,$order_number,$type,$points){
        try {
            $loyaltydata = new LoyaltyHistory();
            $loyaltydata->vendor_id = $vendor_id;
            $loyaltydata->user_id = $user_id;
            $loyaltydata->order_number = $order_number;
            $loyaltydata->type = $type;
            if ($type == 1) {
                $loyaltydata->points = loyaltyhelper::getloyaltydata($vendor_id)->points;
            } else {
                $loyaltydata->points = $points;
            }            
            $loyaltydata->save();
            return 1;
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public static function availablepoints($user_id,$vendor_id){
        $plus=LoyaltyHistory::where('vendor_id',$vendor_id)->where('user_id',$user_id)->where('type',1)->sum('points');
        $minus=LoyaltyHistory::where('vendor_id',$vendor_id)->where('user_id',$user_id)->where('type',2)->sum('points');

        $data = $plus-$minus;
        return $data;
    }

    public static function getloyaltyhistory($vendor_id,$user_id){
        $loyaltyhistory=LoyaltyHistory::where('vendor_id',$vendor_id)->where('user_id',$user_id)->orderBy('id', 'DESC')->get();
        return $loyaltyhistory;
    }
}
