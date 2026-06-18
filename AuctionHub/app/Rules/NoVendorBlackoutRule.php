<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class NoVendorBlackoutRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        // In a real app, you'd check against a vendor_blackouts table
        // For this exam, we'll mock it with a simple rule

        $startsAt = Carbon::parse($value);
        $dayOfWeek = $startsAt->dayOfWeek;
        $hour = $startsAt->hour;

        // Example: No auctions can start on Sundays between 2-4 AM
        if ($dayOfWeek === Carbon::SUNDAY && $hour >= 2 && $hour < 4) {
            $fail('Auctions cannot start during vendor blackout windows (Sundays 2-4 AM).');
        }

        // In real implementation, you would check database:
        // $blackouts = VendorBlackout::where('vendor_id', request()->user()->vendor->id)
        //     ->where('day_of_week', $startsAt->dayOfWeek)
        //     ->where('start_time', '<=', $startsAt->format('H:i:s'))
        //     ->where('end_time', '>=', $startsAt->format('H:i:s'))
        //     ->exists();
        //
        // if ($blackouts) {
        //     $fail('This start time falls within a vendor blackout period.');
        // }
    }
}
