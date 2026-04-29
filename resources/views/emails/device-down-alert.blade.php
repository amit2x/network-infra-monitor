@component('mail::message')
# {{ $alert->title }}

{{ $alert->message }}

**Severity:** {{ ucfirst($alert->severity) }}

@if($alert->device)
**Device:** {{ $alert->device->name }}
**IP Address:** {{ $alert->device->ip_address }}
**Location:** {{ $alert->device->location->full_path ?? 'N/A' }}
@endif

@if($alert->additional_data)
**Additional Information:**
@foreach($alert->additional_data as $key => $value)
- {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach
@endif

@component('mail::button', ['url' => route('alerts.show', $alert->id)])
View Alert
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
