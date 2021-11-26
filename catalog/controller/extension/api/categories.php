<?php
declare(strict_types=1);

/**
 * User: gerasart
 * Site: seonarnia.com
 */
class ControllerExtensionApiCategories extends Controller
{
    public function get(): void
    {
        $this->load->model('catalog/category');

        $result = [
            'code' => 200,
            'message' => "success",
            'data' => [],
        ];
        $parent = 0;
        $level = 1;

        if (isset($this->request->get['parent'])) {
            $parent = $this->request->get['parent'];
        }

        if (isset($this->request->get['level'])) {
            $level = $this->request->get['level'];
        }

        $result['data'] = $this->getCategoriesTree((int)$parent, (int)$level);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    /**
     * @param int $parent Parent category id
     * @param int $level Depth level
     * @return array
     */
    private function getCategoriesTree(int $parent = 0, int $level = 1): array
    {
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        $domain = $this->request->server['HTTP_HOST'] ?? '';
        $isHttps = $this->request->server['HTTPS'] ?? '';
        $https = $isHttps === '1' ? 'https://' : 'http://';
        $result = [];
        $categories = $this->model_catalog_category->getCategories($parent);

        if ($categories && $level > 0) {
            $level--;

            foreach ($categories as $category) {
                if ($category['image']) {
                    $categoryImage = $https . $domain . "/image/{$category['image']}";
                } else {
                    $categoryImage = $this->model_tool_image->resize('placeholder.png', 120, 120);
                }

                $result[] = array(
                    'categoryId' => $category['category_id'],
                    'parentId' => $category['parent_id'],
                    'categoryName' => $category['name'],
                    'image' => $categoryImage,
                    'description' => $category['description'],
                    'href' => $this->url->link('product/category', 'category_id=' . $category['category_id']),
                    'categories' => $this->getCategoriesTree((int)$category['category_id'], (int)$level),
                );
            }
        }

        return $result;
    }
}
