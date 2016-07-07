<?php

require_once "GoogleCloudPrint.php";

class GoogleCloudPrintCustomize extends GoogleCloudPrint
{
    public function __construct()
    {
        parent::__construct();



    }

    public function sendPrintToPrinter($printerid,$printjobtitle,$filepath,$contenttype) {

        // Check if we have auth token
        if(empty($this->authtoken)) {
            // We don't have auth token so throw exception
            throw new Exception("Please first login to Google by calling loginToGoogle function");
        }
        // Check if prtinter id is passed
        if(empty($printerid)) {
            // Printer id is not there so throw exception
            throw new Exception("Please provide printer ID");
        }
        // Open the file which needs to be print
        /*$handle = fopen($filepath, "rb");
        if(!$handle)
        {
            // Can't locate file so throw exception
            throw new Exception("Could not read the file. Please check file path.");
        }
        // Read file content
        $contents = fread($handle, filesize($filepath));
        fclose($handle);*/

        // Prepare post fields for sending print
        $post_fields = array(

            'printerid' => $printerid,
            'title' => $printjobtitle,
            'contentTransferEncoding' => 'base64',
            'content' => base64_encode('BECAUSE I LOVE YOU'), // encode file content as base64
            'contentType' => $contenttype
        );
        // Prepare authorization headers
        $authheaders = array(
            "Authorization: Bearer " . $this->authtoken
        );

        // Make http call for sending print Job
        $this->httpRequest->setUrl(self::PRINT_URL);
        $this->httpRequest->setPostData($post_fields);
        $this->httpRequest->setHeaders($authheaders);
        $this->httpRequest->send();
        $response = json_decode($this->httpRequest->getResponse());

        // Has document been successfully sent?
        if($response->success=="1") {

            return array('status' =>true,'errorcode' =>'','errormessage'=>"", 'id' => $response->job->id);
        }
        else {

            return array('status' =>false,'errorcode' =>$response->errorCode,'errormessage'=>$response->message);
        }
    }

}