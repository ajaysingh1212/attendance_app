<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AttendanceLocationRequest extends FormRequest {
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array { return [
        'attendance_id' => 'sometimes|integer|min:1',
        'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90',
        'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
        'events' => 'sometimes|array|max:100',
        'events.*.id' => 'required|string|max:80',
        'events.*.type' => 'required|in:network_offline,network_online,location_unavailable,location_available',
        'events.*.occurred_at' => 'required|date',
    ]; }
}
