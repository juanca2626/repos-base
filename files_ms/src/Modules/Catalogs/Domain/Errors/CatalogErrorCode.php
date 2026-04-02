<?php

namespace Src\Modules\Catalogs\Domain\Errors;

class CatalogErrorCode
{
    public const SAVE_FAILED = 'CATALOG_SAVE_FAILED';
    public const DELETE_FAILED = 'CATALOG_DELETE_FAILED';
    public const PROCESS_FAILED = 'CATALOG_PROCESS_FAILED';
    public const EXECUTIVES_NOT_FOUND = 'CATALOG_EXECUTIVES_NOT_FOUND';
    public const AURORA_UNAVAILABLE = 'CATALOG_AURORA_UNAVAILABLE';
    public const AURORA_TIMEOUT = 'CATALOG_AURORA_TIMEOUT';
    public const GET_EXECUTIVES_FAILED = 'CATALOG_GET_EXECUTIVES_FAILED';    
}