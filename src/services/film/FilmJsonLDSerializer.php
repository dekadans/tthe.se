<?php

namespace tthe\services\film;

class FilmJsonLDSerializer
{
    /**
     * @param FilmActivity[] $films
     * @return array
     */
    public static function make(array $films): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => array_map(self::makeFilm(...), $films)
        ];
    }

    private static function makeFilm(FilmActivity $film): array
    {
        return [
            '@type' => 'WatchAction',
            'endTime' => $film->date->format('c'),
            'url' => $film->url,
            'object' => [
                '@type' => 'Movie',
                'name' => $film->title,
                'temporal' => $film->filmYear,
                'thumbnailUrl' => $film->posterUrl,
                'sameAs' => "https://www.themoviedb.org/movie/{$film->externalId}"
            ],
            'result' => $film->rating ? [
                '@type' => 'Review',
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $film->rating
                ]
            ] : null
        ];
    }
}