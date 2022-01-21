<?php
class ControllerExtensionMstorePaymentMethod extends Controller {
	private $error = array();
	/**
	 * @api {get} /index.php?route=extension/mstore/payment_method Get payment methods
	 * @apiVersion 0.1.0
	 * @apiName Get payment methods
	 * @apiGroup Checkout
	 *
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * @apiSuccess {Object} data.payment_methods
	 * @apiSuccess {String} data.code
	 * @apiSuccess {String} data.comment
	 */
	public function index() {
		$this->load->language('checkout/checkout');

		if (isset($this->session->data['payment_address'])) {
			// Totals
			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array.
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
					
					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			// Payment Methods
			$method_data = array();

			$this->load->model('setting/extension');

			$results = $this->model_setting_extension->getExtensions('payment');

			$recurring = $this->cart->hasRecurringProducts();

			foreach ($results as $result) {
				if ($this->config->get('payment_' . $result['code'] . '_status')) {
					$this->load->model('extension/payment/' . $result['code']);

					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

					if ($method) {
						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								$method_data[$result['code']] = $method;
							}
						} else {
							$method_data[$result['code']] = $method;
						}
					}
				}
			}

			$sort_order = array();

			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $method_data);

			$this->session->data['payment_methods'] = $method_data;
		}

		if (empty($this->session->data['payment_methods'])) {
			$data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['payment_methods'])) {
			$data['payment_methods'] = $this->session->data['payment_methods'];
		} else {
			$data['payment_methods'] = array();
		}

		if (isset($this->session->data['payment_method']['code'])) {
			$data['code'] = $this->session->data['payment_method']['code'];
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
	 * @api {post} /index.php?route=extension/mstore/payment_method/save Set payment method
	 * @apiVersion 0.1.0
	 * @apiName Set payment method
	 * @apiGroup Checkout
	 *
	 * @apiParam {String} payment_method
	 * @apiParam {String} agree 1 or 0
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

		// Validate if payment address has been set.
		if (!isset($this->session->data['payment_address'])) {
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please set payment address"], "data"=>[]]));
			return;
		}

		$json = file_get_contents('php://input');
		$params = (array) json_decode($json);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate($params)) {
			$this->session->data['payment_method'] = $this->session->data['payment_methods'][$params['payment_method']];
			$this->session->data['comment'] = strip_tags($params['comment']);
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>[]]));
		}else{
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function validate($params){

		if (!isset($params['payment_method'])) {
			$this->error['warning'] = $this->language->get('error_payment');
		} elseif (!isset($this->session->data['payment_methods'][$params['payment_method']])) {
			$this->error['warning'] = $this->language->get('error_payment');
		}

		return !$this->error;
	}
}
