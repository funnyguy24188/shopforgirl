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
        $pdf_size = array('width' => 57, 'height' => 22);
        $pdf_file_name = '';
        if (!empty($this->print_data['items'])) {
            // calculate the height of the document pdf
            /*$count = 0;
            foreach ($this->print_data['items'] as $product_id => $bc)
            {
                $count += (int) $bc[1];
            }
            $height = 106 + ($count * 27);
            $pdf_size['height'] = 45;*/
            ob_start();
            SPGBarcodePrintTemplate::render($this->print_data);
            $html = ob_get_clean();
            $this->pdf_engine = new TCPDF('p', 'pt', array(700,155), true, 'UTF-8', false);
            $this->init_pdf_engine();

            $pdf_file_name = substr(uniqid(), 7) . '.pdf';
            $this->pdf_engine->writeHTML($html, true, 0, true, true);
            $this->pdf_engine->lastPage();
            $this->pdf_engine->Output($this->pdf_convert_path . DIRECTORY_SEPARATOR . $pdf_file_name, 'F');

        }
        return $pdf_file_name;
    }

}