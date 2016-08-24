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
            $html = utf8_encode($html);
            $this->pdf_engine = new TCPDF('L', 'pt', array(155,130), true, 'UTF-8', false);
            $this->pdf_engine->SetAutoPageBreak(TRUE, 0);
            $this->init_pdf_engine();

            $pdf_file_name = substr(uniqid(), 7) . '.pdf';
            $this->pdf_engine->writeHTML($html, true, false, true, false, '');
            $this->pdf_engine->lastPage();
            $this->pdf_engine->Output($this->pdf_convert_path . DIRECTORY_SEPARATOR . $pdf_file_name, 'F');

        }
        return $pdf_file_name;
    }

}