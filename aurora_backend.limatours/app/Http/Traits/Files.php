<?php

namespace App\Http\Traits;

use App\ChannelHotel;
use App\Doctype;
use App\File;
use App\FileAccommodation;
use App\Hotel;
use App\Http\Stella\StellaService;
use App\Client;
use App\Reservation;
use App\ReservationPassenger;
use App\FileService;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

trait Files
{
    private function import_file($nroref)
    {
        $stellaService = new StellaService;

        $success = false;
        $message = 'File already exist';

        $file = File::where('file_number', $nroref)->first();

        if (!$file) {
            $message = 'File not found';

            $file_ifx = $stellaService->find_file($nroref);

            //        return $file_ifx;

            if (count($file_ifx) > 0) {
                $file_ifx = $file_ifx[0];

                $client = Client::where('code', $file_ifx->codcli)->first();

                if ($client) {

                    $executive_code_ = trim($file_ifx->codven);

                    $reservation = Reservation::where('file_code', $nroref)->first();
                    if (!$reservation) {

                        $executive = User::where('code', $executive_code_)->first();

                        if (Auth::check()) {
                            $user_id = Auth::user()->id;
                        } else {
                            $user_ = User::where('code', 'GUEST')->first();
                            $user_id = ($user_) ? $user_->id : 1;
                        }

                        $reservation = new Reservation();
                        $reservation->booking_code = $nroref;
                        $reservation->file_code = $nroref;
                        $reservation->status = 1;
                        $reservation->reservator_type = "excecutive";
                        $reservation->entity = "Stella";
                        $reservation->object_id = $nroref;
                        $reservation->client_id = $client->id;
                        $reservation->executive_id = ($executive) ? $executive->id : 1;
                        $reservation->customer_name = ($file_ifx->descri && $file_ifx->descri!=null && $file_ifx->descri!='' ) ? $file_ifx->descri : "-";
                        $reservation->given_name = "";
                        $reservation->surname = "";
                        $reservation->customer_country = "";
                        $reservation->total_hotels_taxes = 0;
                        $reservation->total_hotels_services = 0;
                        $reservation->total_hotels_discounts = 0;
                        $reservation->total_hotels_subs = 0;
                        $reservation->total_hotels = 0;
                        $reservation->total_services = 0;
                        $reservation->total_services_subs = 0;
                        $reservation->total_services_taxes = 0;
                        $reservation->total_discounts = 0;
                        $reservation->subtotal_amount = 0;
                        $reservation->total_tax = 0;
                        $reservation->total_amount = 0;
                        $reservation->create_user_id = $user_id;
                        $reservation->executive_name = ($executive) ? $executive->name : "Admin";
                        $reservation->client_code = $file_ifx->codcli;
                        $reservation->date_init = $file_ifx->diain;
                        $reservation->status_cron_job_reservation_stella = 9;
                        $reservation->status_cron_job_send_email = 9;
                        $reservation->status_cron_job_error = 0;
                        $reservation->status_cron_job_order_stella = 0;
                        $reservation->save();
                    }

                    $new_file = new File();
                    $new_file->client_id = $client->id;
                    $new_file->reservation_id = $reservation->id;
                    $new_file->order_number = $file_ifx->nroped;
                    $new_file->file_number = $nroref;
                    $new_file->reservation_number = $file_ifx->nrores;
                    $new_file->budget_number = $file_ifx->nropre;
                    $new_file->created_at = $file_ifx->fecha;
                    $new_file->updated_at = $file_ifx->fecha;
                    $new_file->sector_code = $file_ifx->codsec;
                    $new_file->group = trim($file_ifx->grupo);
                    $new_file->sale_type = $file_ifx->concta;
                    $new_file->tariff = trim($file_ifx->tarifa);
                    $new_file->currency = $file_ifx->moncot;
                    $new_file->revision_stages = $file_ifx->succli;
                    $new_file->executive_code = $executive_code_;
                    $new_file->executive_code_sale = trim($file_ifx->codope);
                    $new_file->applicant = $file_ifx->solici;
                    $new_file->file_code_agency = $file_ifx->refext;
                    $new_file->description = $file_ifx->descri;
                    $new_file->lang = $file_ifx->idioma;
                    $new_file->date_in = $file_ifx->diain;
                    $new_file->date_out = $file_ifx->diaout;
                    $new_file->adults = $file_ifx->canadl;
                    $new_file->children = $file_ifx->canchd;
                    $new_file->infants = $file_ifx->caninf;
                    $new_file->use_invoice = $file_ifx->coniva;
                    $new_file->observation = $file_ifx->observ;
                    $new_file->total_paxs = $file_ifx->nropax;
                    $new_file->executive_code_process = trim($file_ifx->operad);
                    $new_file->have_quote = $file_ifx->cotiza;
                    $new_file->have_voucher = $file_ifx->vouche;
                    $new_file->have_ticket = $file_ifx->ticket;
                    $new_file->have_invoice = $file_ifx->factur;
                    $new_file->status = $file_ifx->status;
                    $new_file->promotion = $file_ifx->promos;
                    $new_file->save();

                    if ($client->general_markup === 0 || $client->general_markup === null) {
                        $client->general_markup = (int)(abs($file_ifx->piaced));
                        $client->save();
                    }
                    $success = true;
                    $message = "Guardado correctamente";

                } else {
                    $success = false;
                    $message = "Client not found";
                }
            }
        }

        return ["success" => $success, "message" => $message];
    }

    private function import_passengers($nroref, $reservation_id)
    {
        $success = false;
        $message = 'Passengers not founds';
        $stellaService = new StellaService;

        $key_cache = sprintf('passengers_ifx_%s', $nroref);

        $passengers_ifx = Cache::remember($key_cache, 60 * 60 * 24, function () use ($stellaService, $nroref) {
            $array = ['nroref' => $nroref];
            return $stellaService->search_paxs($array);
        });

//        return($passengers_ifx);

//        if( count( $passengers_ifx ) > 0 ){
//
//            foreach ( $passengers_ifx as $passenger_ifx ){

        if ($passengers_ifx->datpax && count($passengers_ifx->datpax) > 0) {

            foreach ($passengers_ifx->datpax as $passenger_ifx) {

                $doctype = Doctype::where('iso', $passenger_ifx->tipdoc)->first();
                $doctype_id = 5; // Otros
                $doctype_iso = "OTR"; // Otros
                if ($doctype) {
                    $doctype_id = $doctype->id;
                    $doctype_iso = $doctype->iso;
                }

                $new_passenger = new ReservationPassenger();
                $new_passenger->reservation_id = $reservation_id;
                $new_passenger->frequent = $passenger_ifx->nropax;
                $new_passenger->order_number = $passenger_ifx->nroord;
                $new_passenger->sequence_number = $passenger_ifx->nrosec;
                $new_passenger->document_type_id = $doctype_id;
                $new_passenger->doctype_iso = $doctype_iso;
                $new_passenger->document_number = $passenger_ifx->nrodoc;
                $new_passenger->name = $passenger_ifx->nombre;
                $new_passenger->surnames = "";
                $new_passenger->date_birth = Carbon::parse($passenger_ifx->fecnac);
                $new_passenger->type = ($passenger_ifx->tipo !== null) ? trim($passenger_ifx->tipo) : "ADL";
                $new_passenger->suggested_room_type = $passenger_ifx->tiphab;
                $new_passenger->genre = ($passenger_ifx->tipo == "M" || $passenger_ifx->tipo == "F")
                    ? trim($passenger_ifx->tipo) : "M";
                $new_passenger->email = $passenger_ifx->correo;
                $new_passenger->phone = $passenger_ifx->celula;
                $new_passenger->country_iso = $passenger_ifx->nacion;
                $new_passenger->city_iso = $passenger_ifx->ciunac;
                $new_passenger->dietary_restrictions = $passenger_ifx->resali;
                $new_passenger->medical_restrictions = $passenger_ifx->resmed;
                $new_passenger->notes = $passenger_ifx->observ;
                $new_passenger->save();
            }

            $file_ = File::where('file_number', $nroref)->first();
            $file_services = FileService::where('file_id', $file_->id)->get();
            $passengers = ReservationPassenger::where('reservation_id', $reservation_id)->get();
            if (count($passengers) > 0 && count($file_services) > 0) {
                $this->import_accommodations($file_->file_number, $file_services, $passengers);
            }

            $success = true;
            $message = "Guardado correctamente";
        }

        return ["success" => $success, "message" => $message];
    }

    private function import_services($nroref, $file_id, $nroites_not_in)
    {

        $stellaService = new StellaService;

        $success = false;
        $message = 'Services not founds';

        $services_ifx = $stellaService->search_file_services($nroref, 'all', true, $nroites_not_in, true);

//        return $services_ifx;

        if (count($services_ifx) > 0) {

            foreach ($services_ifx as $service_ifx) {

                $file_service = FileService::where('file_id', $file_id)
                    ->where('item_number', $service_ifx->nroite)->first();
                if (!$file_service) {
                    $file_service = new FileService();
                }
                $file_service->file_id = $file_id;
                $file_service->item_number = $service_ifx->nroite;
                $file_service->item_number_parent = $service_ifx->itepaq;
                $file_service->classification_iso = $service_ifx->clase;
                $file_service->code = $service_ifx->codsvs;
                $file_service->total_rooms = $service_ifx->cantid;
                $file_service->code_request_book = $service_ifx->preped;
                $file_service->code_request_invoice = $service_ifx->prefac;
                $file_service->code_request_voucher = $service_ifx->prevou;
                $file_service->status_ifx = $service_ifx->estado;
                $file_service->status_hotel = $service_ifx->estado_hotel;
                $file_service->confirmation_code = $service_ifx->codcfm;
                $file_service->classification = $service_ifx->clasif;
                $file_service->base_name_initial = $service_ifx->desbas_inicial;
                $file_service->base_code = $service_ifx->bastar;
                $file_service->base_name_original = $service_ifx->desbas;
                $file_service->additional_information = $service_ifx->infoad;
                $file_service->total_paxs = $service_ifx->canpax;
                $file_service->category_hotel_name = $service_ifx->categoria_hotel;
                $file_service->number_annulments = $service_ifx->anulado;
                $file_service->relation_nights = $service_ifx->relation;
                $file_service->airline_name = $service_ifx->razon;
                $file_service->airline_code = $service_ifx->ciavue;
                $file_service->airline_number = $service_ifx->nrovue;
                $file_service->category_code_ifx = $service_ifx->catser;
                $file_service->type_code_ifx = $service_ifx->tipsvs;
                $file_service->description = $service_ifx->descri;
                $file_service->start_time = $service_ifx->horin;
                $file_service->departure_time = $service_ifx->horout;
                $file_service->description_ES = $service_ifx->descri_es;
                $file_service->description_ES_code = $service_ifx->flag_es;
                $file_service->description_EN = $service_ifx->descri_en;
                $file_service->description_EN_code = $service_ifx->flag_en;
                $file_service->description_PT = $service_ifx->descri_pt;
                $file_service->description_PT_code = $service_ifx->flag_pt;
                $file_service->description_IT = $service_ifx->descri_it;
                $file_service->description_IT_code = $service_ifx->flag_it;
                $file_service->city_in_iso = $service_ifx->ciuin;
                $file_service->city_out_iso = $service_ifx->ciuout;
                $file_service->city_name = $service_ifx->descri_ciudad;
                $file_service->country_name = $service_ifx->descri_pais;
                $file_service->date_in = $service_ifx->fecin;
                $file_service->date_out = $service_ifx->fecout;
                // more
                $file_service->operation_number = $service_ifx->nroope;
                $file_service->operation_date = $service_ifx->fecope;
                $file_service->operation_type = $service_ifx->tipope;
                $file_service->passenger_type = $service_ifx->tippax;
                $file_service->passenger_sequence_number = $service_ifx->secpax;
                $file_service->starting_country_iso_ifx = $service_ifx->paiin;
                $file_service->start_zone_ifx = $service_ifx->zonin;
                $file_service->starting_country_grouping = $service_ifx->gruin;
                $file_service->out_country_iso_ifx = $service_ifx->paiout;
                $file_service->out_zone_ifx = $service_ifx->zonout;
                $file_service->out_country_grouping = $service_ifx->gruout;
                $file_service->currency = $service_ifx->moneda;
                $file_service->currency_sale = $service_ifx->monvta;
                $file_service->currency_cost = $service_ifx->moncos;
                $file_service->amount_sale = $service_ifx->vtaloc;
                $file_service->amount_cost = $service_ifx->cosloc;
                $file_service->amount_sale_unit = $service_ifx->vtauni;
                $file_service->amount_cost_unit = $service_ifx->cosuni;
                $file_service->taxed_unit_sale = $service_ifx->grvuni;
                $file_service->taxed_unit_cost = $service_ifx->grcuni;
                $file_service->unit_sale_taxes = $service_ifx->ivvuni;
                $file_service->unit_cost_taxes = $service_ifx->ivcuni;
                $file_service->mode_calculation_days = $service_ifx->diario;
                $file_service->mode_calculation_paxs = $service_ifx->paxuni;
                $file_service->total_services = $service_ifx->cansvs;
                $file_service->total_amount = $service_ifx->tarifa;
                $file_service->total_amount_provider = $service_ifx->netpag;
                $file_service->markup_created = $service_ifx->piaced;
                $file_service->total_amount_created = $service_ifx->iatced;
                $file_service->total_amount_invoice = $service_ifx->netfac;
                $file_service->total_amount_taxed = $service_ifx->netgra;
                $file_service->total_amount_exempt = $service_ifx->netexe;
                $file_service->taxes = $service_ifx->tax3;
                $file_service->use_quote = $service_ifx->cotiza;
                $file_service->use_voucher = $service_ifx->vouche;
                $file_service->use_itinerary = $service_ifx->itiner;
                $file_service->voucher_sent = $service_ifx->vouemi;
                $file_service->voucher_number = $service_ifx->nrovou;
                $file_service->use_ticket = $service_ifx->ticket;
                $file_service->ticket_sent = $service_ifx->tktemi;
                $file_service->use_accounting_document = $service_ifx->docum;
                $file_service->accounting_document_sent = $service_ifx->docemi;
                $file_service->branch_number = $service_ifx->nrosuc;
                $file_service->serie = $service_ifx->serie;
                $file_service->document_type = $service_ifx->tipdoc;
                $file_service->document_number = $service_ifx->nrodoc;
                $file_service->document_skeleton = $service_ifx->docupr;
                $file_service->document_purchase_order = $service_ifx->doprem;
                $file_service->lending_accountant = $service_ifx->nropro;
                $file_service->reservation_for_send = $service_ifx->viacom;
                $file_service->reservation_sent = $service_ifx->comemi;
                $file_service->provider_for_assign = $service_ifx->asigna;
                $file_service->provider_assigned = $service_ifx->asiemi;
                $file_service->save();
            }

            $file_ = File::find($file_id);
            $file_services = FileService::where('file_id', $file_id)->get();
            $passengers = ReservationPassenger::where('reservation_id', $file_->reservation_id)->get();
            if (count($passengers) > 0 && count($file_services) > 0) {
                $this->import_accommodations($file_->file_number, $file_services, $passengers);
            }

            $success = true;
            $message = "Guardado correctamente";
        }

        return ["success" => $success, "message" => $message];
    }

    private function update_import_services($nroref)
    {
        // Eliminar los servicios, primero los file accomodations
        $file = File::where('file_number', $nroref)->first();
        $file_services = FileService::where('file_id', $file->id)->get();
        foreach ($file_services as $file_service) {
            FileAccommodation::where('file_service_id', $file_service->id)->delete();
            $file_service->delete();
        }

        // Luego invocar al import services nuevamente
        $reimport = $this->import_services($nroref, $file->id, '');
        return $reimport;
        // En los import de services, y de pasajeros, poner si existe count de su contraparte, si es asi importar file_accommodations
    }

//    private function import_accommodations($nroref, $file_services, $passengers)
//    {
//        $success = true;
//        $message = 'Accommodations not founds';
//        $stellaService = new StellaService;
//        $gets = 0;
//        $errors = 0;
//
//        $passengers_ids = [];
//        if (count($file_services) > 0) {
//            foreach ($passengers as $passenger) {
//                $passengers_ids[$passenger->sequence_number] = $passenger->id;
//            }
//        }
//
//        foreach ($file_services as $file_service) {
//
//            $accommodations_ifx = $stellaService->get_accommodation_file_by_service($nroref, $file_service->item_number);
//
//            FileAccommodation::where('file_service_id', $file_service->id)->delete();
//
//            //        return $accommodations_ifx;
//
//            if (count($accommodations_ifx) > 0) {
//
//                $gets++;
//
//                foreach ($accommodations_ifx as $accommodation_ifx) {
//                    if (!isset($passengers_ids[$accommodation_ifx->nrosec])) {
//                        break;
//                    }
//                    $new_accommodation = new FileAccommodation();
//                    $new_accommodation->file_service_id = $file_service->id;
//                    $new_accommodation->reservation_passenger_id = $passengers_ids[$accommodation_ifx->nrosec];
//                    $new_accommodation->room_key = trim($accommodation_ifx->nrohab);
//                    $new_accommodation->save();
//                }
//
//            }
//        }
//
//        if ($gets > 0 && $errors === 0) {
//            $message = "importados correctamente";
//        }
//
//        return ["success" => $success, "message" => $message];
//    }
    private function import_accommodations($nroref, $file_services, $passengers)
    {
        $success = true;
        $message = 'Accommodations not founds';
        $stellaService = new StellaService;

        $passengers_ids = [];
        if (count($file_services) > 0) {
            foreach ($passengers as $passenger) {
                $passengers_ids[$passenger->sequence_number] = $passenger->id;
            }
        }

        foreach ($file_services as $file_service) {
            FileAccommodation::where('file_service_id', $file_service->id)->delete();
        }

        $key_cache = sprintf('accommodations_ifx_%s', $nroref);

        $accommodations_ifx = Cache::remember($key_cache, 60 * 60 * 24, function () use ($stellaService, $nroref) {
            return $stellaService->get_file_accommodations($nroref);
        });

        foreach ($file_services as $file_service) {

            foreach ($accommodations_ifx as $accommodation_ifx) {
                if (!isset($passengers_ids[$accommodation_ifx->nrosec]) ) {
                    break;
                }
                if($accommodation_ifx->nroite != $file_service->item_number){
                    continue;
                }
                $new_accommodation = new FileAccommodation();
                $new_accommodation->file_service_id = $file_service->id;
                $new_accommodation->reservation_passenger_id = $passengers_ids[$accommodation_ifx->nrosec];
                $new_accommodation->room_key = trim($accommodation_ifx->nrohab);
                $new_accommodation->save();
            }
        }

        return ["success" => $success, "message" => $message,
            'file_services' => $file_services,
            'file_passengers' => $passengers_ids,
            'accommodations_ifx' => $accommodations_ifx
        ];
    }

    public function translate_file_services_ifx($file_, $data, $full)
    {

        $response = [];

        foreach ($data as $d) {
            $response_ = [
                "id" => $d->id,
                "clase" => $d->classification_iso,
                "codsvs" => $d->code,
                "clasif" => $d->classification,
                "nroite" => $d->item_number,
                "itepaq" => $d->item_number_parent,
                "flag_acomodo" => $d->flag_accommodation,
                "nroref" => $file_->file_number,
                "cantid" => $d->total_rooms,
                "preped" => $d->code_request_book,
                "prefac" => $d->code_request_invoice,
                "prevou" => $d->code_request_voucher,
                "estado" => $d->status_ifx,
                "desbas_inicial" => $d->base_name_initial,
                "infoad" => $d->additional_information,
                "canpax" => $d->total_paxs,
                "categoria_hotel" => $d->category_hotel_name,
                "anulado" => $d->number_annulments,
                "desbas" => $d->base_name_original,
                "relation" => $d->relation_nights,
                "razon" => $d->airline_name,
                "ciavue" => $d->airline_code,
                "nrovue" => $d->airline_number,
                "canadl" => $file_->adults,
                "canchd" => $file_->children,
                "caninf" => $file_->infants,
                "catser" => $d->category_code_ifx,
                "tipsvs" => $d->type_code_ifx,
                "bastar" => $d->base_code,
                "descri" => $d->description,
                "horin_prime" => $d->start_time,
                "descri_es" => $d->description_ES,
                "flag_es" => $d->description_ES_code,
                "descri_en" => $d->description_EN,
                "flag_en" => $d->description_EN_code,
                "descri_pt" => $d->description_PT,
                "flag_pt" => $d->description_PT_code,
                "descri_it" => $d->description_IT,
                "flag_it" => $d->description_IT_code,
                "ciuin" => $d->city_in_iso,
                "descri_ciudad" => $d->city_name,
                "descri_pais" => $d->country_name,
                "fecin" => substr($d->date_in, 0, 10),
                "horin" => $d->start_time,
                "ciuout" => $d->city_out_iso,
                "fecout" => substr($d->date_out, 0, 10),
                "horout" => $d->departure_time
            ];

            if ($full) {
                $response_['nroope'] = $d->operation_number;
                $response_['fecope'] = $d->operation_date;
                $response_['tipope'] = $d->operation_type;
                $response_['tippax'] = $d->passenger_type;
                $response_['secpax'] = $d->passenger_sequence_number;
                $response_['paiin'] = $d->starting_country_iso_ifx;
                $response_['zonin'] = $d->start_zone_ifx;
                $response_['gruin'] = $d->starting_country_grouping;
                $response_['paiout'] = $d->out_country_iso_ifx;
                $response_['zonout'] = $d->out_zone_ifx;
                $response_['gruout'] = $d->out_country_grouping;
                $response_['moneda'] = $d->currency;
                $response_['monvta '] = $d->currency_sale;
                $response_['moncos'] = $d->currency_cost;
                $response_['vtaloc'] = $d->amount_sale;
                $response_['cosloc'] = $d->amount_cost;
                $response_['vtauni'] = $d->amount_sale_unit;
                $response_['cosuni'] = $d->amount_cost_unit;
                $response_['grvuni'] = $d->taxed_unit_sale;
                $response_['grcuni'] = $d->taxed_unit_cost;
                $response_['ivvuni'] = $d->unit_sale_taxes;
                $response_['ivcuni'] = $d->unit_cost_taxes;
                $response_['diario'] = $d->mode_calculation_days;
                $response_['paxuni'] = $d->mode_calculation_paxs;
                $response_['cansvs'] = $d->total_services;
                $response_['tarifa'] = $d->total_amount;
                $response_['netpag'] = $d->total_amount_provider;
                $response_['piaced'] = $d->markup_created;
                $response_['iatced'] = $d->total_amount_created;
                $response_['netfac'] = $d->total_amount_invoice;
                $response_['netgra'] = $d->total_amount_taxed;
                $response_['netexe'] = $d->total_amount_exempt;
                $response_['tax3'] = $d->taxes;
                $response_['cotiza'] = $d->use_quote;
                $response_['vouche'] = $d->use_voucher;
                $response_['itiner'] = $d->use_itinerary;
                $response_['vouemi'] = $d->voucher_sent;
                $response_['nrovou'] = $d->voucher_number;
                $response_['ticket'] = $d->use_ticket;
                $response_['tktemi'] = $d->ticket_sent;
                $response_['docum'] = $d->use_accounting_document;
                $response_['docemi'] = $d->accounting_document_sent;
                $response_['nrosuc'] = $d->branch_number;
                $response_['serie'] = $d->serie;
                $response_['tipdoc'] = $d->document_type;
                $response_['nrodoc'] = $d->document_number;
                $response_['docupr'] = $d->document_skeleton;
                $response_['doprem'] = $d->document_purchase_order;
                $response_['nropro'] = $d->lending_accountant;
                $response_['viacom'] = $d->reservation_for_send;
                $response_['comemi'] = $d->reservation_sent;
                $response_['asigna'] = $d->provider_for_assign;
                $response_['asiemi'] = $d->provider_assigned;
            }
            array_push($response, $response_);
        }

        return $response;
    }

    public function find_hotel($hotel_code)
    {

        $hotel = "";

        $channel = ChannelHotel::where('code', $hotel_code)->first();

        if ($channel) {
            $hotel = Hotel::find($channel->hotel_id);
        }

        return (!$hotel) ? '' : $hotel;

    }

}
