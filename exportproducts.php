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
	public function __construct()
	{
		$this->name = 'exportproducts';
		$this->tab = 'administration';
		$this->version = '2.0';
		$this->displayName = 'Export Products';
		$this->author = 'Lee Mark Wood - leemarkwood.com';
		$this->description = $this->l('A module to export all products to csv/xls');

		/* The parent construct is required for translations */
		parent::__construct();
		
	}

	public function install()
	{
		$this->installController('AdminExportProducts', 'Export Products');
		return parent::install();

	}

	private function installController($controllerName, $name) {
		$tab_admin_order_id = Tab::getIdFromClassName('AdminProducts');
		$tab_controller_main = new Tab();
		$tab_controller_main->active = 0;
		$tab_controller_main->class_name = $controllerName;
		foreach (Language::getLanguages() as $language)
			$tab_controller_main->name[$language['id_lang']] = $name;
		$tab_controller_main->id_parent = $tab_admin_order_id;
		$tab_controller_main->module = $this->name;
		$tab_controller_main->add();
		$tab_controller_main->move(Tab::getNewLastPosition(0));
	}
	
	public function uninstall()
	{
		$this->uninstallController('AdminExportProducts');
		return parent::uninstall();
	}
	
	public function uninstallController($controllerName) {
		$tab_controller_main_id = TabCore::getIdFromClassName($controllerName);
		$tab_controller_main = new Tab($tab_controller_main_id);
		$tab_controller_main->delete();
	}


	public function getContent()
	{

	}

	public function displayForm()
	{
		global $smarty, $cookie;
		$smarty->assign('base_dir', __PS_BASE_URI__);
		$smarty->assign('currentIndex', $_SERVER['REQUEST_URI']);
		return $this->display(__FILE__,'exportproducts.tpl');
	}

}
