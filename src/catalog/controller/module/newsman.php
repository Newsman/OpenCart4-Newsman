<?php

namespace Opencart\Catalog\Controller\Extension\Newsman\Module;

/**
 * Class Newsman
 *
 * @property \Opencart\System\Engine\Autoloader       $autoloader
 * @property \Opencart\System\Engine\Registry         $registry
 * @property \Newsman\Nzmloader                       $nzmloader
 * @property \Newsman\Nzmconfig                       $nzmconfig
 * @property \Newsman\Nzmlogger                       $nzmlogger
 * @property \Opencart\System\Engine\Loader           $load
 * @property \Opencart\System\Library\Request         $request
 * @property \Opencart\System\Library\Response        $response
 * @property \Opencart\System\Library\Cart\Cart       $cart
 * @property \Opencart\System\Library\Cart\Customer   $customer
 * @property \Opencart\System\Engine\Config           $config
 * @property \Opencart\System\Library\Session         $session
 * @property \Opencart\Catalog\Model\Checkout\Order    $model_checkout_order
 */
class Newsman extends \Opencart\System\Engine\Controller {
	private static bool $eventAccountNewsletterBeforeRun = false;

	/**
	 * @param \Opencart\System\Engine\Registry $registry
	 */
	public function __construct($registry) {
		parent::__construct($registry);

		$this->autoloader->register('Newsman', DIR_EXTENSION . 'newsman/system/library/newsman/');

		if (!$this->registry->has('nzmloader')) {
			$this->registry->set('nzmloader', new \Newsman\Nzmloader($this->registry));
		}

		if (!$this->registry->has('nzmconfig')) {
			$this->registry->set('nzmconfig', new \Newsman\Nzmconfig($this->registry));
		}

		if (!$this->registry->has('nzmlogger')) {
			$this->registry->set('nzmlogger', new \Newsman\Nzmlogger($this->registry));
		}
	}

	public function index(): void {
		$data = [];

		$this->load->language('extension/newsman/module/newsman');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && !empty($this->request->post['newsman_events'])) {
			$webhooks = new \Newsman\Webhooks($this->registry);
			$webhooks->execute($this->request->post['newsman_events']);
		} else {
			$router = new \Newsman\Export\Router($this->registry);
			$router->execute();
		}

		$this->response->setOutput($this->load->view('extension/newsman/module/newsman', $data));
	}

	public function eventCronGdprBefore(string &$route, array &$args): void {
		$this->load->model('account/gdpr');
		$this->load->model('account/customer');

		$results = $this->model_account_gdpr->getExpires();

		foreach ($results as $result) {
			$customer_info = $this->model_account_customer->getCustomerByEmail($result['email']);

			if ($customer_info) {
				$this->unsubscribe($customer_info['email'], (int)$customer_info['store_id']);
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

	public function cart(): void {
		$items = [];
		$cart = $this->cart->getProducts();
		foreach ($cart as $cart_item) {
			$items[] = array(
				"id"       => $cart_item['product_id'],
				"name"     => $cart_item["name"],
				"price"    => $cart_item["price"],
				"quantity" => $cart_item['quantity']
			);
		}

		header('Access-Control-Allow-Origin: *');
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false); // Older IE browsers
		header("Pragma: no-cache");
		header('Content-Type:application/json');
		echo json_encode($items, JSON_PRETTY_PRINT);
		exit;
	}

	public function eventAccountNewsletterBefore(string &$route, array &$args): void {
		if (self::$eventAccountNewsletterBeforeRun) {
			return;
		}

		self::$eventAccountNewsletterBeforeRun = true;

		if (!$this->customer->isLogged()) {
			return;
		}

		if (!($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['newsletter']))) {
			return;
		}

		$store_id = (int)$this->config->get('config_store_id');

		$new = $this->request->post['newsletter'];
		if (!$this->customer->getNewsletter() && $new) {
			$this->subscribe(
				$this->customer->getEmail(),
				$this->customer->getFirstName(),
				$this->customer->getLastName(),
				$this->customer->getTelephone(),
				$store_id
			);
		} elseif ($this->customer->getNewsletter() && !$new) {
			$this->unsubscribe($this->customer->getEmail(), $store_id);
		}
	}

	public function eventCheckoutRegisterSaveAfter(string &$route, array &$args, mixed &$output): void {
		$this->subscribeFromPost();
	}

	public function eventAccountRegisterAfter(string &$route, array &$args, mixed &$output): void {
		$this->subscribeFromPost();
	}

	private function subscribeFromPost(): void {
		$json = json_decode($this->response->getOutput(), true);
		if (!is_array($json) || (is_array($json) && isset($json['error']))) {
			return;
		}

		if (!(isset($this->request->post['newsletter']) && $this->request->post['newsletter'] == '1' && isset($this->request->post['email']))) {
			return;
		}

		$this->subscribe(
			$this->request->post['email'],
			$this->request->post['firstname'] ?? '',
			$this->request->post['lastname'] ?? '',
			$this->request->post['telephone'] ?? '',
			(int)$this->config->get('config_store_id')
		);
	}

	private function subscribe(string $email, string $firstname, string $lastname, string $telephone = '', int $store_id = 0): void {
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

	public function eventCheckoutOrderAddAfter(string &$route, array &$args, mixed &$output): void {
		$order_id = $output;
		if ($order_id) {
			$this->session->data['newsman_order_id'] = $order_id;
			$save_order = new \Newsman\Action\Order\Save($this->registry);
			$save_order->execute($order_id, true);
		}
	}

	public function eventApiOrderAfter(string &$route, array &$args): void {
		$order_id = null;
		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} elseif (isset($this->request->post['order_id'])) {
			$order_id = (int)$this->request->post['order_id'];
		}
		if (!$order_id) {
			return;
		}

		$call = '';
		if (isset($this->request->get['call'])) {
			$call = $this->request->get['call'];
		}

		switch($call) {
			case 'history_add':
				$order_status_id = (isset($this->request->post['order_status_id'])) ? (int)$this->request->post['order_status_id'] : false;
				if ($order_status_id) {
					$status = new \Newsman\Action\Order\Status($this->registry);
					$status->execute($order_id, $order_status_id, false);
				}
			break;

			case 'confirm':
				$save_order = new \Newsman\Action\Order\Save($this->registry);
				$save_order->execute($order_id, false);
			break;
		}
	}

	public function eventCheckoutSuccessBefore(string &$route, array &$args): void {
		$this->session->data['ga_orderDetails'] = null;
		$this->session->data['ga_orderProducts'] = null;

		$order_id = 0;

		if (isset($this->session->data['order_id'])) {
			$order_id = $this->session->data['order_id'];
		} elseif (isset($this->session->data['newsman_order_id'])) {
			$order_id = $this->session->data['newsman_order_id'];
		}

		if ($order_id) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);

			if ($order_info) {
				$this->session->data['ga_orderDetails'] = $order_info;

				$products = $this->model_checkout_order->getProducts($order_id);

				$this->session->data['ga_orderProducts'] = [];
				foreach ($products as $product) {
					$this->session->data['ga_orderProducts'][] = array_merge($product, [
						'order_id' => $order_id
					]);
				}
			}

			unset($this->session->data['newsman_order_id']);
		}
	}
}
