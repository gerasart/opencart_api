<?php
declare(strict_types=1);

/**
 * User: gerasart
 * Site: seonarnia.com
 */
class ControllerExtensionApiProducts extends Controller
{
    /**
     * Show product in json format.
     * Get @params [int categoryId, int limit, int offset]
     */
    public function get(): void
    {
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $result = [
            'code' => 200,
            'message' => "success",
            'data' => [],
        ];
        $category_id = $this->request->get['categoryId'] ?? 0;
        $limit = $this->request->get['limit'] ?? 20;
        $offset = $this->request->get['offset'] ?? 0;

        $products = $this->model_catalog_product->getProducts(
            [
                'filter_category_id' => $category_id,
                'limit' => $limit,
                'start' => $offset
            ]
        );

        foreach ($products as $product) {
            $special = false;

            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }

            if ((float)$product['special']) {
                $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            }

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format(
                    $this->tax->calculate($product['price'],
                        $product['tax_class_id'],
                        $this->config->get('config_tax')),
                    $this->session->data['currency']
                );
            }

            $result['data'][] = [
                'id' => $product['product_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $price,
                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'thumb' => $image,
                'special' => $special,
                'minimum' => $product['minimum'] > 0 ? $product['minimum'] : 1,
                'rating' => $product['rating'],
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }
}
