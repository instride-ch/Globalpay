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

        $paymentObject = $this->createPaymentObject();

        $params = $this->getGatewayParams($paymentObject);
        $response = $gateway->purchase($params)->send();

        if ($response instanceof \Omnipay\Common\Message\ResponseInterface) {
            if ($response->getTransactionReference()) {
                $paymentObject->setTransactionIdentifier($response->getTransactionReference());
            } else {
                $paymentObject->setTransactionIdentifier($params['transactionId']);
            }

            $paymentObject->save();

            $this->handleOmnipayResponse($response, $paymentObject);
        }
    }

    protected function handleOmnipayResponse(\Omnipay\Common\Message\ResponseInterface $response, \Pimcore\Model\Object\GlobalpayPayment $paymentObject) {
        if ($response instanceof \Omnipay\Common\Message\ResponseInterface) {
            try {
                if ($response->isSuccessful()) {
                    \Pimcore\Logger::notice(sprintf('Globalpay Gateway payment [%s]: Gateway successfully responded redirect!', $this->getGatewayName()));
                    $this->forwardSuccess($paymentObject);
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
                    $this->forwardError($paymentObject, $response);
                }
            } catch(\Exception $e) {
                \Pimcore\Logger::error(sprintf('Globalpay Gateway payment [%s] Error: %s', $this->getGatewayName(), $e->getMessage()));
                throw $e;
            }
        }
    }

    protected function createPaymentObject() {
        $paymentObject = new \Pimcore\Model\Object\GlobalpayPayment();
        $paymentObject->setValues($this->getAllParams());
        $paymentObject->setParent(\Pimcore\Model\Object\Service::createFolderByPath("/globalpay/payments"));
        $paymentObject->setKey(uniqid());
        $paymentObject->setErrorParams(\Pimcore\Tool\Serialize::serialize($this->getParam("errorParams")));
        $paymentObject->setSuccessParams(\Pimcore\Tool\Serialize::serialize($this->getParam("successParams")));
        $paymentObject->setCancelParams(\Pimcore\Tool\Serialize::serialize($this->getParam("cancelParams")));
        $paymentObject->setPublished(true);
        $paymentObject->save();

        return $paymentObject;
    }

    /**
     * @param $purchaseResponse
     * @return null|\Pimcore\Model\Object\GlobalpayPayment
     * @throws Exception
     */
    protected function processResponse($purchaseResponse)
    {
        \Pimcore\Logger::notice(sprintf('Globalpay [%s]: TransactionID: %s ,Status: %s', $this->getGatewayName(), $purchaseResponse->getTransactionReference(), $purchaseResponse->getCode()));

        if (empty($purchaseResponse->getTransactionReference())) {
            throw new \Exception(sprintf('Globalpay [%s]: No valid transaction id given', $this->getGatewayName()));
        }

        $globalPayPayment = $this->getPaymentObject($purchaseResponse);

        if (!$globalPayPayment instanceof \Pimcore\Model\Object\GlobalpayPayment) {
            throw new \Exception(sprintf('Globalpay [%s]: Order with identifier %s not found', $this->getGatewayName(), $purchaseResponse->getTransactionReference()));
        }

        $globalPayPayment->setStatus("success");
        $globalPayPayment->save();

        return $globalPayPayment;
    }

    /**
     * @param \Omnipay\Common\Message\AbstractResponse $purchaseResponse
     * @return \Pimcore\Model\Object\GlobalpayPayment|null
     */
    protected function getPaymentObject(\Omnipay\Common\Message\AbstractResponse $purchaseResponse) {
        $globalPayPayments = \Pimcore\Model\Object\GlobalpayPayment::getByTransactionIdentifier($purchaseResponse->getTransactionReference());
        $globalPayPayments = $globalPayPayments->getObjects();

        if(count($globalPayPayments) === 1) {
            return $globalPayPayments[0];
        }

        return null;
    }

    /**
     * Complete the purchase
     *
     * @return \Omnipay\Common\Message\AbstractResponse
     *
     * @throws Exception
     */
    protected function completePurchaseResponse() {
        if(!$this->getGateway()->supportsCompletePurchase()) {
            throw new \Exception("does not support complete purchase");
        }

        return $this->getGateway()->completePurchase($this->getAllParams())->send();
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
        $params['errorUrl'] = Pimcore\Tool::getHostUrl() . $this->getErrorUrl();
        $params['amount'] = floatval($this->getParam('amount'));
        $params['currency'] = $this->getParam('currency');
        $params['transactionId'] = $globalPayPayment->getId();

        if (count($cardParams) > 0) {
            $params['card'] = new \Omnipay\Common\CreditCard($cardParams);
        }

        return $params;
    }
}
