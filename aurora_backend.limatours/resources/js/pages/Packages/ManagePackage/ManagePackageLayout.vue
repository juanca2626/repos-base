<template>
    <div class="container-fluid">
        <manage-navegator/>
        <div class="mt-3">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import ManageNav from './ManagePackageNav'

    export default {
        components: {
            'manage-navegator': ManageNav
        },
        data () {
            return {}
        },
        computed: {
            showAdd () {
                return this.$route.meta.breadcrumb === 'Texts'
            }
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitlePackage')
        }
    }
</script>

<style lang="stylus">

</style>


