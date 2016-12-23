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

use Omnipay\Controller\Payment;

class Globalpay_PaymentController extends Payment
{
    public function paymentAction()
    {
        $gateway = $this->getModule()->getGateway();

        if (!$gateway->supportsPurchase()) {
            \Pimcore\Logger::error('Globalpay Gateway payment [' . $this->getModule()->getName() . '] does not support purchase');
            throw new \Exception('Gateway does not support purchase!');
        }

        $params = $this->getGatewayParams();
        $response = $gateway->purchase($params)->send();

        if ($response instanceof \Omnipay\Common\Message\ResponseInterface) {

            if ($response->getTransactionReference()) {
                $this->cart->setCustomIdentifier($response->getTransactionReference());
            } else {
                $this->cart->setCustomIdentifier($params['transactionId']);
            }

            $this->cart->save();

            try {
                if ($response->isSuccessful()) {
                    \Pimcore\Logger::notice('Globalpay Gateway payment [' . $this->getModule()->getName() . ']: Gateway successfully responded redirect!');
                    $this->redirect($params['returnUrl']);
                } else if ($response->isRedirect()) {
                    if ($response instanceof \Omnipay\Common\Message\RedirectResponseInterface) {
                        \Pimcore\Logger::notice('Globalpay Gateway payment [' . $this->getModule()->getName() . ']: response is a redirect. RedirectMethod: ' . $response->getRedirectMethod());

                        if ($response->getRedirectMethod() === 'GET') {
                            $this->redirect($response->getRedirectUrl());
                        } else {
                            $this->view->response = $response;
                            $this->_helper->viewRenderer('payment/post', null, true);
                        }
                    }
                } else {
                    throw new \Exception($response->getMessage());
                }
            } catch(\Exception $e) {
                \Pimcore\Logger::error('Globalpay Gateway payment [' . $this->getModule()->getName() . '] Error: ' . $e->getMessage());
            }

        }
    }

    public function paymentReturnAbortAction()
    {
        die('TODO: Canceled checkout');
        // $this->forward('canceled', 'checkout', 'Globalpay', []);
    }

    public function errorAction()
    {
        die('TODO: Error checkout');
        // $this->forward('error', 'checkout', 'Globalpay', []);
    }

    public function confirmationAction()
    {
        $orderId = $this->getParam('order');

        if ($orderId) {
            $order = \CoreShop\Model\Order::getById($orderId);

            if ($order instanceof \CoreShop\Model\Order) {
                $this->session->order = $order;
            }
        }

        parent::confirmationAction();
    }

    /**
     * @return Omnipay\Shop\Provider
     */
    public function getModule()
    {
        if (is_null($this->module)) {
            $this->module = \CoreShop::getPaymentProvider('omnipay' . $this->gateway);
        }

        return $this->module;
    }

    /**
     * Get all required Params for gateway.
     * extend this in your custom omnipay controller.
     *
     * @return array
     */
    public function getGatewayParams()
    {
        $cardParams = $this->getParam('card', []);

        $params = $this->getAllParams();
        $params['returnUrl'] = Pimcore\Tool::getHostUrl() . $this->getModule()->url($this->getModule()->getIdentifier(), 'payment-return');
        $params['cancelUrl'] = Pimcore\Tool::getHostUrl() . $this->getModule()->url($this->getModule()->getIdentifier(), 'payment-return-abort');
        $params['amount'] = $this->cart->getTotal();
        $params['currency'] = \Coreshop::getTools()->getCurrency()->getIsoCode();
        $params['transactionId'] = uniqid();

        if (count($cardParams) > 0) {
            $params['card'] = new \Omnipay\Common\CreditCard($cardParams);
        }

        return $params;
    }
}
