<?php

namespace App\Http\Traits;

use App\QuoteHistoryLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait QuoteHistories
{
    /**
     * @param $quote_id
     * @param $histories / array
     */
    private function store_history_logs( $quote_id, $histories ){

        foreach ( $histories as $history ){
            $new_history_log = new QuoteHistoryLog();
            $new_history_log->quote_id = $quote_id;
            $new_history_log->user_id = Auth::user()->id;
            $new_history_log->type = $history['type'];
            $new_history_log->slug = $history['slug'];
            $new_history_log->description = $history['description'];
            $new_history_log->previous_data = $history['previous_data'];
            $new_history_log->current_data = $history['current_data'];
            $new_history_log->save();
        }

        return true;
    }
    private function move_history_logs($quote_id_original, $quote_id){

        DB::table('quote_history_logs')->where('quote_id', $quote_id_original)
            ->update([
                'quote_id' => $quote_id
            ]);

        return true;
    }

}
