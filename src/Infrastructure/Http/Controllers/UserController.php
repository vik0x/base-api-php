<?php

namespace Src\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Src\Application\User\Command\CreateUserCommand;
use Src\Application\User\Command\UpdateUserCommand;
use Src\Application\User\Command\DeleteUserCommand;

use Src\Application\User\Query\FindUserQuery;
use Src\Application\User\Query\SearchUserQuery;

use Src\Infrastructure\Http\Controller;

final class UserController extends Controller
{
    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $command = new CreateUserCommand($data['name'], $data['email'], $data['password']);
        $this->dispatch($command);

        return $this->jsonResponse($response, [
            'message' => 'User created successfully'
        ], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $this->dispatch(new UpdateUserCommand((int) $args['id'], $data['name'], $data['email']));
        return $this->jsonResponse($response, [
            'message' => 'User updated successfully'
        ]);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $command = new DeleteUserCommand((int) $args['id']);
        $this->dispatch($command);
        return $this->jsonResponse($response, [
            'message' => 'User deleted successfully'
        ]);
    }

    public function find(Request $request, Response $response, array $args): Response
    {
        $query = new FindUserQuery((int) $args['id']);
        $result = $this->dispatch($query);
        return $this->jsonResponse($response, [
            'data' => [
                'id' => $result->id()->value(),
                'name' => $result->name(),
                'email' => $result->email()->value(),
                'created_at' => $result->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $result->updatedAt()?->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function list(Request $request, Response $response): Response
    {
        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        $perPage = (int) ($request->getQueryParams()['per_page'] ?? 15);
        $search = (string) ($request->getQueryParams()['search'] ?? '');
        $command = new SearchUserQuery($page, $perPage, $search);
        $result = $this->dispatch($command);

        $users = array_map(function ($user) {
            return [
                'id' => $user->id()->value(),
                'name' => $user->name(),
                'email' => $user->email()->value(),
                'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->updatedAt()?->format('Y-m-d H:i:s')
            ];
        }, $result['data']);

        return $this->jsonResponse($response, [
            'data' => $users,
            'meta' => [
                'total' => $result['total'],
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($result['total'] / $perPage)
            ]
        ]);
    }
}
