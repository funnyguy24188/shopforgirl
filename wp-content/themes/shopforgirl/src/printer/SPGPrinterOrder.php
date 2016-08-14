<?php

if (in_array('spg-barcode/spg-barcode.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    require_once ABSPATH  . 'wp-content/plugins/spg-barcode/lib/TCPDF/tcpdf.php';
    require_once ABSPATH  . 'wp-content/plugins/spg-barcode/lib/AbstractSPGPrinter.php';
    require_once __DIR__ . '/../template/order_tpl.php';

    
    class SPGPrinterOrder extends AbstractSPGPrinter
    {

        public function __construct($print_data)
        {
            $this->pdf_convert_path_name = 'order_tmp';
            parent::__construct($print_data);
        }


        /**
         * Print the data
         * @param $data
         * @return string
         */

        public function print_data()
        {
            $pdf_size = array('width' => '57', 'height' => '100');
            $pdf_file_name = '';
            if (!empty($this->print_data['items'])) {
                // calculate the height of the document pdf
                $count = count($this->print_data['items']);
                $height = 150 + ($count * 11);
                $pdf_size['height'] = $height;
                ob_start();
                OrderTemplate::render($this->print_data);
                $order_html = ob_get_clean();

                $this->pdf_engine = new TCPDF('P', 'mm', array_values($pdf_size), true, 'UTF-8', false);
                $this->init_pdf_engine();

                $pdf_file_name = substr(uniqid(), 7) . '.pdf';
                $this->pdf_engine->writeHTML($order_html, true, 0, true, true);
                $this->pdf_engine->lastPage();
                $this->pdf_engine->Output($this->pdf_convert_path . DIRECTORY_SEPARATOR . $pdf_file_name, 'F');

            }
            return $pdf_file_name;
        }


    }
}