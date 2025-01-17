<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Smart Resource
 * Use create method to automatically create resource or collection
 */
class ModelResource extends JsonResource
{
    public static function create($resource, ...$args)
    {
        if ($resource instanceof LengthAwarePaginator || $resource instanceof Collection) {
            $collection = CollectionResource::make($resource, get_called_class(), ...$args);

            return $collection;
        }

        return static::make($resource, ...$args);
    }

    public function toArray(Request $request)
    {
        return parent::toArray($request);
    }
}
