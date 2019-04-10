<?php
declare(strict_types=1);
namespace Smic\Pagepath;

class TokenUtility
{
    public static function createToken(array $data): string
    {
        return self::hash(serialize([$data, $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']]));
    }

    public static function validateToken(array $data, string $token): bool
    {
        return $token === self::createToken($data);
    }

    protected static function hash(string $string): string
    {
        return hash('sha256', $string);
    }
}
