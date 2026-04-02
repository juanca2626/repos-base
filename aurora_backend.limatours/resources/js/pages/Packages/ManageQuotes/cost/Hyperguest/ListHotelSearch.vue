<template>
    <div>
        <div class="form-row">
            <h5>
                <font-awesome-icon :icon="['fas', 'bars']"/>
                Resultados de búsqueda:
            </h5>
        </div>

        <div class="form-row" v-if="!hotels">
            <label>Ningún resultado en esta categoría</label>
        </div>

        <div v-else>
            <div :class="'col-12 row_' + (hkey % 2)" v-for="(hotel, hkey) in hotels" :key="hkey">
                <div>
                    <div class="accordion" @click="showContentHotel(hotel)">
                        <h5 style="padding: 7px 7px 0;" class=" d-flex justify-content-between">
                            <strong>
                                <font-awesome-icon :icon="['fas', 'hotel']"/>
                                {{ hotel.name }} </strong>
                                <font-awesome-icon v-if="!(hotel.viewContent) && hotel.rooms.length>0"
                                                    class="right" :icon="['fas', 'plus']"/>
                                <font-awesome-icon v-if="hotel.viewContent && hotel.rooms.length>0"
                                                    class="right" :icon="['fas', 'minus']"/>
                        </h5>
                    </div>
                </div>
                <hr>
                <div class="col-12" v-show="hotel.viewContent">
                    <div v-for="(room, rkey) in hotel.rooms">
                        <div class="rooms-table row canSelectText">
                            <div class="col-4 my-auto">
                                <strong>Nombre {{ hotel.id }}: </strong>{{ room.name }}<br>
                                <strong>Descripción: </strong>{{ room.name }}
                            </div>
                            <div class="col-8 my-auto">
                                <div v-for="(rate, raKey) in room.rates" :class="'col-12 rateRow rateChoosed_' + checkboxs[hotel.id + '_'+ rate.ratePlanId +'_'+ room.room_id]" v-if="rate.rate.length > 0">
                                    <label style="display: block;" :for="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ room.room_id">
                                        <strong>
                                            <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                            <span
                                                v-if="rate!=null">{{ rate.name }}:</span>
                                            <span v-if="rate==null">---</span>
                                        </strong>
                                        <b-form-checkbox
                                            style="float: right;"
                                            :id="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ room.room_id"
                                            :name="'checkbox_' + hotel.id + '_'+ rate.ratePlanId +'_'+ room.room_id"
                                            v-model="checkboxs[hotel.id + '_'+ rate.ratePlanId +'_'+ room.room_id]"
                                            @change="selectRate(catKey, hotel , room, rate)"
                                            :disabled="loading"
                                            >
                                        </b-form-checkbox>
                                    </label>

                                    <div style="margin-left: 30px;">
                                        <span v-if="rate.available > 0"><font-awesome-icon
                                            :icon="['fas', 'check-circle']"/></span>
                                        <span v-if="rate.available <= 0"><font-awesome-icon
                                            :icon="['fas', 'times-circle']"/></span>
                                        {{ rate.amount_days[0].date | formatDate }}
                                        <strong>$ {{ getBestPrice(rate) | formatPrice }} </strong>
                                        <span v-if="rate.amount_days.length > 1">
                                            <a href="javascript:;" v-show="!(rate.showAllRates)"
                                                @click="toggleViewRates(rate)"><font-awesome-icon
                                                :icon="['fas', 'plus']"/></a>
                                            <a href="javascript:;" v-show="rate.showAllRates"
                                                @click="toggleViewRates(rate)"><font-awesome-icon
                                                :icon="['fas', 'minus']"/></a>
                                        </span>
                                    </div>
                                    <div style="margin-left: 30px;" v-for="(amount_day, aKey) in rate.amount_days" v-show="rate.showAllRates">
                                        <span v-if="aKey > 0">
                                            <span><font-awesome-icon
                                                :icon="['fas', 'check-circle']"/></span>
                                            <!-- <span><font-awesome-icon :icon="['fas', 'times-circle']"/></span> -->
                                            {{ amount_day.date | formatDate }}
                                            <strong>$ <span v-if="amount_day.total_adult_base"> {{ amount_day.total_adult_base | formatPrice }} </span></strong>
                                        </span>
                                    </div>
                                    <!-- <div style="margin-left: 30px;"
                                        v-for="(rateDetail, rdKey) in rate.rate"
                                        v-show="rate.showAllRates || rdKey === 0">
                                        <span v-if="rdKey > 0 || (rate.rate && rate.rate.length === 1)">
                                            {{ rateDetail.quantity_adults }} adultos -
                                            <strong>$ {{ rateDetail.total_amount_base | formatPrice }} </strong>
                                            (Total base)
                                        </span>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                        <hr v-if="(rkey + 1 ) < hotel.rooms.length">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

export default {
    components: {
        BFormCheckbox
    },
    props: {
        hyperguestData: Object,
        category: Object,
        date_from: String,
        date_to: String,
        ubigeo: Object,
        deleteItems: Object,
        loading: Boolean,
    },
    data() {
        return {
            hotels: [],
            selectedRates: {},
            checkboxs: [],
        }
    },
    mounted() {
        this.processHotelData();
    },
    methods: {
        processHotelData() {

            if (this.hyperguestData && typeof this.hyperguestData === 'object') {
                // Convertir el objeto de hoteles en un array
                this.hotels = Object.values(this.hyperguestData).map(hotel => ({
                    ...hotel,
                    viewContent: false,
                    // Asegurar que rooms tenga la estructura esperada
                    rooms: hotel.rooms ? hotel.rooms.map(room => ({
                        ...room,
                        // Agregar showAllRates a cada rate si no existe
                        rates: room.rates ? room.rates.map(rate => ({
                            ...rate,
                            showAllRates: rate.showAllRates || false
                        })) : []
                    })) : []
                }));

                this.hotels.forEach(e => {
                    e.rooms.forEach(r => {
                        r.rates.forEach(rt => {
                            this.checkboxs[`${e.id}_${rt.ratePlanId}_${r.id}`] = false;
                        });
                    })
                });
            }

            this.category.currentHotels.forEach((e) => {
                if(e.hyperguest_pull == 1){
                    e.service_rooms_hyperguest.forEach((s) => {
                        this.checkboxs[e.hotel.id + '_'+ s.rate_plan_id +'_'+s.room_id] = true;
                    });
                }
            });
        },

        processDeleteItems(data) {
            if(data.length > 0){
                data.forEach(e => {
                    this.checkboxs[e.hotel_id + '_' + e.rate_plan_id + '_' + e.room_id] = false
                });
                this.$forceUpdate();
            }
        },

        showContentHotel(hotel) {
            hotel.viewContent = !hotel.viewContent;
        },

        toggleViewRates(rate) {
            rate.showAllRates = !rate.showAllRates;
        },

        getBestPrice(rate) {
            if (!rate.rate || rate.rate.length === 0) return 0;
            // Encontrar el precio más bajo entre las opciones de tarifa
            return Math.min(...rate.rate.map(r => (parseFloat(r.total_amount_base) / r.amount_days.length) || 0));
        },

        selectRate(catKey, hotel, room, rate) {
            const rateId = rate.ratePlanId;
            const isSelected = this.checkboxs[`${hotel.id}_${rateId}_${room.room_id}`];
            console.log("rate", rate);
            // Obtener los detalles de la tarifa seleccionada
            const rateDetail = rate && rate.amount_days.length > 0 ? rate : null;

            console.log("rateDetail", rateDetail);
            // precio adults
            const price_adult = rateDetail.amount_days[0].total_adult_base || 0;

            console.log("price_adult", price_adult);

            if (rateDetail) {
                const selectedData = {
                    hotel: {
                        id: hotel.id,
                        name: hotel.name,
                        code: hotel.code,
                        country: hotel.country,
                        category: hotel.category
                    },
                    room: {
                        id: room.room_id,
                        name: room.name,
                        type: room.room_type,
                        max_adults: room.max_adults,
                        max_child: room.max_child,
                        description: room.description
                    },
                    rate: {
                        id: rateId,
                        name: rate.name,
                        plan: rate.name_commercial,
                        price_adult: price_adult,
                        price_amount_base: rateDetail.total_amount_base,
                        price_amount_total: rateDetail.total_amount,
                        quantity_adults: rateDetail.quantity_adults_total,
                        available: rate.available
                    },
                    dates: {
                        from: this.date_from,
                        to: this.date_to
                    },
                    category: this.category
                };

                if(!isSelected){
                    this.$emit('hotel-selected', selectedData);
                }else{
                    this.$emit('hotel-deselected', selectedData);
                }
            }
        },

        formatDate: function (_date) {
            if (_date == undefined) {
                // console.log('fecha no parseada: ' + _date)
                return
            }
            _date = _date.split('-')
            _date = _date[2] + '/' + _date[1] + '/' + _date[0]
            return _date
        },
    },
    filters: {
        formatPrice(price) {
            if (!price) return '0.00';
            return parseFloat(price).toFixed(2);
        }
    },
    watch: {
        hyperguestData: {
            handler(newData) {
                if (newData) {
                    this.processHotelData();
                    // Resetear selecciones cuando cambian los datos
                    this.selectedRates = {};
                }
            },
            deep: true
        },
        deleteItems: {
            handler(newData) {
                this.processDeleteItems(newData);
            }
        },
        category: {
            handler(newData) {
                console.log('El array category:', newData);
            }
        }
    }
}
</script>

<style lang="stylus">
    .rateChoosed_true {
        background: #deffe2;
        border: solid 1px #39573b;
    }

    .rateRow {
        border-radius: 3px;
        padding: 4px 0 4px 6px;
    }

    .bottom0 {
        padding-bottom: 0px !important;
        margin-bottom: 0px !important;
    }

    hr {
        margin: 0 !important;
    }

    .row_0 {
        background: #e3fcff;
        padding: 5px;
        border-radius: 4px;
        line-height: 22px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 13px;
        margin-bottom: 10px !important
    }

    .row_1 {
        background: #ffeeff;
        padding: 5px;
        border-radius: 4px;
        line-height: 22px;
        color: #615555;
        border: solid 1px #8a9b9b;
        font-size: 13px;
        margin-bottom: 10px !important;
    }

    .reEntry {
        float: right;
        border: solid 1px #3ea662;
        border-radius: 5px;
        margin: 5px;
    }

    .switch {
        margin-top: 11px;
        margin-bottom: -6px;
    }

    .sectionResult {
        padding: 14px;
        line-height: 29px;
        border: solid 1px #ffc500;
    }

    .marginB20 {
        margin-bottom: 20px;
    }

    .sectionShare {
        padding: 14px;
        line-height: 29px;
        background: #afecff;
        border: solid 1px #82adeb;
    }

    button:not(:disabled), [type=button]:not(:disabled), [type=reset]:not(:disabled), [type=submit]:not(:disabled) {
        outline: none;
    }

    .panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.2s ease-out;
    }
</style>
