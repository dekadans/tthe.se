<?php

namespace App\Controllers;

use App\Services\Activity\ActivityRepository;
use App\Services\Activity\Transformers\BookTransformer;
use App\Services\Activity\Transformers\FilmTransformer;
use App\Services\Activity\Transformers\ListTransformer;
use App\Services\Activity\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $limit = 10;

        [$list, $resourceTransformer] = match ($type) {
            Type::FILM => [
                $this->repository->films($limit),
                new FilmTransformer(),
            ],
            Type::BOOK => [
                $this->repository->books($limit),
                new BookTransformer(),
            ],
            default => throw new NotFoundHttpException('This type of activity is not yet supported.')
        };

        return new JsonResponse($this->transformer->transform($list, $resourceTransformer));
    }

    #[Get('/{type}/widget', 'activity-widget')]
    public function widget(Type $type): Response
    {
        $limit = 3;
        $list = match ($type) {
            Type::FILM => $this->repository->films($limit),
            Type::BOOK => $this->repository->books($limit),
            default => throw new NotFoundHttpException('This type of activity is not yet supported.')
        };

        $html = $this->twig->render('activity/widget.html.twig', [
            'list' => $list,
            'type' => $type->value,
        ]);

        return new Response($html);
    }
}
