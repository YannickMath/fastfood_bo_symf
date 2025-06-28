<?php
namespace App\EventListener;

use App\Entity\Product;
use App\Exception\ProductCannotBeUpdatedException;
use App\Exception\ProductNotDeletedException;
use App\Exception\ProductNotFoundException;
use App\Exception\ProductNotPossibleToCreateException;
use App\Exception\ProductWithNoCategoryException;
use App\Exception\UserAlreadyExistsException;
use App\Exception\UserEmailAlreadyExistsException;
use App\Exception\UserLoginException;
use App\Exception\UserNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
    //     $exception = $event->getThrowable();

    //     if ($exception instanceof ProductNotFoundException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_NOT_FOUND)); // 404
    //         return;
    //     }

    //     if ($exception instanceof UserAlreadyExistsException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_CONFLICT)); // 409
    //         return;
    //     } 

    //     if ($exception instanceof UserEmailAlreadyExistsException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_CONFLICT)); // 409
    //         return;
    //     }

    //     if ($exception instanceof UserNotFoundException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_NOT_FOUND)); // 404
    //         return;
    //     }

    //     if ($exception instanceof UserLoginException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_UNAUTHORIZED)); // 401
    //         return;
    //     }

    //     if ($exception instanceof ProductCannotBeUpdatedException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST)); // 400
    //         return;
    //     }

    //     if($exception instanceof ProductNotDeletedException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST)); // 400
    //         return;
    //     }

    //     if($exception instanceof ProductNotPossibleToCreateException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST)); // 400
    //         return;
    //     }

    //     if ($exception instanceof ProductNotDeletedException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST)); // 400
    //         return;
    //     }

    //     if ($exception instanceof ProductWithNoCategoryException) {
    //         $event->setResponse(new JsonResponse([
    //             'error' => $exception->getMessage(),
    //         ], Response::HTTP_BAD_REQUEST)); // 400
    //         return;
    //     }
    // }

        $exception = $event->getThrowable();
        $statusCode = match (true) {
            $exception instanceof ProductWithNoCategoryException => Response::HTTP_BAD_REQUEST,
            $exception instanceof ProductNotPossibleToCreateException => Response::HTTP_BAD_REQUEST,
            $exception instanceof ProductNotDeletedException => Response::HTTP_BAD_REQUEST,
            $exception instanceof ProductCannotBeUpdatedException => Response::HTTP_BAD_REQUEST,
            $exception instanceof UserLoginException => Response::HTTP_UNAUTHORIZED,
            $exception instanceof UserNotFoundException => Response::HTTP_NOT_FOUND,
            $exception instanceof UserEmailAlreadyExistsException => Response::HTTP_CONFLICT,
            $exception instanceof UserAlreadyExistsException => Response::HTTP_CONFLICT,
            $exception instanceof ProductNotFoundException => Response::HTTP_NOT_FOUND,
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };

        $event->setResponse(new JsonResponse([
            'error' => $exception->getMessage(),
        ], $statusCode));
    }
}