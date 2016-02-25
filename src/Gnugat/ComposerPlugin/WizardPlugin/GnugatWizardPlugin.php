<?php

/*
 * This file is part of the GnugatWizardPlugin project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\ComposerPlugin\WizardPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Installer\PackageEvent;

use Gnugat\ComposerPlugin\WizardPlugin\DependencyInjection\Factory;

/**
 * On package installation, checks wether or not it's a bundle and if so
 * register it in the application's kernel.
 *
 * @author Loïc Chardonnet <loic.chardonnet@gmail.com>
 */
class GnugatWizardPlugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_PACKAGE_INSTALL => array(
                array('installPackage', 0)
            ),
            ScriptEvents::POST_PACKAGE_UPDATE => array(
                array('updatePackage', 0)
            ),
        );
    }

    /**
 * On Composer's "post-package-install" event, register the package.
 *
 * @param PackageEvent $event
 */
    public function installPackage(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $this->registerPackage($event,$package);
    }

    /**
     * On Composer's "post-package-update" event, register the package.
     *
     * @param PackageEvent $event
     */
    public function updatePackage(PackageEvent $event)
    {
        $package = $event->getOperation()->getTargetPackage();
        $this->registerPackage($event,$package);
    }

    /**
     * Register package after update or install
     *
     * @param PackageEvent $event
     * @param PackageInterface $package
     */
    public function registerPackage(PackageEvent $event, PackageInterface $package)
    {
        $output = $event->getIo();

        if (!$this->supports($package)) {
            return;
        }
        try {
            $this->enablePackage($package);
        } catch (\RuntimeException $e) {
            $output->write(sprintf('Bundle "%s" is already registered', $package));
        }
    }

    /**
     * Checks if the context is supported.
     *
     * @param PackageInterface $package
     *
     * @return bool
     */
    public function supports(PackageInterface $package)
    {
        $isSymfony2Bundle = ('symfony-bundle' === $package->getType());

        return $isSymfony2Bundle;
    }

    /**
     * @param PackageInterface $package
     *
     * @throws \RuntimeException If an error occured during the running
     */
    public function enablePackage(PackageInterface $package)
    {
        $packageRepository = Factory::makePackageRepository($package);
        $bundleFactory = Factory::makeBundleFactory();
        $kernelManipulator = Factory::makeKernelManipulator();

        $composerPackage = $packageRepository->findOneByName($package->getName());
        $bundle = $bundleFactory->make($composerPackage->namespace);

        $kernelManipulator->addBundle($bundle->fullyQualifiedClassname);
    }
}
