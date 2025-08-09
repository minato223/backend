<?php

namespace App\Serializer;

use App\Entity\Employee;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class EmployeeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        // Si la hiringDate est une string invalide, on la remplace par null
        // pour laisser la validation personnalisée s'en charger
        if (isset($data['hiringDate']) && is_string($data['hiringDate'])) {
            $validFormats = [
                'Y-m-d\TH:i:s.uP',
                'Y-m-d\TH:i:sP',
                'Y-m-d\TH:i:s\Z',
                'Y-m-d\TH:i:s',
                'Y-m-d H:i:s',
                'Y-m-d',
            ];

            $isValidDate = false;
            foreach ($validFormats as $dateFormat) {
                if (\DateTime::createFromFormat($dateFormat, $data['hiringDate']) !== false) {
                    $isValidDate = true;
                    break;
                }
            }

            // Essayer aussi le constructeur standard
            if (!$isValidDate) {
                try {
                    new \DateTime($data['hiringDate']);
                    $isValidDate = true;
                } catch (\Exception $e) {
                    // Stocker la valeur originale pour la validation
                    $context['invalid_hiring_date'] = $data['hiringDate'];
                    $data['hiringDate'] = null;
                }
            }
        }

        // Déléguer au denormalizer par défaut
        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Employee::class;
    }

    function getSupportedTypes(?string $format): array
    {
        return [
            Employee::class
        ];
    }
}
