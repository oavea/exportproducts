<?php
include(rtrim(_PS_MODULE_DIR_, '/') . 'exportproducts/lib/PHPExcel.php');

class AdminExportProductsController extends AdminController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function initContent()
	{
		//$this->context = Context::getContext();
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Lee Wood")
							 ->setLastModifiedBy("Lee Wood")
							 ->setTitle("Product Export")
							 ->setSubject("Export")
							 ->setDescription("Export of entire product catalog.")
							 ->setKeywords("export products")
							 ->setCategory("export products");

		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle('Simple');


		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="01simple.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
				parent::initContent();
	}
}