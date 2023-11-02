<?php

namespace chx\Composer;

use Composer\Plugin\Capability\CommandProvider;

class DrupalIssueForkCommandProvider implements CommandProvider
{

    public function getCommands()
    {
        return [
            new DrupalIssueForkCommand(),
            new DrupalIssueUnForkCommand(),
        ];
    }
}
