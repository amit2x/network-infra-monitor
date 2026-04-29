<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create devices');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:switch,router,firewall,access_point,server,other',
            'vendor' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:devices',
            'ip_address' => 'required|ip',
            'mac_address' => 'nullable|mac_address',
            'firmware_version' => 'nullable|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'procurement_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:procurement_date',
            'amc_expiry' => 'nullable|date',
            'eol_date' => 'nullable|date|after:installation_date',
            'remarks' => 'nullable|string|max:1000',
            'is_critical' => 'boolean',
            'monitoring_enabled' => 'boolean',
            'port_count' => 'required_if:type,switch|integer|min:1|max:48',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.unique' => 'This serial number is already registered.',
            'ip_address.unique' => 'This IP address is already assigned to another device.',
            'warranty_expiry.after' => 'Warranty expiry date must be after procurement date.',
            'eol_date.after' => 'End of life date must be after installation date.',
        ];
    }
}
