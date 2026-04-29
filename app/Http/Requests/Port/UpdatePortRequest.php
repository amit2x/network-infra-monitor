<?php

namespace App\Http\Requests\Port;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage ports');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:active,free,down,disabled',
            'service_name' => 'nullable|string|max:255',
            'connected_device' => 'nullable|string|max:255',
            'vlan_id' => 'nullable|integer|min:1|max:4096',
            'speed_mbps' => 'nullable|integer|min:10|max:400000',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The port status must be one of: active, free, down, or disabled.',
            'vlan_id.min' => 'VLAN ID must be between 1 and 4096.',
            'vlan_id.max' => 'VLAN ID must be between 1 and 4096.',
            'speed_mbps.min' => 'Speed must be at least 10 Mbps.',
            'speed_mbps.max' => 'Speed cannot exceed 400000 Mbps (400 Gbps).',
        ];
    }
}
