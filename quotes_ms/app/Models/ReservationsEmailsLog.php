<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationsEmailsLog extends Model
{
    /*
     * Column: email_type
     */
    public const EMAIL_TYPE_CONFIRMATION = 'confirmation';

    public const EMAIL_TYPE_CANCELLATION = 'cancellation';

    public const EMAIL_TYPE_CANCELLATION_PARTIAL = 'cancellation_partial';

    /*
     * Column: email_to
     */
    public const EMAIL_TO_CLIENT = 'client';

    public const EMAIL_TO_HOTEL = 'hotel';

    public const EMAIL_TO_EXECUTIVE = 'executive';

    public const EMAIL_TO_KAM = 'kam';

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
