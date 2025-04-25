<?php

namespace Src\Infrastructure\Auth;

use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Src\Domain\User\User;

class JwtService
{
    public function __construct(
        private string $secretKey,
        private int $tokenExpiration = 3600, // 1 hour by default
        private string $issuer = 'api',
        private string $audience = 'api-clients'
    ) {
    }

    public function generateToken(User $user): string
    {
        $issuedAt  = new DateTimeImmutable();
        $expiresAt = (clone $issuedAt)->modify('+' . $this->tokenExpiration . ' seconds');

        $payload = [
                    'iss'  => $this->issuer,
                    'aud'  => $this->audience,
                    'iat'  => $issuedAt->getTimestamp(),
                    'exp'  => $expiresAt->getTimestamp(),
                    'sub'  => $user->id()->value(),
                    'user' => [
                               'id'    => $user->id()->value(),
                               'email' => $user->email()->value(),
                               'name'  => $user->name(),
                              ],
                   ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * @return array<string, mixed>|null
     */
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            /** @var array<string, mixed> */
            $result = (array) $decoded;
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserIdFromToken(string $token): ?string
    {
        $payload = $this->validateToken($token);

        if (! $payload || ! isset($payload['sub'])) {
            return null;
        }

        return (string) $payload['sub'];
    }
}
