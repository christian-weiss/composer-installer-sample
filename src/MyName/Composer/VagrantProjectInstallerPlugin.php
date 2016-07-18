<?php

namespace MyName\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class MyProjectInstallerPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new MyProjectInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}