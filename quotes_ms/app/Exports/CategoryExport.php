<?php

namespace App\Exports;

use App\Models\QuoteCategory;
use App\Models\QuoteLog;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CategoryExport implements WithMultipleSheets
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

        $query_log_editing_quote = QuoteLog::where('quote_id', $this->quote_id)->where(
            'type',
            'editing_quote'
        )->orderBy('created_at', 'desc')->first(['object_id']);
        $editing_quote = null;
        if ($query_log_editing_quote) {
            $editing_quote = $query_log_editing_quote->object_id;
        }


        $categories = QuoteCategory::where('quote_id', $this->quote_id)->with('type_class.translations')->get();

        foreach ($categories as $category) {
            $sheets[] = new RangesExport(
                $this->quote_id,
                $category["id"],
                $category['type_class']["translations"][0]['value'],
                $this->client_id,
                $this->lang,
                $editing_quote
            );
        }

        return $sheets;
    }
}
