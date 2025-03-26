<?php
use App\Models\Pharmacyfees;

if (!function_exists('getFeeAmount')) {
    function getFeeAmount($feesType, $gender)
    {
        $fee = Pharmacyfees::where('fees_type', $feesType)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'ALL');
            })
            ->first();
        if (!$fee) {
            $fee = Pharmacyfees::where('fees_type', $feesType)
                ->where('gender', 'MALE')
                ->first();
        }

        return $fee ? $fee->fees_amount : 0;
    }
}
