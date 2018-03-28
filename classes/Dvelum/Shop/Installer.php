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
        // Add permissions
        $userInfo = User::getInstance()->getInfo();
        /**
         * @var \Model_Permissions $permissionsModel
         */
        $permissionsModel = Model::factory('Permissions');
        $modules = ['Dvelum_Shop_Category','Dvelum_Shop_Product','Dvelum_Shop_Goods'];
        foreach ($modules as $module){
            if (!$permissionsModel->setGroupPermissions($userInfo['group_id'], $module, 1, 1, 1, 1)) {
                return false;
            }
        }
        return true;
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