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

class Paygate_SecureHosting_Block_Redirect extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        $redirect = Mage::getModel('securehosting/redirect');
        $config = Mage::getModel('securehosting/config');
        $form = new Varien_Data_Form();
        $form->setAction($config->getSecureHostingRedirectUrl())
            ->setId('SecureHosting_checkout')
            ->setName('SecureHosting_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($redirect->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $html = '<html><body>';
        $html.= $config->getRedirectMessage();
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("SecureHosting_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}
