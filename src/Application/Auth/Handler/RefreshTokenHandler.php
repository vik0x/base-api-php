<?php

namespace Src\Application\Auth\Handler;

use Src\Application\Auth\Command\RefreshTokenCommand;
use Src\Domain\Auth\RefreshToken;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\Shared\Exceptions\InvalidTokenException;
use Src\Domain\User\Repositories\UserRepository;
use Src\Infrastructure\Auth\JwtService;

final class RefreshTokenHandler
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private UserRepository $userRepository,
        private JwtService $jwtService
    ) {
    }

    /**
     * @return array{access_token: string, refresh_token: string, token_type: string, expires_in: int}
     */
    public function __invoke(RefreshTokenCommand $command): array
    {
        $tokenValue   = RefreshTokenValue::fromString($command->refreshToken());
        $refreshToken = $this->refreshTokenRepository->findByToken($tokenValue);

        if (! $refreshToken || $refreshToken->isExpired()) {
            throw new InvalidTokenException('Invalid or expired refresh token');
        }

        $user = $this->userRepository->find($refreshToken->userId());

        if (! $user) {
            throw new InvalidTokenException('User not found');
        }

        $this->refreshTokenRepository->deleteByToken($tokenValue);

        $accessToken     = $this->jwtService->generateToken($user);
        $newRefreshToken = RefreshToken::create($user->id()->value());
        $decodedToken    = $this->jwtService->validateToken($accessToken);

        $this->refreshTokenRepository->save($newRefreshToken);

        return [
                'access_token'  => $accessToken,
                'refresh_token' => $newRefreshToken->token()->value(),
                'token_type'    => 'Bearer',
                'expires_in'    => (int) ($decodedToken['exp'] - time()),
               ];
    }
}
