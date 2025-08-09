<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ValidDateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var ValidDate $constraint */

        if (!$constraint instanceof ValidDate) {
            throw new UnexpectedValueException($constraint, ValidDate::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        // Si c'est déjà un objet DateTime, c'est valide
        if ($value instanceof \DateTimeInterface) {
            return;
        }

        // Si ce n'est pas une string, c'est invalide
        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
            return;
        }

        // Tenter de parser la date avec différents formats
        $validFormats = [
            'Y-m-d\TH:i:s.uP',  // 2024-01-15T10:30:00.000000+01:00
            'Y-m-d\TH:i:sP',    // 2024-01-15T10:30:00+01:00
            'Y-m-d\TH:i:s\Z',   // 2024-01-15T10:30:00Z
            'Y-m-d\TH:i:s',     // 2024-01-15T10:30:00
            'Y-m-d H:i:s',      // 2024-01-15 10:30:00
            'Y-m-d',            // 2024-01-15
        ];

        $isValid = false;

        // Essayer les formats définis
        foreach ($validFormats as $format) {
            $dateTime = \DateTime::createFromFormat($format, $value);
            if ($dateTime !== false && $dateTime->format($format) === $value) {
                $isValid = true;
                break;
            }
        }

        // Si aucun format strict ne fonctionne, essayer le constructeur standard
        if (!$isValid) {
            try {
                new \DateTime($value);
                $isValid = true;
            } catch (\Exception $e) {
                $isValid = false;
            }
        }

        if (!$isValid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
