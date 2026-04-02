<template>
    <router-view></router-view>
</template>
<script>
    export default {
        data() {
            return {}
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
        },
        methods: {
            add: function () {

                console.log('Agregar customer')

            },

        }
    }
</script>
<style lang="stylus">

</style>

