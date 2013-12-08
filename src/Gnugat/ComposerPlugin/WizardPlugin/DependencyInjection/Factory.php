<?php

/*
 * This file is part of the GnugatWizardPlugin project.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\ComposerPlugin\WizardPlugin\DependencyInjection;

use Composer\Package\PackageInterface;

use Gnugat\Bundle\WizardBundle\Factory\BundleFactory;
use Gnugat\Bundle\WizardBundle\Provider\ComposerEventPackage;
use Gnugat\Bundle\WizardBundle\Repository\ComposerPackageRepository;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

/**
 * Creates services for the composer plugin.
 *
 * @author Loïc Chardonnet <loic.chardonnet@gmail.com>
 */
class Factory
{
    /**
     * @param PackageInterface $package
     *
     * @return ComposerPackageRepository
     */
    public static function makePackageRepository(PackageInterface $package)
    {
        $provider = new ComposerEventPackage($package);

        return new ComposerPackageRepository($provider);
    }

    /**
     * @return BundleFactory
     */
    public static function makeBundleFactory()
    {
        return new BundleFactory();
    }

    /**
     * @return KernelManipulator
     */
    public static function makeKernelManipulator()
    {
        require_once 'app/AppKernel.php';

        $kernel = new \AppKernel('dev', true);

        return new KernelManipulator($kernel);
    }
}
