<?php
class ControllerExtensionMstoreProduct extends Controller {
	private $error = array();

	/**
	 * @api {get} /index.php?route=extension/mstore/product Get products
	 * @apiVersion 0.1.0
	 * @apiName Get products
	 * @apiGroup Catalog
	 *
	 * 
	 * @apiParam {Number} [category] Get products by Category ID
	 * @apiParam {String} [search] Search products by name
	 * @apiParam {Number} [max_price] Filter products by max price
	 * @apiParam {String} [sort] Default: p.sort_order
	 * @apiParam {String} [order] Default: ASC
	 * @apiParam {Number} [page] Default: 1
	 * @apiParam {Number} [limit] Default: 10
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data  List Product.
	 * @apiSuccess {String} data.product_id
	 * @apiSuccess {String} data.name
	 * @apiSuccess {String} data.description
	 * @apiSuccess {String} data.meta_title
	 * @apiSuccess {String} data.meta_description
	 * @apiSuccess {String} data.meta_keyword
	 * @apiSuccess {String} data.tag
	 * @apiSuccess {String} data.model
	 * @apiSuccess {String} data.sku
	 * @apiSuccess {String} data.upc
	 * @apiSuccess {String} data.ean
	 * @apiSuccess {String} data.jan
	 * @apiSuccess {String} data.isbn
	 * @apiSuccess {String} data.mpn
	 * @apiSuccess {String} data.location
	 * @apiSuccess {String} data.quantity
	 * @apiSuccess {String} data.stock_status
	 * @apiSuccess {String} data.manufacturer_id
	 * @apiSuccess {String} data.manufacturer
	 * @apiSuccess {String} data.price
	 * @apiSuccess {String} data.special
	 * @apiSuccess {String} data.reward
	 * @apiSuccess {String} data.points
	 * @apiSuccess {String} data.tax_class_id
	 * @apiSuccess {String} data.date_available
	 * @apiSuccess {String} data.weight
	 * @apiSuccess {String} data.weight_class_id
	 * @apiSuccess {String} data.length
	 * @apiSuccess {String} data.width
	 * @apiSuccess {String} data.height
	 * @apiSuccess {String} data.length_class_id
	 * @apiSuccess {String} data.subtract
	 * @apiSuccess {Number} data.rating
	 * @apiSuccess {Number} data.reviews
	 * @apiSuccess {String} data.minimum
	 * @apiSuccess {String} data.sort_order
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * @apiSuccess {String} data.date_modified
	 * @apiSuccess {String} data.viewed
	 * @apiSuccess {Array} data.images
	 */

	public function index() {
		$this->load->model('catalog/product');
		$this->load->model('extension/mstore/product');
		$this->load->model('tool/image');

		if (isset($this->request->get['category'])) {
			$category = (int)$this->request->get['category'];
		}

		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		}

		if (isset($this->request->get['max_price'])) {
			$max_price = $this->request->get['max_price'];
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 10;
		}

		$filter_data = array(
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
		);

		if (isset($category)) {
			$filter_data["filter_category_id"] = $category;
			$filter_data["filter_sub_category"] = true;
		}
		if (isset($search)) {
			$filter_data["filter_name"] = $search;
		}
		if (isset($max_price)) {
			$filter_data["filter_max_price"] = $max_price;
		}

		$lang = "en-gb";
		if(isset($this->request->get['lang'])){
			switch ($this->request->get['lang']) {
				case "ru":
					$lang = "ru-ru";
					break;
				case "en":
					$lang = "en-gb";
					break;
				case "he":
					$lang = "he-il";
					break;
				default:
					$lang = $this->request->get['lang'];
			  }
		}

		$results = $this->model_extension_mstore_product->getProducts($filter_data, $lang);
		$data = array();

		foreach ($results as $result) {
			$options=$this->model_catalog_product->getProductOptions($result["product_id"]);
			$result["options"] = $options;
			$images = $this->model_catalog_product->getProductImages($result["product_id"]);
			$thumbnails = array();
			$thumbnails[]= $this->model_tool_image->resize($result['image'], 500, 500);
			foreach ($images as $image) {
				if ($image['image']) {
					$thumbnails[]= $this->model_tool_image->resize($image['image'], 500, 500);
				} else {
					$thumbnails[] = $this->model_tool_image->resize('placeholder.png', 500, 500);
				}
			}
			$result["images"] = $thumbnails;
			$result['price'] = $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'));
			$result['price'] = number_format($result['price'], 2, '.', '');
			$data[] = $result;
		}
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$data]));
	}
}
