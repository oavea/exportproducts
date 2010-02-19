<?php
/**
  * Export Products
  * @category export
  *
  * @author Lee Wood - lmwood.com
  * @copyright Lee Wood / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 0.1
  */
class ExportProducts extends Module
{
	function __construct()
	{
		$this->name = 'exportproducts';
		$this->tab = 'Tools';
		$this->version = '0.1';
		
		/* The parent construct is required for translations */
		parent::__construct();
		
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Export Products');
		$this->description = $this->l('A module to export all products to csv');
	}

	function install()
	{
		if(!file_exists(dirname(__FILE__) . '/install.sql')) {
			return false;
		} elseif(!$sql = file_get_contents(dirname(__FILE__) . '/install.sql')) {
			return false;
		}
	
		$sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
	
		foreach($sql AS $k => $query) {
			Db::getInstance()->Execute(trim($query));
		}
		if (!parent::install())
			return false;
		return true;
	}
	
	function uninstall()
	{
		return(Db::getInstance()->Execute('DROP TABLE ' . _DB_PREFIX_ . 'export_fields') AND 
			   Db::getInstance()->Execute('DROP TABLE ' . _DB_PREFIX_ . 'export_set') AND 
			   parent::uninstall());
	}
	
	function getContent()
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
			
			/* display the module name */
			$this->_html = '<h2>'.$this->displayName.'</h2>';
			
			if($efields = $_POST['export_data']) {
			
			$efields = explode("&export[]=", $efields);
			
			$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` ORDER BY position";
			$field_list = Db::getInstance()->ExecuteS($sql);
			foreach($field_list as $field => $value){
				if(in_array($value['id'], $efields)) {
					$export_fields[$value['database_name']] = array('name' => $value['field_name'], 'category' => $value['category']);
				}
			}
						
			foreach($export_fields as $field => $array) {
				$titles[] = $array['name'];
				
				switch($array['category']) {
					case "products":

						switch($field) {
							case "accessories":
								$inc_accessories = true;
							break;
							case "tags":
								$inc_tags = true;
							break;
							default:
								$fields[] = "p.`" . $field . "`";
							break;
						}
						
					break;
					case "products_lang":
						$fields[] = "pl.`" . $field . "`";
					break;
				}
			}
			
			$sql='SELECT '.implode(', ', $fields).'
			FROM '._DB_PREFIX_.'product as p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category`)
			';
			
			$exportlist = Db::getInstance()->ExecuteS($sql);
			$f=fopen(dirname(__FILE__).'/products.csv', 'w');
			fwrite($f, implode(', ', $titles)."\r\n");
			foreach($exportlist AS $export) {
				$product = new Product($export['id_product']);
				$attribute = array();
				$tags = array();
				$ups = array();
				$accessories = array();
				
				if(isset($export['id_supplier'])) {
					$supplier_name_sql = 'SELECT name FROM `'._DB_PREFIX_.'supplier` WHERE `id_supplier`='.$export['id_supplier'];
					$supplier_name = Db::getInstance()->getRow($supplier_name_sql);
					$export['id_supplier'] = $supplier_name['name'];
				}
				
				if(isset($export['id_tax'])) {
					$tax_rate_sql = 'SELECT rate FROM `'._DB_PREFIX_.'tax` WHERE `id_tax`='.$export['id_tax']; 
					$tax_rate = Db::getInstance()->getRow($tax_rate_sql);
					$export['id_tax'] = $tax_rate['rate'];
				}
				
				$id_tags_sql = 'SELECT id_tag FROM `'._DB_PREFIX_.'product_tag` WHERE `id_product`='.$export['id_product'];
				if(($id_tags = Db::getInstance()->ExecuteS($id_tags_sql)) && (isset($inc_tags))) 
				{
					foreach($id_tags as $key => $value) {
						$tag_name_sql = 'SELECT name FROM `'._DB_PREFIX_.'tag` WHERE `id_tag`='.$value['id_tag'];
						$tag_name = Db::getInstance()->ExecuteS($tag_name_sql);
						foreach($tag_name as $tag => $name) {
							$tags[] = $name['name'];
						}
					}
					$export['tags'] = implode(',', $tags);
				}
				if(isset($inc_accessories)) {
					
					if($acc = $product->getAccessories(1, false)) {
						foreach($acc as $acc_key => $acc_value)
						{
							$accessories[] = $acc_value['reference'];
						}
						$export['accessories'] = implode(',', $accessories);
					} else {
						$export['accessories'] = '';
					}
				}

				foreach($export_fields as $field => $value) {
						$export_final[$field] = $export[$field];
				}
					
				fputcsv($f, $export_final);
				
			}
			Tools::redirect('modules/exportproducts/products.csv');
		}
		$this->_html.=$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm()
	{
		global $smarty, $cookie;
		$smarty->assign('currentIndex', $_SERVER['REQUEST_URI']);
		return $this->display(__FILE__,'exportproducts.tpl');
	}

}
