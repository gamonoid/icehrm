<?php
function extract_additional_fields($fieldsStr) {
	if (empty ($fieldsStr)) {
		return [];
	}

	$fields = json_decode($fieldsStr, true);
	if (empty($fields)) {
		return [];
	}

	usort($fields, function ($a, $b) {
		return (int)$a['position'] - (int)$b['position'];
	});

	return $fields;
}

function create_field($field) {
	if ($field['type'] === 'Text Field') {
		$fieldStr = <<<'FIELD'
<div class="col-12 mb-7">
	<label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">__label__</label>
	<input id="__name__" name="__name__" type="text" class="form-control" placeholder="">
</div>
FIELD;
	} elseif ($field['type'] === 'Text Area') {
		$fieldStr = <<<'FIELD'
<div class="col-lg-12 mb-7">
	<label for="message" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">__label__</label>
	<textarea id="__name__" name="__name__" placeholder="" class="form-control h-px-144"></textarea>
</div>
FIELD;
	} elseif ($field['type'] === 'Select') {
		$options = explode("\n", $field['data']);
		$optionsStr = '';
		foreach ($options as $option) {
			$optionsStr .= "<option value=".$option.">".$option."</option>";
		}
		$fieldStr = <<<'FIELD'
<div class="col-12 mb-7">
	<label for="" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">__label__</label>
	<select id="__name__" name="__name__" type="select" class="form-control">
		__options__
	</select>
</div>
FIELD;
		$fieldStr = str_replace('__options__', $optionsStr, $fieldStr);
	} elseif ($field['type'] === 'Information') {
		$fieldStr = <<<'FIELD'
<div class="col-lg-12 mb-7">
	<label for="message" class="font-size-4 font-weight-semibold text-black-2 mb-5 line-height-reset">__label__</label>
	<p>__text__</p>
</div>
FIELD;
		$fieldStr = str_replace('__text__', $field['data'], $fieldStr);
	}

	$fieldStr = str_replace('__label__', $field['field_name'], $fieldStr);
	$fieldStr = str_replace('__name__', 'Custom_'.str_replace(' ','_',$field['field_name']), $fieldStr);

	return $fieldStr;
}
