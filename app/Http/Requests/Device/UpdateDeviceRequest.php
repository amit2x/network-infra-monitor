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
            // Device basic info
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
            
            // Lifecycle dates
            'procurement_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'amc_expiry' => 'nullable|date',
            'eol_date' => 'nullable|date',
            
            // Settings
            'remarks' => 'nullable|string|max:1000',
            'is_critical' => 'boolean',
            'monitoring_enabled' => 'boolean',
            
            // SNMP fields
            'snmp_enabled' => 'boolean',
            'snmp_community' => 'nullable|string|max:255',
            'snmp_version' => 'nullable|in:1,2c,3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_timeout' => 'nullable|integer|min:1|max:10',
            'snmp_polling_enabled' => 'boolean',
            'snmp_polling_interval' => 'nullable|integer|in:60,300,900,1800,3600',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Handle SNMP enabled checkbox
        if (!$this->has('snmp_enabled')) {
            $this->merge(['snmp_enabled' => false]);
        }
        
        if (!$this->has('snmp_polling_enabled')) {
            $this->merge(['snmp_polling_enabled' => false]);
        }
    }
}