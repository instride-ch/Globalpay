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

use Globalpay\Model\Configuration;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\ResponseInterface;
use Pimcore\Model\Object\GlobalpayPayment;
use Pimcore\Model\Staticroute;
use Pimcore\Tool\Serialize;
use Website\Controller\Action;

class Payment extends Action
{
    /**
     * the gateway's name
     *
     * @var string
     */
    protected $gateway;

    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();

        $activeProviders = Configuration::get('GLOBALPAY.ACTIVEPROVIDERS');

        if (!is_array($activeProviders)) {
            $activeProviders = [];
        }

        if (!in_array($this->getGatewayName(), $activeProviders)) {
            throw new \Exception('Not supported');
        }

        $gatewayName = strtolower($this->getGatewayName());
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

    public function confirmationAction()
    {
        $payments = GlobalpayPayment::getByTransactionIdentifier($this->getParam("transaction"));

        if(count($payments) === 1) {
            $payment = $payments[0];

            $this->forwardSuccess($payment);
        }
    }

    public function errorAction() {
        $payments = GlobalpayPayment::getByTransactionIdentifier($this->getParam("transaction"));

        if(count($payments) === 1) {
            $payment = $payments[0];

            $this->forwardError($payment);
        }
    }

    /**
     * forward success to responsible controller
     *
     * @param GlobalpayPayment $payment
     * @param ResponseInterface $response
     */
    protected function forwardSuccess(GlobalpayPayment $payment, $response) {
        $params = Serialize::unserialize($payment->getSuccessParams());
        $params = array_merge($this->getAllParams(), $params);
        $params['omnipay_response'] = $response;

        $this->forward($payment->getSuccessAction(), $payment->getSuccessController(), $payment->getSuccessModule(), $params);
    }

    /**
     * forward success to responsible controller
     *
     * @param GlobalpayPayment $payment
     * @param ResponseInterface $response
     */
    protected function forwardCancel(GlobalpayPayment $payment, $response) {
        $params = Serialize::unserialize($payment->getCancelParams());
        $params = array_merge($this->getAllParams(), $params);
        $params['omnipay_response'] = $response;

        $this->forward($payment->getCancelAction(), $payment->getCancelController(), $payment->getCancelModule(), $params);
    }

    /**
     * forward error to responsible controller
     *
     * @param GlobalpayPayment $payment
     * @param ResponseInterface $response
     */
    protected function forwardError(GlobalpayPayment $payment, $response) {
        $payment->setStatus("error");
        $payment->save();

        $params = Serialize::unserialize($payment->getErrorParams());
        $params = array_merge($this->getAllParams(), $params);
        $params['omnipay_response'] = $response;

        $this->forward($payment->getErrorAction(), $payment->getErrorController(), $payment->getErrorModule(), $params);
    }

    /**
     * get the omnipay gateway for the current request
     *
     * @return mixed|AbstractGateway
     */
    public function getGateway() {
        if(is_null($this->gateway)) {
            $gateway = \Omnipay\Omnipay::getFactory()->create($this->getGatewayName());

            if($gateway instanceof AbstractGateway) {
                $gateway->initialize(Configuration::get("GLOBALPAY." . strtoupper($this->getGatewayName())));
            }

            $this->gateway = $gateway;

            //$this->action("payment", "payment", "Globalpay", ['gateway' => 'postfinance', 'amount' => 50, 'currency' => 'CHF', 'success' => ['donation', 'success', 'default'], 'error' => []]);
        }

        return $this->gateway;
    }

    /**
     * asseble abort url
     *
     * @return mixed|string
     */
    protected function getCancelUrl() {
        $route = $this->getStaticroute();

        return $route->assemble([
            "act" => "cancel",
            "lang" => $this->language,
            "gateway" => $this->getGatewayName()
        ]);
    }

    /**
     * asseble abort url
     *
     * @param $message
     *
     * @return mixed|string
     */
    protected function getErrorUrl($message = '') {
        $route = $this->getStaticroute();

        return $route->assemble([
            "act" => "error",
            "lang" => $this->language,
            "message" => $message,
            "gateway" => $this->getGatewayName()
        ]);
    }

    /**
     * asseble abort url
     *
     * @return mixed|string
     */
    protected function getSuccessUrl() {
        $route = $this->getStaticroute();

        return $route->assemble([
            "act" => "success",
            "lang" => $this->language,
            "gateway" => $this->getGatewayName()
        ]);
    }

    /**
     * get Staticroute
     *
     * @return Staticroute
     */
    protected function getStaticroute() {
        return Staticroute::getByName($this->getParam("route"));
    }

    /**
     * get the omnipay gateway name
     *
     * @return mixed
     */
    protected function getGatewayName() {
        return $this->getParam("gateway");
    }
}
