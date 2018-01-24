<?php
namespace Dvelum\Shop;

use Dvelum\Config\ConfigInterface;
use Dvelum\App\Session\User;
use Dvelum\Orm\Model;
use Dvelum\Orm\Record;
use Dvelum\Externals;

class Installer extends Externals\Installer
{
    /**
     * Install
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return bool
     */
    public function install(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig)
    {

    }

    /**
     * Uninstall
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return bool
     */
    public function uninstall(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig)
    {

    }
}