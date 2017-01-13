<?php
/**
 * w-vision
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016 Woche-Pass AG (http://www.w-vision.ch)
 * @license    GNU General Public License version 3 (GPLv3)
 */

namespace Globalpay\Plugin;

use Pimcore\Model\Object;
use Pimcore\Tool;

class Install
{
    /**
     * Install CoreShop
     *
     * @return bool
     */
    public function fullInstall()
    {
        $this->createClass('GlobalpayPayment');

        return true;
    }

    /**
     * creates a mew Class if it doesn't exists.
     *
     * @param $className
     * @param bool $updateClass should class be updated if it already exists
     *
     * @return mixed|Object\ClassDefinition
     */
    public function createClass($className, $updateClass = false)
    {
        $class = Object\ClassDefinition::getByName($className);

        if (!$class || $updateClass) {
            $jsonFile = PIMCORE_PLUGINS_PATH."/Globalpay/install/class-$className.json";
            $json = file_get_contents($jsonFile);

            if (!$class) {
                $class = Object\ClassDefinition::create();
            }

            $class->setName($className);
            $class->setUserOwner($this->_getUserId());

            Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, true);

            return $class;
        }

        return $class;
    }

    /**
     * @return \Int User Id
     */
    protected function _getUserId()
    {
        $userId = 0;
        $user = Tool\Admin::getCurrentUser();
        if ($user) {
            $userId = $user->getId();
        }

        return $userId;
    }
}
