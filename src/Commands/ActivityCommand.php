<?php

namespace App\Commands;

use App\Services\Activity\ActivityRepository;
use App\Services\Activity\ItemList;
use App\Services\Activity\Type;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('activity')]
class ActivityCommand extends Command
{
    public function __construct(
        private ActivityRepository $repository,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('The activity type: BOOK or FILM')]
        Type $type,
        #[Argument('The number of items to display.')]
        int $limit = 1
    ): int {
        $list = $this->repository->getForType($type, $limit);

        if ($type === Type::BOOK) {
            $this->printBooks($io, $list);
        } elseif ($type === Type::FILM) {
            $this->printFilms($io, $list);
        } elseif ($type === Type::CODE) {
            $this->printCode($io, $list);
        }
        return Command::SUCCESS;
    }

    private function printBooks(SymfonyStyle $io, ItemList $list): void
    {
        $io->title($list->name);
        $io->table(
            ['Date', 'Title', 'Year', 'Author'],
            array_map(function ($b) {
                return [
                    $b->timestamp->format('Y-m-d'),
                    $b->title,
                    $b->attributes['year'],
                    $b->attributes['author'],
                ];
            }, $list->toArray()),
        );
    }

    private function printFilms(SymfonyStyle $io, ItemList $list): void
    {
        $io->title($list->name);
        $io->table(
            ['Date', 'Title', 'Year', 'Rating'],
            array_map(function ($f) {
                return [
                    $f->timestamp->format('Y-m-d'),
                    $f->title,
                    $f->attributes['year'],
                    $f->attributes['rating'],
                ];
            }, $list->toArray()),
        );
    }

    private function printCode(SymfonyStyle $io, ItemList $list): void
    {
        $io->title($list->name);
        $io->table(
            ['Date', 'Title', 'Language'],
            array_map(function ($r) {
                return [
                    $r->timestamp->format('Y-m-d'),
                    $r->title,
                    $r->attributes['language'],
                ];
            }, $list->toArray()),
        );
    }
}
