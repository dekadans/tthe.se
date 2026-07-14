<?php

namespace App\Commands;

use App\Services\Activity\ActivityRepository;
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
        #[Argument('BOOK or FILM')]
        Type $type,
        #[Argument]
        int $limit = 1
    ): int {
        if ($type === Type::BOOK) {
            $this->testBooks($io, $limit);
        } elseif ($type === Type::FILM) {
            $this->testFilm($io, $limit);
        }
        return Command::SUCCESS;
    }

    private function testBooks(SymfonyStyle $io, int $limit)
    {
        try {
            $books = $this->repository->books($limit);

            foreach ($books as $book) {
                $io->text(json_encode($book));
            }
        } catch (\Throwable $exception) {
            return $exception->getMessage();
        }
    }

    private function testFilm(SymfonyStyle $io, int $limit)
    {
        $films = $this->repository->films($limit);

        foreach ($films as $film) {
            $io->text($film->title);
        }
    }
}
