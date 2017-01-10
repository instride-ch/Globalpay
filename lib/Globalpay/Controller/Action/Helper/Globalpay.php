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

namespace Globalpay\Controller\Action\Helper;

use CoreShop\Controller\Action\Payment;
use CoreShop\Helper\Zend\Action;
use Pimcore\Controller\Action\Helper\ViewRenderer;

/**
 * Class Globalpay
 * @package Globalpay\Controller\Action\Helper
 */
class Globalpay extends \Zend_Controller_Action_Helper_Abstract
{
    /**
     * Constructor
     *
     * Grab local copies of various MVC objects
     */
    public function __construct()
    {

    }

    /**
     * @param $gateway
     * @param $route
     * @param $amount
     * @param $currencyCode
     * @param $successForward
     * @param $cancelForward
     * @param $errorForward
     * @param array $params
     */
    public function direct($gateway, $route, $amount, $currencyCode, $successForward, $cancelForward, $errorForward, $params = [])
    {
        $params = array_merge($params, [
            'gateway' => $gateway,
            'route' => $route,
            'amount' => $amount,
            'currency' => $currencyCode,
            'successController' => $successForward['controller'],
            'successAction' => $successForward['action'],
            'successModule' => $successForward['module'],
            'successParams' => $successForward['params'],
            'errorController' => $errorForward['controller'],
            'errorAction' => $errorForward['action'],
            'errorModule' => $errorForward['module'],
            'errorParams' => $errorForward['params'],
            'cancelController' => $cancelForward['controller'],
            'cancelAction' => $cancelForward['action'],
            'cancelModule' => $cancelForward['module'],
            'cancelParams' => $cancelForward['params']
        ]);

        $filter = new \Zend_Filter_Word_UnderscoreToDash();
        $controller = $filter->filter(ucfirst(strtolower($gateway)));

        $this->getActionController()->forward("payment", $controller, "Globalpay", $params);
    }
}
