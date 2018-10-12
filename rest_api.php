<?php

class ControllerExtensionFeedRestApi extends Controller {

	private $debugIt = false;
	private $ip;
	
	/*
	* Get products
	*/
	public function products() {


		$this->load->model('catalog/product');
	
		$json = array('success' => true, 'products' => array());

		/*check category id parameter*/
		if (isset($this->request->get['category'])) {
			$category_id = $this->request->get['category'];
		} else {
			$category_id = 0;
		}

			if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}


		$products = $this->model_catalog_product->getProducts(array(
			'filter_category_id'        => $category_id,
		    'limit' => $limit,
		));

		foreach ($products as $product) {

			if ($product['image']) {
				$image = '/image/'.$product['image'];
			} else {
				$image = false;
			}

			if ((float)$product['special']) {
				$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$special = false;
			}

			$json['products'][] = array(
					'id'			=> $product['product_id'],
					'name'			=> $product['name'],
					'description'	=> $product['description'],
					'pirce'			=> $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
					'href'			=> $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'thumb'			=> $image,
					'special'		=> $special,
					'rating'		=> $product['rating']
			);
		}

 		$this->response->addHeader('Content-Type: application/json');

		if ($this->debugIt) {
			echo '<pre>';
			print_r($json);
			echo '</pre>';
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}

	public function check() {

		//in future i add safe condition to products()
		if($this->debugIt)
		print_r($_SERVER);


	}

	

}
