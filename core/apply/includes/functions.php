<?php
/**
 * Helpers for the public application form's custom fields.
 *
 * A job definition declares extra fields via its `additional_fields` JSON, e.g.
 *   [{"field_name":"Portfolio URL","type":"Text Field","data":"","position":1}]
 * extract_additional_fields() sorts them; create_field() renders each into the
 * modern apply-form markup. Every field posts as `Custom_<Field Name>` (spaces
 * become underscores) so the backend picks it up — keep that naming stable.
 *
 * Supported types: "Text Field", "Text Area", "Select", "Information".
 */

function extract_additional_fields($fieldsStr) {
    if (empty($fieldsStr)) {
        return [];
    }

    $fields = json_decode($fieldsStr, true);
    if (empty($fields) || !is_array($fields)) {
        return [];
    }

    usort($fields, function ($a, $b) {
        return (int)(isset($a['position']) ? $a['position'] : 0)
             - (int)(isset($b['position']) ? $b['position'] : 0);
    });

    return $fields;
}

function create_field($field) {
    if (empty($field['field_name']) || empty($field['type'])) {
        return '';
    }

    $label = htmlspecialchars($field['field_name'], ENT_QUOTES, 'UTF-8');
    $name  = 'Custom_'.str_replace(' ', '_', $field['field_name']);
    $name  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $data  = isset($field['data']) ? $field['data'] : '';
    $required = !empty($field['required']) ? ' required' : '';
    $reqMark  = !empty($field['required']) ? ' <span class="req">*</span>' : '';

    switch ($field['type']) {
        case 'Text Field':
            return <<<FIELD
<div class="field col-2">
    <label for="{$name}">{$label}{$reqMark}</label>
    <input id="{$name}" name="{$name}" type="text" class="form-control" placeholder=""{$required}>
</div>
FIELD;

        case 'Text Area':
            return <<<FIELD
<div class="field col-2">
    <label for="{$name}">{$label}{$reqMark}</label>
    <textarea id="{$name}" name="{$name}" class="form-control" placeholder=""{$required}></textarea>
</div>
FIELD;

        case 'Select':
            $optionsStr = '';
            foreach (explode("\n", (string)$data) as $option) {
                $option = trim($option);
                if ($option === '') {
                    continue;
                }
                $opt = htmlspecialchars($option, ENT_QUOTES, 'UTF-8');
                $optionsStr .= "<option value=\"{$opt}\">{$opt}</option>";
            }
            return <<<FIELD
<div class="field col-2">
    <label for="{$name}">{$label}{$reqMark}</label>
    <select id="{$name}" name="{$name}" class="form-control"{$required}>
        <option value="">Select…</option>
        {$optionsStr}
    </select>
</div>
FIELD;

        case 'Information':
            $body = nl2br(htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8'));
            return <<<FIELD
<div class="field-info">
    <div class="label">{$label}</div>
    <div class="body">{$body}</div>
</div>
FIELD;
    }

    return '';
}
