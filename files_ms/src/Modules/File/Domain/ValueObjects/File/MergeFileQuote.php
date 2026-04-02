<?php

namespace Src\Modules\File\Domain\ValueObjects\File;
 
use Src\Modules\File\Domain\Model\FileMergeQuoteHotel; 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;
use Carbon\Carbon;
use Src\Modules\File\Domain\Model\FileMergeQuoteFlight;
use Src\Modules\File\Domain\Model\FileMergeQuoteService;

final class MergeFileQuote extends ValueObjectArray
{
    public readonly array $results;

    public function __construct(array $file, array $quote_aurora)
    {
        parent::__construct($quote_aurora);    
        $this->results = $this->parser($file, $quote_aurora);
    }

    /**
     * @return array
     */
    public function parser($file, $quote_aurora): array
    {
 
        $results = [];

        $passengers = collect($quote_aurora['passengers'])->map(function ($passenger){ 
            $date_birth = $passenger->birthday;

            if($passenger->type == 'CHD'){
                $date_birth = Carbon::now()->subYears($passenger->age_child?->age)->format('Y-m-d');
            }
            return [
                'given_name' => $passenger->first_name ? $passenger->first_name : '',
                'surname' => $passenger->last_name ? $passenger->last_name: '',
                'type' => $passenger->type,
                'gender' => $passenger->gender, 
                'date_birth' => $date_birth ? $date_birth : ''
            ];
        })->toArray();
 
        
        $result_hotels = new FileMergeQuoteHotel($file, $quote_aurora, $passengers); 
        $result_services = new FileMergeQuoteService($file, $quote_aurora, $passengers); 
        $result_flights = new FileMergeQuoteFlight($file, $quote_aurora, $passengers); 

        $results = [
            'hotels' => $result_hotels->toArray(),
            'services' => $result_services->toArray(),
            'flights' => $result_flights->toArray()
        ];
 
        return $results;     
    }
     
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->results;
    }
}
