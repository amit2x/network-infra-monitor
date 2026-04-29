<?php

namespace App\Http\Requests\Port;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePortRequest extends FormRequest
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
            'ports' => 'required|array|min:1',
            'ports.*.id' => 'required|integer|exists:ports,id',
            'ports.*.status' => 'nullable|in:active,free,down,disabled',
            'ports.*.service_name' => 'nullable|string|max:255',
            'ports.*.connected_device' => 'nullable|string|max:255',
            'ports.*.vlan_id' => 'nullable|integer|min:1|max:4096',
            'ports.*.description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'ports.required' => 'At least one port must be selected.',
            'ports.*.id.exists' => 'One or more selected ports are invalid.',
            'ports.*.status.in' => 'Invalid status for one or more ports.',
            'ports.*.vlan_id.min' => 'VLAN ID must be between 1 and 4096.',
            'ports.*.vlan_id.max' => 'VLAN ID must be between 1 and 4096.',
        ];
    }
}
