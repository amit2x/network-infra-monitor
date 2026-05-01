<?php

namespace App\Events;

use App\Models\Device;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SNMPThresholdExceeded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $device;
    public $metric;
    public $value;
    public $threshold;
    public $timestamp;

    public function __construct(Device $device, string $metric, float $value, float $threshold)
    {
        $this->device = $device;
        $this->metric = $metric;
        $this->value = $value;
        $this->threshold = $threshold;
        $this->timestamp = now();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('snmp-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'snmp.threshold.exceeded';
    }
}