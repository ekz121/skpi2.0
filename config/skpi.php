<?php

return [
    'minimum_submissions' => 1,
    'demo_mode' => env('SKPI_DEMO_MODE', false),
    'signatory_name' => env('SKPI_SIGNATORY_NAME'),
    'signatory_nidn' => env('SKPI_SIGNATORY_NIDN'),
    'institution_accreditation' => env('SKPI_INSTITUTION_ACCREDITATION'),
    'template_path' => resource_path('templates/skpi-template.docx'),
    'office_binary' => env('SKPI_OFFICE_BINARY'),
];
