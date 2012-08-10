<?php 

	$lang = array(
		// CUSTOMER FACING ERRORS: 
		'rabobank_non_matching_sha'		=> 'Non-matching SHA values suggest that URL was tampered with, and that transaction is being made fraudulently',
		
		'rabobank_error_code_02'			=> 'Please try a different card. The authorization limit on this card has been exceeded. ',
		'rabobank_error_code_17'		=> 'Transaction cancelled by customer',
		'rabobank_error_code_40'			=> 'This transaction type is not supported by this merchant.',
		'rabobank_error_code_60'			=> 'The iDeal transaction is currently pending',
		'rabobank_error_code_75'			=> 'The number of attempts to enter the card number has been exceeded (three tries exhausted), please try a different card.',
		'rabobank_error_code_90'			=> 'This service is temporarily unavailable. Please try again.',
		'rabobank_error_code_97'			=> 'The transaction has expired.',
		'rabobank_error_code_99'			=> 'This service is temporarily unavailable. Please try again.',
		'rabobank_error_code_number'		=> 'Response Code',
		'rabobank_response_default'			=> 'Transaction was declined. Please try again.',
		
		// BACKEND
		'rabobank_title'		=> 'Ideal (Rabobank)',
		'rabobank_overview'		=> '
		<p>This gateway makes use of a file called extload.php which is contained in the CartThrob themes/lib folder. If you have renamed your system folder, or you have moved it relative to your site\'s index.php page, you will need to update this location in the extload.php file. </p>
		
		<h3>Testing</h3>
		<p>During testing you can use the following credit card numbers to test: <br />
		<strong>Visa (Success):</strong> 4100000000000000 <br />
		<strong>Visa (Declined):</strong> 4100000000000002 <br />
		<strong>Visa (Failure):</strong> 4100000000000040 <br />
		<strong>Visa (Canceled):</strong> 4100000000000017 <br />
		<strong>Mastercard (Success):</strong> 5100000000000000 <br />
		<strong>Mastercard (Declined):</strong> 5100000000000002 <br />
		<strong>Mastercard (Failure):</strong> 5100000000000040 <br />
		<strong>Mastercard (Canceled):</strong> 5100000000000017 <br />
		<strong>Maestro (Success):</strong> 5000000000000000
		<strong>Maestro (Declined):</strong> 5000000000000002
		<strong>Maestro (Failure):</strong> 5000000000000040
		<strong>Maestro (Canceled):</strong> 5000000000000017
		</p>
		</h3>Pre-Live Tests</h3>
		<p>Before your account is set to "live" mode by the merchant-support team you can conduct "pre-live" tests. During the PRE-LIVE tests, you have to use a real credit card number, Ideal account and Minitix account because the transactions are sent to the real acquirer to get an authorization. When the pre-live tests are finished and completed, send an approval form to the merchant support team to notify that you are ready to go to "live" mode. 
		</p>
		
		<h3>Account Set Up</h3>
		<p>
			The merchantID is provided by the support team during the registration phase.
			You get keyVersion and the secretKey from <a href="https://download.omnikassa.rabobank.nl">https://download.omnikassa.rabobank.nl</a> using the login and password given by the support team during the registration phase.
			</p>
		',
		'rabobank_merchant_id'      => 'Merchant ID',
		'rabobank_secret_key'       => 'Secret Key',
		'rabobank_key_version'      => 'Key Version',
		'rabobank_test_amount'      => 'Test case',
		'rabobank_test_amount_note'	=> 'During testing, you can specify the response you would like to receive from iDeal',
		'rabobank_failure'          => 'Failure',
		'rabobank_opened'           => 'Opened',
		'rabobank_expired'          => 'Expired',
		'rabobank_cancelled'        => 'Cancelled',
		'rabobank_not_specified'			=> 'Do not specify',
		'rabobank_pre_live'		=> 'Pre-live',
		'rabobank_payment_methods'	=> 'Available Payment Methods',
		'rabobank_payment_methods_note'	=> 'If only IDEAL or only MINITIX is selected, all payment methods will be shown.',
		
	);