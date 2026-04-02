<?php

namespace App\Exports;

use App\QuoteCategory;
use App\QuoteLog;
use App\Reservation;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CategoryPassengerExport implements WithMultipleSheets
{
    use Exportable;

    protected $quote_id;
    protected $client_id;
    protected $lang;

    public function __construct($quote_id = null, $client_id = null, $lang = null)
    {
        $this->quote_id = $quote_id;
        $this->client_id = $client_id;
        $this->lang = $lang;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $category_id = '';
        $editing_quote = null;
        //Buscamos la cotizacion original
        $query_log_editing_quote = QuoteLog::where('quote_id', $this->quote_id)->where('type',
            'editing_quote')->orderBy('created_at', 'desc')->first(['object_id']);


        if ($query_log_editing_quote) {
            $editing_quote = $query_log_editing_quote->object_id;
            //Buscamos si la cotizacion original tiene un file asignado
            $reservation = Reservation::where('entity', 'Quote')
                ->where('object_id', $query_log_editing_quote->object_id)
                ->orderBy('id', 'desc')
                ->first(['id', 'file_code', 'type_class_id']);
            if ($reservation) {
                //Si lo tiene obtenemos la categoria que solo queremos cotizar
                if ($reservation->type_class_id) {
                    $category_id = $reservation->type_class_id;
                }
            }
        }

        if (!empty($category_id)) {
            $categories = QuoteCategory::where('quote_id', $this->quote_id)
                ->where('type_class_id', $category_id)
                ->with('type_class.translations')->get();
        } else {
            $categories = QuoteCategory::where('quote_id', $this->quote_id)
                ->with('type_class.translations')->get();
        }
        foreach ($categories as $category) {
            $sheets[] = new PassengersExport($this->quote_id, $category["id"],
                $category['type_class']["translations"][0]['value'], $this->client_id, $this->lang,
                $editing_quote);
        }

        return $sheets;
    }
}
