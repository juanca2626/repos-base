<template>
    <div class="animated fadeIn">
        <div class="col-sm-12" style="margin-bottom: 20px">
            <label class="typo__label">Escriba y seleccione las amenidades que desee agregar</label>
            <multiselect :clear-on-select="false"
                         :close-on-select="false"
                         :multiple="true"
                         :options="amenitieslist"
                         :placeholder="this.$i18n.t('hotels.hotel_amenity')"
                         :preserve-search="true"
                         :tag-placeholder="this.$i18n.t('hotels.hotel_tag_amenity')"
                         :taggable="true"
                         @tag="addTag"
                         label="name"
                         ref="multiselect"
                         track-by="code"
                         v-model="amenitiestrain">
            </multiselect>
        </div>

        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <button @click="cancelForm" class="btn btn-danger" type="reset" v-if="!loading">
                    {{$t('global.buttons.cancel')}}
                </button>
            </div>
        </div>

    </div>
</template>

<script>

    import { API } from './../../../../api'
    import Multiselect from 'vue-multiselect'

    export default {
        components: {
            Multiselect
        },
        data: () => {
            return {
                loading: false,
                amenitiestrain: [],
                amenitieslist: [],
                translAmenity: [],
            }
        },
        mounted () {
            console.log(this.$route.params.train_id)
            if (this.$route.params.train_id !== undefined) {

                API({
                    method: 'get',
                    url: 'train_template/' + this.$route.params.train_id + '/amenities?lang=' +
                        localStorage.getItem('lang')
                })
                    .then((result) => {

                        let arrayAuxAmenity = result.data.data
                        let j = 0
                        let argData = []
                        arrayAuxAmenity.forEach((amenities) => {
                            argData[j] = {
                                code: amenities.amenity.translations[0].object_id,
                                name: amenities.amenity.translations[0].value
                            }
                            j++
                        })

                        this.amenitiestrain = argData

                    })

            }

            //amenities
            API.get('/amenities/selectbox?lang=' + localStorage.getItem('lang'))
                .then((result) => {
                    let arraytranslAmenity = result.data.data
                    let i = 0
                    arraytranslAmenity.forEach((amenities) => {
                        this.amenitieslist[i] = {
                            code: amenities.translations[0].object_id,
                            name: amenities.translations[0].value
                        }
                        i++
                    })
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Modulo de Trenes',
                    text: this.$t('hotels.error.messages.connection_error')
                })
            })

        },
        computed: {},

        created: function () {

        },
        methods: {
            cancelForm () {
                this.$router.push({ path: '/trains' })
            },
            addTag (newTag) {
                const tag = {
                    name: newTag,
                    code: newTag.substring(0, 2) + Math.floor((Math.random() * 10000000))
                }
                this.amenitiestrain.push(tag)
            },

            submit () {

                //amenities
                let _variable = this.amenitiestrain
                let argData = []
                _variable.forEach((amenity) => {
                    argData.push(amenity.code)
                })

                this.loading = true

                API({
                    method: 'put',
                    url: 'train_template/' + this.$route.params.train_id + '/amenities',
                    data: { amenity_ids: argData }
                })
                    .then((result) => {

                        if (result.data.success === false) {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Modulo de Trenes',
                                text: 'Error'
                            })

                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Modulo de Trenes',
                                text: 'Actualizado Correctamente'
                            })
                        }
                        this.loading = false
                    })
            }
        }
    }
</script>

<style lang="stylus">
</style>


