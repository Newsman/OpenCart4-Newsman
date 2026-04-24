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
 * @property \Opencart\System\Library\Cart\Cart        $cart
 * @property \Opencart\System\Library\Cart\Tax         $tax
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

	/**
	 * Event handler for catalog/view/common/header/after.
	 * Injects remarketing scripts for themes that do not render analytics output.
	 *
	 * @param string $route
	 * @param array  $args
	 * @param string $output
	 *
	 * @return void
	 */
	public function eventHeaderAfter(string &$route, array &$args, string &$output): void {
		$remarketing_output = $this->index();

		if (!empty($remarketing_output)) {
			$output .= $remarketing_output;
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

		if ($this->nzmconfig->isThemeCartCompatibility()) {
			$template = 'extension/newsman/analytics/newsman/cart';
		} else {
			$template = 'extension/newsman/analytics/newsman/minicart';
			$data['nzm_cookie_path'] = $this->getCartCookiePath();
		}

		$this->event->trigger('newsmanremarketing/script_cart_render/before', array(&$data));
		$output = $this->load->view($template, $data);
		$this->event->trigger('newsmanremarketing/script_cart_render/after', array(&$data, &$output));

		return $output;
	}

	/**
	 * Scope of the nzm_cart_sync session cookie used by minicart.twig's
	 * bootstrap. Derived from HTTP_SERVER so multistore installs running
	 * under a subpath (e.g. https://example.com/shop/) do not share one
	 * session flag with sibling stores on the same domain.
	 *
	 * @return string
	 */
	protected function getCartCookiePath(): string {
		$path = parse_url(HTTP_SERVER, PHP_URL_PATH);
		if (!is_string($path) || $path === '' || $path[0] !== '/') {
			return '/';
		}

		return $path;
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

	/**
	 * Event handler for catalog/view/common/cart/after.
	 *
	 * Appends a JSON payload of the current cart products to the rendered minicart
	 * HTML so that the minicart-DOM-based tracker (minicart.twig) can read it
	 * without parsing theme markup.
	 *
	 * Skipped when Theme Cart Compatibility is enabled (cart.twig handles tracking
	 * via XHR/fetch interception in that mode).
	 *
	 * @param string $route
	 * @param array  $args
	 * @param string $output
	 *
	 * @return void
	 */
	public function eventViewCommonCartAfter(string &$route, array &$args, string &$output): void {
		if (!$this->nzmconfig->isRemarketingActive()) {
			return;
		}
		if ($this->nzmconfig->isThemeCartCompatibility()) {
			return;
		}

		$products = array();
		try {
			$cart_products = $this->cart->getProducts();
		} catch (\Exception $e) {
			return;
		}

		$show_price = ($this->customer->isLogged() || !$this->config->get('config_customer_price'));

		foreach ($cart_products as $product) {
			$price = 0.0;
			if ($show_price && isset($product['price'], $product['tax_class_id'])) {
				$unit_price = $this->tax->calculate(
					$product['price'],
					$product['tax_class_id'],
					$this->config->get('config_tax')
				);
				$price = (float)$unit_price;
			}

			$products[] = array(
				'id'       => isset($product['product_id']) ? (string)$product['product_id'] : '',
				'name'     => isset($product['name']) ? (string)$product['name'] : '',
				'price'    => $price,
				'quantity' => isset($product['quantity']) ? (int)$product['quantity'] : 0,
			);
		}

		$json = json_encode(
			$products,
			JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
		if ($json === false) {
			return;
		}

		// Append the JSON inside the cart response. The OC4 default theme refreshes
		// the minicart via $('#cart').load(common/cart.info), which replaces the
		// entire #cart contents with the response body. By appending the tag here it
		// ends up inside #cart on both initial render and subsequent AJAX refreshes.
		$output .= '<script type="application/json" data-newsman-cart>' . $json . '</script>';
	}
}
