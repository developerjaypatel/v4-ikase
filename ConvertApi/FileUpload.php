<?php

namespace ConvertApi;

class FileUpload
{
    function __construct($filePath, $fileName = null)
    {
        $this->filePath = $filePath;
        $this->_fileName = $fileName ?: pathinfo($filePath, PATHINFO_BASENAME);
    }

    function __toString()
    {
        try {
            // Note that the property needs to exist
            // on the class, or therefore the exception
            // will be thrown
            return (string)  $this->getFileID();
        } catch (Exception $exception) {
            // Optionally you can var_dump the error message to see why the exception is being thrown !
            var_dump($exception->getMessage());
            return '';
        }
       
    }

    function getFileID()
    {
        return $this->result()['FileId'];
    }

    function getFileExt()
    {
        return $this->result()['FileExt'];
    }

    private function result()
    {
        if (!isset($this->_result))
            $this->_result = ConvertApi::client()->upload($this->filePath, $this->_fileName);

        return $this->_result;
    }
}
