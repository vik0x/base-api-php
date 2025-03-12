<?php

namespace Src\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Application\ActivityLog\Query\ListActivityLogsQuery;
use Src\Application\ActivityLog\Query\FindActivityLogQuery;
use Src\Infrastructure\Http\Controller;
use Src\Infrastructure\Http\Transformers\ActivityLogTransformer;

final class ActivityLogController extends Controller
{
    public function find(Request $request, Response $response, array $args): Response
    {
        $query       = new FindActivityLogQuery((int) $args['id']);
        $activityLog = $this->commandBus->dispatch($query);
        $data        = $this->fractal->item($activityLog, new ActivityLogTransformer(), 'activity_log');

        return $this->jsonResponse($response, $data);
    }

    public function list(Request $request, Response $response): Response
    {
        $query  = new ListActivityLogsQuery($request->getQueryParams());
        $result = $this->commandBus->dispatch($query);

        $meta = [
                 'pagination' => [
                                  'total'       => $result['total'],
                                  'page'        => $query->page(),
                                  'per_page'    => $query->perPage(),
                                  'total_pages' => ceil($result['total'] / $query->perPage()),
                                  'links'       => [
                                                    'self'  => $request->getUri()->getPath(),
                                                    'first' => $request->getUri()->getPath() . '?page=1',
                                                    'last'  => $request->getUri()->getPath() . '?page=' . ceil($result['total'] / $query->perPage()),
                                                   ],
                                 ],
                ];

        $data = $this->fractal->collection(
            $result['data'],
            new ActivityLogTransformer(),
            'activity_logs',
            $meta
        );

        return $this->jsonResponse($response, $data);
    }
}
