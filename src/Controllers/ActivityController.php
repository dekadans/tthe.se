<?php

namespace App\Controllers;

use App\Services\Activity\ActivityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use tthe\Bagatelle\Http\Attribute\Get;

#[Route('/activity')]
class ActivityController
{
    public function __construct(private ActivityRepository $repository) {}

    #[Get('/watch', 'activity-watch')]
    public function watch(): JsonResponse
    {
        return new JsonResponse([]);
    }

    #[Get('/read', 'activity-read')]
    public function read(): JsonResponse
    {
        return new JsonResponse([]);
    }
}
