<?php

declare(strict_types=1);

/**
 * Copyright 2001-2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @package  Serialize
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Serialize;

/**
 * Factory for creating serializer instances.
 *
 * Provides a centralized way to create and configure serializers with
 * proper dependency injection and validation.
 *
 * @package  Serialize
 * @category Horde
 */
class SerializerFactory
{
    /**
     * Create a JSON serializer.
     *
     * @param string|null $sourceCharset Source charset for UTF-8 conversion on error
     * @return JsonSerializer
     */
    public function createJson(?string $sourceCharset = null): JsonSerializer
    {
        return new JsonSerializer($sourceCharset);
    }

    /**
     * Create a Basic (PHP native) serializer.
     *
     * @return BasicSerializer
     */
    public function createBasic(): BasicSerializer
    {
        return new BasicSerializer();
    }

    /**
     * Create a Base64 encoder/decoder.
     *
     * @return Base64Serializer
     */
    public function createBase64(): Base64Serializer
    {
        return new Base64Serializer();
    }

    /**
     * Create a URL encoder/decoder.
     *
     * @param bool $raw Use rawurlencode/rawurldecode instead of urlencode/urldecode
     * @return UrlSerializer
     */
    public function createUrl(bool $raw = false): UrlSerializer
    {
        return new UrlSerializer($raw);
    }

    /**
     * Create a compression serializer.
     *
     * @param string $mode Compression mode (bzip, gzdeflate, gzcompress, gzencode, lzf)
     * @param int $level Compression level (0-9, default 3)
     * @param int $workfactor BZip2 work factor (default 30)
     * @return CompressionSerializer
     * @throws Exception If compression mode is not supported
     */
    public function createCompression(
        string $mode,
        int $level = 3,
        int $workfactor = 30
    ): CompressionSerializer {
        return new CompressionSerializer($mode, $level, $workfactor);
    }

    /**
     * Create a BZip2 compression serializer.
     *
     * @param int $level Compression level (1-9, default 3)
     * @param int $workfactor Work factor (0-250, default 30)
     * @return CompressionSerializer
     * @throws Exception If bz2 extension is not loaded
     */
    public function createBzip(int $level = 3, int $workfactor = 30): CompressionSerializer
    {
        return $this->createCompression(CompressionSerializer::MODE_BZIP, $level, $workfactor);
    }

    /**
     * Create a GZip deflate serializer.
     *
     * @param int $level Compression level (0-9, default 3)
     * @return CompressionSerializer
     * @throws Exception If zlib extension is not loaded
     */
    public function createGzDeflate(int $level = 3): CompressionSerializer
    {
        return $this->createCompression(CompressionSerializer::MODE_GZ_DEFLATE, $level);
    }

    /**
     * Create a GZip compress serializer.
     *
     * @param int $level Compression level (0-9, default 3)
     * @return CompressionSerializer
     * @throws Exception If zlib extension is not loaded
     */
    public function createGzCompress(int $level = 3): CompressionSerializer
    {
        return $this->createCompression(CompressionSerializer::MODE_GZ_COMPRESS, $level);
    }

    /**
     * Create a GZip encode serializer (RFC 1952 format).
     *
     * @param int $level Compression level (0-9, default 3)
     * @return CompressionSerializer
     * @throws Exception If zlib extension is not loaded
     */
    public function createGzEncode(int $level = 3): CompressionSerializer
    {
        return $this->createCompression(CompressionSerializer::MODE_GZ_ENCODE, $level);
    }

    /**
     * Create an LZF compression serializer.
     *
     * @return CompressionSerializer
     * @throws Exception If lzf extension is not loaded
     */
    public function createLzf(): CompressionSerializer
    {
        return $this->createCompression(CompressionSerializer::MODE_LZF);
    }

    /**
     * Create an IMAP serializer.
     *
     * @param string $mode IMAP mode (imap8, imaputf7, imaputf8)
     * @return ImapSerializer
     * @throws Exception If IMAP mode is not supported
     */
    public function createImap(string $mode): ImapSerializer
    {
        return new ImapSerializer($mode);
    }

    /**
     * Create an IMAP8 (quoted-printable) serializer.
     *
     * @return ImapSerializer
     */
    public function createImap8(): ImapSerializer
    {
        return $this->createImap(ImapSerializer::MODE_IMAP8);
    }

    /**
     * Create an IMAP UTF-7 serializer.
     *
     * @return ImapSerializer
     * @throws Exception If horde/imap_client is not available
     */
    public function createImapUtf7(): ImapSerializer
    {
        return $this->createImap(ImapSerializer::MODE_IMAPUTF7);
    }

    /**
     * Create an IMAP UTF-8 serializer.
     *
     * @return ImapSerializer
     * @throws Exception If horde/mime is not available
     */
    public function createImapUtf8(): ImapSerializer
    {
        return $this->createImap(ImapSerializer::MODE_IMAPUTF8);
    }

    /**
     * Create a UTF-7 charset serializer.
     *
     * @param string $sourceCharset Source charset (default UTF-8)
     * @param string $targetCharset Target charset (default UTF-8)
     * @param bool $withBasic Apply PHP serialization before charset conversion
     * @return Utf7Serializer
     */
    public function createUtf7(
        string $sourceCharset = 'UTF-8',
        string $targetCharset = 'UTF-8',
        bool $withBasic = false
    ): Utf7Serializer {
        return new Utf7Serializer($sourceCharset, $targetCharset, $withBasic);
    }

    /**
     * Create a no-op (passthrough) serializer.
     *
     * @return NoneSerializer
     */
    public function createNone(): NoneSerializer
    {
        return new NoneSerializer();
    }

    /**
     * Create a chained serializer from multiple serializers.
     *
     * @param SerializerInterface ...$serializers Serializers to chain
     * @return ChainedSerializer
     * @throws Exception If no serializers provided
     */
    public function createChained(SerializerInterface ...$serializers): ChainedSerializer
    {
        return new ChainedSerializer($serializers);
    }

    /**
     * Create a serializer by legacy mode constant.
     *
     * @param int $mode Horde_Serialize constant (e.g., Horde_Serialize::JSON)
     * @param mixed $params Optional parameters for the serializer
     * @return SerializerInterface
     * @throws Exception If mode is not supported
     */
    public function createByMode(int $mode, mixed $params = null): SerializerInterface
    {
        return match ($mode) {
            \Horde_Serialize::NONE => $this->createNone(),
            \Horde_Serialize::BASIC => $this->createBasic(),
            \Horde_Serialize::JSON => $this->createJson($params),
            \Horde_Serialize::BASE64 => $this->createBase64(),
            \Horde_Serialize::URL => $this->createUrl(false),
            \Horde_Serialize::RAW => $this->createUrl(true),
            \Horde_Serialize::BZIP => $this->createBzip(
                $params['level'] ?? 3,
                $params['workfactor'] ?? 30
            ),
            \Horde_Serialize::GZ_DEFLATE => $this->createGzDeflate($params['level'] ?? 3),
            \Horde_Serialize::GZ_COMPRESS => $this->createGzCompress($params['level'] ?? 3),
            \Horde_Serialize::GZ_ENCODE => $this->createGzEncode($params['level'] ?? 3),
            \Horde_Serialize::LZF => $this->createLzf(),
            \Horde_Serialize::IMAP8 => $this->createImap8(),
            \Horde_Serialize::IMAPUTF7 => $this->createImapUtf7(),
            \Horde_Serialize::IMAPUTF8 => $this->createImapUtf8(),
            \Horde_Serialize::UTF7 => $this->createUtf7($params ?? 'UTF-8'),
            \Horde_Serialize::UTF7_BASIC => $this->createUtf7($params ?? 'UTF-8', $params ?? 'UTF-8', true),
            default => throw new Exception("Unsupported serialization mode: {$mode}"),
        };
    }
}
