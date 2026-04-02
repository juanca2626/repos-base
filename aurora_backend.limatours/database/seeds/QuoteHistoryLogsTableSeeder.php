<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteHistoryLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {

            $module_id = \App\Module::where('name', 'Quote')->first()->id;

            $es_id = \App\Language::where('iso', 'es')->first()->id;
            $en_id = \App\Language::where('iso', 'en')->first()->id;
            $pt_id = \App\Language::where('iso', 'pt')->first()->id;

            $now = \Carbon\Carbon::now();

            DB::table('translation_frontends')->insert([
                [
                    "slug" => "messages." . "update_accommodation",
                    "value" => "Actualizó acomodación general y del itinerario",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_markup",
                    "value" => "Actualizó el markup",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_name",
                    "value" => "Actualizó el nombre",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_type_general",
                    "value" => "Actualizó el tipo de servicio general",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_adults",
                    "value" => "Actualizó la cantidad de adultos general",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_type_pax",
                    "value" => "Actualizó el tipo de cotización de Paxs a Rangos",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_range",
                    "value" => "Eliminó el rango",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_general",
                    "value" => "Actualizó la fecha general y de todo el itinerario",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_childs",
                    "value" => "Actualizó la cantidad de niños general",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_data_paxs",
                    "value" => "Actualizó datos de pasajero",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_category",
                    "value" => "Eliminó categoría",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_category",
                    "value" => "Agregó categoría",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_estimated",
                    "value" => "Actualizó la fecha estimada",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "copy_category",
                    "value" => "Copió categoría",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_general_adults",
                    "value" => "Agregó cantidad general de adultos",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_service",
                    "value" => "Eliminó servicio",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "replace_service",
                    "value" => "Reemplazó servicio",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_service",
                    "value" => "Agregó servicio",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_extension",
                    "value" => "Agregó extensión",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_flight",
                    "value" => "Agregó Vuelo",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date",
                    "value" => "Actualizó fecha del servicio",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_paxs",
                    "value" => "Actualizó cantidad de Pasajeros",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_occupation",
                    "value" => "Actualizó la acomodación de un servicio",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ]
            ]);

            DB::table('translation_frontends')->insert([
                [
                    "slug" => "messages." . "update_accommodation",
                    "value" => "Updated general accommodation and itinerary",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_markup",
                    "value" => "Updated the markup",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_name",
                    "value" => "Updated the name",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_type_general",
                    "value" => "Updated the general service type",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_adults",
                    "value" => "Updated the general number of adults",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_type_pax",
                    "value" => "Updated the quote rate from Paxs to Ranges",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_range",
                    "value" => "Removed range",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_general",
                    "value" => "Updated the general date and the entire itinerary",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_childs",
                    "value" => "Updated the overall number of children",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_data_paxs",
                    "value" => "Updated passenger data",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_category",
                    "value" => "Deleted category",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_category",
                    "value" => "Added category",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_estimated",
                    "value" => "Updated the estimated date",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "copy_category",
                    "value" => "Copied category",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_general_adults",
                    "value" => "Added general number of adults",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_service",
                    "value" => "Removed service",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "replace_service",
                    "value" => "Replaced service",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_service",
                    "value" => "Added service",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_extension",
                    "value" => "Added extension",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_flight",
                    "value" => "Added flight",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date",
                    "value" => "Updated date of service",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_paxs",
                    "value" => "Updated number of Passengers",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_occupation",
                    "value" => "Updated the accommodation of a service",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ]
            ]);

            DB::table('translation_frontends')->insert([
                [
                    "slug" => "messages." . "update_accommodation",
                    "value" => "Acomodação geral e itinerário atualizados",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_markup",
                    "value" => "Atualizou a marcação",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_name",
                    "value" => "Atualizado o nome",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_type_general",
                    "value" => "Atualizado o tipo de serviço geral",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_adults",
                    "value" => "Atualizado o número geral de adultos",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_type_pax",
                    "value" => "Atualizado a taxa de cotação de Paxs para Range",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_range",
                    "value" => "Range removido",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_general",
                    "value" => "Atualizado a data geral e todo o itinerário",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_general_childs",
                    "value" => "Atualizado o número geral de filhos",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_data_paxs",
                    "value" => "Dados de passageiros atualizados",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_category",
                    "value" => "Categoria excluída",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_category",
                    "value" => "Categoria adicionada",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date_estimated",
                    "value" => "Atualizado a data estimada",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "copy_category",
                    "value" => "Categoria copiada",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_general_adults",
                    "value" => "Adicionado número geral de adultos",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "destroy_service",
                    "value" => "Serviço removido",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "replace_service",
                    "value" => "Serviço substituído",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_service",
                    "value" => "Serviço adicionado",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_extension",
                    "value" => "Extensão adicionada",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "store_flight",
                    "value" => "Voo adicionado",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_date",
                    "value" => "Data atualizada do serviço",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_service_paxs",
                    "value" => "Número atualizado de passageiros",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "messages." . "update_occupation",
                    "value" => "Atualizado a acomodação de um serviço",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ]
            ]);

            DB::table('translation_frontends')->insert([
                [
                    "slug" => "label." . "update",
                    "value" => "Actualizó",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "update",
                    "value" => "Updated",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "update",
                    "value" => "Atualizada",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "store",
                    "value" => "Agregó",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "store",
                    "value" => "Added",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "store",
                    "value" => "Adicionado",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "destroy",
                    "value" => "Eliminó",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "destroy",
                    "value" => "Removed",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "destroy",
                    "value" => "Removido",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "no_registration",
                    "value" => "Ningún registro",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "no_registration",
                    "value" => "No registration",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "no_registration",
                    "value" => "Sem registro",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "back_to_my_quotes",
                    "value" => "Volver a Mis Cotizaciones",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "back_to_my_quotes",
                    "value" => "Back to My Quotes",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "back_to_my_quotes",
                    "value" => "Voltar para minhas citações",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "history_of_changes",
                    "value" => "Historial de Cambios",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "history_of_changes",
                    "value" => "History of Changes",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "history_of_changes",
                    "value" => "História de Mudanças",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "user",
                    "value" => "Usuario",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "user",
                    "value" => "User",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "user",
                    "value" => "Usuário",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "creation_date",
                    "value" => "Fecha de creación",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "creation_date",
                    "value" => "Creation date",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "creation_date",
                    "value" => "Data de criação",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "filter_logs",
                    "value" => "Filtrar registros de logs",
                    "module_id" => $module_id, "language_id" => $es_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "filter_logs",
                    "value" => "Filter log records",
                    "module_id" => $module_id, "language_id" => $en_id,
                    "created_at" => $now, "updated_at" => $now
                ],
                [
                    "slug" => "label." . "filter_logs",
                    "value" => "Filtrar registros de log",
                    "module_id" => $module_id, "language_id" => $pt_id,
                    "created_at" => $now, "updated_at" => $now
                ]
            ]);

        });
    }
}
