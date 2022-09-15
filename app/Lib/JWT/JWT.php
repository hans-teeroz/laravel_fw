<?php

namespace App\Lib\JWT;

use Exception;
use Firebase\JWT\JWT as BaseJWT;
use Psr\Container\ContainerInterface;

class JWT
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getPrivateKey(): string
    {
        return md5(env('SECRET_KEY'));
    }

    public function encode($data, $privateKey = null): string
    {
        if ($privateKey === null) {
            $privateKey = $this->getPrivateKey();
        }
        return BaseJWT::encode($data, $privateKey, 'HS256');
    }

    public function match($jwt, $privateKey = null)
    {
        try {
            if ($privateKey === null) {
                $privateKey = $this->getPrivateKey();
            }
            return BaseJWT::decode($jwt, $privateKey, ['HS256']);
        } catch (Exception $e) {
            return null;
        }
    }
}
