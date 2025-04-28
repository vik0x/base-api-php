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
use Src\Infrastructure\Http\Transformers\UserTransformer;

final class UserController extends Controller
{
    public function create(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);
        $this->commandBus->dispatch(new CreateUserCommand($data));

        return $this->jsonResponse($response, ['message' => 'User created successfully'], 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data       = json_decode($request->getBody()->getContents(), true);
        $data['id'] = $args['id'];
        $this->commandBus->dispatch(new UpdateUserCommand($data));

        return $this->jsonResponse($response, ['message' => 'User updated successfully']);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->commandBus->dispatch(new DeleteUserCommand((int) $args['id']));

        return $this->jsonResponse($response, ['message' => 'User deleted successfully']);
    }

    public function find(Request $request, Response $response, array $args): Response
    {
        $query = new FindUserQuery((int) $args['id']);
        $user  = $this->commandBus->dispatch($query);
        $data  = $this->fractal->item($user, new UserTransformer(), 'user');

        return $this->jsonResponse($response, $data);
    }

    public function list(Request $request, Response $response): Response
    {
        $result = $this->commandBus->dispatch(new SearchUserQuery($request->getQueryParams()));
        $data   = $this->fractal->collection(
            $result->getResults(),
            new UserTransformer(),
            'users',
            $result->getMetadata()
        );

        return $this->jsonResponse($response, $data);
    }
}
