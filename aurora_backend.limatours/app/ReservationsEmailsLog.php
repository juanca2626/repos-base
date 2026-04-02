<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationsEmailsLog extends Model
{
    /*
     * Column: email_type
     */
    const EMAIL_TYPE_CONFIRMATION = 'confirmation';
    const EMAIL_TYPE_CANCELLATION = 'cancellation';
    const EMAIL_TYPE_CANCELLATION_PARTIAL = 'cancellation_partial';
    /*
     * Column: email_to
     */
    const EMAIL_TO_CLIENT = 'client';
    const EMAIL_TO_HOTEL = 'hotel';
    const EMAIL_TO_EXECUTIVE = 'executive';
    const EMAIL_TO_KAM = 'kam';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

}
