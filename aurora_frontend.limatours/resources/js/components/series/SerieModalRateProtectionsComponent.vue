<template>
    <div class="modal fade modal-general modal-rate-protection" id="modal_tarifas" tabindex="-1">
        <div class="modal-dialog modal-dialog__custom"  role="document">
            <div class="modal-content">
                <button class="modal-cerrar" type="button" data-dismiss="modal">{{ translations.label.close }} X</button>
                <div class="modal-rate-protection__container">
                    <div class="modal-body container-content" :class="{'container-content--edit':edit_mode}">
                        <span class="container-content__title">Protección de Tarifas (%)</span>
                        <ul class="container-content__list" :class="{'container-content__list--edit':edit_mode}">
                            <li class="list-item list-item--header" :class="{'list-item--header-edit':edit_mode}">
                                <span class="list-item__year" :class="{'list-item__year--edit':edit_mode}">AÑO</span>
                                <span class="list-item__hotel" :class="{'list-item__hotel--edit':edit_mode}">HOTEL</span>
                                <span class="list-item__service" :class="{'list-item__service--edit':edit_mode}">SERVICIO</span>
                                <span class="list-item__train" :class="{'list-item__train--edit':edit_mode}">TREN</span>
                                <span class="list-item__ghost list-item__ghost--edit" v-if="edit_mode"></span>
                            </li>
                            <li class="list-item list-item--content" :class="{'list-item--content-edit':edit_mode}"
                                v-for="(rate_protection, rp_k) in rate_protections">
                                <select class="list-item__year list-item__year--edit list-item__year-txt" v-model="rate_protection.year" v-if="edit_mode">
                                    <option :value="year" v-for="year in years">{{ year }}</option>
                                </select>
                                <span class="list-item__year list-item__year-txt" v-else>{{ rate_protection.year }}</span>

                                <input type="number" min="0" v-model="rate_protection.hotel" placeholder="3" v-if="edit_mode"
                                       class="list-item__hotel list-item__hotel--edit list-item__hotel-txt">
                                <span class="list-item__hotel list-item__hotel-txt" v-else>{{ rate_protection.hotel }} <span class="list-item__hotel-txt--percentage">%</span></span>

                                <input type="number" min="0" v-model="rate_protection.service" placeholder="5" v-if="edit_mode"
                                       class="list-item__service list-item__service--edit">
                                <span class="list-item__service" v-else>{{ rate_protection.service }} <span class="list-item__service-txt--percentage">%</span></span>

                                <input type="number" min="0" v-model="rate_protection.train" placeholder="10" v-if="edit_mode"
                                       class="list-item__train list-item__train--edit">
                                <span class="list-item__train" v-else>{{ rate_protection.train }} <span class="list-item__train-txt--percentage">%</span></span>

                                <div class="list-item__actions" v-if="edit_mode">
                                    <button type="button" class="content-add" @click="add(rate_protection, rp_k)">+</button>
                                    <button type="button" class="content-delete" :class="{'el_opacity':rp_k===0}" :disabled="rp_k===0" @click="remove(rp_k)">-</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button :disabled="loading" class="button-cancelar cancelar-info" @click="cancel()" v-if="edit_mode">Cancelar</button>
                    <button :disabled="loading"class="button-cancelar button-actualizar" @click="save()" v-if="edit_mode">Guardar</button>

                    <button :disabled="loading" class="button-cancelar cancelar-info" @click="edit_mode=true" v-if="!edit_mode"><span class="icon-edit mr-2"></span> Editar Tarifas</button>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
    export default {
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
                edit_mode : false,
                years : [
                    parseInt( moment().format('YYYY') ),
                    parseInt( moment().format('YYYY') ) + 1,
                    parseInt( moment().format('YYYY') ) + 2,
                    parseInt( moment().format('YYYY') ) + 3,
                    parseInt( moment().format('YYYY') ) + 4,
                ],
                serie_rate_protections : [],
                rate_protections : [
                    {
                        id : null,
                        year : parseInt( moment().format('YYYY') ),
                        hotel : 0,
                        service : 0,
                        train : 0
                    }],
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
                this.search()
            },
            save(){

                this.rate_protections.sort(function(a, b){
                    return a.year - b.year;
                })
                let repeats_ = 0
                let emptys_ = 0
                let years_ = []

                this.rate_protections.forEach( rate_protection=>{
                    if( years_[ rate_protection.year ] ){
                        repeats_++
                    }
                    if( rate_protection.year === '' || rate_protection.year === null ||
                        (rate_protection.hotel === 0 && rate_protection.service === 0 && rate_protection.train === 0 ) ){
                        emptys_++
                    }
                    if( years_[ rate_protection.year ] === undefined ){
                        years_[ rate_protection.year ] = true
                    }
                })

                if( repeats_ > 0 ){
                    this.$toast.warning("No puede ingresar años iguales", {
                        position: 'top-right'
                    })
                    return
                }
                if( emptys_ > 0 ){
                    this.$toast.warning("Por favor complete la información o elimine la fila que no necesite", {
                        position: 'top-right'
                    })
                    return
                }

                this.loading = true

                axios.post(
                    baseExternalURL + 'api/series/'+this.data.serie_id+'/rate_protections',
                    { rate_protections : this.rate_protections }
                )
                    .then((result) => {
                        if( result.data.success ){
                            this.$toast.success(this.translations.messages.realized, {
                                position: 'top-right'
                            })
                            this.search()
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })

            },
            cancel(){
                this.edit_mode = false
                if( this.serie_rate_protections.length > 0 ){
                    this.rate_protections = JSON.parse(JSON.stringify(this.serie_rate_protections))
                } else {
                    this.rate_protections = [
                        {
                            id : null,
                            year : parseInt( moment().format('YYYY') ),
                            hotel : 0,
                            service : 0,
                            train : 0
                        }
                    ]
                }
            },
            add(rate_protection, key){

                let rate_protection_new = {
                    id : null,
                    year : parseInt( rate_protection.year ) + 1,
                    hotel : 0,
                    service : 0,
                    train : 0
                }

                this.rate_protections.splice( (key+1), 0, rate_protection_new)
            },
            remove(key){
                this.rate_protections.splice(key, 1)
            },
            search(){
                this.loading = true
                axios.get(
                    baseExternalURL + 'api/series/'+this.data.serie_id+'/rate_protections'
                )
                    .then((result) => {
                        if( result.data.success ){
                            if( result.data.data.length > 0 ){
                                this.serie_rate_protections = JSON.parse(JSON.stringify(result.data.data))
                                this.rate_protections = result.data.data
                                this.edit_mode = false
                                this.$parent.get_rate_protection()
                            }
                        } else{
                            this.$toast.error('Error', {
                                position: 'top-right'
                            })
                        }
                        this.loading = false
                    })
                    .catch((e) => {
                        console.log(e)
                        this.loading = false
                    })
            },
        }
    }
</script>
<style>
    .el_opacity{
        opacity: 0.5;
    }
</style>
