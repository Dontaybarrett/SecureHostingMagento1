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

class Paygate_SecureHosting_Model_Config extends Varien_Object
{
    /**
     *  Return config var
     *
     *  @param    string Var key
     *  @param    string Default value for non-existing key
     *  @return	  mixed
     */
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
            $value = Mage::getStoreConfig('payment/securehosting/'.$key);
            if (is_null($value) || false===$value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

	public function getRedirectMessage()
    {
    	return $this->getConfigData('redirect_message');
    }
	
    public function getSHreference()
    {
        return $this->getConfigData('shreference');
    }
    
    public function getCheckCode()
    {
    	return $this->getConfigData('checkcode');
    }
	
	public function getFileName()
	{
		return $this->getConfigData('filename');
	}
	
	public function ASActive()
	{
		return (bool) $this->getConfigData('activate_as');
	}
	
	public function getReferrer()
	{
		return $this->getConfigData('as_referrer');
	}
	
	public function getPhrase()
	{
		return $this->getConfigData('as_phrase');
	}
	
	public function getSharedSecret()
	{
		return $this->getConfigData('sharedsecret');
	}
	
	public function TestMode()
	{
		return (bool) $this->getConfigData('testmode');
	}
    
    public function getSecureHostingRedirectUrl(){
		if($this->TestMode()){
			return "https://test.secure-server-hosting.com/secutran/secuitems.php";
		} else {
			return "https://www.secure-server-hosting.com/secutran/secuitems.php";
		}
    	
    }
	
	public function getSecureHostingSecuStringUrl()
	{
		return "https://www.secure-server-hosting.com/secutran/create_secustring.php";
	}
	
	public function getSecureHostingCallbackReferrer()
	{
		return "https://www.secure-server-hosting.com/secutran/ProcessCallbacks.php";
	}
    
    public function autoInvoice(){
    	return (bool) $this->getConfigData('auto_invoice');
    }

}
