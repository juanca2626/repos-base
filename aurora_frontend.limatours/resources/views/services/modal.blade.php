<div id="modal-detail-servicios" class="modal fade show modal-servicios" tabindex="-1" role="dialog"
     aria-labelledby="myLargeModalLabel" aria-hidden="true" ref="vuemodal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button id="modal_detail_close" type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" style="border-radius: 10px!important;">
                <div class="service-modal-container">
                    {{--Itinerario--}}
                    <div v-if="view == 'itinerary'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title" id="itinerary">{{trans('service.label.itinerary')}}</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.descriptions.itinerary">
                                    <div v-for="text in service_detail_selected.descriptions.itinerary">
                                        <div class="font-weight-bold mb-3">{{trans('service.label.day')}}
                                            @{{text.day}}
                                        </div>
                                        <p class="text-justify" v-html="text.description"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6" v-if="service_detail_selected.restrictions.length > 0">
                                <h2 class="modal-title mt-2">{{trans('service.label.restrictions')}}</h2>
                                <hr>
                                <ul style="padding-left: 15px;">
                                    <li v-html="restriction.name"
                                        v-for="restriction in service_detail_selected.restrictions"></li>
                                </ul>
                            </div>
                            <div class="col-md-6" v-if="service_detail_selected.experiences.length > 0">
                                <h2 class="modal-title mt-2">{{trans('service.label.experiences')}}</h2>
                                <hr>
                                <div class="badge mr-2" v-for="experience in service_detail_selected.experiences"
                                     style="color: #fff;width: auto;"
                                     :style="'background-color:' + experience.color +' '">
                                    @{{ experience.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Horarios / Restricciones--}}
                    <div v-if="view == 'schedule'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title"
                                    id="schedule">{{trans('service.label.schedules_restrictions')}}</h2>
                                <hr>
                                <div class="service-seleccion">
                                    <ul class="summary-list">
                                        <li>
                                            <p class="font-weight-bold">{{trans('global.label.monday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.monday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.monday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                    <i class="far fa-clock"></i> @{{ schedule.monday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p class="font-weight-bold">{{trans('global.label.tuesday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.tuesday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.tuesday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                   <i class="far fa-clock"></i> @{{ schedule.tuesday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p>{{trans('global.label.wednesday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.wednesday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.wednesday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                    <i class="far fa-clock"></i> @{{ schedule.wednesday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p>{{trans('global.label.thursday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.thursday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.thursday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                    <i class="far fa-clock"></i> @{{ schedule.thursday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p>{{trans('global.label.Friday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.friday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.friday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                    <i class="far fa-clock"></i> @{{ schedule.friday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p>{{trans('global.label.saturday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.saturday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.saturday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                   <i class="far fa-clock"></i>  @{{ schedule.saturday }}<br>
                                </span>
                                        </li>
                                        <li>
                                            <p>{{trans('global.label.sunday')}}</p>
                                            <i v-if="service_detail_selected.operations.days.sunday"
                                               class="fas fa-check operation-true"></i>
                                            <i v-if="!service_detail_selected.operations.days.sunday"
                                               class="fas fa-times operation-false"></i>
                                            <br>
                                            <span v-for="schedule in service_detail_selected.operations.schedule">
                                    <i class="far fa-clock"></i> @{{ schedule.sunday }}<br>
                                </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row" v-if="service_detail_selected.operations.turns.length > 0">
                            <div class="col-md-12 mt-3"
                                 v-if="service_detail_selected.operations.turns[0][0].departure_time !== '00:00:00'">

                                <div class="col-md-12" v-for="(turn, t) in service_detail_selected.operations.turns"
                                     style="margin-top: 20px;">
                                    <h2>{{trans('service.label.operability')}} <strong
                                            v-if="service_detail_selected.operations.turns.length>1">@{{ t+1 }}</strong>
                                    </h2>
                                    <hr>
                                    <p>{{trans('service.label.departure_time')}}: @{{ turn[0].departure_time }}
                                        @{{ turn[0].shifts_available }}
                                    </p>
                                    <div class="service-seleccion"
                                         v-if="turn[0].detail.length > 0">
                                        <div v-for="day in turn" v-if="day.detail.length>0">
                                            <p><strong>Día @{{ day.day}}</strong></p>
                                            <p v-for="detail in day.detail" class="mb-0">
                                                @{{ detail.start_time }} - @{{ detail.end_time }} / @{{ detail.detail }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    {{--Incluye / No incluye--}}
                    <div v-if="view == 'inclusions'">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-center" v-if="service_detail_selected.inclusions.length==0">
                                    {{ trans('quote.label.empty') }}
                                </div>
                                <div v-for="(day,index) in service_detail_selected.inclusions">
                                    <p class="modal-title" style="font-weight: bold !important;" id="itinerary">{{trans('global.label.day')}}
                                        @{{ index + 1 }}</p>
                                    <hr>
                                    <div v-if="service_detail_selected.inclusions.length > 0">
                                        <strong v-if="day.include && day.include.length > 0">{{trans('service.label.includes')}}:</strong>
                                        <div class="badge mr-2"
                                             :class="{'badge-secondary':include.available_days.available_day,'badge-warning': !include.available_days.available_day}"
                                             v-for="(include,index_include) in day.include">
                                            <span v-html="include.name"></span>
                                            <span class="text-danger" v-if="!include.available_days.available_day">
                                                ({{trans('service.label.inclusion_not_operate')}})
                                            </span>
                                        </div>
                                    </div>
                                    <br v-if="day.include && day.include.length > 0">
                                    <div v-if="service_detail_selected.inclusions.length > 0">
                                        <strong v-if="day.no_include && day.no_include.length > 0">{{trans('service.label.not_include')}}:</strong>
                                        <div class="badge badge-secondary mr-2" v-for="no_include in day.no_include">
                                            <span v-html="no_include.name"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Summary--}}
                    <div v-if="view == 'summary'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title" id="summary">{{trans('service.label.summary')}}</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.descriptions.summary">
                                    <p v-html="service_detail_selected.descriptions.summary"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Notes--}}
                    <div v-if="view == 'notes'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title" id="summary">Remarks</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.notes != null">
                                    <p style="white-space: pre-wrap;" v-html="service_detail_selected.notes"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="view == 'real_notes'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title" id="real_notes">{{trans('service.label.summary')}}</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.real_notes != null">
                                    <div style="white-space: pre-wrap;" v-html="service_detail_selected.real_notes"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="view == 'real_notes_commercial'">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="modal-title" id="real_notes">{{trans('service.label.summary')}}</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.real_notes_commercial != null">
                                    <p style="white-space: pre-wrap;"  v-html="service_detail_selected.real_notes_commercial"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--Add supplements to service shared--}}
                    <div v-if="view == 'addSupplementsService'">
                        <div class="text-center">
                            <div>
                                <img src="/images/shopping-plus.png" alt="shopping-cart" width="70px">
                                <h2 class="title-supplements p-4"
                                    id="itinerary"
                                    style="font: caption">{{trans('service.label.add_supplement_to_reservation')}}</h2>
                            </div>
                            <div class="list-supplements">
                                <form id="main" v-cloak>
                                    <p class="text-left txt-supplements">
                                        {{trans('service.label.select_add_to_your_reservation')}}:
                                    </p>
                                    <div>
                                        <ul>
                                            <li v-for="(supplement_optional,index) in service_detail_selected.supplements.optional_supplements"
                                                :class="{ 'active': supplement_optional.selected}"
                                                class="justify-content-between list-sup">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                   :id="'sup_'+index"
                                                                   v-model="supplement_optional.selected"
                                                                   @change="selectedSupplement(supplement_optional,index)">
                                                            <label class="form-check-label" :for="'sup_'+index">@{{supplement_optional.name}}</label>
                                                        </div>
                                                        <span v-for="day_charge in supplement_optional.days.charge"
                                                              class="mr-2 float-left">
                                                            <i class="fas fa-calendar-check ml-1"></i> @{{ day_charge.day }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="row">
                                                            <div class="col-md-12 p-0 mb-3">
                                                            <span class="ml-5"> USD $ @{{ supplement_optional.rate.price_per_adult }} x
                                                                <i class="fas fa-male"></i>
                                                            </span>
                                                            </div>
                                                            <div class="col-md-12 p-0"
                                                                 v-if="supplement_optional.rate.price_per_child > 0">
                                                            <span class="ml-5"> USD $ @{{ supplement_optional.rate.price_per_child }} x
                                                                <i class="fas fa-child"></i>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row" v-show="supplement_optional.selected">
                                                    <div class="col-md-3">
                                                        <label class="ml-2 text-muted"
                                                               for=""> {{trans('service.label.for_how_many_adults')}}
                                                            :</label>
                                                        <select class="form-control"
                                                                @change="filterSupplement(supplement_optional,index)"
                                                                v-model="parameters_supplements[index].adults">
                                                            <option
                                                                v-for="num_adults in service_detail_selected.quantity_adult"
                                                                :value="num_adults"
                                                                :disabled="supplement_optional.charge_all_pax">@{{
                                                                num_adults }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3"
                                                         v-if="service_detail_selected.quantity_child > 0">
                                                        <label class="ml-2 text-muted"
                                                               for=""> {{trans('service.label.for_how_many_children')}}
                                                            :</label>
                                                        <select class="form-control"
                                                                @change="filterSupplement(supplement_optional,index)"
                                                                v-model="parameters_supplements[index].child">
                                                            <option
                                                                v-for="(num_child,index) in (service_detail_selected.quantity_child + 1)"
                                                                :value="index"
                                                                :disabled="supplement_optional.charge_all_pax">@{{
                                                                index }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6"
                                                         v-show="supplement_optional.days.charge.length > 0 || supplement_optional.days.not_charge.length > 0">
                                                        <label class="ml-2 text-muted">
                                                            {{trans('service.label.for_what_dates_do_you_want_it')}}:
                                                        </label>
                                                        <v-select multiple v-model="parameters_supplements[index].dates"
                                                                  label="day"
                                                                  @input="filterSupplement(supplement_optional,index)"
                                                                  :options="supplement_optional.days.not_charge"
                                                                  :disabled="supplement_optional.days.not_charge.length === 0"
                                                                  placeholder="Selecciona las fechas"
                                                                  class="form-control"/>
                                                    </div>
                                                </div>


                                            </li>
                                        </ul>
                                    </div>

                                    <div class="total mt-2">
                                        {{trans('service.label.total_supplements')}}
                                        :<span>USD $ @{{totalSupplement()}}</span>
                                    </div>
                                    <div class="total mt-2">
                                        {{trans('service.label.total_service_supplements')}}: <span> USD $ @{{totalServiceSupplement()}}</span>
                                    </div>
                                </form>
                            </div>
                            <div class="d-flex">
                                <button class="btn btn-sm btn-seleccionar mt-3 mr-1"
                                        @click="omitir(service_detail_selected)">
                                    {{trans('global.label.omit')}}
                                </button>
                                <button class="btn btn-sm btn-primary mt-3 ml-1"
                                        @click="addCart(service_detail_selected)" :disabled="totalSupplement() == 0">
                                    {{trans('global.label.add')}}
                                </button>
                            </div>

                        </div>
                    </div>

                    <div v-if="view == 'price'">
                        <div class="row">
                            <div class="col-md-12">
                                {{-- <h2 class="modal-title" id="itinerary">Precio</h2>
                                <hr>
                                <div class="service-seleccion">
                                    <div>
                                        <div class="font-weight-bold mb-3">
                                           Precio adulto : USD 888.10
                                        </div>
                                    </div>
                                </div> --}}


                                <div>
                                    <div class="titleModalTarifa">
                                        @{{ service_detail_selected.name }}
                                        {{-- <span>Rate : @{{ service_detail_selected.rate.name }}</span>  --}}
                                        <span> @{{ service_detail_selected.date_reserve }} <br></span>
                                    </div>
                                    <table width="100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Adults(@{{ service_detail_selected.quantity_adult }})</th>
                                                {{-- <template v-if="service_detail_selected.import_childres && service_detail_selected.import_childres.length>0">
                                                    <th class="text-center" v-for="children in service_detail_selected.import_childres" >Children(@{{ children.age }} a)</th>
                                                </template>
                                                <template v-if="service_detail_selected.import_childres.length == 0">
                                                    <th class="text-center">Children(0)</th>
                                                </template> --}}
                                                <th class="text-center">Children(@{{ service_detail_selected.quantity_child }})</th>
                                                <th class="text-center no-border">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">@{{ service_detail_selected.total_amount_adult }}</td>
                                                {{-- <template v-if="service_detail_selected.import_childres && service_detail_selected.import_childres.length>0">
                                                    <td class="text-center" v-for="children in service_detail_selected.import_childres"  > @{{ children.price }} </td>
                                                </template>
                                                <template v-if="service_detail_selected.import_childres.length == 0">
                                                    <td class="text-center"> 0.00 </td>
                                                </template> --}}
                                                <td class="text-center">@{{ service_detail_selected.total_amount_child }}</td>
                                                <td class="text-center no-border">@{{ service_detail_selected.sub_total }}</td>
                                            </tr>
                                            <tr v-if="service_detail_selected.alerta_change_children_ages" >
                                                <td :colspan="service_detail_selected.base_pax + 1" style="font-size: 10px!important; color: red;">
                                                    (*) Hubo un cambio automático en la tarifa de niños, se cambió por la tarifa del adulto porque la edad del niño seleccionado es mayor al máximo configurado en el servicio
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="100%">
                                        <tbody>
                                            <tr class="tarifa_total">
                                                <td colspan="5">{{ trans('hotel.label.subtotal') }} $<b> @{{ service_detail_selected.sub_total }}</b></td>
                                            </tr>
                                            <tr class="tarifa_total">
                                                <td colspan="5">{{ trans('hotel.label.taxes') }} / {{ trans('hotel.label.services') }} $<b>@{{ service_detail_selected.total_taxes }}</b></td>
                                            </tr>
                                            <tr class="tarifa_total">
                                                <td colspan="5">Total $<b>@{{ service_detail_selected.total_amount }}</b></td>
                                            </tr>
                                            <tr class="tarifa_total">
                                                <td colspan="5">{{trans('service.label.cost_per_passenger')}} $<b>@{{ service_detail_selected.price_per_person }}</b></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                </div>




                            </div>
                        </div>

                    </div>

                     <div v-if="view == 'political'">
                        <div class="row" >
                            <div class="col-md-12">
                                <h2 class="modal-title" id="summary"> {{ trans('hotel.label.policy_details') }}</h2>
                                <hr>
                                <div class="service-seleccion" v-if="service_detail_selected.rate.rate_plans[0].political != null" style="display: block;">
                                     <p class="policies-title">
                                        <strong>{{ trans('hotel.label.political_cancellation') }}</strong>
                                        </p>

                                        <div v-if="service_detail_selected.rate.rate_plans[0].political.cancellation.penalties.length">
                                            <div v-for="penalty in service_detail_selected.rate.rate_plans[0].political.cancellation.penalties" :key="penalty.penalty_code">
                                                <p class="text-danger">
                                                @{{ penalty.message }}
                                                </p>
                                                <p>
                                                {{ trans('service.label.penalty_period') }}:
                                                <strong>@{{ penalty.apply_date }} - @{{ penalty.expire_date }}</strong>
                                                </p>
                                                <p>
                                                {{ trans('service.label.penalty_amount') }}:
                                                <strong>USD @{{ penalty.penalty_price }}</strong>
                                                </p>
                                            </div>
                                        </div>


                                        <!-- Notas -->
                                        <div v-if="service_detail_selected.rate.rate_plans[0].political.description && service_detail_selected.rate.rate_plans[0].political.description.value">
                                        <p class="policies-title mt-3">
                                            <b>{{ trans('hotel.label.notes') }}</b>
                                        </p>
                                        <p>@{{ service_detail_selected.rate.rate_plans[0].political.description.value }}</p>
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
