<?php
class ControllerExtensionMstoreReview extends Controller {
	private $error = array();
	/**
	 * @api {get} /index.php?route=extension/mstore/review Get reviews by product
	 * @apiVersion 0.1.0
	 * @apiName Get reviews by product
	 * @apiGroup Catalog
	 *
	 * @apiParam {String} id Product ID
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data  List Review.
	 * @apiSuccess {String} data.review_id
	 * @apiSuccess {String} data.author  
	 * @apiSuccess {String} data.rating
	 * @apiSuccess {String} data.text  Review content
	 * @apiSuccess {String} data.product_id
	 * @apiSuccess {String} data.name   Product Name
	 * @apiSuccess {String} data.price
	 * @apiSuccess {String} data.date_added
	 */

	 /**
	 * @api {post} /index.php?route=extension/mstore/review Post review
	 * @apiVersion 0.1.0
	 * @apiName Post review
	 * @apiGroup Catalog
	 *
	 * @apiParam {String} name Author Name
	 * @apiParam {String} text Review content
	 * @apiParam {String} rating
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data  []
	 */
	public function index() {
		$this->load->language('extension/mstore/review');
		$this->load->model('catalog/review');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$json = file_get_contents('php://input');
			$params = (array) json_decode($json);

			if (isset($this->request->get['id'])) {
				$params["product_id"] = (int)$this->request->get['id'];
			}
			if ($this->validateForm($params)) {
				$this->model_catalog_review->addReview($params["product_id"], $params);
				$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>[]]));
			}else{
				$this->response->addHeader('HTTP/1.0 400 Bad Request');
				$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
			}
		}elseif ($this->request->server['REQUEST_METHOD'] == 'GET') {
			if (isset($this->request->get['id'])) {
				$product_id = (int)$this->request->get['id'];
			}
			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 0;
			}
	
			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = 10;
			}

			$reviews = $this->model_catalog_review->getReviewsByProductId($product_id, ($page - 1) * $limit, $limit);
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$reviews]));

		}
		

	}

	protected function validateForm($params) {
		if (!isset($params['product_id']) || !$params['product_id']) {
			$this->error['product'] = $this->language->get('error_product');
		}

		if ((utf8_strlen($params['name']) < 3) || (utf8_strlen($params['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_author');
		}

		if (utf8_strlen($params['text']) < 1) {
			$this->error['text'] = $this->language->get('error_text');
		}

		if (!isset($params['rating']) || $params['rating'] < 0 || $params['rating'] > 5) {
			$this->error['rating'] = $this->language->get('error_rating');
		}

		return !$this->error;
	}
}