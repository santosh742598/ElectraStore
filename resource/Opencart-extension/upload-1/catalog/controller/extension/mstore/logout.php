<?php
class ControllerExtensionMstoreLogout extends Controller {
	private $error = array();

	/**
	 * @api {post} /index.php?route=extension/mstore/account Logout
	 * @apiVersion 0.1.0
	 * @apiName Logout
	 * @apiGroup Account
	 *
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Array} data  [].
	 * 
	 */
	public function index() {

		if ($this->customer->isLogged()) {
			$this->customer->logout();
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>[]]));
		}else{
			$this->response->addHeader('HTTP/1.0 401 Unauthorized');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["No Permission"], "data"=>[]]));
		}

	}
}