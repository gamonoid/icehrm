<?php
namespace Classes\Data\Query;

class FieldMapping implements \JsonSerializable
{
    protected $class;
    protected $idField;
    protected $nameField;

    /**
     * FieldMapping constructor.
     * @param $class
     * @param $idField
     * @param $nameField
     */
    public function __construct($class, $idField, $nameField)
    {
        $this->class = $class;
        $this->idField = $idField;
        $this->nameField = $nameField;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            $this->class,
            $this->idField,
            $this->nameField,
        ];
    }
}
