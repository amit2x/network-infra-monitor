@php
    $side = $side ?? 'front';
@endphp

@for($u = $rack->total_units; $u >= 1; $u--)
    @php
        $item = $rack->rackItems
            ->where('side', $side)
            ->where('unit_start', '<=', $u)
            ->where('unit_start', '>', $u - ($item['unit_height'] ?? 1))
            ->first();
        
        // Check if this is the first unit of the device
        $isFirstUnit = $item && $item->unit_start == $u;
        $isOccupied = $item !== null;
    @endphp
    
    <div class="rack-row {{ $isOccupied ? 'occupied' : '' }}" 
         style="background-color: {{ $isOccupied ? ($item->color ?? '#4e73df') : 'transparent' }}; 
                {{ !$isFirstUnit && $isOccupied ? 'border-bottom: none;' : '' }}
                {{ $isFirstUnit ? 'border-top: 2px solid white;' : '' }}">
        <div class="rack-unit-number">
            U{{ $u }}
        </div>
        <div class="rack-unit-content">
            @if($isFirstUnit)
                <a href="{{ route('devices.show', $item->device_id) }}" class="text-white text-decoration-none">
                    <strong>{{ $item->device->name }}</strong>
                </a>
                <br>
                <small class="text-white-50">
                    {{ $item->unit_height }}U | {{ ucfirst($item->device->type) }}
                    @if($item->device->status === 'online')
                        <span class="badge bg-success ms-1">Online</span>
                    @elseif($item->device->status === 'offline')
                        <span class="badge bg-danger ms-1">Offline</span>
                    @endif
                </small>
            @endif
        </div>
    </div>
@endfor