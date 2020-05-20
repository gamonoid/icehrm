<?php
namespace Classes\Upload;

class TempFile
{
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function save($path)
    {
        if (!move_uploaded_file($this->data['file']['tmp_name'], $path)) {
            return false;
        }
        return true;
    }

    public function getName()
    {
        return $this->data['file']['name'];
    }

    public function getSize()
    {
        return $this->data['file']['size'];
    }
}
