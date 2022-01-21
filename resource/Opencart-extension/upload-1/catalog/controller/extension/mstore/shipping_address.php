<?php
class ControllerExtensionMstoreShippingAddress extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('account/address');
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$this->model_account_address->getAddresses()]));
	}

	/**
	 * @api {get} /index.php?route=extension/mstore/shipping_address/countries Get all countries
	 * @apiVersion 0.1.0
	 * @apiName Get all countries
	 * @apiGroup Checkout
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data []
	 * 
	 */
	public function countries() {
		$this->load->model('localisation/country');
		$countries = $this->model_localisation_country->getCountries();
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$countries]));
	}

	/**
	 * @api {get} /index.php?route=extension/mstore/shipping_address/states Get states by country
	 * @apiVersion 0.1.0
	 * @apiName Get states by country
	 * @apiGroup Checkout
	 * 
	 * @apiParam {String} countryId
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data []
	 * 
	 */
	public function states() {
		$this->load->model('localisation/zone');
		if (isset($this->request->get['countryId'])) {
			$countryId = $this->request->get['countryId'];
		} else {
			$countryId = -1;
		}
		$states = $this->model_localisation_zone->getZonesByCountryId($countryId);
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$states]));
	}


	/**
	 * @api {post} /index.php?route=extension/mstore/shipping_address/save Set shipping address
	 * @apiVersion 0.1.0
	 * @apiName Set shipping address
	 * @apiGroup Checkout
	 *
	 * @apiParam {String} firstname
	 * @apiParam {String} lastname
	 * @apiParam {String} city
	 * @apiParam {String} address_1
	 * @apiParam {String} address_2
	 * @apiParam {String} country_id
	 * @apiParam {String} postcode
	 * @apiParam {Number} zone_id
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data []
	 * 
	 */
	public function save() {
		$this->load->language('checkout/checkout');
		
		$json = array();

		// Validate if customer is logged in.
		// if (!$this->customer->isLogged()) {
		// 	$this->response->addHeader('HTTP/1.0 401 Unauthorized');
		// 	$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please login"], "data"=>[]]));
		// 	return;
		// }

		$json = file_get_contents('php://input');
		$params = (array) json_decode($json);
		$params["company"] = "";

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($params)) {
			$this->load->model('account/address');
			$this->load->model('extension/mstore/address');

			$address_id = $this->model_extension_mstore_address->findOrAddAddress($this->customer->getId(), $params);
			$this->session->data['shipping_address'] = $this->model_account_address->getAddress($address_id);
	
				// If no default address ID set we use the last address
				if (!$this->customer->getAddressId()) {
					$this->load->model('account/customer');
					
					$this->model_account_customer->editAddressId($this->customer->getId(), $address_id);
				}
				
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$this->session->data['shipping_address']]));
		}else{
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function validate($params){
		if ((utf8_strlen(trim($params['firstname'])) < 1) || (utf8_strlen(trim($params['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($params['lastname'])) < 1) || (utf8_strlen(trim($params['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen(trim($params['address_1'])) < 3) || (utf8_strlen(trim($params['address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($params['city'])) < 2) || (utf8_strlen(trim($params['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($params['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($params['postcode'])) < 2 || utf8_strlen(trim($params['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($params['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($params['zone_id']) || $params['zone_id'] == '' || !is_numeric($params['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		return !$this->error;
	}
}