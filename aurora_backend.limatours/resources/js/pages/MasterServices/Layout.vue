<template>
    <div class="card">
        <div :class="'card-header ' + classCard">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1"/>
            <span v-html="title"></span>

            <div class="card-header-actions">
                <button class="btn btn-info" type="button" :disabled="btn_import_more" @click="import_more()">
                    <i class="fa fa-recycle nav-icon" :class="{'fa-spin':btn_import_more}"></i>
                    Importar más
                </button>
            </div>

        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import {API} from "../../api";

export default {
    data() {
        return {
            title: '',
            classCard: '',
            btn_import_more: false,
        }
    },
    created: function () {
    },
    computed: {},
    mounted: function () {
        this.$i18n.locale = localStorage.getItem('lang')
        this.$root.$on('update_title_master_services', (payload) => {
            this.show_title()
        })
    },
    methods: {
        show_title(){
            if( this.$route.name === 'MasterServiceEquivalences' ){
                this.title = '[t11] Equivalencias'
            } else {
                this.title = '[t03] Servicios Maestros'
            }
        },
        import_more() {
            this.btn_import_more = true

            this.title = '[t03] Servicios Maestros'
            let entity_ = {
                url: '/master_services/import_more',
                name_single: 'servicio',
                name: 'servicios',
                emit: 'update_master_services'
            }
            if( this.$route.name === 'MasterServiceEquivalences' ){
                this.title = '[t11] Equivalencias'
                entity_ = {
                    url: '/equivalences/import_more',
                    name_single: 'equivalencia',
                    name: 'equivalencias',
                    emit: 'update_equivalences'
                }
            }

            API.post(entity_.url)
                .then((result) => {

                    if( result.data.success ){
                        let count_ = result.data.data.length
                        let message_ = "Ninguno por importar"
                        let type_message_ = 'warning'
                        if( count_ === 1 ){
                            message_ = "Se importó " + count_ + " "+entity_.name_single
                            type_message_ = 'success'
                        }
                        if( count_ > 1 ){
                            message_ = "Se importaron " + count_ + " "+entity_.name
                            type_message_ = 'success'
                        }

                        if( count_ === 200 ){
                            this.import_more()
                        }

                        this.$notify({
                            group: 'main',
                            type: type_message_,
                            title: this.$t('global.modules.services'),
                            text: message_
                        })

                        this.$root.$emit(entity_.emit)

                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.services'),
                            text: "Servicio no disponible, por favor contáctese con el administrador"
                        })
                    }

                    this.btn_import_more = false
                    // invocar la clase del otro vue
                }).catch(() => {
                this.btn_import_more = false
            })

        }
    }
}
</script>

<style lang="stylus">

</style>


