<?php
class ControllerExtensionMstorePaymentAddress extends Controller {
	private $error = array();
	
	/**
	 * @api {post} /index.php?route=extension/mstore/payment_address/save Set payment address
	 * @apiVersion 0.1.0
	 * @apiName Set payment address
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
			$this->session->data['payment_address'] = $this->model_account_address->getAddress($address_id);
			$this->session->data['payment_address']["email"] = $params["email"];
			$this->session->data['payment_address']["telephone"] = $params["telephone"];
				
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>[]]));
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