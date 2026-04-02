<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailServiceController extends Controller
{
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    protected function validateArrayJSON($json) {
        // Si es string, intentamos decodificar como JSON
        if (is_string($json)) {
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return $json;
    }

    private function normalizeRecipients($recipients) {
        $recipients = $this->validateArrayJSON($recipients);

        if (empty($recipients)) {
            return [];
        }

        if (!is_array($recipients)) {
            $recipients = explode(",", $recipients);
        }

        // Si los índices son cadenas (array asociativo), tomar las claves
        if (is_array($recipients) && empty(@$recipients[0])) {
            $recipients = array_keys($recipients);
        }

        return array_filter($recipients); // Eliminar valores vacíos o nulos
    }

    private function formatRecipients($recipients) {
        return array_values(array_filter(array_map(function ($recipient) {
            if ($this->validateEmail($recipient)) {
                return ['email' => $recipient, 'name' => $recipient]; // Ajusta el 'name' si es necesario
            }
            return null; // Ignorar valores no válidos
        }, $recipients)));
    }

    public function handle(Request $request)
    {
        try
        {
            $content = $request->__get('content');
            $subject = $request->__get('subject');
            $attachments = $request->__get('attachments') ?? [];
            $recipients = $request->__get('to');
            $recipients_cc = $request->__get('cc');
            $recipients_bcc = $request->__get('bcc');
            $reply_to = $request->has('reply_to') ? $this->toArray($request->get('reply_to')) : '';
            $reply_to = $this->validateArrayJSON($reply_to);

            $recipients = $this->normalizeRecipients($recipients);
            $recipients_cc = $this->normalizeRecipients($recipients_cc);
            $recipients_bcc = $this->normalizeRecipients($recipients_bcc);

            // Formatear los arrays para que tengan 'address' y 'name'
            $recipients = $this->formatRecipients($recipients);
            $recipients_cc = $this->formatRecipients($recipients_cc);
            $recipients_bcc = $this->formatRecipients($recipients_bcc);

            // Enviar el correo
            $mail = Mail::to($recipients);

            if (!empty($recipients_cc)) {
                $mail->cc($recipients_cc);
            }

            if (!empty($recipients_bcc)) {
                $mail->bcc($recipients_bcc);
            }

            if(empty($reply_to) AND isset($recipients[0]))
            {
                $recipient = $recipients[0]; $reply_to = [$recipient, 'Aurora'];
            }

            $mail->send(new \App\Mail\MailingGlobal($reply_to, $subject, $content, $attachments));

            return response()->json([
                'success' => true,
            ]);
        }
        catch(\Exception $ex)
        {
            return $this->throwError($ex);
        }
    }
}
