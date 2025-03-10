<?php

namespace Src\Infrastructure\Http\Services;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Serializer\JsonApiSerializer;

class FractalService
{
  private Manager $fractal;

  public function __construct()
  {
    $this->fractal = new Manager();
    $this->fractal->setSerializer(new JsonApiSerializer());
  }

  public function item($data, $transformer, string $resourceKey = null): array
  {
    $resource = new Item($data, $transformer, $resourceKey);
    return $this->fractal->createData($resource)->toArray();
  }

  public function collection($data, $transformer, string $resourceKey = null, array $meta = []): array
  {
    $resource = new Collection($data, $transformer, $resourceKey);

    if (!empty($meta)) {
      $resource->setMeta($meta);
    }

    return $this->fractal->createData($resource)->toArray();
  }
}
