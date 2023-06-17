<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\SystemEnvironmentBuilder as TestingSystemEnvironmentBuilder;
use TYPO3\TestingFramework\Core\Testbase;

(static function (): void {
    $testbase = new Testbase();

    // These if's are for core testing (package typo3/cms) only. cms-composer-installer does
    // not create the autoload-include.php file that sets these env vars and sets composer
    // mode to true. testing-framework can not be used without composer anyway, so it is safe
    // to do this here. This way it does not matter if 'bin/phpunit' or 'vendor/phpunit/phpunit/phpunit'
    // is called to run the tests since the 'relative to entry script' path calculation within
    // SystemEnvironmentBuilder is not used. However, the binary must be called from the document
    // root since getWebRoot() uses 'getcwd()'.
    if (!getenv('TYPO3_PATH_ROOT')) {
        putenv('TYPO3_PATH_ROOT=' . rtrim($testbase->getWebRoot(), '/'));
    }
    if (!getenv('TYPO3_PATH_WEB')) {
        putenv('TYPO3_PATH_WEB=' . rtrim($testbase->getWebRoot(), '/'));
    }

    $testbase->defineSitePath();

    // We can use the "typo3/cms-composer-installers" constant "TYPO3_COMPOSER_MODE" to determine composer mode.
    // This should be always true except for TYPO3 mono repository.
    $composerMode = defined('TYPO3_COMPOSER_MODE') && TYPO3_COMPOSER_MODE === true;
    $requestType = SystemEnvironmentBuilder::REQUESTTYPE_BE | SystemEnvironmentBuilder::REQUESTTYPE_CLI;
    TestingSystemEnvironmentBuilder::run(0, $requestType, $composerMode);

    $testbase->createDirectory(Environment::getPublicPath() . '/typo3conf/ext');
    $testbase->createDirectory(Environment::getPublicPath() . '/typo3temp/assets');
    $testbase->createDirectory(Environment::getPublicPath() . '/typo3temp/var/tests');
    $testbase->createDirectory(Environment::getPublicPath() . '/typo3temp/var/transient');

    // Retrieve an instance of class loader and inject to core bootstrap
    $classLoader = require $testbase->getPackagesPath() . '/autoload.php';
    Bootstrap::initializeClassLoader($classLoader);

    // Initialize default TYPO3_CONF_VARS
    $configurationManager = new ConfigurationManager();
    $GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

    $cache = new PhpFrontend(
        'core',
        new NullBackend('production', [])
    );

    // Set all packages to active
    $packageManager = Bootstrap::createPackageManager(
        PackageManager::class,
        Bootstrap::createPackageCache($cache)
    );

    GeneralUtility::setSingletonInstance(
        PackageManager::class,
        $packageManager
    );
    ExtensionManagementUtility::setPackageManager($packageManager);

    GeneralUtility::purgeInstances();
})();
