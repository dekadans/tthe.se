<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand('routes', 'Prints all registered routes.')]
class RoutesCommand extends Command
{
    public function __construct(private readonly RouterInterface $router)
    {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Filter by path prefix', shortcut: 'p')]
        ?string $prefix = null
    ): int {
        [$headers, $rows] = $this->getRouteTable();

        if ($prefix) {
            $rows = array_filter($rows, fn($r) => str_starts_with($r[1], $prefix));
        }

        if (!$rows) {
            $io->error('No routes were found!');
            return Command::FAILURE;
        }

        $io->table($headers, $rows);
        return Command::SUCCESS;
    }

    private function getRouteTable(): array
    {
        $routes = [];
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            $routes[] = [
                $name,
                $route->getPath(),
                implode(',', $route->getMethods()) ?: '*',
                $route->getDefault('_controller'),
            ];
        }

        usort($routes, fn($a, $b) => $a[1] <=> $b[1]);

        return [
            ['Name', 'Path', 'Methods', 'Controller'],
            $routes,
        ];
    }
}
