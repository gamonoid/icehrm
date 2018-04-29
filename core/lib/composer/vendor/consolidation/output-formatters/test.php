<?php

use Consolidation\OutputFormatters\Transformations\WordWrapper;

include 'vendor/autoload.php';

$wrapper = new WordWrapper(78);

$data = [
    'name' => ['Name', ':', 'Rex', ],
    'species' => ['Species', ':', 'dog', ],
    'food' => ['Food', ':', 'kibble', ],
    'legs' => ['Legs', ':', '4', ],
    'description' => ['Description', ':', 'Rex is a very good dog, Brett. He likes kibble, and has four legs.', ],
];

$result = $wrapper->wrap($data);

var_export($result);

