<?php

namespace App\Http\Resources;

use App\Paginators\Paginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BasePaginatorCollection extends ResourceCollection
{
    /**
     * The order for the collection.
     *
     * @var string|null
     */
    protected $order;

    public function __construct($resource, string $order = null)
    {
        $this->order = $order;
        $resource = Paginator::fromLengthAwarePaginator($resource);

        parent::__construct($resource);
    }
    
    /**
     * Retorna a ordem
     *
     * @return string|null
     */
    protected function getOrder()
    {
        return $this->order;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();
    }
}
