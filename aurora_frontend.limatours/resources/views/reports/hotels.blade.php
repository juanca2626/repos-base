{{-- Modal cancelar habitacion    --}}
<b-modal id="cancel-room" title="Confirmar Cancelación" centered ref="modal-will-cancel-reservation-room" size="md" class="modal-central" style="border-radius: 1rem!important;">

    <h2 style="font-size: 20px !important; margin-bottom: 1.5rem!important; color: #2E2E2E;">Cancelar habitación</h2>
    <p class="text-left py-4" style="color: #2E2E2E; font-size: 1.8rem;">Estás a un paso de eliminar la habitación. ¿Estás seguro?</p>
    <div class="alert alert-warning p-0 d-flex">
        <div class="ml-3" v-if="text_add_!==''">
            <i class="fa fa-exclamation-circle"></i>
        </div>
        <div class="ml-3" v-if="text_add_!==''">
            <span v-html="text_add_"></span>
        </div>
    </div>
    <p class="text-left" style="color: #2E2E2E; font-size: 1.5rem;">Ingresa un comentario para el proveedor:</p>
    <textarea class="form-control px-3 py-2" :disabled="blockPage" v-model="message_provider" cols="30" rows="3" style="border: 1px solid #d6d6d6;"></textarea>

    <br>
    <label for="block_email_provider" @click="see_email_provider()">
        <input type="checkbox" name="block_email_provider" v-model="block_email_provider"> No enviar email al hotel
    </label>

    <div class="d-flex justify-content-between align-items-center mt-5">
        <button @click="hideModal()" :disabled="blockPage" class="btn-cancel">{{trans('reservations.label.cancel')}}</button>
        <button class="btn-primary" :disabled="blockPage" @click="do_cancel_reservation_room()" type="button">
            {{trans('reservations.label.yes_continue')}}
        </button>
    </div>

    <div slot="modal-footer">

    </div>
</b-modal>

{{-- End modal cancelar habitacion    --}}

<div>
    <div class="py-5" v-if="header_detail.file_number !== ''">
        <h3>Numero de File: <span class="color-primary line-bottom">@{{ header_detail.file_number }}</span></h3>
        <p><strong>Nombre del grupo:</strong> @{{ header_detail.group_name }}</p>
        <p><strong>Ejecutivo:</strong> @{{ header_detail.executive }}</p>
    </div>

    {{-- Bulk Actions Bar --}}
    <div v-if="selectedRooms.length > 0" class="bulk-actions-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="p-3">
                <strong>@{{ selectedRooms.length }}</strong> habitación(es) seleccionada(s)
            </div>
            <div>
                <button class="btn btn-danger mr-2" @click="openBulkCancelModal()" :disabled="isBulkCancelling">
                    <i class="fas fa-trash-alt mr-1"></i> Eliminar Seleccionadas (@{{ selectedRooms.length }})
                </button>
                <button class="btn btn-secondary" @click="clearSelection()" :disabled="isBulkCancelling">
                    <i class="fas fa-times mr-1"></i> Limpiar Selección
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row text-center head-result mx-0">
            <div class="col-3">{{trans('reservations.label.hotel')}}</div>
            <div class="col-1 col-action" @click="changeFilter('date_order')">
                {{trans('reservations.label.arrival_date')}}
                <i class="fa fa-arrow-down" v-if="date_order==='asc'"></i>
                <i class="fa fa-arrow-up" v-else></i>
            </div>
            <div class="col-1">{{trans('reservations.label.departure_date')}}</div>
            <div class="col-1">{{trans('reservations.label.nights')}}</div>
            <div class="col-4">Status</div>
            <div class="col-1 px-0">{{trans('reservations.label.confirmation_code')}}</div>
            <div class="col-1"></div>
        </div>
        <div v-for="(resHotel,index) in reservationsHotels" :key="index">
            <div class="row text-center body-result py-2 mx-0">
                {{-- 1 Nombre de Hotel--}}
                <div class="col-3 clasi_resHotelName mt-0 px-3">
                    <b>@{{ resHotel.hotel_name }} (@{{ resHotel.hotel_code }}) <i class="fa fa-link" style="cursor: pointer;" @click="showUrlHotel(resHotel)"></i></b>
                </div>
                {{-- 2 Fecha de llegada al hotel --}}
                <div class="col-1 clasi_resCheckIn fecha-llegada">
                    <div>
                        <div>@{{ dateFormatLatin(resHotel.check_in) }}</div>
                    </div>
                </div>
                {{-- 3 Fecha de salida al hotel --}}
                <div class="col-1 clasi_resCheckOut fecha-salida">
                    <div class="">
                        <div>@{{ dateFormatLatin(resHotel.check_out) }}</div>
                    </div>
                </div>
                {{-- 4 Noches --}}
                <div class="col-1 clasi_resNightsNum text-center">
                    @{{ resHotel.nights }}<i class="icon-moon ml-1" style="font-weight: bold"></i>
                </div>
                {{-- 5 Status --}}
                <div class="col-4 clasi_resHotelStatusText mt-0">
                    <div class="box-status" v-if="resHotel.status === 0">
                        <p class="clasi_cancelada">
                            {{trans('reservations.label.cancelled')}}
                        </p>
                        <p v-if="hasPenalty(resHotel)" class="clasi_conPenalidad">
                            &nbsp;{{trans('reservations.label.with_penalty')}}
                        </p>
                    </div>
                    {{-- status = 1 --}}
                    <div class="box-status" v-else>
                        <p v-if="resHotel.status === 3" class="clasi_sinConfirmar">
                            {{trans('reservations.label.unconfirmed')}}
                        </p>
                        <p v-if="hasPenalty(resHotel)" class="clasi_limVencido">
                            <i class="icon-clock mr-1" style="font-size: 16px"></i> {{trans('reservations.label.expired_limit')}}
                        </p>
                        <p v-else-if="resHotel.status === 1 || resHotel.status === 2" class="clasi_sinPenalidad">
                            {{trans('reservations.label.confirmed')}}
                        </p>
                    </div>
                    <span v-for="resHotelRoom in resHotel.reservations_hotel_rooms" class="resRoomCode">
                    </span>
                </div>
                {{-- 6 Confirmacion de codigo --}}
                <div class="col-1 clasi_resCheckOut td-rounded-right font-weight-bold">
                    <div v-if="resHotel.status != 0">
                        <span v-if="(resHotel.channel_reservation_codes_label !=='')">
                            @{{ resHotel.channel_reservation_codes_label }}
                        </span>
                        <span v-else>
                            <a class="clasi_botonEditar" v-if="checkIsFile(resHotel)" @click="openModalCodeConfirm(resHotel, index)"
                                href="javascript:void(0)" style="color: #eb5757; font-weight: 600; border-bottom: 1px solid #eb5757;">
                                <i class="icon-edit mr-2"></i>{{trans('reservations.label.add')}}
                            </a>
                        </span>
                    </div>
                    <div v-else>
                        <span v-if="(resHotel.reservations_hotel_rooms[0].channel_reservation_code !== null && resHotel.reservations_hotel_rooms[0].channel_reservation_code !=='')">
                            @{{ resHotel.reservations_hotel_rooms[0].channel_reservation_code }}
                        </span>
                    </div>
                </div>
                {{-- 7 Acciones --}}
                <div class="col-1 d-flex align-items-center justify-content-start">
                    <a href="#"
                        v-show="[0, 1, 2, 3].includes(resHotel.status)"
                        class="text-secondary mr-3 p-1"
                        style="text-decoration: none;"
                        data-toggle="collapse"
                        :data-target="'#table_'+index"
                        @click.prevent="editReservationCollapse(resHotel, index); toggle_see_rooms(resHotel, !resHotel.toggle_)">
                        <i class="fas fa-lg" :class="resHotel.toggle_ ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </a>
                    <a href="#" v-if="resHotel.status != 0"
                        class="text-danger p-1"
                        @click.prevent="openModalCancelReservation(resHotel, index)">
                        <i class="fas fa-trash-alt fa-lg"></i>
                    </a>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <div v-if="resHotel.toggle_" class="container mx-5 my-4" style="font-size: 12px;">
                    <div class="d-block">
                        <table class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col" style="width: 50px;"></th>
                                    <th scope="col">#</th>
                                    <th scope="col">{{trans('reservations.label.room')}}</th>
                                    <th scope="col">{{trans('reservations.label.rate_plan')}}</th>
                                    <th scope="col">{{trans('reservations.label.price')}}</th>
                                    <th scope="col">{{trans('reservations.label.politics_cancel')}}</th>
                                    <th scope="col">{{trans('reservations.label.cancel')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(resRoom,index_room_aurora) in resHotel.reservations_hotel_rooms" :key="index_room_aurora" v-if="resRoom.channel_id===1" :style="resRoom.onRequest == '0' ? 'background-color:#dee2e6' : ''">
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="reservation-checkbox"
                                            :disabled="resRoom.status === 0"
                                            :checked="isRoomSelected(resRoom)"
                                            @change="toggleRoomSelection(resRoom, resHotel)"
                                            title="Seleccionar habitación">
                                    </td>
                                    <td class="clasi_resCode">
                                        <span v-if="resRoom.status === 0" class="clasi clasi_cancelada"></span>
                                        <span v-else-if="resRoom.status === 3" class="clasi clasi_sinConfirmar"></span>
                                        <span v-else-if="resRoom.status === 1 || resRoom.status === 2"
                                            class="clasi clasi_sinPenalidad"></span>
                                        @{{ resRoom.id }}
                                    </td>
                                    <td class="clasi_resRoomName">
                                        @{{ resRoom.room_name }} - @{{ resRoom.room_code }}
                                    </td>
                                    <td class="clasi_resRatePlanName">
                                        @{{ resRoom.rate_plan_name }} - @{{ resRoom.rate_plan_code }}
                                    </td>

                                    <td class="clasi_resRoomPrince">
                                        $ @{{ resRoom.total_amount + resRoom.total_tax_and_services_amount }}
                                    </td>
                                    <td class="clasi_resRoomPrince">
                                        <div v-for="policies_cancellation in resRoom.policies_cancellation">
                                            <p>
                                                @{{ policies_cancellation.message }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="clasi_resRoomPrince">
                                        <a class="clasi_botonCancelar" v-if="resRoom.status !== 0" href="javascript:void(0)"
                                            @click="will_cancel_reservation_room(resHotel.reservation.file_code, resHotel.id, resRoom.id)">
                                            <span class="icon-ac-trash-2"></span>
                                        </a>
                                    </td>
                                </tr>
                                <tr v-for="(resRoomHyperguest,index_room_hyperguest) in resHotel.reservations_hotel_rooms" :key="index_room_hyperguest" v-if="resRoomHyperguest.channel_id===6">
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="reservation-checkbox"
                                            :disabled="resRoomHyperguest.status === 0"
                                            :checked="isRoomSelected(resRoomHyperguest)"
                                            @change="toggleRoomSelection(resRoomHyperguest, resHotel)"
                                            title="Seleccionar habitación">
                                    </td>
                                    <td class="clasi_resCode">
                                        <span v-if="resRoomHyperguest.status === 0" class="clasi clasi_cancelada"></span>
                                        <span v-else-if="resRoomHyperguest.status === 3" class="clasi clasi_sinConfirmar"></span>
                                        <span v-else-if="resRoomHyperguest.status === 1 || resRoomHyperguest.status === 2"
                                            class="clasi clasi_sinPenalidad"></span>
                                        @{{ resRoomHyperguest.id }}
                                    </td>
                                    <td class="clasi_resRoomName">
                                        @{{ resRoomHyperguest.room_name }} - @{{ resRoomHyperguest.room_code }}
                                    </td>
                                    <td class="clasi_resRatePlanName">
                                        @{{ resRoomHyperguest.rate_plan_name }} - @{{ resRoomHyperguest.rate_plan_code }}
                                    </td>

                                    <td class="clasi_resRoomPrince">
                                        $ @{{ resRoomHyperguest.total_amount + resRoomHyperguest.total_tax_and_services_amount }}
                                    </td>
                                    <td class="clasi_resRoomPrince">
                                        <div v-for="policies_cancellation in resRoomHyperguest.policies_cancellation">
                                            <p>
                                                @{{ policies_cancellation.message }}
                                            </p>
                                        </div>
                                    </td>
                                    <template>

                                        <td class="clasi_resRoomPrince"> <!-- v-if="checkRoomHyperguest(resRoomHyperguest.channel_id)" -->
                                            <a class="clasi_botonCancelar" v-if="resRoomHyperguest.status !== 0" href="javascript:void(0)"
                                                @click="will_cancel_reservation_room_by_channel(resHotel.reservation.file_code, resHotel.id)">
                                                <span class="icon-ac-trash-2"></span>
                                            </a>
                                        </td>

                                    </template>
                                    <template v-if="index_room_hyperguest > 0">
                                        <td></td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <template v-if="page > 0">
                    <li class="page-item">
                        <a class="page-link" v-on:click="changePage(0)" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M12.5 15a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5M10 8a.5.5 0 0 1-.5.5H3.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L3.707 7.5H9.5a.5.5 0 0 1 .5.5" />
                            </svg>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" v-on:click="changePage(page - 1)" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </a>
                    </li>
                </template>
                <template v-for="(_page, p) in pages">
                    <li v-bind:class="['page-item', (page == p) ? 'active' : '']" v-if="show_pages[p]">
                        <a class="page-link" href="#" v-on:click="changePage(p)">
                            @{{ _page }}
                        </a>
                    </li>
                </template>
                <template v-if="page < (pages - 1)">
                    <li class="page-item">
                        <a class="page-link" v-on:click="changePage(page + 1)" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                            </svg>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" v-on:click="changePage(pages - 1)" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-bar-right" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M6 8a.5.5 0 0 0 .5.5h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L12.293 7.5H6.5A.5.5 0 0 0 6 8m-2.5 7a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5" />
                            </svg>
                        </a>
                    </li>
                </template>
            </ul>
        </nav>
    </div>
    <div class="text-center py-5">
        <button class="btn-primary" type="button" @click="go_to_hotels()">Ver otros hoteles</button>
    </div>
</div>


{{-- Modal bulk cancel reservations --}}
<b-modal id="modal-bulk-cancel" ref="modal-bulk-cancel" title="Cancelar Reservas Seleccionadas" centered size="md" class="modal-central" style="border-radius: 1rem!important;">
    <h2 style="font-size: 20px !important; margin-bottom: 1.5rem!important; color: #2E2E2E;">Cancelación Masiva</h2>
    <p class="text-left py-4" style="color: #2E2E2E; font-size: 1.8rem;">
        Estás a punto de eliminar <strong>@{{ selectedRooms.length }}</strong> habitación(es). ¿Estás seguro?
    </p>

    <div class="alert alert-info p-3">
        <i class="fa fa-info-circle mr-2"></i>
        <span>Las siguientes habitaciones serán eliminadas:</span>
        <ul class="mt-2 mb-0" style="max-height: 200px; overflow-y: auto;">
            <li v-for="room in selectedRooms" :key="room.id">
                @{{ room.room_name }} - @{{ room.room_code }} (ID: @{{ room.id }})
            </li>
        </ul>
    </div>

    <p class="text-left" style="color: #2E2E2E; font-size: 1.5rem;">Ingresa un comentario para el proveedor:</p>
    <textarea class="form-control px-3 py-2" :disabled="isBulkCancelling" v-model="message_provider" cols="30" rows="3" style="border: 1px solid #d6d6d6;"></textarea>

    <br>
    <label for="block_email_provider_bulk">
        <input type="checkbox" name="block_email_provider_bulk" v-model="block_email_provider"> No enviar email al hotel
    </label>

    <div class="d-flex justify-content-between align-items-center mt-5">
        <button @click="closeBulkCancelModal()" :disabled="isBulkCancelling" class="btn-cancel">Cancelar</button>
        <button class="btn-primary" :disabled="isBulkCancelling" @click="processBulkCancellation()" type="button">
            <span v-if="!isBulkCancelling">Sí, Cancelar Todas</span>
            <span v-else><i class="fas fa-spinner fa-spin"></i> Procesando...</span>
        </button>
    </div>

    <div slot="modal-footer"></div>
</b-modal>
