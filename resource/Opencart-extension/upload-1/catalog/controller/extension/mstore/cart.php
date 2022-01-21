<?php
class ControllerExtensionMstoreCart extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('checkout/cart');
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$this->cart->getProducts()]));
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/cart/add Add products to cart
	 * @apiVersion 0.1.0
	 * @apiName Add products to cart
	 * @apiGroup Checkout
	 *
	 * 
	 * @apiParam {Array}  body
	 * @apiParam {String} body.product_id
	 * @apiParam {String} body.quantity
	 * 
	 * * @apiParamExample {array} Request-Example:
 	 *     [{
     *       "product_id": "40",
	 *        "quantity": "2"
 	 *     }]
 	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * @apiSuccess {Number} data.total_product_count
	 * 
	 */

	public function add() {
		$this->load->language('checkout/cart');

		$json = array();

		$json = file_get_contents('php://input');
		$params = json_decode($json);
		foreach ($params as $item) {
			if (isset($item->option)) {
				$option = $item->option;
			} else {
				$option = array();
			}
			$this->cart->add($item->product_id, $item->quantity, $option);
		}
		// Unset all shipping and payment methods
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);

		$data = ["total_product_count"=>$this->cart->countProducts()];
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$data]));
	}

	/**
	 * @api {delete} /index.php?route=extension/mstore/cart/emptyCart Empty cart
	 * @apiVersion 0.1.0
	 * @apiName Empty cart
	 * @apiGroup Checkout
	 *
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * @apiSuccess {Number} data.total_product_count
	 * 
	 */
	public function emptyCart() {
		$this->load->language('checkout/cart');
		if ($this->request->server['REQUEST_METHOD'] == 'DELETE') {
			$products = $this->cart->getProducts();
			foreach ($products as $item) {
				$this->cart->remove($item["cart_id"]);
			}
			// Unset all shipping and payment methods
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['coupon']);

			$data = ["total_product_count"=>$this->cart->countProducts()];
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$data]));
		}else{
			$this->response->addHeader('HTTP/1.0 404 Not Found');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["Method not found"], "data"=>[]]));
		}
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/cart/coupon Apply Coupon
	 * @apiVersion 0.1.0
	 * @apiName Apply Coupon
	 * @apiGroup Checkout
	 *
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * @apiSuccess {Number} data.total_product_count
	 * 
	 */
	public function coupon() {
		$this->load->language('extension/total/coupon');

		$json = file_get_contents('php://input');
		$params = json_decode($json);
		if (isset($params->coupon)) {
			$coupon = $params->coupon;
			$this->load->model('extension/total/coupon');
			$coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);
			if ($coupon_info) {
				$this->session->data['coupon'] = $coupon;
				$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$coupon_info]));
			}else{
				$this->error['error'] = $this->language->get('error_coupon');
				unset($this->session->data['coupon']);
				$this->response->addHeader('HTTP/1.0 400 Bad Request');
				$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
			}
		} else {
			$this->error['empty'] = $this->language->get('error_empty');
			unset($this->session->data['coupon']);
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	/**
	 * @api {get} /index.php?route=extension/mstore/cart/coupons Get Coupons
	 * @apiVersion 0.1.0
	 * @apiName Get Coupons
	 * @apiGroup Checkout
	 *
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data
	 * 
	 */
	public function coupons() {
		$this->load->language('extension/total/coupon');
		$this->load->model('extension/mstore/coupon');
		$coupons = $this->model_extension_mstore_coupon->getCoupons();
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$coupons]));
	}
}