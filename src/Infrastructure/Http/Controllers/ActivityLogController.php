<?php

namespace Src\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Application\ActivityLog\Query\FindActivityLogQuery;
use Src\Application\ActivityLog\Query\ListActivityLogsQuery;
use Src\Infrastructure\Http\Controller;
use Src\Infrastructure\Http\Transformers\ActivityLogTransformer;

class ActivityLogController extends Controller
{
    /**
     * @param array<string, string> $args
     */
    public function find(Request $request, Response $response, array $args): Response
    {
        $id     = (int) $args['id'];
        $query  = new FindActivityLogQuery($id);
        $result = $this->commandBus->dispatch($query);

        $data = $this->fractal->item($result, new ActivityLogTransformer());
        return $this->jsonResponse($response, $data);
    }

    public function list(Request $request, Response $response): Response
    {
        /** @var array{page?: int, perPage?: int, search?: string} $params */
        $params = $request->getQueryParams();
        $query  = new ListActivityLogsQuery($params);
        $result = $this->commandBus->dispatch($query);

        $data = $this->fractal->collection(
            $result['data'],
            new ActivityLogTransformer(),
            null,
            isset($result['meta']) ? $result['meta'] : []
        );

        return $this->jsonResponse($response, $data);
    }
}
