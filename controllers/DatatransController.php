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
 * Class Globalpay_DatatransController
 */
class Globalpay_DatatransController extends Globalpay_PaymentController
{
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

            $this->forwardCancel($globalPayPayment);
        } catch(\Exception $e) {
            \Pimcore\Logger::notice($e->getMessage());

            throw $e;
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

            throw new Exception("Something bad happened here");
        }
    }

    /**
     * @param \Pimcore\Model\Object\GlobalpayPayment $globalPayPayment
     *
     * @return array
     */
    public function getGatewayParams($globalPayPayment)
    {
        $params = parent::getGatewayParams($globalPayPayment);

        $params['errorUrl'] = Pimcore\Tool::getHostUrl() . $this->getErrorUrl();

        return $params;
    }
}