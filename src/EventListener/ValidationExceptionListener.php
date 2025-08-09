<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 1000)]
class ValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Intercepter les erreurs de parsing de dates
        if ($exception instanceof NotNormalizableValueException) {
            $message = $exception->getMessage();

            // Vérifier si c'est une erreur de date/timezone
            if (
                str_contains($message, 'Failed to parse time string') ||
                str_contains($message, 'timezone could not be found')
            ) {

                // Extraire la valeur erronée du message d'erreur
                preg_match('/\(([^)]+)\)/', $message, $matches);
                $invalidValue = $matches[1] ?? 'valeur invalide';

                $response = new JsonResponse([
                    '@context' => '/api/contexts/ConstraintViolationList',
                    '@id' => '/api/validation_errors/hiringDate',
                    '@type' => 'ConstraintViolationList',
                    'title' => 'An error occurred',
                    'detail' => sprintf(
                        'hiringDate: La date d\'embauche "%s" n\'est pas au format valide. Formats acceptés: "2024-01-15", "2024-01-15T10:30:00", "2024-01-15T10:30:00Z", etc.',
                        $invalidValue
                    ),
                    'status' => 422,
                    'violations' => [
                        [
                            'propertyPath' => 'hiringDate',
                            'message' => sprintf(
                                'La date d\'embauche "%s" n\'est pas au format valide. Formats acceptés: "2024-01-15", "2024-01-15T10:30:00", "2024-01-15T10:30:00Z", etc.',
                                $invalidValue
                            ),
                            'code' => null
                        ]
                    ]
                ], 422);

                $event->setResponse($response);
                return;
            }
        }

        // Intercepter aussi les autres erreurs de format pour les DateTimeImmutable
        if ($exception instanceof \Exception) {
            $message = $exception->getMessage();
            if (
                str_contains($message, 'DateTimeImmutable') &&
                (str_contains($message, 'Failed to parse') || str_contains($message, 'timezone'))
            ) {

                $response = new JsonResponse([
                    '@context' => '/api/contexts/ConstraintViolationList',
                    '@id' => '/api/validation_errors/hiringDate',
                    '@type' => 'ConstraintViolationList',
                    'title' => 'An error occurred',
                    'detail' => 'hiringDate: La date d\'embauche doit être au format valide (ex: "2024-01-15", "2024-01-15T10:30:00", "2024-01-15T10:30:00Z")',
                    'status' => 422,
                    'violations' => [
                        [
                            'propertyPath' => 'hiringDate',
                            'message' => 'La date d\'embauche doit être au format valide (ex: "2024-01-15", "2024-01-15T10:30:00", "2024-01-15T10:30:00Z")',
                            'code' => null
                        ]
                    ]
                ], 422);

                $event->setResponse($response);
                return;
            }
        }
    }
}
