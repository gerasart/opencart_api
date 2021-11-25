<?php

class ControllerExtensionFeedRestApi extends Controller
{
    /**
     * Show product in json format.
     */
    public function products(): void
    {
        $this->load->model('catalog/product');
        $result      = ['success' => true, 'products' => []];
        $category_id = $this->request->get['category'] ?? 0;
        $limit = $this->config->get('theme_'.$this->config->get('config_theme').'_product_limit');

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        }

        $products = $this->model_catalog_product->getProducts(
            [
                'filter_category_id' => $category_id,
                'limit'              => $limit,
            ]
        );
        
        foreach ($products as $product) {
            $image   = false;
            $special = false;
            
            if ($product['image']) {
                $image = '/image/'.$product['image'];
            }

            if ((float)$product['special']) {
                $special = $this->currency->format(
                    $this->tax->calculate(
                        $product['special'],
                        $product['tax_class_id'],
                        $this->config->get('config_tax')
                    )
                );
            }
            
            $price = $this->currency->format(
                $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))
            );
            
            $result['products'][] = [
                'id'          => $product['product_id'],
                'name'        => $product['name'],
                'description' => $product['description'],
                'price'       => $price,
                'href'        => $this->url->link('product/product', 'product_id='.$product['product_id']),
                'thumb'       => $image,
                'special'     => $special,
                'rating'      => $product['rating'],
            ];
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }
}
