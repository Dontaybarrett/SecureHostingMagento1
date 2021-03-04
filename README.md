# Legacy Magento Cart

## Installation


1.	Copy the directory "app" to the root of your shopping cart where you should already find directory called "app".

2.	Upload the below HTML files to your Secure Hosting account. We recommend uploading the default files first, testing,
	then amending these files as required. File uploads are managed within your Secure Hosting	and Payments account via 
	the menu option 'Settings -> File Manager':
	- magento_template.html
	- htmlgood.html
	- htmlbad.html

3.	Delete the content of the directory /{site root}/var/cache/, don't worry Magento will rebuild this without a problem.
   
4.	Go into the Magento Admin Interface and open the payment modules under 'System -> Configuration -> Sales -> Payment Methods',
	you will find SecureHosting now appears as a payment module. Expand the module and enable it. You will also need to
	configure the below settings in order to complete the basic set-up:

	- SH Reference - This is the SH Reference for your Secure Hosting and Payments account. This is also known as the Client
		Login, you will find the value for this within the Company Details section of your Secure Hosting and Payments account.

	- Check Code - This is the second level security check code for your Secure Hosting and Payments account, it is a second
		unique identifier for your account. The value of your check code can be found within the Comany Details 
		section of your Secure Hosting and Payments account.

	- File Name - This is the file name of the payment page template you need to upload to your Secure Hosting and Payments account. The file name of the example template provided with this integration module is 
		"magento_template.html". You can rename this file if you desire, you only need to ensure the name of the file you upload to your Secure Hosting and Payments account is correctly set here.

5. The Magento integration uses the Callbacks from Secure Hosting feature to update your Magento backend, confirming that an order has been placed. It is strongly recommended that the Callbacks are secured by a Shared Secret value, generating a unique verification string, known only to yourself and Secure Hosting. The secret phrase should be 8 characters or longer. In order to activate the Shared Secret callback verification, enter your Shared Secret value in the Magento admin interface and within the Advanced Settings of your Secure Hosting account. The Shared Secret value can be anything you want, it just needs to be the same in both Magento and Secure Hosting. For more information on verifying Callbacks, please refer to page 22 of the SHP Technical Integration Guide which can be found https://cs.monek.com/portal/kb/articles/shp-technical-integration-guide.

6.  The transaction process is as follows:
	- Customer redirects from your website to the Secure Hosting payment template to enter card details. (Order is created within Magento and is set to Pending).
	- Customer enters card details and redirects back to the Magento success page. (Order is updated to Processing).
	- Secure Hosting makes a callback to your website to confirm payment has been made. (Order is updated to Complete).
	- Goods can now be shipped.

7.	You can enable test mode and put through test transactions using the test card details within our integration guide. Don't forget to turn this off before going live!



## Advanced Configuration

### Advanced Secuitems


The Secure Hosting and Payments system supports the facility to secure your checkout from tampering, the facility is supported
by the Advanced Secuitems feature. In order for the Advanced Secuitems to work correctly, it must be correctly configured in
both the Magento Admin Interface and your Secure Hosting and Payments account. The settings for the Advanced Secuitems functionality
within your Secure Hosting and Payments account are found within the Advanced Settings section of the account.

1.	Activate Advanced Secuitems - In order to activate use of the Advanced Secuitems, set to "Yes". You will also need to activate
		the feature within your Secure Hosting and Payments account, this is performed by checking the below setting: 
	- Activate advanced secuitems security -
	
	In addition to activating the Advanced Secuitems in your Secure Hosting account, you must enter "transactionamount" into the list of fields to be encrypted.
	
2.	Advanced Secuitems Phrase - When securing your checkout, the Secure Hosting and Payments system uses a unique phrase to create it's
		encrypted string. The phrase entered into your Magento web site here must match the phrase configured within the Advanced Settings section
		of your Secure Hosting and Payments account otherwise the system will block your transactions.
	
3.	Shopping Cart Referrer - As part of the security in generating the encrypted string, the Secure Hosting and Payments system needs to verify the
		shopping cart request, this is done by checking the referrer. The referrer configured here must match the referrer configured within your
		Secure Hosting and Payments account within the Advances Settings. An example referrer for your site would be "http://www.example.com/index.php".

