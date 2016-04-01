<?php
class Paygate_SecureHosting_redirectController extends Mage_Core_Controller_Front_Action
{
	
protected $_order;
	protected $_config;
	
	public function getConfig()
    {
    	if($this->_config == null)
        	$this->_config = Mage::getSingleton('securehosting/config');
        return $this->_config;
    }
	
	
	public function getOrder ()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }
	
	public function redirectAction()
	{ 
	    $session = Mage::getSingleton('checkout/session');
        $session->setSecureHostingStandardQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('securehosting')->__('Customer was redirected to Secure Hosting and Payments')
        );
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('securehosting/redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
	}
	
	public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getSecureHostingStandardQuoteId());
        $session->unsSecureHostingStandardQuoteId();

        $order = $this->getOrder();
        
        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

       $this->_createInvoice($order);
        //sets the status to 'pending'.
        $msg = 'Payment completed via Secure Hosting.';
        $newStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING ,true,$msg,false);
        $order->addStatusToHistory($newStatus,$msg);
        $order->save();
        
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->setIsActive(false)->save();
        
        $order->save();
        $this->_redirect('checkout/onepage/success');
    }
    

    protected function _createInvoice($orderObj)
    {
        if (!$orderObj->canInvoice()) {
            return false;
        }
        $invoice = $orderObj->prepareInvoice();
        $invoice->register();
        if($invoice->canCapture()){
            $invoice->capture();
        }
        $invoice->save();
        $orderObj->addRelatedObject($invoice);
        return $invoice;
    }

	public function notifyAction ()
	{
        try {
			
			$SharedSecret = $this->getConfig()->getSharedSecret();
			if(!empty($SharedSecret)){
				if(!$this->getRequest()->getParam('verify')){
					throw new Exception('Callback error - No callback verification');
				}
				
				if($this->getRequest()->getParam('verify') != hash('sha1', $this->getConfig()->getSharedSecret().$this->getRequest()->getParam('transactionnumber').$this->getConfig()->getSharedSecret())){
					throw new Exception('Callback error - Invalid callback verification');
				}
			}
			
            $transactionNumber = $this->getRequest()->getParam('transactionnumber');

			if($transactionNumber != '-1'){
				if (!$this->getRequest()->getParam('orderid')) {
					throw new Exception('Callback error - OrderId not given');
				}

				if (@$_SERVER['HTTP_REFERER'] != $this->getConfig()->getSecureHostingCallbackReferrer()) {
					throw new Exception('Callback error - Referer: "'.@$_SERVER['HTTP_REFERER'].'" Not valid');
				}

				$config = $this->getConfig();
				$order = Mage::getModel('sales/order');
				$order->loadByIncrementId(urldecode($this->getRequest()->getParam('orderid')));

				if (!$order->getId()) {
					throw new Exception('Callback error - Order with given ID: "'.$this->getRequest()->getParam('orderid').'" not found');
				}

				if($config->autoInvoice() AND $order->canInvoice()){
					$invoice = $order->prepareInvoice();
					$invoice->register();
					Mage::getModel('core/resource_transaction')
						->addObject($invoice)
						->addObject($invoice->getOrder())
						->save();
					$invoice->sendEmail(true,'');
					$newStatus = Mage_Sales_Model_Order::STATE_COMPLETE;
					$message = Mage::helper('securehosting')->__("Transaction complete, transaction reference: \"$_GET[transactionnumber]\"<br />Order Invoiced");
				} else {
					$newStatus = Mage_Sales_Model_Order::STATE_PROCESSING;
					$message = Mage::helper('securehosting')->__("Transaction complete, transaction reference: \"$_GET[transactionnumber]\"");
				}
				$order->addStatusToHistory($newStatus,$message);
				$order->save();
			}
			echo "success";
        } catch (Exception $e) {
            Mage::logException($e);
            echo $e->getMessage();
        }
	}

	protected function saveInvoice (Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
        	$invoiceId = Mage::getModel('sales/order_invoice_api')
        		->create($order->getIncrementId(), array());
        	$invoice = Mage::getModel('sales/order_invoice')
        		->loadByIncrementId($invoiceId);
        	$invoice->capture()->save();
        }
    }
}
