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

 class Globalpay_PostfinanceController extends Globalpay_PaymentController
 {
     /**
      * This Action listen to server2server communication
      */
     public function paymentReturnServerAction()
     {
         $requestData = $this->parseRequestData();

         $this->disableLayout();
         $this->disableViewAutoRender();

         \Pimcore\Logger::log('OmniPay paymentReturnServer [Postfinance]. TransactionID: ' . $requestData['transaction'] . ', Status: ' . $requestData['status']);

         if ($requestData['status'] === 5) {
             if (!empty($requestData['transaction'])) {
                 $cart = \CoreShop\Model\Cart::findByCustomIdentifier($requestData['transaction']);

                 if ($cart instanceof \CoreShop\Model\Cart) {

                     \Pimcore\Logger::notice('OmniPay paymentReturnServer [Postfinance]: create order with: ' . $requestData['transaction']);

                     $order = $cart->createOrder(
                         \CoreShop\Model\Order\State::getById(\CoreShop\Model\Configuration::get('SYSTEM.ORDERSTATE.PAYMENT')),
                         $this->getModule(),
                         $cart->getTotal(),
                         $this->view->language
                     );

                     $payments = $order->getPayments();

                     foreach ($payments as $p) {
                         $dataBrick = new \Pimcore\Model\Object\Objectbrick\Data\CoreShopPaymentOmnipay($p);
                         $dataBrick->setTransactionId($requestData['transaction']);
                         $p->save();
                     }

                 }else {
                     \Pimcore\Logger::notice('Globalpay paymentReturnServer [Postfinance]: Cart with identifier' . $requestData['transaction'] . 'not found');
                 }
             } else {
                 \Pimcore\Logger::notice('Globalpay paymentReturnServer [Postfinance]: No valid transaction id given');
             }
         } else {
             \Pimcore\Logger::notice('Globalpay paymentReturnServer [Postfinance]: Error Status: ' . $requestData['status']);
         }

         exit;
     }

     /**
      * This Action can be called via Frontend
      * @throws \CoreShop\Exception
      * @throws \CoreShop\Exception\ObjectUnsupportedException
      */
     public function paymentReturnAction()
     {
         $requestData = $this->parseRequestData();

         $this->disableLayout();
         $this->disableViewAutoRender();

         \Pimcore\Logger::notice('Globalpay paymentReturn [Postfinance]. TransactionID: ' . $requestData['transaction'] . ', Status: ' . $requestData['status']);

         $redirectUrl = '';

         if ($requestData['status'] === 5) {
             if (!empty($requestData['transaction'])) {
                 $cart = \CoreShop\Model\Cart::findByCustomIdentifier($requestData['transaction']);

                 if ($cart instanceof \CoreShop\Model\Cart) {

                     \Pimcore\Logger::notice('Globalpay paymentReturn [Postfinance]: create order with: ' . $requestData['transaction']);

                     $order = $cart->createOrder(
                         \CoreShop\Model\Order\State::getById(\CoreShop\Model\Configuration::get('SYSTEM.ORDERSTATE.PAYMENT')),
                         $this->getModule(),
                         $cart->getTotal(),
                         $this->view->language
                     );

                     $payments = $order->getPayments();

                     foreach ($payments as $p) {
                         $dataBrick = new \Pimcore\Model\Object\Objectbrick\Data\CoreShopPaymentOmnipay($p);
                         $dataBrick->setTransactionId($requestData['transaction']);
                         $p->save();
                     }

                     $redirectUrl = Pimcore\Tool::getHostUrl() . $this->getModule()->getConfirmationUrl($order);
                 } else {
                     \Pimcore\Logger::notice('Globalpay paymentReturn [Postfinance]: Cart with identifier' . $requestData['transaction'] . 'not found');
                     $redirectUrl = Pimcore\Tool::getHostUrl() . $this->getModule()->getErrorUrl('cart with identifier' . $requestData['transaction'] . 'not found');
                 }
             } else {
                 \Pimcore\Logger::notice('Globalpay paymentReturn [Postfinance]: No valid transaction id given');
                 $redirectUrl = Pimcore\Tool::getHostUrl() . $this->getModule()->getErrorUrl('no valid transaction id given');
             }
         } else {
             \Pimcore\Logger::notice('Globalpay paymentReturn [Postfinance]: Error Status: ' . $requestData['status']);
             $redirectUrl = Pimcore\Tool::getHostUrl() . $this->getModule()->getErrorUrl('Postfinance returned with an error. Error Status: ' . $requestData['status']);
         }

         $this->redirect( $redirectUrl );
         exit;
     }

     public function getGatewayParams()
     {
         $params = parent::getGatewayParams();

         $language = $this->language;
         $gatewayLanguage = 'en_EN';

         if (!empty($language )) {
             $gatewayLanguage = $language . '_' . strtoupper($language);
         }

         $params['language'] = $gatewayLanguage;

         return $params;
     }

     private function parseRequestData()
     {
         /**
          * @var $transaction
          * CoreShop transaction ID
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
             'transaction' => $transaction,
             'status' => $status,
             'payId' => $payId,
             'payIdSub' => $payIdSub,
             'ncError' => $ncError
        ];
     }
 }
