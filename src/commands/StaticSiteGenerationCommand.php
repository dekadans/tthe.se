<?php

namespace tthe\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tthe\framework\FileManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class StaticSiteGenerationCommand extends Command
{
    public function __construct(
        protected FileManager $files
    )
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('generate-site');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $twig = new Environment(new FilesystemLoader(__DIR__.'/../templates'));
        $template = $twig->load('me.html.twig');

        $data = json_decode(
            $this->files->read($_ENV['DATA_FILE']),
            associative: true
        );

        $rendered_site = $template->render($data);

        $this->files->write($_ENV['STATIC_FILE'], $rendered_site);

        $output->writeln("Done!");
        return Command::SUCCESS;
    }
}