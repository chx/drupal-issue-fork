<?php

namespace chx\Composer\Plugin;

use chx\Composer\DrupalIssueForkCommandProvider;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class DrupalIssueFork implements PluginInterface, Capable
{

    public function activate(Composer $composer, IOInterface $io)
    {
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public function getCapabilities()
    {
        return [
            CommandProvider::class => DrupalIssueForkCommandProvider::class,
        ];
    }
}
