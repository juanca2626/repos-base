<template>
    <div class="card">
        <div :class="'card-header ' + classCard">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            <span>Auditoria Clientes</span>
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


