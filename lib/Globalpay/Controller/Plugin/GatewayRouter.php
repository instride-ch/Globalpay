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

namespace Globalpay\Controller\Plugin;

use Pimcore\Model\Staticroute;

/**
 * Class TemplateRouter
 *
 * @package Globalpay\Controller\Plugin
 */
class GatewayRouter extends \Zend_Controller_Plugin_Abstract
{
    /**
     * Checks if Controller is available in Template and use it.
     *
     * @param \Zend_Controller_Request_Abstract $request
     */
    public function routeShutdown(\Zend_Controller_Request_Abstract $request)
    {
        $gatewayRequest = clone $request;

        if ($request->getModuleName() === 'Globalpay') {
            $frontController = \Zend_Controller_Front::getInstance();

            $gateway = ucfirst($request->getParam('gateway'));

            $filter = new \Zend_Filter_Word_UnderscoreToDash();
            $gateway = $filter->filter(ucfirst(strtolower($gateway)));

            $gatewayRequest->setControllerName($gateway);

            if ($frontController->getDispatcher()->isDispatchable($gatewayRequest)) {
                $request->setControllerName($gateway);
            }
        }
    }
}
