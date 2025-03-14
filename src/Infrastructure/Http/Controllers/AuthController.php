<?php

namespace Src\Infrastructure\Http\Controllers;

use Src\Infrastructure\Http\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Src\Application\Auth\Command\LoginUserCommand;
use Src\Application\Auth\Command\LogoutUserCommand;
use Src\Application\Auth\Command\RefreshTokenCommand;
use Src\Domain\Shared\Exceptions\InvalidTokenException;
use Src\Domain\Shared\ValueObjects\Email;

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        if (
            ! isset($data['email']) ||
            ! isset($data['password'])
        ) {
            return $this->jsonResponse($response, ['message' => 'Email and password are required'], 400);
        }

        $token = $this->commandBus->dispatch(new LoginUserCommand($data['email'], $data['password']));

        return $this->jsonResponse($response, $token, 200);
    }

    public function logout(Request $request, Response $response)
    {
        $token = $this->getTokenFromHeader($request);
        $this->commandBus->dispatch(new LogoutUserCommand($token, null));

        return $this->jsonResponse($response, ['message' => 'User logged out successfully'], 200);
    }

    public function refresh(Request $request, Response $response)
    {
        $token = $this->getTokenFromHeader($request);

        if (! $token) {
            throw new InvalidTokenException('Invalid token', 401);
        }

        $token = $this->commandBus->dispatch(new RefreshTokenCommand($token));

        return $this->jsonResponse($response, $token, 200);
    }

    protected function getTokenFromHeader(Request $request): string
    {
        $header = $request->getHeader('Authorization');
        if (! isset($header[0])) {
            throw new InvalidTokenException('Invalid token', 401);
        }

        $token = explode('Bearer ', $header[0]);
        $token = $token[1] ?? null;

        if (! $token) {
            throw new InvalidTokenException('Invalid token', 401);
        }

        return $token;
    }
}
