<?php

namespace Opencart\Catalog\Controller\Extension\Newsman\Analytics;

/**
 * Class Newsmanremarketing
 *
 * @property \Opencart\System\Engine\Autoloader       $autoloader
 * @property \Opencart\System\Engine\Registry         $registry
 * @property \Newsman\Nzmloader                       $nzmloader
 * @property \Newsman\Nzmconfig                       $nzmconfig
 * @property \Opencart\System\Engine\Loader           $load
 * @property \Opencart\System\Engine\Event            $event
 * @property \Opencart\System\Library\Session         $session
 * @property \Opencart\System\Engine\Config           $config
 * @property \Opencart\System\Library\Request         $request
 * @property \Opencart\System\Library\Cart\Customer   $customer
 * @property \Opencart\System\Library\DB              $db
 * @property \Opencart\Catalog\Model\Catalog\Product   $model_catalog_product
 * @property \Opencart\Catalog\Model\Catalog\Category  $model_catalog_category
 * @property \Opencart\Catalog\Model\Checkout\Order    $model_checkout_order
 */
class Newsmanremarketing extends \Opencart\System\Engine\Controller {
	/**
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		$this->autoloader->register('Newsman', DIR_EXTENSION . 'newsman/system/library/newsman/');

		if (!$this->registry->has('nzmloader')) {
			$this->registry->set('nzmloader', new \Newsman\Nzmloader($this->registry));
		}

		$this->nzmloader->autoload();

		if (!$this->registry->has('nzmconfig')) {
			$this->registry->set('nzmconfig', new \Newsman\Nzmconfig($this->registry));
		}
	}

	public function index(): string {
		if (!$this->nzmconfig->isRemarketingActive()) {
			return '';
		}

		$this->load->model('checkout/order');

		$is_add_page_view = true;
		$output = $this->getTrackingScriptJs();
		$output .= $this->getCartJs();
		$output .= $this->getCustomerIdentifyJs();

		switch ($this->getCurrentRoute()) {
			case 'product/product':
				$output .= $this->getProductViewJs();
				break;

			case 'product/category':
				$output .= $this->getCategoryViewJs();
				break;

			case 'checkout/success':
				$is_add_page_view = false;
				$output .= $this->getPurchaseJs();
				break;
		}

		if ($is_add_page_view) {
			$output .= $this->getPageViewJs();
		}

		return $output;
	}

	protected function getTrackingScriptJs(): string {
		$data = array();
		$track = new \Newsman\Remarketing\Script\Track($this->registry);
		$config_js = '';
		$this->event->trigger('newsmanremarketing/script_tracking_config/before', array(&$config_js));
		$track->setJsConfig($config_js);
		$data['tracking_script_js'] = $track->getScript();

		$data['tag_attrib'] = $this->getScripTagAttributes();
		$data['nzm_run'] = $track->escapeHtml($this->nzmconfig->getJsTrackRunFunc());
		$data['is_anonymize_ip'] = $this->nzmconfig->isAnonymizeIp();

		$no_track_script = '';
		$this->event->trigger('newsmanremarketing/script_tracking_no_track/before', array(&$no_track_script));
		$data['no_track_script'] = $no_track_script;

		$currency_code = $this->session->data['currency'] ?? $this->config->get('config_currency');
		$data['currency_code'] = $track->escapeHtml($currency_code);

		$this->event->trigger('newsmanremarketing/script_tracking_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/track', $data);
		$this->event->trigger('newsmanremarketing/script_tracking_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getCartJs(): string {
		$data = array('tag_attrib' => $this->getScripTagAttributes());

		$data['base_url'] = rtrim(HTTP_SERVER, '/');

		$data['nzm_time_diff'] = 5000;
		if ($this->getCurrentRoute() === 'checkout/success') {
			$data['nzm_time_diff'] = 1000;
		}

		$this->event->trigger('newsmanremarketing/script_cart_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/cart', $data);
		$this->event->trigger('newsmanremarketing/script_cart_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getPageViewJs(): string {
		$page_view = new \Newsman\Remarketing\Action\PageView($this->registry);
		$page_view->setEvent($this->event);

		$data = array(
			'page_view_js' => $page_view->getJs(),
			'tag_attrib'   => $this->getScripTagAttributes()
		);

		$this->event->trigger('newsmanremarketing/script_page_view_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/pageview', $data);
		$this->event->trigger('newsmanremarketing/script_page_view_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getCustomerIdentifyJs(): string {
		if (!$this->customer->isLogged()) {
			return '';
		}

		if ($this->getCurrentRoute() === 'checkout/success') {
			return '';
		}

		$identify = new \Newsman\Remarketing\Action\CustomerIdentify($this->registry);
		$identify->setEvent($this->event);

		$data = array(
			'customer_identify_js' => $identify->getJs($this->customer),
			'tag_attrib'           => $this->getScripTagAttributes()
		);

		$this->event->trigger('newsmanremarketing/script_customer_identify_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/customeridentify', $data);
		$this->event->trigger('newsmanremarketing/script_customer_identify_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getProductViewJs(): string {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$product_view = new \Newsman\Remarketing\Action\ProductView($this->registry);
		$product_view->setEvent($this->event)
			->setDb($this->db)
			->setRequest($this->request)
			->setProductModel($this->model_catalog_product)
			->setCategoryModel($this->model_catalog_category)
			->setCheckoutOrderModel($this->model_checkout_order);

		$data = array(
			'product_view_js' => $product_view->getJs(),
			'tag_attrib'      => $this->getScripTagAttributes()
		);

		$this->event->trigger('newsmanremarketing/script_product_view_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/productview', $data);
		$this->event->trigger('newsmanremarketing/script_product_view_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getCategoryViewJs(): string {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$category_view = new \Newsman\Remarketing\Action\CategoryView($this->registry);
		$category_view->setEvent($this->event)
			->setDb($this->db)
			->setRequest($this->request)
			->setProductModel($this->model_catalog_product)
			->setCategoryModel($this->model_catalog_category)
			->setCheckoutOrderModel($this->model_checkout_order);

		$data = array(
			'category_view_js' => $category_view->getJs(),
			'tag_attrib'       => $this->getScripTagAttributes()
		);

		$this->event->trigger('newsmanremarketing/script_category_view_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/categoryview', $data);
		$this->event->trigger('newsmanremarketing/script_category_view_render/after', array(&$data, &$output));

		return $output;
	}

	protected function getPurchaseJs(): string {
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$purchase = new \Newsman\Remarketing\Action\Purchase($this->registry);
		$purchase->setEvent($this->event)
			->setDb($this->db)
			->setRequest($this->request)
			->setProductModel($this->model_catalog_product)
			->setCategoryModel($this->model_catalog_category)
			->setCheckoutOrderModel($this->model_checkout_order);

		$order_details = array();
		if (isset($this->session->data['ga_orderDetails'])) {
			$order_details = $this->session->data['ga_orderDetails'];
		}
		$order_products = array();
		if (isset($this->session->data['ga_orderProducts'])) {
			$order_products = $this->session->data['ga_orderProducts'];
		}
		$data = array(
			'purchase_js' => $purchase->getJs($order_details, $order_products),
			'tag_attrib'  => $this->getScripTagAttributes()
		);

		$this->event->trigger('newsmanremarketing/script_purchase_render/before', array(&$data));
		$output = $this->load->view('extension/newsman/analytics/newsman/purchase', $data);
		$this->event->trigger('newsmanremarketing/script_purchase_render/after', array(&$data, &$output));

		unset($this->session->data['ga_orderDetails']);
		unset($this->session->data['ga_orderProducts']);

		if (isset($this->session->data['newsman_order_id'])) {
			unset($this->session->data['newsman_order_id']);
		}

		return $output;
	}

	protected function getScripTagAttributes(): string {
		$script_tag_attributes = '';
		$this->event->trigger(
			'newsmanremarketing/script_tracking_attributes/before',
			array(&$script_tag_attributes)
		);

		return $script_tag_attributes;
	}

	protected function getCurrentRoute(): string {
		$route = '';
		if (isset($this->request->get['route'])) {
			$route = (string)$this->request->get['route'];
		}
		$this->event->trigger('newsmanremarketing/remarketing_get_current_route/after', array(&$route));

		return $route;
	}
}
