<?php

namespace Src\Modules\FileV2\Application\Validators;

class FileValidator
{
    public function validate(array $data): void
    {
        if (empty($data['file_code'])) {
            throw new \Exception('file_code es obligatorio');
        } 
    }
}