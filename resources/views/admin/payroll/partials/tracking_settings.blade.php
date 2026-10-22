<section class="tracking-settings" style="padding:22px;margin:18px 0;border:1px solid #dbe5ef;border-radius:16px;background:#f4f8fc">
    <small style="color:#526885;letter-spacing:1.5px;font-weight:700">ATTENDANCE POLICY</small>
    <h5 class="mt-2">Office holding &amp; tracking</h5>
    <p class="text-muted">Start a holding timer after office verification. Leaving early requires admin review.</p>
    @if(auth()->user()?->is_admin && !($trackingReadOnly ?? false))
    <div class="row">
        <div class="col-md-6 form-group">
            <label for="tracking_enabled">Location tracking</label>
            <select id="tracking_enabled" name="tracking_enabled" class="form-control">
                <option value="0" {{ !old('tracking_enabled', $employee->tracking_enabled ?? false) ? 'selected' : '' }}>Disabled</option>
                <option value="1" {{ old('tracking_enabled', $employee->tracking_enabled ?? false) ? 'selected' : '' }}>Enabled</option>
            </select>
        </div>
        <div class="col-md-6 form-group">
            <label for="tracking_hours">Holding time (hours)</label>
            <input id="tracking_hours" name="tracking_hours" type="number" min="0.08" max="24" step="0.01" class="form-control" required value="{{ old('tracking_hours', $employee->tracking_hours ?? 1) }}">
            <small class="text-muted">Default: 1 hour. Location checked every 5 minutes.</small>
        </div>
    </div>
    @foreach(['tracking_enabled', 'tracking_hours'] as $field)
        @error($field)<div class="text-danger">{{ $message }}</div>@enderror
    @endforeach
    @else
    <strong>{{ ($employee->tracking_enabled ?? false) ? 'Enabled - '.$employee->tracking_hours.' hours' : 'Disabled' }}</strong>
    <small class="d-block text-muted">Only an admin can change this policy.</small>
    @endif
</section>
