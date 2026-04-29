<form id="portForm" onsubmit="updatePort(event, {{ $device->id }}, {{ $port->id }})">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Port Number</label>
        <input type="text" class="form-control" value="{{ $port->port_number }}" readonly>
    </div>

    <div class="mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select" required>
            <option value="active" {{ $port->status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="free" {{ $port->status === 'free' ? 'selected' : '' }}>Free</option>
            <option value="down" {{ $port->status === 'down' ? 'selected' : '' }}>Down</option>
            <option value="disabled" {{ $port->status === 'disabled' ? 'selected' : '' }}>Disabled</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="service_name" class="form-label">Service Name</label>
        <input type="text" name="service_name" id="service_name"
               class="form-control" value="{{ $port->service_name }}"
               placeholder="e.g., CCTV, WiFi, Server">
    </div>

    <div class="mb-3">
        <label for="connected_device" class="form-label">Connected Device</label>
        <input type="text" name="connected_device" id="connected_device"
               class="form-control" value="{{ $port->connected_device }}"
               placeholder="Device name or identifier">
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="vlan_id" class="form-label">VLAN ID</label>
            <input type="number" name="vlan_id" id="vlan_id"
                   class="form-control" value="{{ $port->vlan_id }}"
                   placeholder="1-4096" min="1" max="4096">
        </div>
        <div class="col-md-6">
            <label for="speed_mbps" class="form-label">Speed (Mbps)</label>
            <input type="number" name="speed_mbps" id="speed_mbps"
                   class="form-control" value="{{ $port->speed_mbps }}"
                   placeholder="e.g., 1000">
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" id="description"
                  class="form-control" rows="2">{{ $port->description }}</textarea>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Port
        </button>
    </div>
</form>
