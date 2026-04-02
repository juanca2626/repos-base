<template>
    <div class="card">
        <div :class="'card-header ' + classCard">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            <span>Auditoria Paquetes</span>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    export default {
        data () {
            return {
                title: '',
                classCard: '',
                train: '',
                admintrain: ''
            }
        },
        created: function () {
            this.train = "Administrador de Trenes"
            this.admintrain = this.$i18n.t('trains.manage_train')
            this.edittrain = 'Editar Paquete'

            this.$root.$on('updateTitleTrain', (payload) => {
                if( payload.title ){
                    this.title = payload.title
                } else {
                    this.showTitle()
                }
            })
        },
        computed: {
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {

        }
    }
</script>

<style lang="stylus">

</style>


