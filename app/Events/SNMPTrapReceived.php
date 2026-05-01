<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SNMPTrapReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;
    public $trap;
    public $timestamp;

    public function __construct(Device $device, array $trap)
    {
        $this->device = $device;
        $this->trap = $trap;
        $this->timestamp = now();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('snmp-traps'),
            new Channel('device.' . $this->device->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'snmp.trap.received';
    }
}