<?php
namespace Consolidation\OutputFormatters\Transformations;

use Dflydev\DotAccessData\Data;

class UnstructuredDataFieldAccessor
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($fields)
    {
        $data = new Data($this->data);
        $result = new Data();
        foreach ($fields as $key => $label) {
            $item = $data->get($key);
            if (isset($item)) {
                if ($label == '.') {
                    if (!is_array($item)) {
                        return $item;
                    }
                    foreach ($item as $key => $value) {
                        $result->set($key, $value);
                    }
                } else {
                    $result->set($label, $data->get($key));
                }
            }
        }
        return $result->export();
    }
}
