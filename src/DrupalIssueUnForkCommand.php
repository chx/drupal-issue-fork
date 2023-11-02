<?php

namespace chx\Composer;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\Package\Version\VersionParser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DrupalIssueUnForkCommand extends BaseCommand
{

    protected function configure()
    {
        $this->setName('drupal-issue-un-fork');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = new JsonFile(Factory::getComposerFile(), null, $this->getIO);
        $configSource = new JsonConfigSource($configFile);
        print count($configFile->read()['repositories']);
        return 0;
    }
}
