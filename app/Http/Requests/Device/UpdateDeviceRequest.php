<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit devices');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:switch,router,firewall,access_point,server,other',
            'vendor' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:devices,serial_number,' . $this->device->id,
            'ip_address' => 'required|ip',
            'mac_address' => 'nullable|mac_address',
            'firmware_version' => 'nullable|string|max:255',
            'status' => 'required|in:online,offline,maintenance,decommissioned',
            'location_id' => 'required|exists:locations,id',
            'procurement_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'amc_expiry' => 'nullable|date',
            'eol_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
            'is_critical' => 'boolean',
            'monitoring_enabled' => 'boolean',
        ];
    }
}
