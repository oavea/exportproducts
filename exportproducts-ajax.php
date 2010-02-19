<?php
/////////////////////////////////////////////////////////////////////////////////////////////////////
// Include the config files required
/////////////////////////////////////////////////////////////////////////////////////////////////////
include_once(dirname(__FILE__).'../../../config/config.inc.php');
include_once(dirname(__FILE__).'../../../init.php');
include_once(dirname(__FILE__).'../../../classes/AdminTab.php');

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
		case "clearSelected":
			$array = array('position' => 0);
			$query = Db::getInstance()->autoExecute(_DB_PREFIX_."export_fields", $array, 'UPDATE', 'position != 0');
			
			if(!$query) {
				echo false;
			}
			echo true;
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