<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('example', 'Just an example')]
class ExampleCommand extends Command
{
    public function __invoke(
        SymfonyStyle $io
    ): int {
        $io->text('Example command');
        return Command::SUCCESS;
    }
}
