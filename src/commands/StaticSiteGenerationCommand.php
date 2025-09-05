<?php

namespace tthe\commands;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tthe\framework\FileManager;
use tthe\services\posts\PostRepository;
use Twig\Environment;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;

class StaticSiteGenerationCommand extends Command
{
    private Environment $twig;

    public function __construct(
        protected FileManager $files,
        protected PostRepository $postRepository
    )
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__.'/../templates'));
        $this->twig->addExtension(new IntlExtension());
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('generate-site');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generateCV($output);
        $this->generatePosts($output);
        return Command::SUCCESS;
    }

    private function generateCV(OutputInterface $output): void
    {
        $template = $this->twig->load('me.html.twig');

        $data = json_decode(
            $this->files->read($_ENV['CV_DATA']),
            associative: true
        );

        $renderedSite = $template->render($data);

        $this->files->write($_ENV['CV_GENERATED'], $renderedSite);

        $output->writeln("Generated CV.");
    }

    private function generatePosts(OutputInterface $output): void
    {
        $posts = $this->postRepository->getAll();
        $template = $this->twig->load('post.html.twig');

        $markdown = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        foreach ($posts as $post) {
            if ($post->contentType == 'text/markdown') {
                $parsedContent = $markdown->convert($post->content);
            } else {
                $parsedContent = $post->content;
            }

            $data = ['post' => $post, 'content' => $parsedContent];
            $renderedPost = $template->render($data);
            $path = $_ENV['POST_GENERATED'] . $post->id . '.html';

            $this->files->write($path, $renderedPost);

            $output->writeln("Generated post with id {$post->id}.");
        }
    }
}