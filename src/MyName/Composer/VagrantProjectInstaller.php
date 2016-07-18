<?php

namespace MyName\Composer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;

class MyProjectInstaller extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prefix = substr($package->getPrettyName(), 0, strlen('myname/'));
        if ('myname/' !== $prefix) {
            throw new \InvalidArgumentException(
                'Unable to install MyProject. '
                .'It should always start their package name with '
                .'"myname/", but prefix of: '. $package->getPrettyName() . ' is: '.$prefix
            );
        }

        return 'myproject/';
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'my-project' === $packageType;
    }
    
    /**
     * This method is NOT called on "composer remove myname/myproject" - A bug?
     * 
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        echo "You called method: isInstalled()";
        $installPath = $this->getInstallPath($package);
        if ($repo->hasPackage($package) && is_readable($installPath)) {
            return true;
        }
        return false;
    }
    
    /**
     * This method is executed on "composer install" and "composer update"
     * 
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $installPath = $this->getInstallPath($package);

        if (!is_dir($installPath)) {
            mkdir($installPath, 0777, true);
        }
        $this->downloadManager->download($package, $installPath);
    }

    /**
     * This method is NOT exexuted on "composer update" - A bug?
     * 
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        echo "You called method: update()";
        $installPath = $this->getInstallPath($initial);
        $this->filesystem->removeDirectory($installPath);
        $this->install($repo, $target);
    }
    
    #uninstall(), here you can determine the actions that need to be executed when the package needs to be removed.
    /**
     * This method is NOT called on "composer remove myname/campusvm". A bug?
     * Package "myname/myproject" listed for update is not installed. Ignoring.
     * 
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        echo "You called method: uninstall()";

        if (!$repo->hasPackage($package)) {
            throw new \InvalidArgumentException('Package is not installed: '.$package);
        }

        $this->removeCode($package);
        $this->removeBinaries($package);
        $this->removePackage($package);

        $downloadPath = $this->getPackageBasePath($package);
        if (strpos($package->getName(), '/')) {
            $packageVendorDir = dirname($downloadPath);
            if (is_dir($packageVendorDir) && $this->filesystem->isDirEmpty($packageVendorDir)) {
                Silencer::call('rmdir', $packageVendorDir);
            }
        }
    }

}