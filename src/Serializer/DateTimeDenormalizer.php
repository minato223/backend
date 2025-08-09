<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class DateTimeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!is_string($data)) {
            throw new InvalidArgumentException('La date d\'embauche doit être une chaîne de caractères au format valide (ex: "2024-01-15T10:30:00Z" ou "2024-01-15")');
        }

        // Vérifier si c'est un format de date valide
        $validFormats = [
            'Y-m-d\TH:i:s.uP',  // 2024-01-15T10:30:00.000000+01:00
            'Y-m-d\TH:i:sP',    // 2024-01-15T10:30:00+01:00
            'Y-m-d\TH:i:s\Z',   // 2024-01-15T10:30:00Z
            'Y-m-d\TH:i:s',     // 2024-01-15T10:30:00
            'Y-m-d H:i:s',      // 2024-01-15 10:30:00
            'Y-m-d',            // 2024-01-15
            'c',                // ISO 8601
        ];

        $dateTime = null;
        foreach ($validFormats as $dateFormat) {
            $dateTime = \DateTimeImmutable::createFromFormat($dateFormat, $data);
            if ($dateTime !== false) {
                break;
            }
        }

        // Si aucun format n'est valide, essayer le constructeur standard
        if ($dateTime === false) {
            try {
                $dateTime = new \DateTimeImmutable($data);
            } catch (\Exception $e) {
                throw new InvalidArgumentException(
                    sprintf(
                        'La date d\'embauche "%s" n\'est pas au format valide. Formats acceptés: "2024-01-15", "2024-01-15T10:30:00", "2024-01-15T10:30:00Z", etc.',
                        $data
                    )
                );
            }
        }

        return $dateTime;
    }

    function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === \DateTimeImmutable::class;
    }

    function getSupportedTypes(?string $format): array
    {
        return [
            \DateTimeImmutable::class
        ];
    }
}
