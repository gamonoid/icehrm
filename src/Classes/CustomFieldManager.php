<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:29 AM
 */

namespace Classes;

use Metadata\Common\Model\CustomFieldValue;

class CustomFieldManager
{
    public function addCustomField($type, $id, $name, $value)
    {
        $customFieldValue = new CustomFieldValue();
        $customFieldValue->Load(
            "type = ? and name = ? and object_id = ?",
            array($type, $name, $id)
        );

        if ($customFieldValue->object_id != $id) {
            $customFieldValue->name = $name;
            $customFieldValue->object_id = $id;
            $customFieldValue->type = $type;
            $customFieldValue->created = date("Y-md-d H:i:s");
        }

        $customFieldValue->value = $value;
        $customFieldValue->updated = date("Y-md-d H:i:s");
        $customFieldValue->Save();
    }

    public function getCustomFields($type, $id)
    {
        $customFieldValue = new CustomFieldValue();
        $list = $customFieldValue->Find(
            "type = ? and object_id = ?",
            array($type, $id)
        );

        return $list;
    }

    public function enrichObjectCustomFields($table, $object)
    {
        $customFieldsList = BaseService::getInstance()->getCustomFields($table);
        $customFieldsListOrdered = array();
        $customFields = array();
        foreach ($customFieldsList as $cf) {
            $customFields[$cf->name] = $cf;
        }

        $customFieldValues = $this->getCustomFields($table, $object->id);
        $object->customFields = array();
        foreach ($customFieldValues as $cf) {
            if (!isset($customFields[$cf->name])) {
                continue;
            }

            $type = $customFields[$cf->name]->field_type;
            $label = $customFields[$cf->name]->field_label;
            $order = $customFields[$cf->name]->display_order;
            $section = $customFields[$cf->name]->display_section;

            $customFieldsListOrdered[] = $order;

            if ($type == "text" || $type == "textarea") {
                $object->customFields[$label] = $cf->value;
            } elseif ($type == 'select' || $type == 'select2') {
                $options = $customFields[$cf->name]->field_options;
                if (empty($options)) {
                    continue;
                }

                $jsonOptions = json_decode($options);
                foreach ($jsonOptions as $option) {
                    if ($option->value == $cf->value) {
                        $object->customFields[$label] = $option->label;
                    }
                }
            } elseif ($type == 'select2multi') {
                $resArr = array();
                $options = $customFields[$cf->name]->field_options;
                if (empty($options) || empty($cf->value)) {
                    continue;
                }
                $jsonOptions = json_decode($options);
                $jsonOptionsKeys = array();
                foreach ($jsonOptions as $option) {
                    $jsonOptionsKeys[$option->value] = $option->label;
                }

                $valueList = json_decode($cf->value, true);
                foreach ($valueList as $val) {
                    if (!isset($jsonOptionsKeys[$val])) {
                        $resArr[] = $val;
                    } else {
                        $resArr[] = $jsonOptionsKeys[$val];
                    }
                }

                $object->customFields[$label]  = implode('<br/>', $resArr);
            } elseif ($type == "date") {
                if (!empty($cf->value)) {
                    $object->customFields[$label] = $cf->value;
                } else {
                    $object->customFields[$label] = date("F j, Y", strtotime($cf->value));
                }
            } elseif ($type == "datetime") {
                if (!empty($cf->value)) {
                    $object->customFields[$label] = $cf->value;
                } else {
                    $object->customFields[$label] = date("F j, Y, g:i a", strtotime($cf->value));
                }
            } elseif ($type == "time") {
                if (!empty($cf->value)) {
                    $object->customFields[$label] = $cf->value;
                } else {
                    $object->customFields[$label] = date("g:i a", strtotime($cf->value));
                }
            }

            $object->customFields[$label] = array($object->customFields[$label], $section);
        }
        array_multisort($customFieldsListOrdered, SORT_DESC, SORT_NUMERIC, $object->customFields);

        return $object;
    }

    public function syncMigrations()
    {
    }
}
