<?php
class ControllerExtensionMstoreShippingMethod extends Controller {
	private $error = array();

	/**
	 * @api {get} /index.php?route=extension/mstore/shipping_method Get shipping methods
	 * @apiVersion 0.1.0
	 * @apiName Get shipping methods
	 * @apiGroup Checkout
	 *
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * @apiSuccess {Object} data.shipping_methods
	 * @apiSuccess {String} data.code
	 * @apiSuccess {String} data.comment
	 */
	public function index() {
		$this->load->language('checkout/checkout');

		if (isset($this->session->data['shipping_address'])) {
			// Shipping Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('shipping');
			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status') === "1") {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);
					if ($quote) {
						$method_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['shipping_methods'] = $method_data;
		}

		if (empty($this->session->data['shipping_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['shipping_methods'])) {
			$data['shipping_methods'] = $this->session->data['shipping_methods'];
		} else {
			$data['shipping_methods'] = array();
		}

		if (isset($this->session->data['shipping_method']['code'])) {
			$data['code'] = $this->session->data['shipping_method']['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->session->data['comment'])) {
			$data['comment'] = $this->session->data['comment'];
		} else {
			$data['comment'] = '';
		}
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$data]));
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/shipping_method/save Set shipping method
	 * @apiVersion 0.1.0
	 * @apiName Set shipping method
	 * @apiGroup Checkout
	 *
	 * @apiParam {String} shipping_method
	 * @apiParam {String} comment
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data []
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

		// Validate if shipping address has been set.
		if (!isset($this->session->data['shipping_address'])) {
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please set shipping address"], "data"=>[]]));
			return;
		}

		$json = file_get_contents('php://input');
		$params = (array) json_decode($json);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($params)) {
			$shipping = explode('.', $params['shipping_method']);
			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
			$this->session->data['comment'] = strip_tags($params['comment']);
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>[]]));
		}else{
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function validate($params){
		if (!isset($params['shipping_method']) || $params['shipping_method'] == '') {
			$this->error['shipping_method'] = $this->language->get('error_shipping');
		}else{
			$shipping = explode('.', $params['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$this->error['shipping_method'] = $this->language->get('error_shipping');
			}
		}

		return !$this->error;
	}
}