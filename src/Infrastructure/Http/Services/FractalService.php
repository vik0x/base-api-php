<?php

namespace Src\Infrastructure\Http\Services;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\PagerfantaPaginatorAdapter;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;

class FractalService
{
    private Manager $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new JsonApiSerializer());
    }

    /**
     * @param mixed $data
     * @param TransformerAbstract $transformer
     * @param string|null $resourceKey
     * @return array<string, mixed>
     */
    public function item($data, $transformer, ?string $resourceKey = null): array
    {
        $resource = new Item($data, $transformer, $resourceKey);
        $result   = $this->fractal->createData($resource)->toArray();

        if (! is_array($result) || empty($result)) {
            /** @var array<string, mixed> */
            return [];
        }

        /** @var array<string, mixed> */
        return $result;
    }

    /**
     * @param mixed $data
     * @param TransformerAbstract $transformer
     * @param string|null $resourceKey
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    public function collection($data, $transformer, ?string $resourceKey = null, array $meta = []): array
    {
        $resource = new Collection($data, $transformer, $resourceKey);

        if ($meta !== []) {
            $resource->setMeta($meta);
        }

        $result = $this->fractal->createData($resource)->toArray();

        if (! is_array($result) || empty($result)) {
            /** @var array<string, mixed> */
            return [];
        }

        /** @var array<string, mixed> */
        return $result;
    }
}
