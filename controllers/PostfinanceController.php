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
            $payment = $this->_processRequest();
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
            $globalPayPayment = $this->_processRequest();

            $this->forwardSuccess($globalPayPayment);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());
            $this->redirect($this->getErrorUrl($e->getMessage()));
        }
    }

    public function cancelAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $globalPayPayment = $this->_processRequest();

            $this->forwardCancel($globalPayPayment);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());
            $this->redirect($this->getErrorUrl($e->getMessage()));
        }
    }

    public function errorAction()
    {
        $this->disableLayout();
        $this->disableViewAutoRender();

        try {
            $globalPayPayment = $this->_processRequest();

            if($globalPayPayment instanceof \Pimcore\Model\Object\GlobalpayPayment) {
                $this->forwardError($globalPayPayment);
            }
            else {
                throw new \Exception("Payment Information not found");
            }
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());

            $this->redirect($this->getErrorUrl($e->getMessage()));
        }
    }

    private function _processRequest()
    {
        $requestData = $this->parseRequestData();

        \Pimcore\Logger::notice(sprintf('Globalpay [Postfinance]: TransactionID: %s ,Status: %s', $requestData['transaction'], $requestData['status']));

        if (empty($requestData['transaction'])) {
            throw new \Exception('Globalpay [Postfinance]: No valid transaction id given');
        }

        $globalPayPayment = \Pimcore\Model\Object\GlobalpayPayment::getById($requestData['transaction']);

        if (!$globalPayPayment instanceof \Pimcore\Model\Object\GlobalpayPayment) {
            throw new \Exception(sprintf('Globalpay [Postfinance]: Order with identifier %s not found', $requestData['transaction']));
        }

        $globalPayPayment->setStatus("success");
        $globalPayPayment->save();

        return $globalPayPayment;
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

    private function parseRequestData()
    {
        /**
         * @var $transaction
         * Globalpay transaction ID
         *
         */
        $transaction = $_REQUEST['orderID'];

        /**
         * @var $status
         *
         * @see https://e-payment-postfinance.v-psp.com/en/guides/user%20guides/statuses-and-errors/statuses
         *
         * 0 => incomplete / not valid
         * 1 => canceled by user
         * 2 => canceled by financial institution
         * 5 => approved
         * 9 => payment requested
         *
         */
        $status = (int) $_REQUEST['STATUS'];

        /**
         * @var $payId
         */
        $payId = $_REQUEST['PAYID'];

        /**
         * @var $payIdSub
         */
        $payIdSub = $_REQUEST['PAYIDSUB'];

        /**
         * @var $ncError
         */
        $ncError = $_REQUEST['NCERROR'];

        return [
            'transaction'           => $transaction,
            'status'                => $status,
            'payId'                 => $payId,
            'payIdSub'              => $payIdSub,
            'ncError'               => $ncError
        ];
    }
}