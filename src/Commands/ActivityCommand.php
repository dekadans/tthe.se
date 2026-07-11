<?php

namespace App\Commands;

use App\Services\Activity\ActivityReaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('activity')]
class ActivityCommand extends Command
{
    public function __construct(private ActivityReaderInterface $bookActivity)
    {
        parent::__construct();
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $this->testBooks($io);
        return Command::SUCCESS;
    }

    private function testBooks(SymfonyStyle $io)
    {
        try {
            $books = $this->bookActivity->read(50);

            foreach ($books as $book) {
                $io->text(json_encode($book));
            }
        } catch (\Throwable $exception) {
            return $exception->getMessage();
        }
    }
}