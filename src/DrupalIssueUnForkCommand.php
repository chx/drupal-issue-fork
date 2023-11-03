<?php

namespace chx\Composer;

use Composer\Command\BaseCommand;
use Composer\Console\Input\InputArgument;
use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DrupalIssueUnForkCommand extends DrupalIssueForkCommand
{

    protected function configure()
    {
        $this->setName('drupal-issue-unfork')
            ->setDescription('Removes an issue fork repo from a Drupal project and uses latest version instead.')
            ->setDefinition([
              new InputArgument('drupal-project', InputArgument::REQUIRED, 'A Drupal project name like homebox.')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getIO();
        $project = $input->getArgument('drupal-project');
        $fileName = Factory::getComposerFile();
        $file = new JsonFile($fileName);
        $config = $file->read();
        foreach ($config['repositories'] as $key => &$repo) {
            if (empty($repo['url'])) {
                continue;
            }
            if ($repo['url'] === 'https://packages.drupal.org/8' && !empty($repo['exclude'])) {
                $repo['exclude'] = array_diff($repo['exclude'], ["drupal/$project"]);
                if (!$repo['exclude']) {
                    unset($repo['exclude']);
                }
            }
            if (preg_match("#^https://git.drupalcode.org/issue/$project-\d+\.git$#", $repo['url'])) {
                unset($config['repositories'][$key]);
            }
        }
        $this->writeConfig($file, $config);
        $args = [
            'command' => 'show',
            'package' => "drupal/$project",
            '--available' => TRUE,
            '--format' => 'json',
        ];
        $showOutput = new BufferedOutput();
        $this->getApplication()->doRun(new ArrayInput($args), $showOutput);
        // There can be warnings before the actual output.
        preg_match('/\{.*\}$/s', $showOutput->fetch(), $matches);
        $version = json_decode($matches[0], TRUE)['versions'][0];
        $version = preg_replace('/(\d+\.\d+)\.\d+$/', '\1', $version);
        $args = [
            'command' => 'require',
            'packages' => ["drupal/$project:~$version"],
            '--no-update' => true,
        ];
        $this->getApplication()->run(new ArrayInput($args), $output);
        return 0;
    }
}
