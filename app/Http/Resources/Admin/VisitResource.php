<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user,   // ✅ user_id की जगह actual `user` column
            'title' => $this->title, // ✅ नया field
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location' => $this->location,
            'visited_time' => $this->visited_time,
            'visited_out_latitude' => $this->visited_out_latitude,
            'visited_out_longitude' => $this->visited_out_longitude,
            'visited_out_location' => $this->visited_out_location,
            'visited_out_time' => $this->visited_out_time,
            'visited_duration' => $this->visited_duration,

            // Media (Spatie library)
            'visited_counter_image' => $this->getFirstMediaUrl('visited_counter_image'),
            'visit_self_image' => $this->getFirstMediaUrl('visit_self_image'),
            'visited_out_counter_image' => $this->getFirstMediaUrl('visited_out_counter_image'),
            'visited_out_self_image' => $this->getFirstMediaUrl('visited_out_self_image'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

}
