<?php

namespace Classes\Crypt;

/**
 * Authenticated symmetric encryption for application secrets.
 *
 * Replaces the hand-rolled AesCtr, which had three defects that compounded:
 *   - the key was the raw first 32 bytes of the secret pushed through a single AES
 *     block expansion, with no KDF, no salt and no iterations, and shorter secrets
 *     were zero-padded, shrinking the keyspace;
 *   - the CTR nonce was floor(rand(0, 0xffff)) plus a millisecond timestamp, i.e.
 *     16 bits from a non-cryptographic PRNG, so nonce reuse under concurrency was
 *     realistic — and reusing a CTR keystream is catastrophic;
 *   - there was no MAC, so CTR ciphertext could be bit-flipped at will and nothing
 *     detected the tampering.
 *
 * This uses AES-256-GCM (which authenticates as well as encrypts) with a 96-bit
 * random nonce and a key derived per message via HKDF-SHA256 over a random 16-byte
 * salt.
 *
 * HKDF rather than PBKDF2 is deliberate. Every secret passed here is already
 * high-entropy — APP_SEC is a 256-bit CSPRNG value and the per-user secret is a
 * bcrypt hash — so there is nothing to stretch, and decrypt() runs on every
 * authenticated REST request, where PBKDF2's iteration count would be a per-request
 * cost for no gain. BackupService keeps PBKDF2 because its secret is a
 * human-chosen backup password, which genuinely does need stretching.
 *
 * Wire format: "v2." . base64(salt[16] | iv[12] | tag[16] | ciphertext)
 *
 * decrypt() transparently falls back to AesCtr for anything without the "v2."
 * prefix, so tokens, password-reset links and encrypted settings written before
 * this change keep working. The prefix cannot collide with a legacy value: "."
 * is not in the base64 alphabet AesCtr emits.
 */
class IceCrypt
{
    const PREFIX = 'v2.';
    const CIPHER = 'aes-256-gcm';
    const SALT_LENGTH = 16;
    const IV_LENGTH = 12;   // 96-bit nonce, the size GCM is specified for
    const TAG_LENGTH = 16;
    const HKDF_INFO = 'icehrm-secret-v2';

    /**
     * @param string $plaintext
     * @param string $secret
     * @return string|false the encoded blob, or false if encryption failed
     */
    public static function encrypt($plaintext, $secret)
    {
        if (!is_string($secret) || $secret === '') {
            return false;
        }

        $salt = random_bytes(self::SALT_LENGTH);
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        $ciphertext = openssl_encrypt(
            (string) $plaintext,
            self::CIPHER,
            self::deriveKey($secret, $salt),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            return false;
        }

        return self::PREFIX.base64_encode($salt.$iv.$tag.$ciphertext);
    }

    /**
     * @param string $blob a v2 blob, or a legacy AesCtr ciphertext
     * @param string $secret
     * @return string|false the plaintext, or false if the blob was tampered with,
     *                      truncated, or encrypted under a different secret
     */
    public static function decrypt($blob, $secret)
    {
        if (!is_string($blob) || $blob === '' || !is_string($secret) || $secret === '') {
            return false;
        }

        if (substr($blob, 0, strlen(self::PREFIX)) !== self::PREFIX) {
            // Written before this class existed. AesCtr provides no integrity, which is
            // exactly why nothing new is encrypted with it — this branch exists only so
            // outstanding reset links, live tokens and stored settings keep working, and
            // it can be deleted once those have all been rewritten.
            return AesCtr::decrypt($blob, $secret, 256);
        }

        $raw = base64_decode(substr($blob, strlen(self::PREFIX)), true);
        $minimum = self::SALT_LENGTH + self::IV_LENGTH + self::TAG_LENGTH;
        if ($raw === false || strlen($raw) <= $minimum) {
            return false;
        }

        $salt = substr($raw, 0, self::SALT_LENGTH);
        $iv = substr($raw, self::SALT_LENGTH, self::IV_LENGTH);
        $tag = substr($raw, self::SALT_LENGTH + self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($raw, $minimum);

        // Returns false when the tag does not verify — i.e. the ciphertext was modified
        // or the secret is wrong. Callers must treat false as "reject", never as "empty".
        return openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            self::deriveKey($secret, $salt),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }

    /**
     * Per-message 256-bit key. The random salt means two messages encrypted under the
     * same secret never share a key, so a nonce repeat across messages is harmless.
     *
     * @param string $secret
     * @param string $salt
     * @return string raw 32-byte key
     */
    private static function deriveKey($secret, $salt)
    {
        return hash_hkdf('sha256', $secret, 32, self::HKDF_INFO, $salt);
    }
}
