<?php

namespace App\Controllers;

use App\Services\Activity\ActivityRepository;
use App\Services\Activity\Transformers\BookTransformer;
use App\Services\Activity\Transformers\CodeTransformer;
use App\Services\Activity\Transformers\FilmTransformer;
use App\Services\Activity\Transformers\ListTransformer;
use App\Services\Activity\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use tthe\Bagatelle\Http\Attribute\Get;
use Twig\Environment;

#[Route('/activity')]
class ActivityController
{
    public function __construct(
        private ActivityRepository $repository,
        private ListTransformer $transformer,
        private Environment $twig
    ) {}

    #[Get('/{type}', 'activity-json')]
    public function json(Type $type): JsonResponse
    {
        $resourceTransformer = match ($type) {
            Type::FILM => new FilmTransformer(),
            Type::BOOK => new BookTransformer(),
            Type::CODE => new CodeTransformer()
        };

        $list = $this->repository->getForType($type, 10);
        return new JsonResponse($this->transformer->transform($list, $resourceTransformer));
    }

    #[Get('/{type}/widget', 'activity-widget')]
    public function widget(Type $type): Response
    {
        $clientCache = (int) $_ENV['ACTIVITY_WIDGET_CACHE_TTL'] ?? 60;
        $list = $this->repository->getForType($type, 3);

        $html = $this->twig->render('activity/widget.html.twig', [
            'list' => $list,
            'type' => $type->value,
        ]);

        return new Response($html)->setMaxAge($clientCache);
    }
}
