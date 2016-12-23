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

namespace Globalpay;

/**
 * Class Tool
 * @package Globalpay
 */
class Tool
{
    /**
     * Get all supported payment gateways
     *
     * @return mixed
     */
    public static function getSupportedGateways()
    {
        $package = json_decode(file_get_contents(PIMCORE_PLUGINS_PATH . '/Globalpay/composer.json'), true);

        return $package['extra']['gateways'];
    }
}
