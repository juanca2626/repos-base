<b-modal title="Confirmar Cancelación" centered ref="modal-will-cancel-reservation-room" size="md" class="modal-central">

    <h4 style="margin-bottom: 15px">Escríbele un mensaje al proveedor (Opc.):</h4>
        <textarea class="form-control" :disabled="blockPage" v-model="message_provider" cols="30" rows="5"></textarea>
    <hr>

    <div class="alert alert-warning"><i class="fa fa-exclamation-circle"></i> @{{ text_add_ }}</div>

    <div class="d-flex justify-content-between align-items-center mt-5">
        <button @click="hideModal()" :disabled="blockPage" class="btn btn-cancelar w-25">{{trans('reservations.label.cancel')}}</button>
        <button class="btn btn-success" :disabled="blockPage" @click="do_cancel_reservation_room()" type="button">
            {{trans('global.label.do')}}
        </button>
    </div>

    <div slot="modal-footer">

    </div>
</b-modal>

</a>

<div class="clasificacion d-flex justify-content-end mt-5 mb-3">
    <div class="clasi_sinPenalidad"><i class="fa fa-circle"></i> {{trans('reservations.label.days_left_without_penalty')}}</div>
    <div class="clasi_conPenalidad"><i class="fa fa-circle"></i> {{trans('reservations.label.with_penalty')}}</div>
    <div class="clasi_sinConfirmar"><i class="fa fa-circle"></i> {{trans('reservations.label.unconfirmed')}}</div>
    <div class="clasi_cancelada"><i class="fa fa-circle"></i> {{trans('reservations.label.cancelled')}}</div>
</div>

<div>
    <div class="container">
        <div class="row text-center head-result">
            <div class="col-1">{{trans('reservations.label.file')}}</div>
            <div class="col-2">{{trans('reservations.label.hotel')}}</div>
            <div class="col-2">{{trans('reservations.label.politics')}}</div>
            <div class="col-1">{{trans('reservations.label.reservation_date')}}</div>
            <div class="col-1">{{trans('reservations.label.executive')}}</div>
            <div class="col-1">{{trans('reservations.label.arrival_date')}}</div>
            <div class="col-1">{{trans('reservations.label.nights')}}</div>
            <div class="col-1">{{trans('reservations.label.departure_date')}}</div>
            <div class="col-1">{{trans('reservations.label.confirmation_code')}}</div>
            <div class="col-1"></div>
        </div>
        <div v-for="(resHotel,index) in reservationsHotels" :key="index">
            <div class="row text-center body-result" >
                <div class="col-1 clasi_resCode td-rounded-left">
                    <span v-if="resHotel.status === 0" class="clasi clasi_cancelada"></span>
                    <span v-else-if="resHotel.status === 3" class="clasi clasi_sinConfirmar"></span>
                    <span v-else-if="hasPenalty(resHotel)" class="clasi clasi_conPenalidad"></span>
                    <span v-else-if="resHotel.status === 1 || resHotel.status === 2" class="clasi clasi_sinPenalidad"></span>
                    @{{ resHotel.reservation.file_code }}
                    <br><br>
                    <button type="button" class="btn btn-success"
                            v-show="resHotel.status == 0 || resHotel.status == 1 || resHotel.status == 2 || resHotel.status == 3"
                            @click="editReservationCollapse(resHotel, index)"
                            data-toggle="collapse" :data-target="'#table_'+index">
                        <i class="fas fa-plus-square"></i> Rooms
                    </button>
                </div>
                <div class="col-2 clasi_resHotelName">
                    <b>@{{ resHotel.hotel_name }} (@{{ resHotel.hotel_code }}) <i class="fa fa-link" style="cursor: pointer;"
                                                                                  @click="showUrlHotel(resHotel)"></i></b>
                    <span v-for="resHotelRoom in resHotel.reservations_hotel_rooms" class="resRoomCode font-weight-bold" style="color: #6c6c6c;">
                R: @{{ resHotelRoom.room_code }}
                </span>
                </div>
                <div class="col-2 clasi_resHotelStatusText">
                    <div class="box-status" v-if="resHotel.status === 0">
                        <p class="clasi_cancelada">
                            {{trans('reservations.label.cancelled')}}
                        </p>
                        <p v-if="hasPenalty(resHotel)" class="clasi_conPenalidad">
                            &nbsp;{{trans('reservations.label.with_penalty')}}
                        </p>
                    </div>
{{--                    status = 1 --}}
                    <div class="box-status" v-else>
                        <p v-if="resHotel.status === 3" class="clasi_sinConfirmar">
                            {{trans('reservations.label.unconfirmed')}}
                        </p>
                        <p v-if="hasPenalty(resHotel)" class="clasi_limVencido">
                            <i class="icon-clock mr-1" style="font-size: 16px"></i>    {{trans('reservations.label.expired_limit')}}
                        </p>
                        <p v-else-if="resHotel.status === 1 || resHotel.status === 2" class="clasi_sinPenalidad">
                            {{trans('reservations.label.confirmed')}}
                        </p>
                    </div>

                    <span v-for="resHotelRoom in resHotel.reservations_hotel_rooms" class="resRoomCode">
                    <p class="room_cancelation">
                        <div class="font-weight-bold" style="color: #6c6c6c;">R: @{{ resHotelRoom.room_code }}</div>

                        <p v-if="" v-for="policies_cancellation in resHotelRoom.policies_cancellation">
                            @{{ policies_cancellation.message }}
                        </p>
                    </p>
                </span>
                </div>
                <div class="col-1 clasi_resCreateDate fecha-reserva">
                    <div>
                        <div>@{{ dateFormatLatin(resHotel.created_at) }}</div>
                        <span>@{{ getTimeDate(resHotel.created_at) }}</span>
                    </div>
                    <div v-if="resHotel.status === 0">
                        <div>C: @{{ dateFormatLatin(resHotel.updated_at) }}</div>
                        <span>@{{ getTimeDate(resHotel.updated_at) }}</span>
                    </div>
                </div>
                <div class="col-1 clasi_resExecutive">
                    <div>
                        <div class="font-weight-bold"><i class="fas fa-user-tie mr-1"></i>@{{ resHotel.reservation.executive_name }}</div>
                        <span>C: @{{ resHotel.executive_name }}</span>
                    </div>
                </div>
                <div class="col-1 clasi_resCheckIn fecha-llegada">
                    <div>
                        <div>@{{ dateFormatLatin(resHotel.check_in) }}</div>
                        <span>@{{ resHotel.check_in_time }}</span>
                    </div>
                </div>
                <div class="col-1 clasi_resNightsNum text-center">
                    @{{ resHotel.nights }}<i class="icon-moon ml-1" style="font-weight: bold"></i>
                </div>
                <div class="col-1 clasi_resCheckOut fecha-salida">
                    <div class="">
                        <div>@{{ dateFormatLatin(resHotel.check_out) }}</div>
                        <span>@{{ resHotel.check_out_time }}</span>
                    </div>
                </div>
                <div class="col-1 clasi_resCheckOut fecha-salida">
                    {{--                 <span v-for="resHotelRoom in resHotel.reservations_hotel_rooms" class="resRoomCode">--}}
                    <span class="resRoomCode">
                    <p class="room_cancelation">
                        <div class="badge badge-info font-weight-bold" style="color: #ffffff;"
                             v-if="resHotel.reservations_hotel_rooms[0].channel_reservation_code != null && resHotel.reservations_hotel_rooms[0].channel_reservation_code != ''">
                         @{{ resHotel.reservations_hotel_rooms[0].channel_reservation_code }}
                        </div>
                    </p>
                </span>
                </div>
                <div class="col-1 clasi_resCheckOut td-rounded-right font-weight-bold" v-if="resHotel.status != 0">
{{--                    <div>--}}
{{--                        <a class="clasi_botonEditar" @click="editReservation(resHotel, index)" href="javascript:void(0)">--}}
{{--                            <i class="icon-edit mr-2"></i> {{trans('reservations.label.modify')}}--}}
{{--                        </a>--}}
{{--                    </div>--}}
                    <div>
                        <a class="clasi_botonEditar" v-if="checkIsFile(resHotel)" @click="openModalCodeConfirm(resHotel, index)" href="javascript:void(0)">
                            <i class="icon-edit mr-2"></i>{{trans('reservations.label.num_confirmation')}}
                        </a>
                    </div>
                    <div>
                        <a class="clasi_botonCancelar" @click="cancelReservation(resHotel.reservation.file_code, resHotel.id, index)" href="javascript:void(0)">
                            <i class="icon-minus-circle mr-2"></i>{{trans('reservations.label.cancel')}}
                        </a>
                    </div>
                </div>
                <div class="td clasi_resCheckOut td-rounded-right font-weight-bold"  v-else>
                </div>
            </div>
            <div class="row text-center">
                <div :id="'table_'+index" class="collapse container" style="font-size: 12.5px">
                    <div class="row">
                        <div class="col" style="padding: 4rem 10rem;">
                            <table class="table">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">{{trans('reservations.label.room')}}</th>
                                    <th scope="col">{{trans('reservations.label.rate_plan')}}</th>
                                    <th scope="col">{{trans('reservations.label.channel')}}</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">{{trans('reservations.label.adults')}}</th>
                                    <th scope="col">{{trans('reservations.label.children')}}</th>
                                    <th scope="col">{{trans('reservations.label.price')}}</th>
                                    <th scope="col"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(resRoom,index_room) in edit_reservation.reservations_hotel_rooms" :key="index_room">
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
                                    <td class="clasi_resChannelName">
                                        @{{ resRoom.channel_code }}
                                    </td>
                                    <td class="clasi_resChannelName">
                                        <span class="alert alert-warning" v-if="resRoom.onRequest == 0">RQ</span>
                                        <span class="alert alert-success" v-else>
                                            OK
                                            <span class="label-warning label-join" v-if="resRoom.stella_updated_at !== null">
                                                (Stella)
                                            </span>
                                        </span>
                                    </td>
                                    <td class="clasi_resAdultsNum">
                                        @{{ resRoom.adult_num + resRoom.extra_num }}
                                    </td>
                                    <td class="clasi_resChildsNum">
                                        @{{ resRoom.child_num }}
                                    </td>
                                    <td class="clasi_resRoomPrince">
                                        @{{ resRoom.total_amount + resRoom.total_tax_and_services_amount }}
                                    </td>
                                    <td class="clasi_resRoomPrince">
{{--                                        <a class="clasi_botonCancelar" v-if="resRoom.status !== 0" href="javascript:void(0)"--}}
{{--                                           @click="cancelReservationRoom(edit_reservation.reservation.file_code, edit_reservation.id, resRoom.id)">--}}
{{--                                            {{trans('reservations.label.cancel')}}--}}
{{--                                        </a>--}}

                                        <a class="clasi_botonCancelar" v-if="resRoom.status !== 0" href="javascript:void(0)"
                                           @click="will_cancel_reservation_room(edit_reservation.reservation.file_code, edit_reservation.id, resRoom.id)">
                                            {{trans('reservations.label.cancel')}}
                                        </a>

                                        <span v-else>
                                            <button type="button" class="alert alert-warning">
                                                {{trans('reservations.label.cancelled')}}
                                            </button>

                                            <table v-if="resRoom.stella_updated_at === null">
                                                <tr>
                                                    <td colspan="2">@{{ resRoom.updated_at  | formatDate }}</td>
                                                </tr>
                                                <tr v-if="resRoom.penality_included">
                                                    <td>Penalidad:</td>
                                                    <td>@{{ resRoom.penality_amount }}</td>
                                                </tr>
                                                <tr v-else>
                                                    <td colspan="2">Sin Penalidad</td>
                                                </tr>
                                            </table>

                                        </span>

                                    </td>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>










