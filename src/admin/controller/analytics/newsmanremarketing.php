<?php

namespace Opencart\Admin\Controller\Extension\Newsman\Analytics;

/**
 * Class Newsmanremarketing
 *
 * @property \Opencart\System\Engine\Autoloader              $autoloader
 * @property \Opencart\System\Engine\Registry                $registry
 * @property \Newsman\Nzmloader                              $nzmloader
 * @property \Newsman\Nzmconfig                              $nzmconfig
 * @property \Newsman\Nzmsetup                               $nzmsetup
 * @property \Newsman\Nzmlogger                              $nzmlogger
 * @property \Opencart\System\Engine\Loader                  $load
 * @property \Opencart\System\Engine\Config                  $config
 * @property \Opencart\System\Library\Session                $session
 * @property \Opencart\System\Library\Request                $request
 * @property \Opencart\System\Library\Response               $response
 * @property \Opencart\System\Library\Document               $document
 * @property \Opencart\System\Library\Url                    $url
 * @property \Opencart\System\Library\Language               $language
 * @property \Opencart\Admin\Model\Setting\Setting           $model_setting_setting
 * @property \Opencart\Admin\Model\Extension\Newsman\Setting $model_extension_newsman_setting
 * @property \Opencart\System\Library\Cart\User              $user
 */
class Newsmanremarketing extends \Opencart\System\Engine\Controller {
	/**
	 * @var int
	 */
	protected $store_id;

	/**
	 * @var string
	 */
	protected $module_name = "newsmanremarketing";

	/**
	 * @var array
	 */
	protected $error = array();

	/**
	 * @var array
	 */
	protected $location = array(
		'module'      => 'extension/newsman/analytics/newsmanremarketing',
		'marketplace' => 'marketplace/extension'
	);

	protected $names = array(
		'token'              => 'user_token',
		'setting'            => 'analytics_newsmanremarketing',
		'action'             => 'action',
		'template_extension' => ''
	);

	/**
	 * @var array
	 */
	protected $field_names = array(
		'status',
		'trackingid',
		'anonymize_ip',
		'send_telephone',
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

	protected function breadcrumbs() {
		$this->load->language('extension/newsman/analytics/newsmanremarketing');

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
				'type' => 'analytics'
			])
		);

		$breadcrumbs[] = array(
			'text' => $this->language->get('heading_title') . ' - ' . $store_name,
			'href' => $this->url->link($this->location['module'], [
				$this->names['token'] => $this->session->data[$this->names['token']],
				'store_id' => $this->store_id
			])
		);

		return $breadcrumbs;
	}

	public function index(): void {
		$this->nzmsetup->upgrade();

		$data = $this->load->language('extension/newsman/analytics/newsmanremarketing');
		$this->load->model('setting/setting');
		$this->load->model('extension/newsman/setting');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . ' (' . $this->language->get('text_default') . ')',
			'href'     => $this->url->link($this->location['module'], $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=0', true)
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
				'href'     => $this->url->link($this->location['module'], $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=' . $result['store_id'], true)
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

		$data['action'] = $this->url->link($this->location['module'], $this->names['token'] . '=' . $this->session->data[$this->names['token']] . '&store_id=' . $this->store_id, true);

		$data['text_store'] = $this->language->get('text_store');
		$data['text_config_for_store'] = sprintf($this->language->get('text_config_for_store'), $data['store_name'], $this->store_id);

		foreach ($this->field_names as $field) {
			$data[$this->names['setting'] . '_' . $field] = $this->model_setting_setting->getValue($this->names['setting'] . '_' . $field, $this->store_id);
		}

		if (strcasecmp($this->request->server['REQUEST_METHOD'], 'POST') == 0 && $this->validate()) {
			$settings = array();
			$settings[$this->names['setting'] . '_register'] = $this->module_name;
			foreach ($this->field_names as $field) {
				$settings[$this->names['setting'] . '_' . $field] = $this->request->post[$this->names['setting'] . '_' . $field];
			}
			$this->load->model('extension/newsman/setting');
			$this->model_extension_newsman_setting->editSetting($this->names['setting'], $settings, $this->store_id);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link($this->location['module'], [
				$this->names['token'] => $this->session->data[$this->names['token']],
				'type' => 'analytics',
				'store_id' => $this->store_id
			]));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['breadcrumbs'] = $this->breadcrumbs();
		$data['cancel'] = $this->url->link($this->location['marketplace'], [
			$this->names['token'] => $this->session->data[$this->names['token']],
			'type' => 'analytics'
		]);

		if (strcasecmp($this->request->server['REQUEST_METHOD'], 'POST') == 0) {
			foreach ($this->field_names as $field) {
				$data[$this->names['setting'] . '_' . $field] = $this->request->post[$this->names['setting'] . '_' . $field];
			}
		}

		$data['url_newsman_settings'] = $this->url->link('extension/newsman/module/newsman', [
			$this->names['token'] => $this->session->data[$this->names['token']],
			'store_id' => $this->store_id
		]);

		$data['is_remarketing_connected'] = false;
		if (!empty($data[$this->names['setting'] . '_trackingid'])) {
			$remarketing_settings = $this->getRemarketingSettings($this->nzmconfig->getListId($this->store_id));
			if ($remarketing_settings !== false && !empty($remarketing_settings) && isset($remarketing_settings['site_id'])) {
				$remarketing_id = $remarketing_settings['site_id'] . '-' . $remarketing_settings['list_id'] . '-' .
					$remarketing_settings['form_id'] . '-' . $remarketing_settings['control_list_hash'];
				if ($data[$this->names['setting'] . '_trackingid'] == $remarketing_id) {
					$data['is_remarketing_connected'] = true;
				}
			}
		}

		$data['logo'] = HTTP_CATALOG . 'extension/newsman/admin/view/image/newsman-logo.png';
		$version = new \Newsman\Util\Version($this->registry);
		$data['extension_version'] = $version->getVersion();
		$data['text_extension_version'] = $this->language->get('text_extension_version');

		$this->response->setOutput($this->load->view('extension/newsman/analytics/newsmanremarketing', $data));
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

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/newsman/analytics/newsmanremarketing')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->request->post[$this->names['setting'] . '_trackingid']) {
			$this->error['warning'] = 'Newsman Remarketing code required';
		}

		return !$this->error;
	}
}
