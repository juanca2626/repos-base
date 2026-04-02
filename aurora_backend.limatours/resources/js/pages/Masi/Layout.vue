<template>
    <!--
    <div class="card">
        <div class="card-body" style="padding-top:1.5rem;padding-left:0;padding-right:0;">
            <router-view></router-view>
        </div>
    </div>
    -->
    <div>
        <router-view></router-view>
    </div>
</template>

<script>
    export default {
        data() {
            return {}
        },
        computed: {
            showAdd() {
                // return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'units')
            }
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
        }
    }

</script>

<style scoped>

</style>
