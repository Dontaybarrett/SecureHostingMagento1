<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Paygate_SecureHosting
 */

class Paygate_SecureHosting_Model_Redirect extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'securehosting';
    protected $_formBlockType = 'securehosting/form';

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;

    protected $_order = null;
    protected $_config = null;


    /**
     * Get Config model
     *
     * @return object Paygate_SecureHostingApi_Model_Config
     */
    public function getConfig()
    {
    	if($this->_config == null)
        	$this->_config = Mage::getSingleton('securehosting/config');
        return $this->_config;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder ()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order; 
    }
    
    public function getOrderPlaceRedirectUrl(){
    	return Mage::getUrl('securehosting/redirect/redirect', array('_secure' => true));
    }
    
    protected function getSuccessURL ()
    {
        return Mage::getUrl('securehosting/redirect/success', array('_secure' => true));
    }

    /**
     *  Return URL for SecureHosting notification
     *
     *  @return	  string Notification URL
     */
    protected function getNotificationURL ()
    {
        return Mage::getUrl('securehosting/redirect/notify/', array('_secure' => true));
    }
    
    public function getStandardCheckoutFormFields ()
    {
        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }

        $billingAddress = $order->getBillingAddress();
		$shippingAddress = $order->getShippingAddress();


        if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } elseif ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
        } else {
            $email = '';
        }

        $secuitems = "";
		foreach($order->getAllVisibleItems() as $item){
            $_p = array($item->getProductId(),
                        $item->getSku(),
                        $item->getName(),
                        sprintf("%01.2f", $item->getPrice()),
                        number_format($item->getIsQtyDecimal()? intval($item->getQtyOrdered()) : $item->getQtyOrdered(),0,'',''),
                        sprintf("%01.2f", $item->getRowTotal())
                );
            $secuitems .= '['.implode('|', $_p).']';
		}
		
		$TransactionAmount = sprintf("%01.2f", $order->getBaseGrandTotal());

        $fields = array(
        				'shreference'				=> $this->getConfig()->getSHreference(),
        				'checkcode'					=> $this->getConfig()->getCheckCode(),
        				'filename'					=> $this->getConfig()->getSHreference().'/'.$this->getConfig()->getFileName(),
        				'secuitems'					=> $secuitems,
        				'orderid'					=> $order->getRealOrderId(),
			
                        'transactionamount'			=> $TransactionAmount,
        				'subtotal'					=> sprintf("%01.2f", ($order->getBaseGrandTotal()-($order->getShippingAmount()+$order->getTaxAmount())),2,".",""),
        				'transactiontax'			=> sprintf("%01.2f", $order->getTaxAmount()),
        				'shippingcharge'			=> sprintf("%01.2f", $order->getShippingAmount()),
                        'transactioncurrency'		=> $order->getBaseCurrencyCode(),
			
                        'cardholdersname'			=> $billingAddress->getFirstname().' '.$billingAddress->getLastname(),
                        'cardholderaddr1'			=> $billingAddress->getStreet1(),
                        'cardholderaddr2'			=> $billingAddress->getStreet2(),
                        'cardholdercity'			=> $billingAddress->getCity(),
                        'cardholderstate'			=> $billingAddress->getRegion(),
                        'cardholderpostcode'		=> $billingAddress->getPostcode(),
                        'cardholdercountry'			=> $billingAddress->getCountry(),
                        'cardholdertelephonenumber'	=> $billingAddress->getTelephone(),
			
                        'shippingname'				=> $shippingAddress->getFirstname().' '.$shippingAddress->getLastname(),
                        'shippingaddr1'				=> $shippingAddress->getStreet1(),
                        'shippingaddr2'				=> $shippingAddress->getStreet2(),
                        'shippingcity'				=> $shippingAddress->getCity(),
                        'shippingstate'				=> $shippingAddress->getRegion(),
                        'shippingpostcode'			=> $shippingAddress->getPostcode(),
                        'shippingcountry'			=> $shippingAddress->getCountry(),
                        'shippingtelephonenumber'	=> $shippingAddress->getTelephone(),
			
                        'cardholdersemail'			=> $email,
                        'success_url'				=> $this->getSuccessURL(),
                        'callbackurl'				=> $this->getNotificationURL(),
                        'callbackdata'				=> "orderid|#orderid|transactionamount|#transactionamount"
                        );
		
		if($this->getConfig()->ASActive()){
			if(preg_match('/([a-zA-Z0-9]{32})/', $this->GetAdvancedSecuitems($secuitems, $TransactionAmount), $Matches))
				$fields['secustring'] = $Matches[1];
		}
        

        return $fields;
    }
	
	private function GetAdvancedSecuitems($secuitems, $TransactionAmount){
		$post_data = "shreference=".$this->getConfig()->getSHreference();
		$post_data .= "&secuitems=".$secuitems;
		$post_data .= "&secuphrase=".$this->getConfig()->getPhrase();
		$post_data .= "&transactionamount=".$TransactionAmount;
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $this->getConfig()->getSecureHostingSecuStringUrl());
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_REFERER, $this->getConfig()->getReferrer()); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		$secuString = trim(curl_exec($ch));
		curl_close($ch);
		
		return $secuString;
	}
    
        
}
