<?php
class AdminProductsController extends AdminProductsControllerCore
{
	public function initToolbar() {
		$this->toolbar_btn['Export'] = array(
			'href' => $this->context->link->getAdminLink('AdminExportProducts', true),
			'desc' => $this->l('Export Products')
		);
		parent::initToolbar();
	}
	
}