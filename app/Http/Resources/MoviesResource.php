<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MoviesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'overview'     => $this->overview,
            'release_date' => $this->release_date,
            'image'        => $this->image,
            'movie_id'     => $this->movie_id,
        ];
    }
}
