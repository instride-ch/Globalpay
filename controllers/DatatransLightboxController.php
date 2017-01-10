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

require 'DatatransController.php';

/**
 * Class Globalpay_DatatransLightboxController
 */
class Globalpay_DatatransLightboxController extends Globalpay_DatatransController
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
        $this->view->params = [];

        $response = $gateway->purchase($params);

        if ($response->getTransactionReference()) {
            $paymentObject->setTransactionIdentifier($response->getTransactionReference());
        } else {
            $paymentObject->setTransactionIdentifier($params['transactionId']);
        }
        $paymentObject->save();

        $filter = new \Zend_Filter_Word_CamelCaseToDash();

        foreach($response->getData() as $key => $value) {
            $this->view->params[$filter->filter($key)] = $value;
        }
    }
}