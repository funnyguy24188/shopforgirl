<?php
require_once 'AbstractSPGPrinter.php';
require_once 'class-spg-barcode-admin-print-template.php';


class SPGPrinterBarcode extends AbstractSPGPrinter
{


    public function __construct($print_data)
    {
        $this->pdf_convert_path_name = 'tmp_pdf';
        parent::__construct($print_data);

    }


    public function print_data()
    {
        $pdf_file_name = '';
        if (!empty($this->print_data['items'])) {

            ob_start();
            SPGBarcodePrintTemplate::render($this->print_data);
            $html = ob_get_clean();
            $this->pdf_engine = new TCPDF('L', 'mm', array(45,50), true, 'UTF-8', false);
            $this->init_pdf_engine();
            $this->pdf_engine->SetAutoPageBreak(true, 0);
            $pdf_file_name = substr(uniqid(), 7) . '.pdf';
            $this->pdf_engine->writeHTML($html, true, false, true, false, '');
            $this->pdf_engine->Output($this->pdf_convert_path . DIRECTORY_SEPARATOR . $pdf_file_name, 'F');

        }
        return $pdf_file_name;
    }

}