<?php

namespace Opencart\Admin\Controller\Extension\Newsman\Module;

/**
 * Class Newsman
 *
 * @property \Opencart\System\Engine\Autoloader              $autoloader
 * @property \Opencart\System\Engine\Registry                $registry
 * @property \Newsman\Nzmloader                              $nzmloader
 * @property \Newsman\Nzmconfig                              $nzmconfig
 * @property \Newsman\Nzmsetup                               $nzmsetup
 * @property \Newsman\Nzmlogger                              $nzmlogger
 * @property \Newsman\Util\Version                           $nzmversion
 * @property \Opencart\System\Engine\Loader                  $load
 * @property \Opencart\System\Engine\Config                  $config
 * @property \Opencart\System\Library\Session                $session
 * @property \Opencart\System\Library\Request                $request
 * @property \Opencart\System\Library\Response               $response
 * @property \Opencart\System\Library\Document               $document
 * @property \Opencart\System\Library\Url                    $url
 * @property \Opencart\System\Library\Language               $language
 * @property \Opencart\Admin\Model\Setting\Setting           $model_setting_setting
 * @property \Opencart\Admin\Model\Setting\Store             $model_setting_store
 * @property \Opencart\Admin\Model\Extension\Newsman\Setting $model_extension_newsman_setting
 * @property \Opencart\System\Library\Cart\User              $user
 */
class Newsman extends \Opencart\System\Engine\Controller {
	/**
	 * @var int
	 */
	protected $store_id;

	/**
	 * @var string
	 */
	protected $module_name = "newsman";

	/**
	 * @var array
	 */
	protected $location = array(
		'module'      => 'extension/newsman/module',
		'marketplace' => 'marketplace/extension'
	);

	/**
	 * @var array
	 */
	protected $names = array(
		'token'              => 'user_token',
		'setting'            => 'newsman',
		'action'             => 'action',
		'template_extension' => ''
	);

	protected $field_names = array(
		'user_id',
		'api_key',
		'list_id',
		'segment',
		'newsletter_double_optin',
		'send_user_ip',
		'server_ip',
		'export_authorize_header_name',
		'export_authorize_header_key',
		'developer_log_severity',
		'developer_log_clean_days',
		'developer_api_timeout',
		'developer_active_user_ip',
		'developer_user_ip',
		'export_subscribers_by_store',
		'export_customers_by_store'
	);

	/**
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		$this->store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;

		$this->autoloader->register('Newsman', DIR_EXTENSION . 'newsman/system/library/newsman/');

		if (!$this->registry->has('nzmloader')) {
			$this->registry->set('nzmloader', new \Newsman\Nzmloader($this->registry));
		}

		$this->nzmloader->autoload();

		if (!$this->registry->has('nzmconfig')) {
			$this->registry->set('nzmconfig', new \Newsman\Nzmconfig($this->registry));
		}
		if (!$this->registry->has('nzmsetup')) {
			$this->registry->set('nzmsetup', new \Newsman\Nzmsetup($this->registry));
		}
		if (!$this->registry->has('nzmlogger')) {
			$this->registry->set('nzmlogger', new \Newsman\Nzmlogger($this->registry));
		}
	}

	public function index(): void {
		$this->nzmsetup->upgrade();

		if ($this->isStartOauth()) {
			$this->response->redirect($this->url->link('extension/newsman/module/newsman.step1', [
				'store_id' => $this->store_id,
				$this->names['token'] => $this->session->data[$this->names['token']]
			]));
		}

		$this->editModule();
	}

	public function step1(): void {
		$this->nzmsetup->upgrade();
		$data = $this->load->language('extension/newsman/module/newsman');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['logo'] = HTTP_CATALOG . 'extension/newsman/admin/view/image/newsman-logo.png';
		$version = new \Newsman\Util\Version($this->registry);
		$data['extension_version'] = $version->getVersion();
		$data['text_extension_version'] = $this->language->get('text_extension_version');
		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['oauth_url'] = $this->getOauthUrl();

		$this->load->model('setting/store');
		$store_info = $this->model_setting_store->getStore($this->store_id);
		if ($store_info) {
			$store_name = $store_info['name'];
		} else {
			$store_name = $this->config->get('config_name') . $this->language->get('text_default');
		}
		$data['text_setup_for_store'] = sprintf($this->language->get('text_setup_for_store'), $store_name, $this->store_id);

		$this->setSessionCookieLax();

		$this->load->model('setting/setting');
		$data['newsman_user_id'] = $this->model_setting_setting->getValue('newsman_user_id', $this->store_id);
		$data['newsman_api_key'] = $this->model_setting_setting->getValue('newsman_api_key', $this->store_id);
		$data['back'] = $this->url->link('extension/newsman/module/newsman', [
			'store_id' => $this->store_id,
			$this->names['token'] => $this->session->data[$this->names['token']]
		]);

		$this->addPageLayout($data);

		$step3_error = isset($this->request->get['step3_error']) ? $this->request->get['step3_error'] : '';
		if (!empty($step3_error)) {
			$data['error'] = $this->language->get('error_step3_save');
		}

		$this->response->setOutput($this->load->view('extension/newsman/module/newsman/step1_login', $data));
	}

	public function step2(): void {
		$this->nzmsetup->upgrade();
		$data = $this->load->language('extension/newsman/module/newsman');
		$this->load->model('extension/newsman/setting');

		$data['error'] = '';
		$data['show_retry_button'] = false;

		$data['heading_title'] = $this->language->get('heading_title');
		$data['logo'] = HTTP_CATALOG . 'extension/newsman/admin/view/image/newsman-logo.png';
		$version = new \Newsman\Util\Version($this->registry);
		$data['extension_version'] = $version->getVersion();
		$data['text_extension_version'] = $this->language->get('text_extension_version');
		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['oauth_url'] = $this->getOauthUrl();

		$this->load->model('setting/store');
		$store_info = $this->model_setting_store->getStore($this->store_id);
		if ($store_info) {
			$store_name = $store_info['name'];
		} else {
			$store_name = $this->config->get('config_name') . $this->language->get('text_default');
		}
		$data['text_setup_for_store'] = sprintf($this->language->get('text_setup_for_store'), $store_name, $this->store_id);

		$this->setSessionCookieLax();

		$oauth_error = isset($this->request->get['error']) ? $this->request->get['error'] : '';
		if (!empty($oauth_error)) {
			if ($oauth_error === 'access_denied') {
				$data['error'] = $this->language->get('error_access_denied');
			} elseif ($oauth_error === 'missing_lists') {
				$data['error'] = $this->language->get('error_missing_lists');
			} else {
				$data['error'] = 'Unknown error: ' . $oauth_error;
			}
		}

		if (!empty($oauth_error)) {
			$data['show_retry_button'] = true;
			$this->addPageLayout($data);
			$this->response->setOutput($this->load->view('extension/newsman/module/newsman/step2_list', $data));
			return;
		}

		$code = isset($this->request->get['code']) ? $this->request->get['code'] : '';
		if (empty($code)) {
			$data['show_retry_button'] = true;
			$data['error'] = $this->language->get('error_token_missing');
			$this->addPageLayout($data);
			$this->response->setOutput($this->load->view('extension/newsman/module/newsman/step2_list', $data));
			return;
		}

		$authenticate_token = $this->generateRandomPassword(32);
		$this->load->model('extension/newsman/setting');
		$this->model_extension_newsman_setting->editSetting(
			'newsman',
			array(
				'newsman_authenticate_token' => $authenticate_token,
			),
			$this->store_id
		);

		$curl_body = array(
			'grant_type'   => 'authorization_code',
			'code'         => $code,
			'client_id'    => 'nzmplugin',
			'redirect_uri' => ''
		);
		$ch = curl_init($this->nzmconfig->getOautTokenhUrl($this->store_id));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_body);
		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			$data['show_retry_button'] = true;
			$data['error'] .= ' Response error: ' . curl_error($ch);
		}
		curl_close($ch);

		if ($response !== false) {
			$response = json_decode($response);
			$data['user_id'] = $response->user_id;
			$data['api_key'] = $response->access_token;

			$data['creds'] = json_encode(
				array(
					'newsman_userid' => $response->user_id,
					'newsman_apikey' => $response->access_token
				)
			);

			$email_lists = array();
			foreach ($response->lists_data as $l) {
				if (stripos($l->name, 'SMS:') !== false) {
					continue;
				}
				$email_lists[] = array(
					'id'   => $l->list_id,
					'name' => $l->name
				);
			}
			$data['email_lists'] = $email_lists;
			$data['email_lists_length'] = count($email_lists);
		} else {
			$data['show_retry_button'] = true;
			$data['error'] .= ' Error sending cURL request.';
		}

		$data['action'] = $this->url->link('extension/newsman/module/newsman.step3', [
			'store_id' => $this->store_id,
			$this->names['token'] => $this->session->data[$this->names['token']]
		]);
		$this->addPageLayout($data);
		$this->response->setOutput($this->load->view('extension/newsman/module/newsman/step2_list', $data));
	}

	public function step3(): void {
		$this->nzmsetup->upgrade();
		$this->load->model('setting/setting');
		$this->load->model('setting/store');
		$this->load->model('extension/newsman/setting');

		$user_id = isset($this->request->post['user_id']) ? $this->request->post['user_id'] : '';
		$api_key = isset($this->request->post['api_key']) ? $this->request->post['api_key'] : '';
		$list_id = isset($this->request->post['list_id']) ? $this->request->post['list_id'] : '';
		if (empty($user_id) || empty($api_key) || empty($list_id)) {
			$this->response->redirect($this->url->link('extension/newsman/module/newsman.step1', [
				'store_id' => $this->store_id,
				$this->names['token'] => $this->session->data[$this->names['token']],
				'step3_error' => 1
			]));
		}

		$settings = array(
			'newsman_user_id' => $user_id,
			'newsman_api_key' => $api_key,
			'newsman_list_id' => $list_id
		);
		$this->load->model('extension/newsman/setting');
		$this->model_extension_newsman_setting->editSetting('newsman', $settings, $this->store_id);
		$this->model_extension_newsman_setting->editSetting('module_newsman', array('module_newsman_status' => 1), $this->store_id);

		$this->nzmconfig->init(true);
		$this->nzmsetup->upgrade();

		$remarketing_response = $this->getRemarketingSettings($list_id, $user_id, $api_key);
		if ($remarketing_response && isset($remarketing_response['site_id'])) {
			$remarketing_id = $remarketing_response['site_id'] . '-' . $remarketing_response['list_id'] . '-' .
				$remarketing_response['form_id'] . '-' . $remarketing_response['control_list_hash'];
			$settings = [
				'analytics_newsmanremarketing_register'   => 'newsmanremarketing',
				'analytics_newsmanremarketing_trackingid' => $remarketing_id,
				'analytics_newsmanremarketing_status'     => 1
			];
			$this->load->model('extension/newsman/setting');
			$this->model_extension_newsman_setting->editSetting('analytics_newsmanremarketing', $settings, $this->store_id);
		}

		// Save integration setup in Newsman.
		$authenticate_token = $this->nzmconfig->getAuthenticateToken($this->store_id);
		$integration_result = $this->saveListIntegrationSetup(
			$list_id,
			$this->getStorefrontUrl(),
			$authenticate_token,
			$user_id,
			$api_key
		);
		if ($integration_result === false) {
			$this->response->redirect($this->url->link('extension/newsman/module/newsman.step1', [
				'store_id' => $this->store_id,
				$this->names['token'] => $this->session->data[$this->names['token']],
				'step3_error' => 1
			]));
			return;
		}

		// @deprecated
		// $url = $this->getStorefrontUrl() . "index.php?route=extension/newsman/module/newsman&newsman=products.json&nzmhash=" . $api_key;
		// $result = $this->setFeedOnList(
		// 	$list_id,
		// 	$url,
		// 	$this->getStorefrontUrl(),
		// 	'NewsMAN',
		// 	true,
		// );
		// if (is_array($result) && !empty($result['feed_id'])) {
		// 	$this->session->data['success'] = 'Products feed installed in Newsman.';
		// 	$auth_name = $this->generateRandomHeaderName();
		// 	$auth_value = $this->generateRandomPassword();
		// 	// @deprecated
		// 	$result = $this->updateFeedAuthorize(
		// 		$list_id,
		// 		$result['feed_id'],
		// 		$auth_name,
		// 		$auth_value
		// 	);
		//
		// 	if ($result !== false) {
		// 		$this->load->model('extension/newsman/setting');
		// 		$this->model_extension_newsman_setting->editSetting(
		// 			'newsman',
		// 			array(
		// 				'newsman_export_authorize_header_name' => $auth_name,
		// 				'newsman_export_authorize_header_key'  => $auth_value,
		// 			),
		// 			$this->store_id
		// 		);
		// 	}
		// } else {
		// 	$this->session->data['warning'] = 'Products feed could not be installed. It may already exist in Newsman.';
		// }

		$this->response->redirect($this->url->link('extension/newsman/module/newsman', [
			'store_id' => $this->store_id,
			$this->names['token'] => $this->session->data[$this->names['token']]
		]));
	}

	public function getRemarketingSettings($list_id, $user_id = null, $api_key = null) {
		try {
			if ($user_id === null) {
				$user_id = $this->nzmconfig->getUserId($this->store_id);
			}
			if ($api_key === null) {
				$api_key = $this->nzmconfig->getApiKey($this->store_id);
			}

			$context = new \Newsman\Service\Context\Configuration\EmailList();
			$context->setUserId($user_id)
				->setApiKey($api_key)
				->setListId($list_id);
			$get_settings = new \Newsman\Service\Configuration\Remarketing\GetSettings($this->registry);

			return $get_settings->execute($context);
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			return false;
		}
	}

	public function setFeedOnList($list_id, $url, $website, $type = 'fixed', $return_id = false) {
		try {
			if ($list_id === null) {
				$list_id = $this->nzmconfig->getListId($this->store_id);
			}

			$context = new \Newsman\Service\Context\Configuration\SetFeedOnList();
			$context->setListId($list_id)
				->setUrl($url)
				->setWebsite($website)
				->setType($type)
				->setReturnId($return_id);

			$set_feed = new \Newsman\Service\Configuration\SetFeedOnList($this->registry);
			$result = $set_feed->execute($context);

			return $result;
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			return false;
		}
	}

	protected function updateFeedAuthorize($list_id, $feed_id, $auth_name, $auth_value) {
		try {
			if ($list_id === null) {
				$list_id = $this->nzmconfig->getListId($this->store_id);
			}

			$properties = array(
				'auth_header_name'  => $auth_name,
				'auth_header_value' => $auth_value,
			);

			$context = new \Newsman\Service\Context\Configuration\UpdateFeed();
			$context->setListId($list_id)
				->setFeedId($feed_id)
				->setProperties($properties);
			$set_feed = new \Newsman\Service\Configuration\UpdateFeed($this->registry);

			return $set_feed->execute($context);
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			return false;
		}
	}

	/**
	 * Call API saveListIntegrationSetup
	 *
	 * @param string      $list_id List ID.
	 * @param string      $storefront_url Storefront URL.
	 * @param string      $authenticate_token Authenticate token.
	 * @param null|string $user_id User ID.
	 * @param null|string $api_key API key.
	 *
	 * @return bool
	 */
	public function saveListIntegrationSetup($list_id, $storefront_url, $authenticate_token, $user_id = null, $api_key = null) {
		try {
			if ($user_id === null) {
				$user_id = $this->nzmconfig->getUserId($this->store_id);
			}
			if ($api_key === null) {
				$api_key = $this->nzmconfig->getApiKey($this->store_id);
			}

			$api_url = rtrim($storefront_url, '/') . '/index.php?route=extension/newsman/module/newsman';

			$version = new \Newsman\Util\Version($this->registry);
			$payload = array(
				'api_url'                  => $api_url,
				'api_key'                  => $authenticate_token,
				'plugin_version'           => $version->getVersion(),
				// 'platform_name'            => 'OpenCart',
				'platform_version'         => VERSION,
				'platform_language'        => 'PHP',
				'platform_language_version' => phpversion(),
				'platform_server_ip'       => (new \Newsman\Util\ServerIpResolver())->resolve(),
			);

			$context = new \Newsman\Service\Context\Configuration\SaveListIntegrationSetup();
			$context->setUserId($user_id)
				->setApiKey($api_key)
				->setListId($list_id)
				->setIntegration('opencart')
				->setPayload($payload);

			$service = new \Newsman\Service\Configuration\Integration\SaveListIntegrationSetup($this->registry);
			$service->execute($context);

			return true;
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);

			return false;
		}
	}

	protected function generateRandomHeaderName($length = 16, $recursion_depth = 0) {
		if ($recursion_depth > 3) {
			$characters = 'abcdefghijklmnopqrstuvwxyz';
			return substr(str_shuffle($characters), 0, $length);
		}

		$characters = 'abcdefghijklmnopqrstuvwxyz-';
		$characters_length = strlen($characters);
		$random_string = '';

		for ($i = 0; $i < $length; $i++) {
			$random_string .= $characters[random_int(0, $characters_length - 1)];
		}

		$random_string = ltrim($random_string, '-');
		$random_string = rtrim($random_string, '-');
		$random_string = preg_replace('/-{2,}/', '-', $random_string);

		if (strlen($random_string) < $length / 2) {
			$additional = $this->generateRandomHeaderName(
				$length - strlen($random_string),
				$recursion_depth + 1
			);
			$random_string .= $additional;
		}

		return $random_string;
	}

	protected function generateRandomPassword($length = 16) {
		$lowercase = 'abcdefghijklmnopqrstuvwxyz';
		$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numbers = '0123456789';

		$all_chars = $lowercase . $uppercase . $numbers;
		$chars_length = strlen($all_chars);

		$password = '';
		for ($i = 0; $i < $length; $i++) {
			$password .= $all_chars[random_int(0, $chars_length - 1)];
		}

		$has_lowercase = preg_match('/[a-z]/', $password);
		$has_uppercase = preg_match('/[A-Z]/', $password);
		$has_number = preg_match('/[0-9]/', $password);

		if (!$has_lowercase) {
			$password[random_int(0, $length - 1)] = $lowercase[random_int(0, strlen($lowercase) - 1)];
		}
		if (!$has_uppercase) {
			$password[random_int(0, $length - 1)] = $uppercase[random_int(0, strlen($uppercase) - 1)];
		}
		if (!$has_number) {
			$password[random_int(0, $length - 1)] = $numbers[random_int(0, strlen($numbers) - 1)];
		}

		return $password;
	}

	public function isStartOauth() {
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('newsman', $this->store_id);
		if (empty($setting["newsman_user_id"]) || empty($setting["newsman_api_key"])) {
			return true;
		} else {
			return false;
		}
	}

	public function setSessionCookieLax() {
		// OC4 session cookie fix for OAuth redirect
		$option = [
			'expires'  => $this->config->get('config_session_expire') ? time() + (int)$this->config->get('config_session_expire') : 0,
			'path'     => $this->config->get('session_path'),
			'secure'   => $this->request->server['HTTPS'],
			'httponly' => false,
			'SameSite' => 'Lax'
		];

		setcookie($this->config->get('session_name'), $this->session->getId(), $option);
	}

	public function getOauthUrl() {
		$redirect_uri = $this->url->link(
			'extension/newsman/module/newsman.step2',
			[
				'store_id' => $this->store_id,
				$this->names['token'] => $this->session->data[$this->names['token']]
			],
			true
		);
		$redirect_uri = urlencode($redirect_uri);

		return str_replace('__redirect_url__', $redirect_uri, $this->nzmconfig->getOauthUrl($this->store_id));
	}

	protected function addPageLayout(&$data) {
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
	}

	protected function breadcrumbs() {
		$this->load->language('extension/newsman/module/newsman');

		$this->load->model('setting/store');
		$store_info = $this->model_setting_store->getStore($this->store_id);
		if ($store_info) {
			$store_name = $store_info['name'];
		} else {
			$store_name = $this->config->get('config_name') . $this->language->get('text_default');
		}

		$breadcrumbs = array();
		$breadcrumbs[] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', [
				$this->names['token'] => $this->session->data[$this->names['token']]
			])
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($this->location['marketplace'], [
				$this->names['token'] => $this->session->data[$this->names['token']],
				'type' => 'module'
			])
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('heading_title') . ' - ' . $store_name,
			'href' => $this->url->link($this->location['module'] . '/newsman', [
				$this->names['token'] => $this->session->data[$this->names['token']],
				'store_id' => $this->store_id
			])
		);

		return $breadcrumbs;
	}

	public function editModule(): void {
		$this->load->model('setting/setting');
		$this->load->model('extension/newsman/setting');
		$data = $this->load->language('extension/newsman/module/newsman');
		$version = new \Newsman\Util\Version($this->registry);
		$data['extension_version'] = $version->getVersion();
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['cancel'] = $this->url->link($this->location['marketplace'], [
			$this->names['token'] => $this->session->data[$this->names['token']],
			'type' => 'module'
		]);

		if (!empty($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
		if (!empty($this->session->data['error'])) {
			$data['error'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}

		if (!empty($this->session->data['warning'])) {
			$data['warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		}

		if (!$this->user->hasPermission('modify', 'extension/newsman/module/newsman')) {
			$data['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
			'href'     => $this->url->link($this->location['module'] . '/newsman', $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=0', true)
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
				'href'     => $this->url->link($this->location['module'] . '/newsman', $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=' . $result['store_id'], true)
			);
		}

		$data['store_id'] = $this->store_id;
		$data['is_multistore'] = (count($data['stores']) > 1);

		$store_info = $this->model_setting_store->getStore($this->store_id);
		if ($store_info) {
			$data['store_name'] = $store_info['name'];
		} else {
			$data['store_name'] = $this->config->get('config_name') . $this->language->get('text_default');
		}

		$data['action'] = $this->url->link($this->location['module'] . '/newsman', $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=' . $this->store_id, true);

		$data['text_store'] = $this->language->get('text_store');
		$data['text_config_for_store'] = sprintf($this->language->get('text_config_for_store'), $data['store_name'], $this->store_id);

		$this->addPageLayout($data);

		foreach ($this->field_names as $field) {
			$data[$this->names['setting'] . '_' . $field] = $this->model_setting_setting->getValue($this->names['setting'] . '_' . $field, $this->store_id);
		}

		$data['module_newsman_status'] = $this->model_setting_setting->getValue('module_newsman_status', $this->store_id);

		if (strcasecmp($this->request->server['REQUEST_METHOD'], 'POST') == 0 && $this->validate()) {
			$previous_list_id = $data['newsman_list_id'];
			$previous_user_id = $data['newsman_user_id'];
			$previous_api_key = $data['newsman_api_key'];
			$settings = array();
			foreach ($this->field_names as $field) {
				$settings[$this->names['setting'] . '_' . $field] = $this->request->post[$this->names['setting'] . '_' . $field];
			}
			$settings_status = array(
				'module_newsman_status' => $this->request->post['module_newsman_status']
			);

			$this->load->model('extension/newsman/setting');
			$this->model_extension_newsman_setting->editSetting($this->names['setting'], $settings, $this->store_id);
			$this->model_extension_newsman_setting->editSetting('module_newsman', $settings_status, $this->store_id);

			// Call saveListIntegrationSetup if the list ID, user ID, or API key changed.
			$new_list_id = $settings['newsman_list_id'];
			$new_user_id = $settings['newsman_user_id'];
			$new_api_key = $settings['newsman_api_key'];
			if (!empty($new_list_id) && (
				$new_list_id !== $previous_list_id ||
				$new_user_id !== $previous_user_id ||
				$new_api_key !== $previous_api_key
			)) {
				$this->nzmconfig->init(true);
				$authenticate_token = $this->nzmconfig->getAuthenticateToken($this->store_id);
				if (empty($authenticate_token)) {
					$authenticate_token = $this->generateRandomPassword(32);
					$this->model_extension_newsman_setting->editSetting(
						'newsman',
						array('newsman_authenticate_token' => $authenticate_token),
						$this->store_id
					);
				}
				$integration_result = $this->saveListIntegrationSetup(
					$new_list_id,
					$this->getStorefrontUrl(),
					$authenticate_token
				);
				if ($integration_result === false) {
					// Revert the list ID to the previous value.
					$this->model_extension_newsman_setting->editSetting(
						'newsman',
						array('newsman_list_id' => $previous_list_id),
						$this->store_id
					);
					$this->session->data['error'] = 'Could not save integration setup. The list was not changed.';
					$this->response->redirect($this->url->link($this->location['module'] . '/newsman', [
						$this->names['token'] => $this->session->data[$this->names['token']],
						'type'     => 'module',
						'store_id' => $this->store_id
					]));
					return;
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link($this->location['module'] . '/newsman', [
				$this->names['token'] => $this->session->data[$this->names['token']],
				'type'     => 'module',
				'store_id' => $this->store_id
			]));
		}

		if (strcasecmp($this->request->server['REQUEST_METHOD'], 'POST') == 0) {
			foreach ($this->field_names as $field) {
				$data[$this->names['setting'] . '_' . $field] = $this->request->post[$this->names['setting'] . '_' . $field];
			}
			$data['module_newsman_status'] = $this->request->post['module_newsman_status'];
		}

		$data['developer_log_severity_options'] = array();
		foreach ($this->nzmlogger->getCodes() as $code => $type) {
			$data['developer_log_severity_options'][] = array(
				'code' => $code,
				'type' => $type
			);
		}

		$data['is_connected'] = false;
		$data['list_options'] = array();
		$list_data = $this->getAllLists($data['newsman_user_id'], $data['newsman_api_key']);
		if ($list_data !== false) {
			$data['is_connected'] = true;
			$data['list_options'] = $list_data;
		}

		$data['segment_options'] = array();
		if ($data['newsman_list_id'] > 0) {
			$segment_data = $this->getAllSegmentsByList($data['newsman_user_id'], $data['newsman_api_key'], $data['newsman_list_id']);
			if ($segment_data !== false) {
				$data['segment_options'] = $segment_data;
			}
		}

		$data['url_remarketing_settings'] = $this->url->link('extension/newsman/analytics/newsmanremarketing', [
			$this->names['token'] => $this->session->data[$this->names['token']],
			'store_id' => $this->store_id
		]);
		$data['reconfigure'] = $this->url->link('extension/newsman/module/newsman.step1', [
			'store_id' => $this->store_id,
			$this->names['token'] => $this->session->data[$this->names['token']]
		]);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['logo'] = HTTP_CATALOG . 'extension/newsman/admin/view/image/newsman-logo.png';

		$this->response->setOutput($this->load->view('extension/newsman/module/newsman', $data));
	}

	public function getAllLists($user_id, $api_key) {
		$return = array();
		try {
			$context = new \Newsman\Service\Context\Configuration\User();
			$context->setStoreId($this->store_id)
				->setUserId($user_id)
				->setApiKey($api_key);
			$get_lists = new \Newsman\Service\Configuration\GetListAll($this->registry);
			$list_data = $get_lists->execute($context);
			foreach ($list_data as $list_item) {
				if ($list_item['list_type'] == 'sms') {
					continue;
				}
				$return[] = $list_item;
			}
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			return false;
		}
		return $return;
	}

	public function getAllSegmentsByList($user_id, $api_key, $list_id) {
		try {
			$context = new \Newsman\Service\Context\Configuration\EmailList();
			$context->setStoreId($this->store_id)
				->setUserId($user_id)
				->setApiKey($api_key)
				->setListId($list_id);
			$get_segments = new \Newsman\Service\Configuration\GetSegmentAll($this->registry);
			$return = $get_segments->execute($context);
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			return false;
		}
		return $return;
	}

	public function validate() {
		if (!$this->user->hasPermission('modify', 'extension/newsman/module/newsman')) {
			return false;
		}
		return true;
	}

	protected function getStorefrontUrl() {
		$url = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;
		if ($this->store_id > 0) {
			$this->load->model('setting/store');
			$store_info = $this->model_setting_store->getStore($this->store_id);
			if ($store_info) {
				$url = $store_info['url'];
			}
		}
		return $url;
	}

	public function install(): void {
		$this->nzmsetup->install();
	}

	public function uninstall(): void {
		$this->nzmsetup->uninstall();
	}

	/**
	 * @deprecated No longer exposed in admin UI.
	 */
	public function exportsubscribers(): void {
		if (!$this->validate()) {
			$this->load->language('extension/newsman/module/newsman');
			$this->session->data['error'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/newsman/module/newsman', [
				'user_token' => $this->session->data['user_token'],
				'store_id' => $this->store_id
			]));
		}

		try {
			$cron = new \Newsman\Export\Retriever\CronSubscribers($this->registry);
			$results = $cron->process(array(), $this->store_id);
			$messages = array();
			foreach ($results as $result) {
				if (isset($result['status'])) {
					$messages[] = $result['status'];
				}
			}
			if (!empty($messages)) {
				$this->session->data['success'] = implode(' ', $messages);
			}
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			$this->session->data['error'] = $e->getMessage();
		}

		$this->response->redirect($this->url->link('extension/newsman/module/newsman', [
			'user_token' => $this->session->data['user_token'],
			'store_id' => $this->store_id
		]));
	}

	/**
	 * @deprecated No longer exposed in admin UI.
	 */
	public function exportorders(): void {
		if (!$this->validate()) {
			$this->load->language('extension/newsman/module/newsman');
			$this->session->data['error'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/newsman/module/newsman', [
				'user_token' => $this->session->data['user_token'],
				'store_id' => $this->store_id
			]));
		}

		try {
			$cron = new \Newsman\Export\Retriever\CronOrders($this->registry);
			$data = array(
				'created_at' => array(
					'from' => $this->nzmconfig->getOrderDate($this->store_id)
				)
			);
			$last_days = (isset($this->request->get['last-days'])) ? (int)$this->request->get['last-days'] : false;
			if ($last_days !== false) {
				$data['created_at']['from'] = date('Y-m-d', strtotime('-' . $last_days . ' days'));
			}

			$results = $cron->process($data, $this->store_id);
			$messages = array();
			foreach ($results as $result) {
				if (isset($result['status'])) {
					$messages[] = $result['status'];
				}
			}
			if (!empty($messages)) {
				$this->session->data['success'] = implode(' ', $messages);
			}
		} catch (\Exception $e) {
			$this->nzmlogger->logException($e);
			$this->session->data['error'] = $e->getMessage();
		}

		$this->response->redirect($this->url->link('extension/newsman/module/newsman', [
			'user_token' => $this->session->data['user_token'],
			'store_id' => $this->store_id
		]));
	}

	public function eventCleanLogs(string &$route, array &$args): void {
		$this->nzmloader->autoload();
		$clean_log = new \Newsman\Util\CleanLog($this->registry);
		$clean_log->cleanLogs();
	}

	public function eventSetupUpgrade(string &$route, array &$args): void {
		$this->nzmloader->autoload();
		if (!$this->registry->has('nzmsetup')) {
			$this->registry->set('nzmsetup', new \Newsman\Nzmsetup($this->registry));
		}
		$this->nzmsetup->upgrade();
	}

	public function eventMenuBefore(string &$route, array &$args): void {
		$this->load->language('extension/newsman/module/newsman');

		$newsman = [];

		if ($this->user->hasPermission('access', 'extension/newsman/module/newsman')) {
			$newsman[] = [
				'name'     => $this->language->get('text_settings'),
				'href'     => $this->url->link('extension/newsman/module/newsman', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->store_id),
				'children' => []
			];
		}

		if ($this->user->hasPermission('access', 'extension/newsman/analytics/newsmanremarketing')) {
			$newsman[] = [
				'name'     => $this->language->get('text_newsman_remarketing'),
				'href'     => $this->url->link('extension/newsman/analytics/newsmanremarketing', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $this->store_id),
				'children' => []
			];
		}

		if ($newsman) {
			$args['menus'][] = [
				'id'       => 'menu-newsman',
				'icon'     => 'fa-solid fa-envelope',
				'name'     => 'NewsMAN',
				'href'     => '',
				'children' => $newsman
			];
		}
	}


	public function eventSaveCustomerBefore(string &$route, array &$args): void {
		$this->nzmloader->autoload();

		$data = $this->request->post;
		$customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : 0;

		if ($customer_id > 0) {
			$this->load->model('customer/customer');
			$customer_info = $this->model_customer_customer->getCustomer($customer_id);

			if ($customer_info && isset($data['newsletter'])) {
				$old_newsletter = (bool)$customer_info['newsletter'];
				$new_newsletter = (bool)$data['newsletter'];

				if ($old_newsletter !== $new_newsletter) {
					if ($new_newsletter) {
						$this->subscribe($data['email'], $data['firstname'], $data['lastname'], $data['telephone'] ?? '', (int)$data['store_id']);
					} else {
						$this->unsubscribe($data['email'], (int)$data['store_id']);
					}
				}
			}
		} else {
			if (isset($data['newsletter']) && $data['newsletter'] && isset($data['email'])) {
				$this->subscribe($data['email'], $data['firstname'] ?? '', $data['lastname'] ?? '', $data['telephone'] ?? '', (int)($data['store_id'] ?? 0));
			}
		}
	}

	public function eventDeleteCustomerBefore(string &$route, array &$args): void {
		$this->nzmloader->autoload();

		if (!isset($this->request->post['selected'])) {
			return;
		}

		$selected = (array)$this->request->post['selected'];

		$this->load->model('customer/customer');

		foreach ($selected as $customer_id) {
			$customer_info = $this->model_customer_customer->getCustomer((int)$customer_id);

			if ($customer_info && !empty($customer_info['email']) && !empty($customer_info['newsletter'])) {
				$this->unsubscribe($customer_info['email'], (int)$customer_info['store_id']);
			}
		}
	}

	private function subscribe(string $email, string $firstname, string $lastname, string $telephone, int $store_id): void {
		try {
			$email_action = new \Newsman\Action\Subscribe\Email($this->registry);

			$properties = array();
			if ($this->nzmconfig->isSendTelephone($store_id)) {
				if (!empty($telephone)) {
					$properties['phone'] = $telephone;
				}
			}

			$options = array();
			$segment_id = $this->nzmconfig->getSegmentId($store_id);
			if (!empty($segment_id)) {
				$options['segments'] = array($segment_id);
			}

			$email_action->execute(
				$email,
				$firstname,
				$lastname,
				$properties,
				$options,
				$store_id
			);
		} catch (\Exception $e) {
			if ($this->registry->has('nzmlogger')) {
				$this->nzmlogger->logException($e);
			}
		}
	}

	private function unsubscribe(string $email, int $store_id): void {
		try {
			$email_action = new \Newsman\Action\Subscribe\Email($this->registry);
			$email_action->unsubscribe($email, $store_id);
		} catch (\Exception $e) {
			if ($this->registry->has('nzmlogger')) {
				$this->nzmlogger->logException($e);
			}
		}
	}
}
