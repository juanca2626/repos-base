
<template>
    <div>
        <div class="modal modal--cotizacion" id="modal-accommodation" tabindex="-1" role="dialog" style="overflow: scroll;">
            <div class="modal-dialog modal--cotizacion__document" role="document">
                <div class="modal-content modal--cotizacion__content">
                    <div class="modal-header">
                        <button class="close" type="button" v-on:click="closeModal('modal-accommodation', true)" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal--cotizacion__header">
                            <h3 class="modal-title"><b>Acomodo general de pasajeros</b></h3>
                        </div>
                        <div class="modal--cotizacion__body">
                            <div class="alert alert-warning" v-if="file.flag_hotel > 0">Ya existe información asignada al acomodo de los pasajeros en los hoteles registrados.</div>
                            <div class="alert alert-warning" v-if="loading">Cargando..</div>
                            <div class="d-block" v-if="!loading">
                                <div class="row" style="align-items: flex-start;">
                                    <div class="col-md-12" style="margin-bottom: 5px;">
                                        <b>Asignar en:</b>
                                    </div>
                                    <div class="col-md-12">
                                        <button v-for="(hotel,h) in hotels" @click="checkHotel(h)"
                                                class="btn btn-danger" type="submit" v-if="!loading"
                                                style="float: left; margin-right: 5px; margin-bottom: 5px; ">
                                            <i v-if="hotel.check" class="fas fa-check-square"></i>
                                            <i v-if="!hotel.check" class="fas fa-square"></i>
                                            {{ hotel.descri }} <br />
                                            <small style="margin-right:3px;"
                                                   v-if="hotel[type] > 0"
                                                   v-for="(type, t) in types_room">
                                                * {{ hotel[type] }} {{ type }}
                                            </small>
                                        </button>
                                    </div>
                                </div>
                                <div class="row" style="align-items: flex-start;">
                                    <div class="col-lg-3">
                                        <h4 style="margin: 15px 0;">Pasajeros</h4>
                                        <div class="alert alert-warning" v-if="loading_passengers">Cargando..</div>
                                        <draggable class="list-group"
                                                   v-if="!loading_passengers"
                                                   group="people"
                                                   v-model="all_passengers">
                                            <div class="list-group-item"
                                                 v-if="ignore.indexOf(passenger.nroite) === -1"
                                                 v-for="(passenger, p) in all_passengers">
                                                {{ passenger.nombre }} <b>({{ passenger.tipo }})</b>
                                            </div>
                                        </draggable>
                                    </div>
                                    <div class="col-lg-3">
                                        <h4 style="margin: 15px 0;">Hab. Simples</h4>
                                        <draggable class="list-group"
                                                   group="people"
                                                   style="min-height:30px;background-color:#f6f6f6;margin-bottom:2rem;margin-top:1rem;"
                                                   v-model="accommodation_simple">
                                            <div class="list-group-item"
                                                 v-for="(passenger, p) in accommodation_simple">
                                                {{ passenger.nombre }} <b>({{ passenger.tipo }})</b>
                                            </div>
                                        </draggable>
                                    </div>
                                    <div class="col-lg-3">
                                        <h4 style="margin: 15px 0;">Hab. Dobles</h4>
                                        <draggable class="list-group"
                                                   group="people"
                                                   style="min-height:30px;background-color:#f6f6f6;margin-bottom:2rem;margin-top:1rem;"
                                                   v-model="accommodation_doble">
                                            <div class="list-group-item"
                                                 v-for="(passenger, p) in accommodation_doble">
                                                {{ passenger.nombre }} <b>({{ passenger.tipo }})</b>
                                            </div>
                                        </draggable>
                                    </div>
                                    <div class="col-lg-3">
                                        <h4 style="margin: 15px 0;">Hab. Triples</h4>
                                        <draggable class="list-group"
                                                   group="people"
                                                   style="min-height:30px;background-color:#f6f6f6;margin-bottom:2rem;margin-top:1rem;"
                                                   v-model="accommodation_triple">
                                            <div class="list-group-item"
                                                 v-for="(passenger, p) in accommodation_triple">
                                                {{ passenger.nombre }} <b>({{ passenger.tipo }})</b>
                                            </div>
                                        </draggable>
                                    </div>
                                </div>

                                <div id="modalAlerta">
                                    <div class="group-btn">
                                        <button type="button"
                                                v-bind:disabled="loading_button || loading || loading_passengers"
                                                v-if="file.flag_hotel == 0"
                                                v-on:click="saveRoomsGeneral()"
                                                class="btn btn-primary">Guardar acomodo
                                        </button>
                                        <button type="button"
                                                v-bind:disabled="loading_button || loading || loading_passengers"
                                                v-if="file.flag_hotel > 0"
                                                v-on:click="showModal('modalAlertaPaxsSave')"
                                                class="btn btn-primary">Guardar acomodo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalAlertaPaxsSave" v-if="modal" tabindex="1" role="dialog" class="modal modal--cotizacion">
            <div role="document" class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center">
                            <div class="icon">
                                <i class="icon-alert-circle" v-if="!loading"></i>
                                <i class="spinner-grow" v-if="loading"></i>
                            </div>
                            <strong v-if="!loading">¿Está seguro de reemplazar el acomodo?</strong>
                            <strong v-if="loading">{{ translations.label.loading }}</strong>
                        </h4>
                        <p class="text-center" v-if="!loading"><strong>Ya existe información asignada al acomodo de los pasajeros en los hoteles registrados.</strong></p>
                        <div class="group-btn" v-if="!loading">
                            <button type="button" @click="saveRoomsGeneral()" data-dismiss="modal"
                                    class="btn btn-secondary">Reemplazar acomodo
                            </button>
                            <button type="button" @click="closeModal('modalAlertaPaxsSave')" data-dismiss="modal"
                                    class="btn btn-primary">Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: () => {
            return {
                nrofile: 0,
                file: {},
                ignore: [],
                modal: false,
                loading: true,
                loading_button: true,
                loading_passengers: true,
                all_passengers: [],
                accommodation_simple: [],
                accommodation_doble: [],
                accommodation_triple: [],
                hotels: [],
                types_room: [
                    'SGL',
                    'DBL',
                    'TPL'
                ],
                type_room: {},
                translations: {}
            }
        },
        created: function () {
            this.lang = localStorage.getItem('lang')

        },
        mounted: function() {

        },
        computed: {

        },
        methods: {
            searchHotels: function () {
                let vm = this
                vm.showModal('modal-accommodation')

                axios.get(baseExternalURL + 'api/files/' + this.nrofile + '?lang='+localStorage.getItem('lang'))
                // axios.get(baseExpressURL + 'api/v1/files/' + this.nrofile)
                    .then(response => {
                        // this.file = response.data.data[0]
                        this.file = this.translate_data_file(response.data.data)

                        axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/services')
                        // axios.get(baseExpressURL + 'api/v1/files/' + this.nrofile + '/services')
                            .then(response => {

                                this.services_ws = this.translate_data_services( response.data.file, response.data.data, 0 )

                                axios.post(baseURL + 'search_hotels', {
                                    lang: this.lang,
                                    services: this.services_ws,
                                    city: '',
                                    filters: ''
                                })
                                    .then((response) => {
                                        vm.$set(vm, 'hotels', response.data.hotels)
                                        vm.$set(vm, 'type_room', response.data.type_room)

                                        vm.loading = false
                                        vm.loading_button = false
                                        vm.modal = true

                                        vm.accommodation_simple = []
                                        vm.accommodation_doble = []
                                        vm.accommodation_triple = []

                                        vm.searchPassengers()
                                    })
                                    .catch(error => {
                                        this.$toast.error(this.translations.messages.internal_error, {
                                            position: 'top-right'
                                        })
                                    })
                            })
                            .catch(error => {
                                this.$toast.error(this.translations.messages.internal_error, {
                                    position: 'top-right'
                                })
                            })
                    })
                    .catch(error => {
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },

            translate_data_services(file_, services, full){

                let services_ = []

                services.forEach( (data)=>{
                    let data_ = {
                        id: data.id,
                        clase: data.classification_iso,
                        codsvs: data.code,
                        clasif: data.classification,
                        nroite: data.item_number,
                        itepaq: data.item_number_parent,
                        flag_acomodo: data.flag_accommodation,
                        nroref: file_.file_number,
                        cantid: data.total_rooms,
                        preped: data.code_request_book,
                        prefac: data.code_request_invoice,
                        prevou: data.code_request_voucher,
                        estado: data.status_ifx,
                        estado_hotel: data.status_hotel,
                        codcfm: data.confirmation_code,
                        desbas_inicial: data.base_name_initial,
                        infoad: data.additional_information,
                        canpax: data.total_paxs,
                        categoria_hotel: data.category_hotel_name,
                        anulado: data.number_annulments,
                        desbas: data.base_name_original,
                        relation: data.relation_nights,
                        razon: data.airline_name,
                        ciavue: data.airline_code,
                        nrovue: data.airline_number,
                        canadl: file_.adults,
                        canchd: file_.children,
                        caninf: file_.infants,
                        catser: data.category_code_ifx,
                        tipsvs: data.type_code_ifx,
                        bastar: data.base_code,
                        descri: data.description,
                        horin_prime: data.start_time,
                        descri_es: data.description_ES,
                        flag_es: data.description_ES_code,
                        descri_en: data.description_EN,
                        flag_en: data.description_EN_code,
                        descri_pt: data.description_PT,
                        flag_pt: data.description_PT_code,
                        descri_it: data.description_IT,
                        flag_it: data.description_IT_code,
                        ciuin: data.city_in_iso,
                        descri_ciudad: data.city_name,
                        descri_pais: data.country_name,
                        fecin: data.date_in,
                        horin: data.start_time,
                        ciuout: data.city_out_iso,
                        fecout: data.date_out,
                        horout: data.departure_time
                    }
                    services_.push(data_)
                } )

                return services_
            },
            showModal: function  (_modal) {
                setTimeout(function () {
                    $('#' + _modal).modal('show')
                }, 10)
            },
            closeModal: function (_modal, _close) {
                let vm = this
                $('#' + _modal).modal('hide')

                if(_close == true)
                {
                    setTimeout(function () {
                        vm.modal = false
                    }, 1000)
                }
            },
            setTranslations() {
                axios.get(baseURL+'translation/'+localStorage.getItem('lang')+'/slug/board').then((data) => {
                    this.translations = data.data
                })
            },
            checkHotel: function (_hotel) {
                this.hotels[_hotel].check = !this.hotels[_hotel].check
            },
            searchPassengers: function () {
                this.loading_passengers = true
                this.all_passengers = []
                this.ignore = []
                // axios.get(baseExpressURL + 'api/v1/passenger/conpax?nroref=' + this.nrofile)
                axios.get(baseExternalURL + 'api/files/' + this.nrofile + '/passengers')
                    .then(response => {
                        this.loading_passengers = false

                        if(response.data.data.length > 0)
                        {
                            this.all_passengers = this.translate_data_passengers(response.data.data)
                            localStorage.setItem('all_passengers', JSON.stringify(this.all_passengers))

                            this.fillAccommodation()
                        }
                    })
                    .catch(response => {
                        console.log(error)
                        this.$toast.error(this.translations.messages.internal_error, {
                            position: 'top-right'
                        })
                    })
            },

            translate_data_file(data){
                let data_ = {
                    id: data.id,
                    nroemp: 5,
                    identi: "R",
                    nroref: data.file_number,
                    nrores: data.reservation_number,
                    nropre: data.budget_number,
                    fecha: data.created_at,
                    codsec: data.sector_code,
                    grupo: data.group,
                    concta: data.sale_type,
                    tarifa: data.tariff,
                    moncot: data.currency,
                    succli: data.revision_stages,
                    codcli: data.client.code,
                    codven: data.executive_code,
                    codope: data.executive_code_sale,
                    solici: data.applicant,
                    refext: data.file_code_agency,
                    descri: data.description,
                    idioma: data.lang,
                    diain: data.date_in,
                    diaout: data.date_out,
                    canadl: data.adults,
                    canchd: data.children,
                    caninf: data.infants,
                    razon: data.client.name,
                    cuit: data.client.ruc,
                    coniva: data.use_invoice,
                    direcc: data.client.address,
                    codciu: data.client.city_code,
                    ciudad: data.client.city_name,
                    postal: data.client.postal_code,
                    provin: null,
                    pais: data.client.countries.translations[0].value,
                    telefo: data.client.phone,
                    cliopc: null,
                    razopc: null,
                    cuiopc: null,
                    ivaopc: null,
                    diropc: null,
                    codopc: null,
                    ciuopc: null,
                    posopc: null,
                    proopc: null,
                    paiopc: null,
                    telopc: null,
                    observ: data.observation,
                    nropax: data.total_paxs,
                    codgru: data.client.countries.iso_ifx,
                    operad: data.executive_code_process,
                    cotiza: data.have_quote,
                    vouche: data.have_voucher,
                    ticket: data.have_ticket,
                    factur: data.have_invoice,
                    status: data.status,
                    nrotot: data.total_paxs,
                    promos: data.promotion,
                    flag_hotel: data.total_accommodation,
                    piaced:"-"+data.client.general_markup+".00",
                    nroped:data.order_number,
                    tipoventa:data.sale_type,
                    razoncli:data.client.name
                }
                return data_
            },
            translate_data_passengers(passengers){

                let passengers_ = []

                passengers.forEach( (data)=>{
                    let data_ = {
                        id: data.id,
                        nroemp: 5,
                        identi: "R",
                        nroref: data.reservation.file_code,
                        nrosec: data.sequence_number,
                        nroord: data.order_number,
                        nropax: data.frequent,
                        nombre: data.name,
                        tipo: data.type,
                        sexo: data.genre,
                        fecnac: data.date_birth,
                        ciunac: data.city_iso,
                        nacion: data.country_iso,
                        tipdoc: data.doctype_iso,
                        nrodoc: data.document_number,
                        tiphab: data.suggested_room_type,
                        status: "OK",
                        correo: data.email,
                        celula: data.phone,
                        resmed: data.medical_restrictions,
                        resali: data.dietary_restrictions,
                        observ: data.notes
                    }
                    passengers_.push(data_)
                } )

                return passengers_
            },
            accommodationPassengers: function (_type, file, hotels) {

                if(hotels.length > 0)
                {
                    this.file = file
                    this.nrofile = file.nroref
                    this.hotels = hotels

                    this.loading = false
                    this.loading_button = false
                    this.modal = true

                    this.accommodation_simple = []
                    this.accommodation_doble = []
                    this.accommodation_triple = []

                    this.searchPassengers()
                    this.showModal('modal-accommodation')
                }
                else
                {
                    this.nrofile = file
                    this.searchHotels()
                }
            },
            fillAccommodation: function () {
                let vm = this

                setTimeout(function () {
                    vm.all_passengers.forEach((item, i) => {

                        console.log(item)

                        if(item.tiphab == 1)
                        {
                            vm.accommodation_simple.push(item)
                            vm.ignore.push(item.nroite)
                        }

                        if(item.tiphab == 2)
                        {
                            vm.accommodation_doble.push(item)
                            vm.ignore.push(item.nroite)
                        }

                        if(item.tiphab == 3)
                        {
                            vm.accommodation_triple.push(item)
                            vm.ignore.push(item.nroite)
                        }
                    })
                }, 10)

            },
            saveRoomsGeneral: function () {

                this.loading = true
                this.loading_button = true
                this.modal = ''

                // axios.post(baseExpressURL + 'api/v1/files/' + this.nrofile + '/rooms_pax_general', {
                axios.post(baseExternalURL + 'api/files/' + this.nrofile + '/accommodations/general', {
                    simple: this.accommodation_simple,
                    doble: this.accommodation_doble,
                    triple: this.accommodation_triple,
                    hotels: this.hotels
                })
                    .then(response => {

                        this.loading = false
                        this.loading_button = false
                        this.all_passengers = JSON.parse(localStorage.getItem('all_passengers'))

                        this.accommodation_simple = []
                        this.accommodation_doble = []
                        this.accommodation_triple = []

                        this.$toast.success('Pasajeros acomodados correctamente', {
                            position: 'top-right'
                        })

                        this.closeModal('modal-accommodation', true)

                        if(localStorage.getItem('search_passengers') == 1)
                        {
                            // this.$parent.searchPassengers()
                            this.$parent.orderServices()
                            localStorage.removeItem('search_passengers')
                            // this.$parent.searchPassengers()
                        }
                    })
                    .catch(e => {
                        console.log(e)
                    })
            },
        }
    }
</script>
