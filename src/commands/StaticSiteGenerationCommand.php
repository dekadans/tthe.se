<?php

namespace tthe\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class StaticSiteGenerationCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate-site');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fs = new Filesystem();

        $twig = new Environment(new FilesystemLoader(__DIR__.'/../templates'));
        $template = $twig->load('index.html.twig');

        $data = json_decode(
            $fs->readFile(__DIR__.$_ENV['DATA_FILE']),
            associative: true
        );

        $rendered_site = $template->render($data);

        $fs->dumpFile(__DIR__.$_ENV['STATIC_FILE'], $rendered_site);

        $output->writeln("Done!");
        return Command::SUCCESS;
    }
}