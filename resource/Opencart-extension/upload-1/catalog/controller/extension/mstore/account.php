<?php
class ControllerExtensionMstoreAccount extends Controller {
	private $error = array();

	/**
	 * @api {post} /index.php?route=extension/mstore/account/logout Logout
	 * @apiVersion 0.1.0
	 * @apiName Logout
	 * @apiGroup Account
	 *
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * 
	 */
	public function logout() {
		$this->customer->logout();
		$this->cart->clear();
		$this->response->setOutput(json_encode(["success"=>1, "error"=>[]]));
	}

	/**
	 * @api {get} /index.php?route=extension/mstore/account Get user info
	 * @apiVersion 0.1.0
	 * @apiName Get user info
	 * @apiGroup Account
	 *
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data  User info.
	 * @apiSuccess {String} data.customer_id
	 * @apiSuccess {String} data.customer_group_id
	 * @apiSuccess {String} data.store_id
	 * @apiSuccess {String} data.language_id
	 * @apiSuccess {String} data.firstname
	 * @apiSuccess {String} data.lastname
	 * @apiSuccess {String} data.email
	 * @apiSuccess {String} data.telephone
	 * @apiSuccess {String} data.fax
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * 
	 */
	public function index() {
		$this->load->language('account/login');
		$this->load->model('account/customer');

		if (!$this->customer->isLogged()) {
			$this->response->addHeader('HTTP/1.0 401 Unauthorized');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please login to get user info"], "data"=>[]]));
		}else{
			$customer = $this->model_account_customer->getCustomer($this->customer->getId());
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
		}
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/account/login Login
	 * @apiVersion 0.1.0
	 * @apiName Login
	 * @apiGroup Account
	 *
	 * @apiParam {String} email
	 * @apiParam {String} password
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data  User info.
	 * @apiSuccess {String} data.customer_id
	 * @apiSuccess {String} data.customer_group_id
	 * @apiSuccess {String} data.store_id
	 * @apiSuccess {String} data.language_id
	 * @apiSuccess {String} data.firstname
	 * @apiSuccess {String} data.lastname
	 * @apiSuccess {String} data.email
	 * @apiSuccess {String} data.telephone
	 * @apiSuccess {String} data.fax
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * 
	 */
	public function login() {

		$this->load->language('account/login');
		$this->load->model('account/customer');

		$json = file_get_contents('php://input');
		$params = (array) json_decode($json);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateLogin($params)) {
			$customer = $this->model_account_customer->getCustomer($this->customer->getId());
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
		}else{
			$this->response->addHeader('HTTP/1.0 401 Unauthorized');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function validateLogin($params) {
		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($params['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($params['email']);

		if ($customer_info && !$customer_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($params['email'], $params['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($params['email']);
			} else {
				$this->model_account_customer->deleteLoginAttempts($params['email']);
			}
		}

		return !$this->error;
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/account/register Register
	 * @apiVersion 0.1.0
	 * @apiName Register
	 * @apiGroup Account
	 *
	 * @apiParam {String} firstname
	 * @apiParam {String} lastname
	 * @apiParam {String} email
	 * @apiParam {String} telephone
	 * @apiParam {String} password
	 * @apiParam {String} confirm
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data  User info.
	 * @apiSuccess {String} data.customer_id
	 * @apiSuccess {String} data.customer_group_id
	 * @apiSuccess {String} data.store_id
	 * @apiSuccess {String} data.language_id
	 * @apiSuccess {String} data.firstname
	 * @apiSuccess {String} data.lastname
	 * @apiSuccess {String} data.email
	 * @apiSuccess {String} data.telephone
	 * @apiSuccess {String} data.fax
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * 
	 */
	public function register() {

		$this->load->language('account/register');
		$this->load->model('account/customer');

		$json = file_get_contents('php://input');
		$params = json_decode($json);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateRegister($params)) {
			$customer_id = $this->model_account_customer->addCustomer((array) $params);
			if ($this->validateLogin((array)$params)) {
				$customer = $this->model_account_customer->getCustomer($this->customer->getId());
				$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
			}else{
				$this->response->addHeader('HTTP/1.0 400 Bad Request');
				$this->response->setOutput(json_encode(["success"=>0, "error"=>["Register fail"], "data"=>[]]));
			}
		}else{
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function validateRegister($params) {
		if (!isset($params->firstname) || (utf8_strlen(trim($params->firstname)) < 1) || (utf8_strlen(trim($params->firstname)) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		
		if (!isset($params->lastname) || (utf8_strlen(trim($params->lastname)) < 1) || (utf8_strlen(trim($params->lastname)) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		// if (!isset($params->telephone) || (utf8_strlen(trim($params->telephone)) < 1) || (utf8_strlen(trim($params->telephone)) > 32)) {
		// 	$this->error['telephone'] = $this->language->get('error_telephone');
		// }

		if (!isset($params->email) || (utf8_strlen($params->email) > 96) || !filter_var($params->email, FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}elseif ($this->model_account_customer->getTotalCustomersByEmail($params->email)) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if (!isset($params->password) || (utf8_strlen(html_entity_decode($params->password, ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($params->password, ENT_QUOTES, 'UTF-8')) > 40)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if (!isset($params->confirm) || $params->confirm != $params->password) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}
		
		return !$this->error;
	}

	/**
	 * @api {post} /index.php?route=extension/mstore/account/socialLogin Social Login
	 * @apiVersion 0.1.0
	 * @apiName Social Login
	 * @apiGroup Account
	 *
	 * @apiParam {String} token
	 * @apiParam {String} type facbook,google,sms,apple,firebase_sms
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data  User info.
	 * @apiSuccess {String} data.customer_id
	 * @apiSuccess {String} data.customer_group_id
	 * @apiSuccess {String} data.store_id
	 * @apiSuccess {String} data.language_id
	 * @apiSuccess {String} data.firstname
	 * @apiSuccess {String} data.lastname
	 * @apiSuccess {String} data.email
	 * @apiSuccess {String} data.telephone
	 * @apiSuccess {String} data.fax
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * 
	 */
	public function socialLogin() {

		$this->load->language('account/login');
		$this->load->model('account/customer');

		$json = file_get_contents('php://input');
		$params = (array) json_decode($json);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSocialLogin($params)) {
			$customer = $this->model_account_customer->getCustomer($this->customer->getId());
			$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
		}else{
			$this->response->addHeader('HTTP/1.0 401 Unauthorized');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>array_values($this->error), "data"=>[]]));
		}
	}

	private function jwtDecode($token){
        $splitToken = explode(".", $token);
        $payloadBase64 = $splitToken[1]; // Payload is always the index 1
        $decodedPayload = json_decode(urldecode(base64_decode($payloadBase64)), true);
        return $decodedPayload;
	}
	
	private function validateSocialLogin($params) {
		if (isset($params["token"]) && isset($params["type"])) {
			$type = $params["type"];
			$token = $params["token"];
			if ($type == "facebook") {
				$fields = "id,name,first_name,last_name,email,picture.type(large)";
				$url = 'https://graph.facebook.com/me/?fields='.$fields.'&access_token=' . $token;
	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				$result = curl_exec($ch);
				curl_close($ch);
	
				$result = json_decode($result, true);
	
				if (isset($result["email"])) {
					$firstName = $result["first_name"];
					$lastName = $result["last_name"];
					$email = $result["email"];
					$avatar = isset($result["picture"]) &&  isset($result["picture"]["data"]) ? $result["picture"]["data"]["url"] : "";
					return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
				} else {
					$this->error['warning'] = "Your 'token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Facebook app.";
				}
			}elseif($type == "google"){
				$url = 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=' . $token;
	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec($ch);
				curl_close($ch);
	
				$result = json_decode($result, true);
	
				if (isset($result["email"])) {
					$firstName = $result["given_name"];
					$lastName = $result["family_name"];
					$email = $result["email"];
					$avatar = $result["picture"];
					return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
				} else {
					$this->error['warning'] = "Your 'token' did not return email of the user. Without 'email' user can't be logged in or registered. Get user email extended permission while joining the Google app.";
				}
			}elseif($type == "sms"){
				$url = 'https://graph.accountkit.com/v1.3/me/?access_token=' . $token;
	
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec($ch);
				curl_close($ch);
	
				$result = json_decode($result, true);
	
				if (isset($result["phone"])) {
					$firstName = $result["phone"]["country_prefix"];
					$lastName = $result["phone"]["national_number"];
					$email = $result["phone"]["national_number"]."@mstore.io";
					$avatar = "";
					return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
				} else {
					$this->error['warning'] = "Your 'token' did not return phone of the user. Without 'phone' user can't be logged in or registered. Get user phone extended permission while joining the app.";
				}
			}elseif($type == "firebase_sms"){
					$firstName = $token;
					$lastName = "";
					$email = $token."@mstore.io";
					$avatar = "";
					return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
			}elseif($type == "apple"){
				$decoded = $this->jwtDecode($token);
				$email = $decoded["email"];
				$firstName = explode("@", $email)[0];
				$lastName = "";
				$avatar = "";
				return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
			}
		}else{
			if($params["type"] == "apple"){
				$firstName = $params["fullName"];
				$lastName = "";
				$email = $params["email"];
				$avatar = "";
				return $this->createSocialLogin($firstName, $lastName, $email, $avatar);
			}else{
				$this->error['warning'] = "Invalid params";
				return !$this->error;
			}
		}
	}

	private function createSocialLogin($firstName, $lastName, $email, $avatar){
	   // Check how many login attempts have been made.
	   $login_info = $this->model_account_customer->getLoginAttempts($email);

	   if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
		   $this->error['warning'] = $this->language->get('error_attempts');
	   }

	   // Check if customer has been approved.
	   $customer_info = $this->model_account_customer->getCustomerByEmail($email);

	   if ($customer_info && !$customer_info['status']) {
		   $this->error['warning'] = $this->language->get('error_approved');
	   }

	   if (!$this->error) {
			if ($customer_info) {
				if (!$this->customer->login($email, "mstore123")) {
					$this->error['warning'] = $this->language->get('error_login');
					$this->model_account_customer->addLoginAttempt($email);
				} else {
					$this->model_account_customer->deleteLoginAttempts($email);
				}
			}else{
				$params['firstname'] = $firstName;
				$params['lastname'] = $lastName;
				$params['telephone'] = "";
				$params['email'] = $email;
				$params['password'] = "mstore123";
				$params['confirm'] = "mstore123";
				$customer_id = $this->model_account_customer->addCustomer((array) $params);
				if ($this->validateLogin((array)$params)) {
					$customer = $this->model_account_customer->getCustomer($this->customer->getId());
					$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
				}else{
					$this->response->addHeader('HTTP/1.0 400 Bad Request');
					$this->response->setOutput(json_encode(["success"=>0, "error"=>["Register fail"], "data"=>[]]));
				}
			}
	   }

	   return !$this->error;
	}

	/**
	 * @api {put} /index.php?route=extension/mstore/account/edit Update UserInfo
	 * @apiVersion 0.1.0
	 * @apiName UpdateUserInfo
	 * @apiGroup Account
	 *
	 * @apiParam {String} [firstname]
	 * @apiParam {String} [lastname]
	 * @apiParam {String} [email]
	 * @apiParam {String} [telephone]
	 * @apiParam {String} [password]
	 * 
	 * @apiSuccess {Number} success 1: Success, 0: Fail.
	 * @apiSuccess {Array} error  List error messages.
	 * @apiSuccess {Object} data  User info.
	 * @apiSuccess {String} data.customer_id
	 * @apiSuccess {String} data.customer_group_id
	 * @apiSuccess {String} data.store_id
	 * @apiSuccess {String} data.language_id
	 * @apiSuccess {String} data.firstname
	 * @apiSuccess {String} data.lastname
	 * @apiSuccess {String} data.email
	 * @apiSuccess {String} data.telephone
	 * @apiSuccess {String} data.fax
	 * @apiSuccess {String} data.status
	 * @apiSuccess {String} data.date_added
	 * 
	 */
	public function edit() {

		$this->load->language('account/register');
		$this->load->model('account/customer');

		$json = file_get_contents('php://input');
		$params = json_decode($json, true);

		if ($this->request->server['REQUEST_METHOD'] == 'PUT') {
			if (!$this->customer->isLogged()) {
				$this->response->addHeader('HTTP/1.0 401 Unauthorized');
				$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please login to update user info"], "data"=>[]]));
			}else{
				$customer = $this->model_account_customer->getCustomer($this->customer->getId());
				if(empty($customer)){
					$this->response->addHeader('HTTP/1.0 401 Unauthorized');
					$this->response->setOutput(json_encode(["success"=>0, "error"=>["Please login to update user info"], "data"=>[]]));
				}else{
					$data = ["firstname" => $customer["firstname"], "lastname" => $customer["lastname"], "email" => $customer["email"], "telephone" => $customer["telephone"], "custom_field" => $customer["custom_field"]];
					$data = array_merge($data, $params);
					$this->model_account_customer->editCustomer($this->customer->getId(), $data);
					$customer = $this->model_account_customer->getCustomer($this->customer->getId());
					if(isset($data["password"])){
						$this->model_account_customer->editPassword($customer["email"], $data["password"]);
					}
					$this->response->setOutput(json_encode(["success"=>1, "error"=>[], "data"=>$customer]));
				}
				
			}
		}else{
			$this->response->addHeader('HTTP/1.0 400 Bad Request');
			$this->response->setOutput(json_encode(["success"=>0, "error"=>["Method is incorrect"], "data"=>[]]));
		}
	}
}