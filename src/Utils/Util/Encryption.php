<?php
namespace App\Utils\Util;

final class Encryption
{
    private static $key = 'da+mfAG2Mrl7h8+XR0yG3AiVK4rKgR18zXxsjZnHYt4=';

    public static function Encrypt(string $message)
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $cipherText = sodium_crypto_secretbox($message, $nonce, base64_decode(self::$key));

        $encoded = base64_encode($nonce . $cipherText);

        return $encoded;
    }

    public static function Decrypt(string $encoded)
    {
        $decoded = base64_decode($encoded);

        if ($decoded === false) {
            throw new \Exception('The encoding failed');
        }

        if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new \Exception('The message was truncated');
        }

        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');

        $cipherText = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $message = sodium_crypto_secretbox_open($cipherText, $nonce, base64_decode(self::$key));

        if ($message === false) {
            throw new \Exception('The message was tampered with in transit');
        }

        return $message;
    }
}