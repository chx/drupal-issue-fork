<?php

namespace chx\Composer;

use Composer\Command\BaseCommand;
use Composer\Console\Input\InputArgument;
use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrupalIssueForkCommand extends BaseCommand
{

    protected function configure()
    {
        $this
            ->setName('drupal-issue-fork')
            ->setDescription('Sets a Drupal project to use an issue fork')
            ->setDefinition([
              new InputArgument('fork-url', InputArgument::REQUIRED, 'An issue fork URL like https://git.drupalcode.org/issue/brandfolder-3286340/-/tree/3286340-automated-drupal-10')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        if (!preg_match('#(?P<url>https://git.drupalcode.org/issue/(?P<project>.*)-\d+)/-/tree/(?P<branch>.*)$#', $input->getArgument('fork-url'), $matches)) {
            $io->writeError("Can't parse URL argument, it should be something like https://git.drupalcode.org/issue/brandfolder-3286340/-/tree/3286340-automated-drupal-10\n");
            return 1;
        }
        ['project' => $project, 'url' => $url, 'branch' => $branch] = $matches;
        $project = "drupal/$project";
        $forkRepository = [
            'type' => 'git',
            'url' =>  "$url.git",
        ];
        $found = FALSE;
        $fileName = Factory::getComposerFile();
        $file = new JsonFile($fileName);
        $config = $file->read();
        foreach ($config['repositories'] as &$repo) {
            if (($repo['url'] ?? '') === 'https://packages.drupal.org/8') {
                $repo['exclude'][] = $project;
                $repo['exclude'] = array_unique($repo['exclude']);
            }
            $found = $found || ($repo['type'] === 'git' && $repo['url'] === $forkRepository['url']);
        }
        if (!$found) {
            $config['repositories'][] = $forkRepository;
        }
        if (isset($config['require-dev'][$project])) {
          $config['require-dev'][$project] = "dev-$branch";
        }
        else {
          $config['require'][$project] = "dev-$branch";
        }
        $this->writeConfig($file, $config);
        $io->writeError('<info>'.$fileName.' has been updated</info>');
        return 0;
    }

    protected function writeConfig($file, $config) {
        // Copy-paste from JsonConfigSource.
        foreach (['require', 'require-dev', 'conflict', 'provide', 'replace', 'suggest', 'config', 'autoload', 'autoload-dev', 'scripts', 'scripts-descriptions', 'support'] as $prop) {
            if (isset($config[$prop]) && $config[$prop] === []) {
                $config[$prop] = new \stdClass;
            }
        }
        foreach (['psr-0', 'psr-4'] as $prop) {
            if (isset($config['autoload'][$prop]) && $config['autoload'][$prop] === []) {
                $config['autoload'][$prop] = new \stdClass;
            }
            if (isset($config['autoload-dev'][$prop]) && $config['autoload-dev'][$prop] === []) {
                $config['autoload-dev'][$prop] = new \stdClass;
            }
        }
        foreach (['platform', 'http-basic', 'bearer', 'gitlab-token', 'gitlab-oauth', 'github-oauth', 'preferred-install'] as $prop) {
            if (isset($config['config'][$prop]) && $config['config'][$prop] === []) {
                $config['config'][$prop] = new \stdClass;
            }
        }
        $file->write($config);
    }
}
