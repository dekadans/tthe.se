<?php

namespace App\Commands;

use Google\Client;
use Google\Service\Sheets;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('activity')]
class ActivityCommand extends Command
{
    public function __construct(private FileLocatorInterface $locator)
    {
        parent::__construct();
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->text($this->testBooks());
        return Command::SUCCESS;
    }

    private function testBooks()
    {
        $client = new Client();
        $key = $this->locator->locate($_ENV['GOOGLE_APPLICATION_CREDENTIALS']);
        $client->setAuthConfig($key);
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        try {
            $sheetsApi = new Sheets($client);
            $sheetData = $sheetsApi->spreadsheets_values->get($_ENV['SPREADSHEET_ID'], $_ENV['SPREADSHEET_RANGE']);
            [$title, $author, $year] = $sheetData->values[0];

            return $title;
        } catch (\Throwable $exception) {
            return $exception->getMessage();
        }
    }
}