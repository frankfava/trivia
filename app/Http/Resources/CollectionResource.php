<?php

namespace App\Http\Resources;

use Illuminate\Container\Container;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectionResource extends ResourceCollection
{
    /**
     * The resource instance.
     *
     * @var mixed
     */
    protected $resourceClass;

    protected $items;

    protected array $args;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($collection, ?string $resourceClass = null, ...$args)
    {
        $this->args = $args;
        $this->resourceClass = $resourceClass;

        parent::__construct($collection);
    }

    /**
     * Get the resources in this collection
     */
    public function data($request = null)
    {
        $request = $request ?: Container::getInstance()->make('request');

        return $this->collection->map(function ($item) use ($request) {
            return $this->resourceClass::make($item, ...$this->args)->toArray($request);
        })->all();
    }

    public function toArray($request = null)
    {
        $data = ['data' => $this->data($request)];

        if ($this->resource instanceof LengthAwarePaginator) {
            $paginator = (object) $this->resource->toArray();
            $data = array_merge([
                // Pagination
                'current_page' => $paginator->current_page,
                'from' => $paginator->from,
                'last_page' => $paginator->last_page,
                'per_page' => $paginator->per_page,
                'to' => $paginator->to,
                'total' => $paginator->total,

                // Ignored: links ,path ,first_page_url ,last_page_url ,next_page_url ,prev_page_url
            ], $data);
        }

        return $data;
    }
}
