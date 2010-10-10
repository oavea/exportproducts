<?php
/**
  * Export Products
  * @category export
  *
  * @author Lee Wood - lmwood.com
  * @copyright Lee Wood / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 0.5
  */
class ExportProducts extends Module
{
	function __construct()
	{
		$this->name = 'exportproducts';
		$this->tab = 'Tools';
		$this->version = '0.5';
		$this->displayName = 'Export Products';
		
		/* The parent construct is required for translations */
		parent::__construct();
		$this->page = basename(__FILE__, '.php');
		$this->description = $this->l('A module to export all products to csv');
	}

	function install()
	{
		
		$export_exists = 'DROP TABLE IF EXISTS  `' . _DB_PREFIX_ . 'export_fields`, `' . _DB_PREFIX_ . 'export_set`';

		$export_fields_sql = "
		CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "export_fields` (
			`id` int(10) NOT NULL auto_increment,
			`field_name` varchar(50) NOT NULL,
			`database_name` varchar(50) NOT NULL,
			`category` varchar(50) NOT NULL,
			`position` int(2) NOT NULL default '0',
			PRIMARY KEY  (`id`)
		)";
	
		$export_set_sql = '
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'export_set` (
		  `id` int(10) unsigned NOT NULL auto_increment,
		  `set_name` varchar(200) NOT NULL,
		  `set_values` text NOT NULL,
		  PRIMARY KEY  (`id`)
		)';

		$export_fields_data_sql = "
		INSERT INTO `" . _DB_PREFIX_ . "export_fields` (`id`, `field_name`, `database_name`, `category`, `position`) VALUES
		(1, 'Product Id', 'id_product', 'products', 0),
		(2, 'Product Reference', 'reference', 'products', 0),
		(3, 'Name', 'name', 'products_lang', 0),
		(4, 'Short Description', 'description_short', 'products_lang', 0),
		(5, 'Long Description', 'description', 'products_lang', 0),
		(6, 'Quantity', 'quantity', 'products', 0),
		(7, 'Price', 'price', 'products', 0),
		(8, 'Wholesale Price', 'wholesale_price', 'products', 0),
		(9, 'Supplier Name', 'id_supplier', 'products', 0),
		(10, 'Manufacturer', 'id_manufacturer', 'products', 0),
		(11, 'Tax %', 'id_tax', 'products', 0),
		(12, 'Categories', 'id_category_default', 'products', 0),
		(13, 'On Sale', 'on_sale', 'products', 0),
		(14, 'EAN 13', 'ean13', 'products', 0),
		(15, 'Reduction Price', 'reduction_price', 'products', 0),
		(16, 'Reduction %', 'reduction_percent', 'products', 0),
		(17, 'Reduction From', 'reduction_from', 'products', 0),
		(18, 'Reduction To', 'reduction_to', 'products', 0),
		(19, 'Supplier Reference', 'supplier_reference', 'products', 0),
		(20, 'Weight', 'weight', 'products', 0),
		(21, 'Date Added', 'date_add', 'products', 0),
		(22, 'Active', 'active', 'products', 0),
		(23, 'Meta Title', 'meta_title', 'products_lang', 0),
		(24, 'Meta Description', 'meta_description', 'products_lang', 0),
		(25, 'Meta Keywords', 'meta_keywords', 'products_lang', 0),
		(26, 'Available Now', 'available_now', 'products_lang', 0),
		(27, 'Available Later', 'available_later', 'products_lang', 0),
		(28, 'Tags', 'tags', 'products', 0),
		(29, 'Accessories', 'accessories', 'products', 0),
		(30, 'Images', 'images', 'products', 0);
		";
	
		return(Db::getInstance()->Execute($export_exists) AND
			   Db::getInstance()->Execute($export_fields_sql) AND
			   Db::getInstance()->Execute($export_set_sql) AND
			   Db::getInstance()->Execute($export_fields_data_sql) AND
			   parent::install());
	}
	
	function uninstall()
	{
		return(Db::getInstance()->Execute('DROP TABLE ' . _DB_PREFIX_ . 'export_fields') AND 
			   Db::getInstance()->Execute('DROP TABLE ' . _DB_PREFIX_ . 'export_set') AND 
			   parent::uninstall());
	}
	
	public function getContent()
	{
		global $smarty;

			$sql="SELECT * FROM `"._DB_PREFIX_."export_fields`";
			$field_list = Db::getInstance()->ExecuteS($sql);
			foreach($field_list as $value) {
				if($value['position'] != "0") {
					$currentfields[intval($value['position'])] = $value;
				} else {
					$availablefields[] = $value;
				}
			}
			if(isset($currentfields)) {
				ksort($currentfields);
				$smarty->assign("current_fields",$currentfields);
			}
			$smarty->assign("available_fields", $availablefields);

			$sql="SELECT * FROM `"._DB_PREFIX_."export_set`";
			if($field_list = Db::getInstance()->ExecuteS($sql)) {
				foreach($field_list as $value) {
					$sets[$value['id']] = $value['set_name'];
				}
				$smarty->assign('sets', $sets);
			}
				
			$langs = Language::getLanguages();
			$smarty->assign('langs', $langs);
			
			/* display the module name */
			$this->_html = '<h2>'.$this->displayName.'</h2>';
			
			
		$this->_html.=$this->displayForm();
		return $this->_html;
	}

	public function displayForm()
	{
		global $smarty, $cookie;
		$smarty->assign('base_dir', __PS_BASE_URI__);
		$smarty->assign('currentIndex', $_SERVER['REQUEST_URI']);
		return $this->display(__FILE__,'exportproducts.tpl');
	}

}
