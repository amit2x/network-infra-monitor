<?php

namespace App\Http\Requests\Alert;

use Illuminate\Foundation\Http\FormRequest;

class StoreAlertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device_id' => 'nullable|exists:devices,id',
            'type' => 'required|in:device_down,device_up,warranty_expiry,amc_expiry,high_cpu,high_memory,port_down',
            'severity' => 'required|in:critical,high,medium,low',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'additional_data' => 'nullable|json',
        ];
    }
}
