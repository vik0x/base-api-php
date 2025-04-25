<?php

namespace Src\Application\Auth\Handler;

use Src\Application\Auth\Command\LoginUserCommand;
use Src\Domain\Auth\RefreshToken;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Domain\Shared\Exceptions\InvalidCredentialsException;
use Src\Domain\User\Repositories\UserRepository;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Infrastructure\Auth\JwtService;

final class LoginUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private RefreshTokenRepository $refreshTokenRepository,
        private JwtService $jwtService
    ) {
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int, user: array<string, mixed>}
     */
    public function __invoke(LoginUserCommand $command): array
    {
        $email = new Email($command->email());
        $user  = $this->userRepository->findByEmail($email);

        if (! $user || ! $user->verifyPassword($command->password())) {
            throw new InvalidCredentialsException();
        }

        $accessToken  = $this->jwtService->generateToken($user);
        $refreshToken = RefreshToken::create($user->id()->value());
        try {
            $decodedToken = $this->jwtService->validateToken($accessToken);
        } catch (\Exception $e) {
            throw new InvalidCredentialsException();
        }

        $this->refreshTokenRepository->save($refreshToken);

        return [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken->token()->value(),
                'token_type'    => 'Bearer',
                'expires_in'    => (int) ($decodedToken['exp'] - time()),
                'user'          => $user->toArray(),
               ];
    }
}
