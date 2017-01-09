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

use Globalpay\Controller\Payment;

class Globalpay_PaymentController extends Payment
{
    public function paymentAction()
    {
        $gateway = $this->getGateway();

        if (!$gateway->supportsPurchase()) {
            \Pimcore\Logger::error(sprintf('Globalpay Gateway payment [%s] does not support purchase', $this->getGatewayName()));
            throw new \Exception('Gateway does not support purchase!');
        }

        $paymentObject = new \Pimcore\Model\Object\GlobalpayPayment();
        $paymentObject->setValues($this->getAllParams());
        $paymentObject->setParent(\Pimcore\Model\Object\Service::createFolderByPath("/globalpay/payments"));
        $paymentObject->setKey(uniqid());
        $paymentObject->setErrorParams(\Pimcore\Tool\Serialize::serialize($this->getParam("errorParams")));
        $paymentObject->setSuccessParams(\Pimcore\Tool\Serialize::serialize($this->getParam("successParams")));
        $paymentObject->setCancelParams(\Pimcore\Tool\Serialize::serialize($this->getParam("cancelParams")));
        $paymentObject->setPublished(true);
        $paymentObject->save();

        $params = $this->getGatewayParams($paymentObject);
        $response = $gateway->purchase($params)->send();

        if ($response instanceof \Omnipay\Common\Message\ResponseInterface) {

            if ($response->getTransactionReference()) {
                $paymentObject->setTransactionIdentifier($response->getTransactionReference());
            } else {
                $paymentObject->setTransactionIdentifier($params['transactionId']);
            }

            $paymentObject->save();

            try {
                if ($response->isSuccessful()) {
                    \Pimcore\Logger::notice(sprintf('Globalpay Gateway payment [%s]: Gateway successfully responded redirect!', $this->getGatewayName()));
                    $this->redirect($params['returnUrl']);
                } else if ($response->isRedirect()) {
                    if ($response instanceof \Omnipay\Common\Message\RedirectResponseInterface) {
                        \Pimcore\Logger::notice(sprintf('Globalpay Gateway payment [%s]: response is a redirect. RedirectMethod: %s', $this->getGatewayName(), $response->getRedirectMethod()));

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
                \Pimcore\Logger::error(sprintf('Globalpay Gateway payment [%s] Error: %s', $this->getGatewayName(), $e->getMessage()));
                throw $e;
            }

        }
    }

    /**
     * Get all required Params for gateway.
     * extend this in your custom omnipay controller.
     *
     * @param \Pimcore\Model\Object\GlobalpayPayment $globalPayPayment
     *
     * @return array
     */
    public function getGatewayParams($globalPayPayment)
    {
        $cardParams = $this->getParam('card', []);

        $params = $this->getAllParams();
        $params['returnUrl'] = Pimcore\Tool::getHostUrl() . $this->getSuccessUrl();
        $params['cancelUrl'] = Pimcore\Tool::getHostUrl() . $this->getCancelUrl();
        $params['amount'] = floatval($this->getParam('amount'));
        $params['currency'] = $this->getParam('currency');
        $params['transactionId'] = $globalPayPayment->getId();

        if (count($cardParams) > 0) {
            $params['card'] = new \Omnipay\Common\CreditCard($cardParams);
        }

        return $params;
    }
}
