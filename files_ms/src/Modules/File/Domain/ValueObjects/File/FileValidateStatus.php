<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Exceptions\FileStatusException; 
use Src\Modules\File\Domain\Model\File; 
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class FileValidateStatus extends StringOrNullableValueObject
{
    public readonly string $file_status;
    public array $status = [
        'OK' => 'Abierto',
        'XL' => 'Anulado',
        'BL' => 'Bloqueado',
        'CE' => 'Cerrado',
        'PF' => 'Por Facturar',
    ];

    public function __construct(string | null $file_status)
    {
        // parent::__construct($file_status);    
        return $this->parser($file_status);
    }

    /**
     * @return array
     */
    public function parser($file_status): string | null
    {        
        if($file_status !== null and strtoupper($file_status) != 'OK' ){ 
            throw new FileStatusException("The file is ".$this->status[strtoupper($file_status)]." cannot be modified.");     
        }

        return $file_status;
    }
 
    /**
     * @return array
     */
    public function jsonSerialize(): string | null
    {
        return $this->file_status;
    }
}
