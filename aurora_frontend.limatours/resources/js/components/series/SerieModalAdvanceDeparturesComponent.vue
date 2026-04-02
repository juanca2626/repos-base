<template>
    <div class="modal-rango">
        <div class="modal fade modal-general modal-info  ranks-dates__modal-content " id="modal-advance-departures">
            <div class="modal-dialog modal-content__container" role="document">
                <div class="modal-content container-content">
                    <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                    <div class="modal-body" style="padding: 0rem 1rem 1.5rem!important;">
                        <div class="modal-title" style="text-align: center;">Agregar rango de fechas</div>
                        <br>
                        <div class="modal-info-editar">
                            <div class="d-flex">
                                <div class="container-content__tabs tab-frame">
                                    <input class="" type="radio" v-model="radio_range_by" value="days" name="radio_range_by" id="tab1">
                                    <label for="tab1">
                                        Por días
                                    </label>

                                    <input class="" type="radio" v-model="radio_range_by" value="months" name="radio_range_by" id="tab2">
                                    <label for="tab2">
                                        Por meses
                                    </label>

                                    <div class="tabs-tab1 tab">
                                        <div class="tabs1__group-input">
                                            <div class="content-header__datepicker">
                                                <div class="datepicker-left">
                                                    <date-picker :config="optionsR" class="datepicker-left__start mr-1" v-model="date_from" placeholder="31/09/2021"></date-picker>
                                                    <i class=" icon-calendar-confirm icon-resource icon-serie-fecha1 icon-serie-fecha1--left"></i>
                                                </div>
                                                <div class="datepicker-right">
                                                    <date-picker :config="optionsR" class="datepicker-right__end" v-model="date_to" placeholder="31/09/2021"></date-picker>
                                                    <i class="icon-calendar-confirm icon-resource icon-serie-fecha1 icon-serie-fecha1--right"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tabs1__group-days">
                                            <p class="group-days__detail">
                                                <span class="consider">Considerar</span>
                                                <!-- DAY SELECT ADD CLASS day--select -->
                                                <span class="day" :class="{'day--select':day.choose}" @click="day.choose=!(day.choose)" v-for="day in days">{{ day.name }}</span>
                                                <!-- ALL SELECT ADD CLASS all--select -->
                                                <span class="all cursor-pointer" @click="choose_all_days()">Todos</span>
                                            </p>
                                        </div>
                                        <div class="tabs1__group-message">
                                            <span class="group-message__top">Se agregarán <span class="top-bold">5 salidas:</span></span>
                                            <span class="group-message__bottom">Los martes entre <span class="bottom-bold">1 Nov 2021 - 31 Nov 2021</span></span>
                                        </div>
                                        <!--                                        <div class="tabs1__group-actions">-->
                                        <!--                                            <a class="group-actions__cancel" data-dismiss="modal">Cancelar</a>-->
                                        <!--                                            <a class="group-actions__ready" data-dismiss="modal">Listo</a>-->
                                        <!--                                        </div>-->
                                    </div>
                                    <div class="tabs-tab2 tab">
                                        <div class="tabs-tab2__date">
                                            <select class="custom-select" id="custom-select6">
                                                <option value ="">2021</option>
                                                <option value ="">2022</option>
                                                <option value ="">2023</option>
                                            </select>
                                        </div>
                                        <div class="tabs-tab2__include">
                                            <div class="include-date">
                                                <span class="include-txt">Incluir Mes</span>
                                                <div class="include-months">
                                                    <span class="include-months__month">Ene</span>
                                                    <span class="include-months__month">Feb</span>
                                                    <span class="include-months__month">Mar</span>
                                                    <span class="include-months__month">Abr</span>
                                                    <span class="include-months__month">May</span>
                                                    <span class="include-months__month">Jun</span>
                                                    <span class="include-months__month">Jul</span>
                                                    <span class="include-months__month">Ago</span>
                                                    <span class="include-months__month">Set</span>
                                                    <span class="include-months__month">Oct</span>
                                                    <span class="include-months__month">Nov</span>
                                                    <span class="include-months__month">Dic</span>
                                                    <span class="include-months__month include-months__month--all">todos</span>
                                                </div>
                                            </div>
                                            <div class="include-filter">
                                                <span class="include-filter__txt">Filtrar</span>
                                                <select class="custom-select" id="custom-select7">
                                                    <option value ="">Fechas</option>
                                                    <option value ="">Días</option>
                                                </select>
                                            </div>

                                            <div class="include-consider">
                                                <span class="include-consider__txt">Considerar</span>
                                                <div class="include-consider__day">
                                                    <div class="span-day">1</div>
                                                    <div class="span-day">2</div>
                                                    <div class="span-day">3</div>
                                                    <div class="span-day">4</div>
                                                    <div class="span-day">5</div>
                                                    <div class="span-day">6</div>
                                                    <div class="span-day">7</div>
                                                    <div class="span-day">8</div>
                                                    <div class="span-day">9</div>
                                                    <div class="span-day">10</div>
                                                    <div class="span-day">11</div>
                                                    <div class="span-day">12</div>
                                                    <div class="span-day">13</div>
                                                    <div class="span-day">14</div>
                                                    <div class="span-day">15</div>
                                                    <div class="span-day">16</div>
                                                    <div class="span-day">17</div>
                                                    <div class="span-day">18</div>
                                                    <div class="span-day">19</div>
                                                    <div class="span-day">20</div>
                                                    <div class="span-day">21</div>
                                                    <div class="span-day">22</div>
                                                    <div class="span-day">23</div>
                                                    <div class="span-day">24</div>
                                                    <div class="span-day">25</div>
                                                    <div class="span-day">26</div>
                                                    <div class="span-day">27</div>
                                                    <div class="span-day">28</div>
                                                    <div class="span-day">29</div>
                                                    <div class="span-day">30</div>
                                                    <div class="span-day">31</div>
                                                    <div class="span-day span-day--all">todas</div>
                                                </div>
                                            </div>
                                            <div class="consider-message">
                                                <p class="consider-message__txt">Se agregarán <span class="txt-bold">365 salidas.</span></p>
                                                <span class="consider-message__all">Todos los días del 2021</span>
                                            </div>
                                            <!--                                            <div class="tabs2__group-actions">-->
                                            <!--                                                <a class="group-actions__cancel" data-dismiss="modal">Cancelar</a>-->
                                            <!--                                                <a class="group-actions__ready" data-dismiss="modal">Listo</a>-->
                                            <!--                                            </div>-->
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex" style="margin-top: 5px;">
                            <button :disabled="loading" class="button-cancelar cancelar-info" type="button" data-dismiss="modal">{{ translations.label.cancel }}</button>
                            <button :disabled="loading" class="button-cancelar button-actualizar" @click="save" type="button">Listo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
<script>
    import DatePicker from 'vue-bootstrap-datetimepicker/src/component'
    export default {
        components: { DatePicker },
        props: ['data'],
        data: () => {
            return {
                lang: '',
                loading: false,
                baseExternalURL: window.baseExternalURL,
                translations: {
                    label : {},
                    messages : {},
                    validations : {},
                },
                optionsR: {
                    format: 'DD/MM/YYYY',
                    useCurrent: false,
                    minDate: moment().format('YYYY-MM-DD')
                },
                radio_range_by : "days",
                date_from:"",
                date_to:"",
                days:[
                    {
                        name : "L",
                        choose : false
                    },
                    {
                        name : "M",
                        choose : false
                    },
                    {
                        name : "X",
                        choose : false
                    },
                    {
                        name : "J",
                        choose : false
                    },
                    {
                        name : "V",
                        choose : false
                    },
                    {
                        name : "S",
                        choose : false
                    },
                    {
                        name : "D",
                        choose : false
                    },
                ],
                months:[],
                number_days:[],
                weeks:[],
                years:[],
            }
        },
        created: function () {
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
        },
        computed: {

        },
        methods: {
            load() {
                this.translations = this.data.translations
            },
            choose_all_days(){

            },
            save(){

                // if( this.form.name === '' || this.form.date_start === '' ){
                //     this.$toast.warning(this.translations.messages.complete_info, {
                //         position: 'top-right'
                //     })
                //     return
                // }
                //
                // this.loading = true
                //
                // axios.post(
                //     baseExternalURL + 'api/series', this.form
                // )
                //     .then((result) => {
                //         if( result.data.success ){
                //             this.$toast.success(this.translations.messages.saved_correctly, {
                //                 position: 'top-right'
                //             })
                //             if( this.action_after === 'go_edit' ){
                //                 window.location = '/serie/' + btoa( result.data.serie_id )
                //             } else {
                //                 this.$parent._closeModal()
                //                 this.$parent.search()
                //             }
                //         } else{
                //             this.$toast.error('Error', {
                //                 position: 'top-right'
                //             })
                //         }
                //         this.loading = false
                //     })
                //     .catch((e) => {
                //         console.log(e)
                //     })
            },
        }
    }
</script>
<style>

</style>
