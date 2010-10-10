<?php
/////////////////////////////////////////////////////////////////////////////////////////////////////
// Include the config files required
/////////////////////////////////////////////////////////////////////////////////////////////////////
include_once(dirname(__FILE__).'../../../config/config.inc.php');
include_once(dirname(__FILE__).'../../../init.php');
include_once(dirname(__FILE__).'../../../classes/AdminTab.php');


if(isset($_POST['export'])) {
				
			$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` WHERE position !=0 ORDER BY position ASC";
			$field_list = Db::getInstance()->ExecuteS($sql);

			foreach($field_list as $field => $value){
					$export_fields[$value['database_name']] = array('name' => $value['field_name'], 'category' => $value['category']);
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
							case "images":
								$inc_images = true;
							break;
							case "id_product":
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
			$lang = $_REQUEST['lang'];
			$sql='SELECT p.`id_product`, '.implode(', ', $fields).'
			FROM '._DB_PREFIX_.'product as p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product`)
			WHERE pl.`id_lang`=' . $lang . ' GROUP BY p.`id_product`
			';
			
			$delimiter = $_REQUEST['delimiter'];
			$exportlist = Db::getInstance()->ExecuteS($sql);
	
			$fileName = 'products_stream.csv';
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header('Content-Description: File Transfer');
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename={$fileName}");
			header("Expires: 0");
			header("Pragma: public");

			$f = @fopen( 'php://output', 'w' );
			
			fwrite($f, implode($delimiter, $titles) . "\r\n");
			foreach($exportlist AS $export) {
				$product = new Product($export['id_product'], true, $lang);
				$tags = array();
				$accessories = array();
				$export_final = array();
				$imagelinks = array();
				$cats = array();
				
				if(isset($export['id_category_default'])) {
					$categories = $product->getIndexedCategories($export['id_product']);
					foreach($categories as $cat) {
						$category = new Category($cat['id_category'], $lang);
						$cats[] = $category->name;
					}
					$export['id_category_default'] = implode(",", $cats);
				}
				
				if($inc_images) {
					$link = new Link();
					$images = $product->getImages($export['id_product']);
					foreach($images as $image) {
						$imagelinks[] = "http://" . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').$link->getImageLink($product->link_rewrite, $product->id .'-'. $image['id_image']);
					}
					$export['images'] = implode(",", $imagelinks);
				}
				
				if(isset($export['id_manufacturer'])) {
					$export['id_manufacturer'] = $product->manufacturer_name;
				}
				
				if(isset($export['meta_description'])) {
					$export['meta_description'] = $product->meta_description;
				}
				
				if(isset($export['meta_title'])) {
					$export['meta_title'] = $product->meta_title;
				}
				
				if(isset($export['meta_keywords'])) {
					$export['meta_keywords'] = $product->meta_keywords;
				}
				if(isset($export['id_supplier'])) {
					$export['id_supplier'] = $product->supplier_name;
				}
				
				if(isset($export['id_tax'])) {
					$export['id_tax'] = $product->tax_rate;
				}
				
				if(isset($inc_tags)) 
				{
					$export['tags'] = $product->getTags(1);
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
				
				if($_REQUEST['wcurrency'] == 1) {
				$params['currency'] = Tools::setCurrency();
					if(isset($export['price'])) {
						$params['price'] = $product->price;
						$export['price'] = $product->displayWtPriceWithCurrency($params, $smarty);
					}
				
					if(isset($export['wholesale_price'])) {
						$params['price'] = $product->wholesale_price;
						$export['wholesale_price'] = $product->displayWtPriceWithCurrency($params, $smarty);
					}
				}
				foreach($export_fields as $field => $value) {
						$export_final[$field] = iconv("UTF-8", "cp1250//TRANSLIT", $export[$field]);
				}
					
				fputcsv($f, $export_final, $delimiter, '"');
				
			}
			fclose($f);
}


if(isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	switch($action) {
		case "updateRecordsListings":
			$export_id_order = $_POST['export'];
			$listingCounter = 1;
			$array = array();
			foreach($export_id_order as $key => $id) {
				$array['position'] = intval($listingCounter);
				$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array,'UPDATE', 'id=' . intval($id));
    			if(!$query) {
					echo false;
				}
        		$listingCounter++;
    		}
    		$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` WHERE position != 0 ORDER BY position ASC";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "clearRecordsListings":
			$export_id_order = $_POST['export'];
			$listingCounter = 1;
			$array = array();
			foreach($export_id_order as $key => $id) {
				$array['position'] = 0;
				$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array,'UPDATE', 'id=' . intval($id));
    			if(!$query) {
					echo false;
				}
        		$listingCounter++;
    		}
    		$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` WHERE position != 0 ORDER BY position ASC";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "clearposition":
			$id = substr($_REQUEST['id'], (strpos($_REQUEST['id'],'_') - strlen($_REQUEST['id']) + 1));
			$array = array('position' => 0);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array,'UPDATE', 'id=' . intval($id));
			if(!$query) {
				echo false;
			}
			echo true;
		break;
		case "addposition":
			$id = substr($_REQUEST['id'], (strpos($_REQUEST['id'],'_') - strlen($_REQUEST['id']) + 1));
			$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` WHERE position != 0 ORDER BY position DESC LIMIT 1";
			$field_list = Db::getInstance()->ExecuteS($sql);
			$lastplace = intval($field_list[0]['position']) + 1;
			$array = array('position' => $lastplace);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array,'UPDATE', 'id=' . intval($id));
			if(!$query) {
				echo false;
			}
			echo true;
		break;
		case "loadSet":
			$array = array('position' => 0);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array, 'UPDATE', 'position != 0');
			$setid = $_REQUEST['setid'];
			$sql="SELECT * FROM `"._DB_PREFIX_."export_set` WHERE id = " . $setid;
			$field_list = Db::getInstance()->ExecuteS($sql);
			$positions = unserialize($field_list[0]['set_values']);
			$listingCounter = 1;
			$array = array();
			foreach($positions as $key => $id) {
				$array['position'] = intval($listingCounter);
				$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array,'UPDATE', 'id=' . intval($id));
    			if(!$query) {
					echo false;
				}
        		$listingCounter++;
    		}
    		
    		$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` WHERE position != 0 ORDER BY position ASC";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "getSets":
			$sql="SELECT id, set_name FROM `"._DB_PREFIX_."export_set`";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "saveSet":
			$set_values = serialize($_POST['export']);
			$set_name = $_REQUEST['name'];
			$array = array('set_name' => $set_name, 'set_values' => $set_values);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_set", $array, 'INSERT');
			$id = Db::getInstance()->Insert_ID();
			if(!$query) {
				echo false;
			}
			echo $id;
		break;
		case "selectAll":
			$query = Db::getInstance()->Execute("UPDATE `"._DB_PREFIX_."export_fields` SET position=id");
    		$sql="SELECT * FROM `"._DB_PREFIX_."export_fields` ORDER BY position ASC";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "clearSelected":
			$array = array('position' => 0);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array, 'UPDATE', 'position != 0');
			
			if(!$query) {
				echo false;
			}
			$sql="SELECT * FROM `"._DB_PREFIX_."export_fields`";
			$field_list = Db::getInstance()->ExecuteS($sql);
			echo json_encode($field_list);
		break;
		case "deleteSet":
			$setid = $_REQUEST['setid'];
			$query = Db::getInstance()->delete(_DB_PREFIX_."export_set", 'id=' + $setid, '1');
			if(!$query) {
				echo false;
			}
			echo $setid;
		break;
		default:
		break;
	}	
}
?>