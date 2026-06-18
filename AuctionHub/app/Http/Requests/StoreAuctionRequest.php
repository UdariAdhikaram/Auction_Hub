<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoVendorBlackoutRule;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Check if user is a vendor and approved
        return $this->user()->role === 'vendor'
            && $this->user()->vendor
            && $this->user()->vendor->approved_at !== null;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'starts_at' => [
                'required',
                'date',
                'after:now',
                new NoVendorBlackoutRule(), // Custom rule
            ],
            'ends_at' => 'required|date|after:starts_at',
            'reserve_price' => 'required|numeric|min:0.01',
            'current_price' => 'required|numeric|min:0.01',
            'bid_increment' => 'required|numeric|min:0.01',
            'status' => 'in:draft,scheduled,live',
        ];
    }

    public function messages(): array
    {
        return [
            'starts_at.after' => 'Auction must start in the future.',
            'ends_at.after' => 'End time must be after start time.',
        ];
    }
}
