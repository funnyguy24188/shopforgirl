<?php


class SPGPrinterConfig
{
    const LEFT_MARGIN = 1;
    const RIGHT_MARGIN = 12;
    const TOP_MARGIN = 0;
    const BOTTOM_MARGIN = 0;

}

abstract class AbstractSPGPrinter
{
    protected $printers = array();
    // for convert the data to pdf file for print
    protected $pdf_engine = null;
    // pdf convert path
    protected $pdf_convert_path_name = 'tmp_print';
    protected $pdf_convert_path = '';
    protected $print_data = '';

    public function __construct($print_data)
    {
        $this->printers = array();
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'];
        $full_path = $base_path . DIRECTORY_SEPARATOR . $this->pdf_convert_path_name;
        if (!is_dir($full_path)) {
            mkdir($full_path);
            chmod($full_path, 777);
        }
        $this->print_data = $print_data;
        $this->pdf_convert_path = $full_path;

    }

    public function get_full_path()
    {
        return $this->pdf_convert_path;
    }

    protected function init_pdf_engine()
    {
        $this->pdf_engine->SetCreator(PDF_CREATOR);
        $this->pdf_engine->SetAuthor('SHOPFORGIRL PRINTER');
        $this->pdf_engine->SetTitle('SHOPFORGIRL TITLE');
        $this->pdf_engine->SetSubject('SHOPFORGIRL');

        // set default monospaced font
        $this->pdf_engine->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // remove default header/footer
        $this->pdf_engine->setPrintHeader(false);
        $this->pdf_engine->setPrintFooter(false);

        // set margin
        $this->pdf_engine->SetMargins(SPGPrinterConfig::LEFT_MARGIN, SPGPrinterConfig::TOP_MARGIN, SPGPrinterConfig::RIGHT_MARGIN, true);
        $this->pdf_engine->SetHeaderMargin(SPGPrinterConfig::TOP_MARGIN);
        $this->pdf_engine->SetFooterMargin(SPGPrinterConfig::BOTTOM_MARGIN);

        // set font
        $this->pdf_engine->SetFont('dejavusans', '', 8);


        // add a page
        $this->pdf_engine->AddPage();
    }

    abstract protected function print_data();

}