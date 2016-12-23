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

use Globalpay\Model;

class Globalpay_AdminController extends \Pimcore\Controller\Action\Admin
{
    public function getProvidersAction(){
        $gateways = \Globalpay\Tool::getSupportedGateways();
        $available = [];
        $activeProviders = Model\Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');

        if (!is_array($activeProviders)) {
            $activeProviders = [];
        }

        foreach ($gateways as $gateway) {
            $class = \Omnipay\Common\Helper::getGatewayClassName($gateway);
            if (\Pimcore\Tool::classExists($class)) {
                if (!in_array($gateway, $activeProviders)) {
                    $available[] = ['name' => $gateway];
                }
            }
        }

        $this->_helper->json(['data' => $available]);
    }

    public function getProviderOptionsAction()
    {
        $provider = $this->getParam('provider');
        $gateway = \Omnipay\Omnipay::getFactory()->create($provider);

        $this->_helper->json(['options' => $gateway->getParameters()]);
    }

    public function getActiveProvidersAction()
    {
        $activeProviders = Model\Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');
        $result = [];

        if (is_array($activeProviders)) {
            foreach ($activeProviders as $provider) {
                $result[] = $this->getProviderArray($provider);
            }
        }

        $this->_helper->json($result);
    }

    public function addProviderAction()
    {
        $gateway = $this->getParam('provider');
        $gateways = \Globalpay\Tool::getSupportedGateways();

        if (in_array($gateway, $gateways)) {
            $activeProviders = Model\Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');

            if (!is_array($activeProviders)) {
                $activeProviders = [];
            }

            if (!in_array($gateway, $activeProviders)) {
                $activeProviders[] = $gateway;

                Model\Configuration::set('GLOBALPAY.ACTIVEPROVIDERS', $activeProviders);

                $gateway = \Omnipay\Omnipay::getFactory()->create($gateway);

                $this->_helper->json(['success' => true, 'name' => $gateway, 'settings' => $gateway->getParameters()]);
            }
        }

        $this->_helper->json(['success' => false]);
    }

    public function removeProviderAction() {
        $gateway = $this->getParam('provider');

        if (in_array($gateway, \Globalpay\Tool::getSupportedGateways())) {
            $activeProviders = Model\Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');

            if (!is_array($activeProviders)) {
                $activeProviders = [];
            }

            if (in_array($gateway, $activeProviders)) {

                $index = array_search($gateway, $activeProviders);

                if ($index >= 0) {
                    unset($activeProviders[$index]);
                }

                Model\Configuration::set('GLOBALPAY.ACTIVEPROVIDERS', $activeProviders);


                $this->_helper->json(['success' => true]);
            }
        }

        $this->_helper->json(['success' => false]);
    }

    /**
     * @param $name
     * @return array
     */
    protected function getProviderArray($name) {
        $gateway = \Omnipay\Omnipay::getFactory()->create($name);

        $data = [
            'name' => $name,
            'settings' => $gateway->getParameters()
        ];

        return $data;
    }

    public function getAction()
    {
        $config = new Model\Configuration\Listing();
        $config->setFilter(function ($entry) {
            if (startsWith($entry['key'], 'GLOBALPAY.')) {
                return true;
            }

            return false;
        });

        $valueArray = [];

        foreach ($config->getConfigurations() as $c) {
            $valueArray[$c->getKey()] = $c->getData();
        }

        $response = [
            'values' => $valueArray
        ];

        $this->_helper->json($response);
        $this->_helper->json(false);
    }

    public function setAction()
    {
        $values = \Zend_Json::decode($this->getParam('data'));
        $values = array_htmlspecialchars($values);

        foreach ($values as $key => $value) {
            Model\Configuration::set($key, $value);
        }

        $this->_helper->json(['success' => true]);
    }
}
