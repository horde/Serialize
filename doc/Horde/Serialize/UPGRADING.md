# Upgrading to Modern API

## Quick Migration

### Old API (lib/ - PSR-0)
```php
// Static methods, magic constants
$json = Horde_Serialize::serialize($data, Horde_Serialize::JSON);
$data = Horde_Serialize::unserialize($json, Horde_Serialize::JSON);

// Chained modes via arrays
$encoded = Horde_Serialize::serialize(
    $data,
    [Horde_Serialize::BASIC, Horde_Serialize::GZ_COMPRESS, Horde_Serialize::BASE64]
);
```

### New API (src/ - PSR-4)
```php
use Horde\Serialize\SerializerFactory;

$factory = new SerializerFactory();

// Single serializer
$serializer = $factory->createJson();
$json = $serializer->serialize($data);
$data = $serializer->unserialize($json);

// Chained serializers
$chained = $factory->createChained(
    $factory->createBasic(),
    $factory->createGzCompress(),
    $factory->createBase64()
);
$encoded = $chained->serialize($data);
```

## Available Serializers

| Factory Method | Purpose |
|----------------|---------|
| `createJson()` | JSON encoding |
| `createBasic()` | PHP serialize() |
| `createBase64()` | Base64 encoding |
| `createUrl($raw)` | URL encoding (raw=false) or raw (raw=true) |
| `createBzip($level)` | BZip2 compression |
| `createGzDeflate($level)` | Gzip deflate |
| `createGzCompress($level)` | Gzip compress |
| `createGzEncode($level)` | Gzip encode (RFC 1952) |
| `createLzf()` | LZF compression |
| `createImap8()` | Quoted-printable |
| `createUtf7()` | UTF-7 charset conversion |
| `createNone()` | No-op passthrough |
| `createChained()` | Chain multiple serializers |

## Legacy Compatibility

Old `Horde_Serialize` class remains functional. No breaking changes.

Both APIs can coexist during migration.
