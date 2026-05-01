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
            // Device basic info
            'name' => 'required|string|max:255',
            'type' => 'required|in:switch,router,firewall,access_point,server,other',
            'vendor' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255|unique:devices',
            'ip_address' => 'required|ip',
            'mac_address' => 'nullable|mac_address',
            'firmware_version' => 'nullable|string|max:255',
            'location_id' => 'required|exists:locations,id',
            
            // Lifecycle dates
            'procurement_date' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:procurement_date',
            'amc_expiry' => 'nullable|date',
            'eol_date' => 'nullable|date|after:installation_date',
            
            // Settings
            'remarks' => 'nullable|string|max:1000',
            'is_critical' => 'boolean',
            'monitoring_enabled' => 'boolean',
            'port_count' => 'required_if:type,switch|integer|min:1|max:48',
            
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

    public function messages(): array
    {
        return [
            'serial_number.unique' => 'This serial number is already registered.',
            'warranty_expiry.after' => 'Warranty expiry date must be after procurement date.',
            'eol_date.after' => 'End of life date must be after installation date.',
            'snmp_port.min' => 'SNMP port must be between 1 and 65535.',
            'snmp_port.max' => 'SNMP port must be between 1 and 65535.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default SNMP values if SNMP is enabled
        if ($this->has('snmp_enabled') && $this->snmp_enabled) {
            $this->merge([
                'snmp_port' => $this->snmp_port ?? 161,
                'snmp_timeout' => $this->snmp_timeout ?? 1,
                'snmp_version' => $this->snmp_version ?? '2c',
                'snmp_polling_interval' => $this->snmp_polling_interval ?? 300,
            ]);
        }
    }
}