<?php 
class Cartthrob_ideal_rabobank extends Cartthrob_payment_gateway
{
	public $title = 'rabobank_title';
 	public $overview = 'rabobank_overview';
 	public $settings = array(
		array(
			'name' =>  'rabobank_merchant_id', 
			'short_name' => 'merchant_id', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'rabobank_secret_key',
			'short_name' => 'secret_key', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'rabobank_key_version',
			'short_name' => 'key_version', 
			'type' => 'text', 
			'default' => '', 
		),
		array(
			'name' =>  'mode',
			'short_name' => 'mode',
			'type' => 'radio',
			'default' => 'test',
			'options' => array(
				'test' => 'test',
				'pre-live' => 'rabobank_pre_live',
				'live' => 'live',
			)
		),
		array(
			'name' =>  'rabobank_test_amount',
			'short_name' => 'test_amount', 
			'type' => 'select',
			'default' => 'other',
			'rabobank_test_amount_note',
			'options'	=> array(
					'test_200'		=> 'rabobank_cancelled',
					'test_300'		=> 'rabobank_expired',
					'test_400'		=> 'rabobank_opened',
					'test_500'		=> 'rabobank_failure',
					'other'		=> 'rabobank_not_specified',
				), 
		),
		array(
			'name' =>  'rabobank_payment_methods',
			'short_name' => 'payment_methods', 
			'type' => 'multiselect', 
			'note'		=> 'rabobank_payment_methods_note',
			'options'	=> array(
				'MAESTRO'		=>'MAESTRO', 
				'MINITIX'		=> 'MINITIX',
				'MASTERCARD'	=> 'MASTERCARD',
				'VISA'			=> 'VISA',
				'IDEAL'			=> 'iDEAL',
			),
		),
 	);
	
	public $required_fields = array(
	);
	
	public $fields = array(
		'first_name'           ,
		'last_name'            ,
		'address'              ,
		'address2'             ,
		'city'                 ,
 		'zip'                  ,
		'country_code'         ,
		'shipping_first_name'  ,
		'shipping_last_name'   ,
		'shipping_address'     ,
		'shipping_address2'    ,
		'shipping_city'        ,
 		'shipping_zip'         ,
		'shipping_country_code',
		'phone'                ,
		'email_address'        ,
		);
	
 
	public 	$host			= "https://payment-webinit.simu.omnikassa.rabobank.nl/paymentServlet"; 
	private $merchant_id 	= "002020000000001";
	private $secret_key		= "002020000000001_KEY1";
	private $key_version	= "1";
	
	public function initialize()
	{
		if ($this->plugin_settings('mode')== "live" || $this->plugin_settings('mode') == "pre-live")
		{
			$this->host = "https://payment-webinit.omnikassa.rabobank.nl/paymentServlet"; 
			$this->merchant_id = $this->plugin_settings('merchant_id'); 
			$this->secret_key = $this->plugin_settings('secret_key'); 
			$this->key_version = $this->plugin_settings('key_version');
		}
	}
	/**
	 * process_payment
	 *
 	 * @param string $credit_card_number 
	 * @return mixed | array | bool An array of error / success messages  is returned, or FALSE if all fails.
	 * @author Chris Newton
	 * @access public
	 * @since 1.0.0
	 */
	public function process_payment($credit_card_number)
	{
		// requires values to be without , . 
		$total = $this->order('total')* 100; 
		
		if ($this->plugin_settings('mode')=="test" && $this->plugin_settings('test_amount')!="other")
		{
			$total = str_replace("test_", "", $this->plugin_settings('test_amount'));
			// uses preset amounts to test success and failure for iDeal
		}

		$data_to_hash = array(
			'amount'			=> $total ,
			'currencyCode'		=> $this->iso_currency_convert($this->order('currency_code')),  
			'merchantId'		=> $this->merchant_id,
			'normalReturnUrl'	=> $this->response_script(ucfirst(get_class($this))),
			'transactionReference'	=> $this->order('order_id') ."t". time(),
			'keyVersion'		=> $this->key_version,
			'customerLanguage'	=> ($this->order('language') ? strtolower($this->order('language')) : "nl" ),
			'automaticResponseUrl'	=> $this->response_script(ucfirst(get_class($this))),
			'orderId'	=> $this->order('order_id'),
 		); 
		
		if ($this->plugin_settings('payment_methods'))
		{
			foreach( $this->plugin_settings('payment_methods') as $value)
			{
				if (!isset($data_to_hash['paymentMeanBrandList']))
				{
					$data_to_hash['paymentMeanBrandList'] = $value;
				}
				else
				{
					$data_to_hash['paymentMeanBrandList'] .= ",".$value;
				}
			}
			// don't pass it along if it's ONLY ideal or MINITIX
			if (isset($data_to_hash['paymentMeanBrandList']) 
				&& ($data_to_hash['paymentMeanBrandList'] == "IDEAL" 
				|| $data_to_hash['paymentMeanBrandList'] == "MINITIX"))
				{
					unset($data_to_hash['paymentMeanBrandList']);
				}
		}
		$data = NULL;
		foreach( $data_to_hash as $key => $value)
		{
			// check for encoding. Data mut be in UTF-8, or seal will not work
		    $is_utf8 = mb_detect_encoding($value, 'UTF-8', true);
			if (!$is_utf8)
			{
				$value = utf8_encode($value);
			}
			$data.=$key ."=".$value."|";
		}
		// getting rid of last "|"
		$data = substr_replace($data ,"",-1);
		
		$hash = hash('sha256', $data.$this->secret_key); 
		// all other data should be sent in data_to_hash array. ignored if sent here
		$post_array = array(
			'Data'		=> $data,
			'InterfaceVersion'	=> 'HP_1.0',
			'Seal'				=> $hash,
		); 
		// generates a jump page. 
		$this->gateway_exit_offsite($post_array, NULL, $this->host); 
		exit; 
 
	}
	// END
	public function extload($post)
	{
		$auth['authorized']	 	= FALSE; 
		$auth['declined'] 		= FALSE; 
		$auth['transaction_id']	= NULL;
		$auth['failed']			= TRUE; 
		$auth['error_message']	= "";
		
		$orderId = NULL; 
		$responseCode = NULL;
		$authorisationId = NULL; 
		$paymentMeanBrand = NULL; 
		$transactionReference = NULL; 
		
		if (  $this->arr($post, "Data") )
		{
			// format is amount=1223|something=some| gotta get string into variables
			$new_array = array(); 
			$array = explode("|", $this->xss_clean($post['Data'])); 
			foreach( $array as $key => $value)
			{
				// getting at the name=value and adding it back to an array
				list($key, $value) = explode("=", $value);
				$new_array[$key] = $value;
			}
			
			// only getting out the variables we want from what was sent. 
			extract( $new_array, EXTR_IF_EXISTS ); 
			$order_id =  (isset($orderId) ? $orderId : NULL); 
			if (!$order_id)
			{
				$resp['error_message']	= $this->lang('rabobank_order_id_not_specified'); 
				$this->gateway_order_update($auth, $post['ct_action'] );
				exit; 
			}
		}
 		// relaunching entire cart. may have multiple sends, so we need to update users sesssion too.
		$this->relaunch_cart(NULL, $order_id);

		$response_data = $this->xss_clean($this->arr($post, "Data")); 
		$response_seal = $this->xss_clean($this->arr($post, "Seal")); 
		
		// non matching sha values
		if (hash('sha256', $response_data.$this->secret_key) != $response_seal) 
		{
			$auth = array(
				'authorized' 	=> FALSE,
				'error_message'	=>  $this->lang('rabobank_non_matching_sha'),
				'failed'		=> TRUE,
				'declined'		=> FALSE,
				'transaction_id'=> NULL 
				);
			$this->gateway_order_update($auth, $order_id, $this->order('return'));
			exit;
		}

		switch($responseCode)
		{
			case "00": 
				$auth = array(
					'authorized' 	=> TRUE,
					'error_message'	=> NULL,
					'failed'		=> FALSE,
					'declined'		=> FALSE,
					'transaction_id'=> ($authorisationId? $authorisationId: $transactionReference), //authorization id is only returned on ideal transactions
					);
			break;
			case "02": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_02'),
					'failed'		=> FALSE,
					'declined'		=> TRUE,
					'transaction_id'=> NULL 
					);
			break;
			case "17": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_17'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
			break;
			case "40": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_40'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
			break;
			case "60": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_60'),
					'failed'		=> FALSE,
					'declined'		=> FALSE,
					'processing'	=> TRUE,
					'transaction_id'=> NULL 
					);
			break;
			case "75": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_75'),
					'failed'		=> FALSE,
					'declined'		=> TRUE,
					'transaction_id'=> NULL 
					);
			break;
			case "90": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_90'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
			break;
			case "97": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_97'),
					'failed'		=> FALSE,
					'expired'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
			break;
			case "99": 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ".$this->lang('rabobank_response_default'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
			break;
			default: 
				$auth = array(
					'authorized' 	=> FALSE,
					'error_message'	=> $this->lang('rabobank_error_code_number').":".$responseCode." ". $this->lang('rabobank_response_default'),
					'failed'		=> TRUE,
					'declined'		=> FALSE,
					'transaction_id'=> NULL 
					);
					
		}
		//adding the payment type
  		$this->update_order(array('card_type' =>  $paymentMeanBrand));
		$this->gateway_order_update($auth, $order_id, $this->order('return'));
		exit;
	}
	function iso_currency_convert($currency_code)
	{
		$codes['EUR'] = array('Euro', '978');
		$codes['USD'] = array('US Dollars', '840');
		$codes['CHF'] = array('Swiss Francs', '756');
		$codes['GBP'] = array('British Pounds Sterling', '826');
		$codes['CAD'] = array('Canadian Dollars', '124');
		$codes['JPY'] = array('Japanese yen', '392');
		$codes['MXN'] = array('Mexican Pesos', '484');
		$codes['AUD'] = array('Australian Dollars', '036');
		$codes['NZD'] = array('New Zealand Dollars', '554');
		$codes['NOK'] = array('Norwegian Krone', '578');
		$codes['BRL'] = array('Brazilian Real', '986');
		$codes['ARS'] = array('Argentine Peso', '032');
		$codes['KHR'] = array('Cambodia Riel', '116');
		$codes['TWD'] = array('New Taiwanese Dollars', '901');
		$codes['SEK'] = array('Swedish Krona', '752');
		$codes['DKK'] = array('Danish Krone', '208');
		$codes['KRW'] = array('South Korean Won', '410');
		$codes['SGD'] = array('Singapore Dollars', '702');
 
		if (array_key_exists($currency_code, $codes))
		{
			return $codes[$currency_code][1]; 
		}
		return "978";
	}
	function arr($array, $key)
	{
		if (isset($array[$key]))
		{
			return $array[$key]; 
		}
		else
		{
			return NULL; 
		}
	}
 
}
// END Class