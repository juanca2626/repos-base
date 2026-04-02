<?php

namespace Src\Shared\Infrastructure\Auth;
 
use Src\Shared\Domain\Auth\TokenValidatorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Exception;

class CognitoTokenValidator_DELETE implements TokenValidatorInterface
{
    private string $region;
    private string $userPoolId;
    private string $jwksUrl;

    public function __construct()
    {
 
        $this->region = config('aws.region');
        $this->userPoolId = config('aws.pool_id');
        $this->jwksUrl = "https://cognito-idp.{$this->region}.amazonaws.com/{$this->userPoolId}/.well-known/jwks.json";
    }

    public function validate(string $jwt): array
    {
        $jwks = json_decode(file_get_contents($this->jwksUrl), true);
        $keys = JWK::parseKeySet($jwks);

        try {
            $decoded = JWT::decode($jwt, $keys);
            return (array) $decoded;
        } catch (Exception $e) {
            throw new \RuntimeException('Invalid token: ' . $e->getMessage());
        }
    }
}