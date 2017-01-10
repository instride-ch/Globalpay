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

require 'PaymentController.php';

/**
 * Class Globalpay_PostfinanceController
 */
class Globalpay_PostfinanceController extends Globalpay_PaymentController
{
    /**
     * This Action listens to server2server communication
     *
     * this action validates the GlobalpayPayment Object
     */
    public function paymentReturnServerAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $response = $this->completePurchaseResponse();
            $globalPayPayment = $this->processResponse($response);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());
        }

        exit;
    }

    /**
     * This Action can be called via Frontend
     *
     * @throws \Exception
     */
    public function successAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $response = $this->completePurchaseResponse();
            $globalPayPayment = $this->processResponse($response);

            $this->forwardSuccess($globalPayPayment, $response);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());

            throw $e;
        }
    }

    public function cancelAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $response = $this->completePurchaseResponse();
            $globalPayPayment = $this->processResponse($response);

            $this->forwardCancel($globalPayPayment, $response);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());


        }
    }

    public function errorAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $response = $this->completePurchaseResponse();
            $globalPayPayment = $this->processResponse($response);

            if($globalPayPayment instanceof \Pimcore\Model\Object\GlobalpayPayment) {
                $this->forwardError($globalPayPayment, $response);
            }
            else {
                throw new \Exception("Payment Information not found");
            }
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());

            throw new \Exception("Something bad happened");
        }
    }

    /**
     * @param \Omnipay\Common\Message\AbstractResponse $purchaseResponse
     * @return \Pimcore\Model\Object\GlobalpayPayment|null
     */
    protected function getPaymentObject(\Omnipay\Common\Message\AbstractResponse $purchaseResponse) {
        return \Pimcore\Model\Object\GlobalpayPayment::getById($purchaseResponse->getData()['ORDERID']);
    }


    /**
     * @param \Pimcore\Model\Object\GlobalpayPayment $globalPayPayment
     *
     * @return array
     */
    public function getGatewayParams($globalPayPayment)
    {
        $params = parent::getGatewayParams($globalPayPayment);

        $language = $this->language;
        $gatewayLanguage = 'en_EN';

        if(!empty($language)) {
            $gatewayLanguage = $language . '_' . strtoupper($language);
        }

        $params['language'] = $gatewayLanguage;

        return $params;
    }
}