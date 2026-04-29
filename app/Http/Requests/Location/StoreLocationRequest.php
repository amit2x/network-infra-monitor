<?php

namespace App\Http\Requests\Location;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create locations');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => [
                'required',
                Rule::in(['airport', 'terminal', 'it_room', 'rack'])
            ],
            'parent_id' => [
                'nullable',
                'exists:locations,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parent = \App\Models\Location::find($value);
                        $typeHierarchy = [
                            'airport' => 0,
                            'terminal' => 1,
                            'it_room' => 2,
                            'rack' => 3
                        ];

                        if ($typeHierarchy[$this->type] <= $typeHierarchy[$parent->type]) {
                            $fail('Invalid parent location for the selected type.');
                        }
                    }
                },
            ],
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'The location type must be one of: Airport, Terminal, IT Room, or Rack.',
        ];
    }
}
