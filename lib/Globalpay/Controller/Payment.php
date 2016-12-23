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

 namespace Globalpay\Controller;

 class Payment extends \CoreShop\Controller\Action\Payment
 {
     /**
      * the gateway's name
      *
      * @var string
      */
     protected $gateway;

    /**
     * init controller
     * @return [type] [description]
     */
     public function init()
     {
         parent::init();

         $this->gateway = $this->getParam('gateway');
         $activeProviders = \Globalpay\Model\Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');

         if (!is_array($activeProviders)) {
             $activeProviders = [];
         }

         if (!in_array($this->gateway, $activeProviders)) {
             throw new \Exception('Not supported');
         }

         $gatewayName = strtolower($this->getModule()->getGateway()->getShortName());
         $pluginPath = PIMCORE_PLUGINS_PATH . '/Globalpay/views/scripts/' . $gatewayName;

         $this->view->setScriptPath(
             array_merge(
                 $this->view->getScriptPaths(),
                 [
                     $pluginPath,
                     PIMCORE_WEBSITE_PATH . '/views/scripts/globalpay/' . $gatewayName
                 ]
             )
         );
     }

     /**
      * @return \Globalpay\Shop\Provider
      */
     public function getModule()
     {
         if (is_null($this->module)) {
             $this->module = \CoreShop::getPaymentProvider('omnipay' . $this->gateway);
         }

         return $this->module;
     }
 }
