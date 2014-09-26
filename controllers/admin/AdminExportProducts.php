<?php
/**
 * Export Products
 * @category export
 *
 * @author Oavea - Oavea.com
 * @copyright Oavea / PrestaShop
 * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
 * @version 2.0
 */

class AdminExportProductsController extends ModuleAdminController {

	public $available_fields;

	public function __construct()
	{
		$this->bootstrap = true;

		$this->meta_title = $this->l('Export Products');
		parent::__construct();
		if (! $this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));

		$this->available_fields = array(
			'id'                        => array('label' => 'Product ID'),
			'active'                    => array('label' => 'Active (0/1)'),
			'name'                      => array('label' => 'Name'),
			'category'                  => array('label' => 'Categories (x,y,z...)'),
			'price_tex'                 => array('label' => 'Price tax excluded'),
			'price_tin'                 => array('label' => 'Price tax included'),
			'id_tax_rules_group'        => array('label' => 'Tax rules ID'),
			'wholesale_price'           => array('label' => 'Wholesale price'),
			'on_sale'                   => array('label' => 'On sale (0/1)'),
			'reduction_price'           => array('label' => 'Discount amount'),
			'reduction_percent'         => array('label' => 'Discount percent'),
			'reduction_from'            => array('label' => 'Discount from (yyyy-mm-dd)'),
			'reduction_to'              => array('label' => 'Discount to (yyyy-mm-dd)'),
			'reference'                 => array('label' => 'Reference #'),
			'supplier_reference'        => array('label' => 'Supplier reference #'),
			'supplier_name'             => array('label' => 'Supplier'),
			'manufacturer_name'         => array('label' => 'Manufacturer'),
			'ean13'                     => array('label' => 'EAN13'),
			'upc'                       => array('label' => 'UPC'),
			'ecotax'                    => array('label' => 'Ecotax'),
			'width'                     => array('label' => 'Width'),
			'height'                    => array('label' => 'Height'),
			'depth'                     => array('label' => 'Depth'),
			'weight'                    => array('label' => 'Weight'),
			'quantity'                  => array('label' => 'Quantity'),
			'minimal_quantity'          => array('label' => 'Minimal quantity'),
			'visibility'                => array('label' => 'Visibility'),
			'additional_shipping_cost'  => array('label' => 'Additional shipping cost'),
			'unity'                     => array('label' => 'Unit for the unit price'),
			'unit_price'                => array('label' => 'Unit price'),
			'description_short'         => array('label' => 'Short description'),
			'description'               => array('label' => 'Description'),
			'tags'                      => array('label' => 'Tags (x,y,z...)'),
			'meta_title'                => array('label' => 'Meta title'),
			'meta_keywords'             => array('label' => 'Meta keywords'),
			'meta_description'          => array('label' => 'Meta description'),
			'link_rewrite'              => array('label' => 'URL rewritten'),
			'available_now'             => array('label' => 'Text when in stock'),
			'available_later'           => array('label' => 'Text when backorder allowed'),
			'available_for_order'       => array('label' => 'Available for order (0 = No, 1 = Yes)'),
			'available_date'            => array('label' => 'Product available date'),
			'date_add'                  => array('label' => 'Product creation date'),
			'show_price'                => array('label' => 'Show price (0 = No, 1 = Yes)'),
			'image'                     => array('label' => 'Image URLs (x,y,z...)'),
			'delete_existing_images'    => array(
				'label' => 'Delete existing images (0 = No, 1 = Yes)'
			),
			'features'                  => array('label' => 'Feature (Name:Value:Position:Customized)'),
			'online_only'               => array('label' => 'Available online only (0 = No, 1 = Yes)'),
			'condition'                 => array('label' => 'Condition'),
			'customizable'              => array('label' => 'Customizable (0 = No, 1 = Yes)'),
			'uploadable_files'          => array('label' => 'Uploadable files (0 = No, 1 = Yes)'),
			'text_fields'               => array('label' => 'Text fields (0 = No, 1 = Yes)'),
			'out_of_stock'              => array('label' => 'Action when out of stock'),
			'shop'                      => array(
				'label' => 'ID / Name of shop',
				'help'  => 'Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.',
			),
			'advanced_stock_management' => array(
				'label' => 'Advanced Stock Management',
				'help'  => 'Enable Advanced Stock Management on product (0 = No, 1 = Yes).',
			),
			'depends_on_stock'          => array(
				'label' => 'Depends on stock',
				'help'  => '0 = Use quantity set in product, 1 = Use quantity from warehouse.',
			),
			'warehouse'                 => array(
				'label' => 'Warehouse',
				'help'  => 'ID of the warehouse to set as storage.'
			),
		);

	}

	public function renderView()
	{

		return $this->renderConfigurationForm();

	}

	public function renderConfigurationForm()
	{
		$lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
		$langs = Language::getLanguages();

		foreach ($langs as $key => $language)
		{
			$options[] = array('id_option' => $language['id_lang'], 'name' => $language['name']);
		}

		$cats = Category::getCategories($lang->id, true, false);

		$categories[] = array('id_option' => 99999, 'name' => 'All');

		foreach($cats as $key => $cat)
		{
			$categories[] = array('id_option' => $cat['id_category'], 'name' => $cat['name']);
		}

		$inputs = array(
			array(
				'type'    => 'select',
				'label'   => $this->l('Language'),
				'desc'    => $this->l('Choose a language you wish to export'),
				'name'    => 'export_language',
				'class'   => 't',
				'options' => array(
					'query' => $options,
					'id'    => 'id_option',
					'name'  => 'name'
				),
			),
			array(
				'type'  => 'text',
				'label' => $this->l('Delimiter'),
				'name'  => 'export_delimiter',
				'value' => ',',
				'desc'  => $this->l('The character to separate the fields')
			),
			array(
				'type' => 'radio',
				'label' => $this->l('Export active products?'),
				'name' => 'export_active',
				'values' => array(
					array('id' => 'active_off', 'value'=> 0, 'label' => 'no, export all products.'),
					array('id' => 'active_on', 'value'=> 1, 'label' => 'yes, export only active products'),
				),
				'is_bool' => true,
			),
			array(
				'type'    => 'select',
				'label'   => $this->l('Product Category'),
				'desc'    => $this->l('Choose a product category you wish to export'),
				'name'    => 'export_category',
				'class'   => 't',
				'options' => array(
					'query' => $categories,
					'id'    => 'id_option',
					'name'  => 'name'
				),
			),
		);

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Export Options'),
					'icon'  => 'icon-cogs'
				),
				'input'  => $inputs,
				'submit' => array(
					'title' => $this->l('Export'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;

		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitExport';
		$helper->currentIndex = self::$currentIndex;
		$helper->token = Tools::getAdminTokenLite('AdminExportProducts');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'    => $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}


	public function getConfigFieldsValues()
	{
		return array(
			'export_delimiter' => ';',
			'export_language'  => (int) Configuration::get('PS_LANG_DEFAULT')
		);
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitExport')) {
			$delimiter = Tools::getValue('export_delimiter');
			$id_lang = Tools::getValue('export_language');

			set_time_limit(0);
			$fileName = 'products_'.date("Y_m_d_H_i_s").'.csv';
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Description: File Transfer');
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename={$fileName}");
			header("Expires: 0");
			header("Pragma: public");

			$f = fopen('php://output', 'w');

			foreach ($this->available_fields as $field => $array)
				$titles[] = $array['label'];

			fputcsv($f, $titles, $delimiter, '"');

			$export_active = (Tools::getValue('export_active') == 0 ? false : true);
			$export_category = (Tools::getValue('export_category') == 99999 ? false : Tools::getValue('export_category'));

			$products = Product::getProducts($id_lang, 0, 0, 'id_product', 'ASC', $export_category, $export_active);

			foreach ($products as $product)
			{
				$line = array();
				$p = new Product($product['id_product'], true, $id_lang, 1);

				foreach ($this->available_fields as $field => $array)
				{
					if (isset($p->$field) && !is_array($p->$field))
					{
						$line[$field] = $p->$field ? $p->$field : ' ';
					}
					else
					{
						switch ($field)
						{
							case 'price_tex':
								$line['price_tex'] = $p->getPrice(false);
								$line['price_tin'] = $p->getPrice(true);

								break;
							case 'upc':
								$line['upc'] = $p->upc ? $p->upc : ' ';

								break;
							case 'features':
								$line['features'] = '';
								$features = $p->getFrontFeatures($id_lang);
								$position = 1;

								foreach ($features as $feature)
								{
									$line['features'] .= $feature['name'] . ':' . $feature['value'] . ':' . $position;
									$position++;
								}

								break;
							case 'reduction_price':
								$specificPrice = SpecificPrice::getSpecificPrice($p->id, 1, 0, 0, 0, 0);

								$line['reduction_price'] = '';
								$line['reduction_percent'] = '';
								$line['reduction_from'] = '';
								$line['reduction_to'] = '';

								if ($specificPrice['reduction_type'] == "amount")
								{
									$line['reduction_price'] = $specificPrice['reduction'];
								}
								elseif ($specificPrice['reduction_type'] == "percent")
								{
									$line['reduction_percent'] = $specificPrice['reduction'];
								}

								if ($line['reduction_price'] !== '' || $line['reduction_percent'] !== '')
								{
									$line['reduction_from'] = date_format(date_create($specificPrice['from']), "Y-m-d");
									$line['reduction_to'] = date_format(date_create($specificPrice['to']), "Y-m-d");
								}

								break;
							case 'tags':
								$tags = $p->getTags($id_lang);

								$line['tags'] = $tags;

								break;
							case 'image':

								$link = new Link();
								$imagelinks = array();
								$images = $p->getImages($id_lang);
								foreach ($images as $image) {
									$imagelinks[] = Tools::getShopProtocol() . $link->getImageLink($p->link_rewrite, $p->id . '-' . $image['id_image']);
								}
								$line['image'] = implode(",", $imagelinks);

								break;
							case 'delete_existing_images':
								$line['delete_existing_images'] = 0;

								break;
							case 'shop':
								$line['shop'] = 1;

								break;
							case 'warehouse':
								$warehouses = Warehouse::getWarehousesByProductId($p->id);
								$line['warehouse'] = '';
								if (! empty($warehouses))
								{
									function getWarehouses($id_warehouses)
									{
										return $id_warehouses['id_warehouse'];
									}
                                    $line['warehouse'] = implode(',', array_map("getWarehouses", $warehouses));
								}

								break;
						}
					}
				}
				fputcsv($f, $line, $delimiter, '"');
			}
			fclose($f);
			die();
		}
	}

	public function initContent()
	{
		$this->content = $this->renderView();
		parent::initContent();
	}
}
