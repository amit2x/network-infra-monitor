<?php

namespace App\Http\Requests\Monitoring;

use Illuminate\Foundation\Http\FormRequest;

class RunMonitoringRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('run monitoring');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'device_id' => 'nullable|exists:devices,id',
            'critical_only' => 'nullable|boolean',
            'timeout' => 'nullable|integer|min:1|max:10',
        ];
    }
}
