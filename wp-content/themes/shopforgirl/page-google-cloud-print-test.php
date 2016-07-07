<?php
require_once 'lib/google-cloud-print/GoogleCloudPrint.php';
session_start();
// Create object
$gcp = new GoogleCloudPrint();
$gcp->setAuthToken($_SESSION['accessToken']);

$printers = $gcp->getPrinters();
print_r($printers);die;

$printerid = "";
if(count($printers)==0) {

    echo "Could not get printers";
    exit;
}
else {

    $printerid = $printers[0]['id']; // Pass id of any printer to be used for print
    // Send document to the printer
    $resarray = $gcp->sendPrintToPrinter($printerid, "Printing Doc using Google Cloud Printing", "./pdf.pdf", "application/pdf");

    if($resarray['status']==true) {

        echo "Document has been sent to printer and should print shortly.";
    }
    else {
        echo "An error occured while printing the doc. Error code:".$resarray['errorcode']." Message:".$resarray['errormessage'];
    }
}

