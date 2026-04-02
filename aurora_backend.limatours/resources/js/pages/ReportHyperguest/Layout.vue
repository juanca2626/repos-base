<template>
    <div class="card">

        <div class="card-header">
          <font-awesome-icon :icon="['fas', 'hotel']" class="mr-1"/>
            {{ title }}
          <div class="card-header-actions"> 
            <router-link :to="{ name: 'createReportHyperguest' }" v-if="showAdd">
                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                {{ $t('global.buttons.add') }}
            </router-link>           
           
           
          </div>
        
        </div>
        <div class="card-body">
            <router-view>
              
            </router-view>

            

        </div>
    </div>
</template>

<script>
import ReportHyperguest from './Lista.vue';


  export default {
   
    data() {
        return {
            title: "Reporte Hyperguest",
            client: "",
            adminclient: "",
            loading: false,
        };
    },
    created: function () {
    },
    computed: {
        showAdd() {
            return this.$route.meta.breadcrumb === "Lista";
            // && this.$ability.can('create', 'report')
        },
        
    },
    mounted: function () {
        this.$i18n.locale = localStorage.getItem("lang");
    },
    methods: {
      
    },
    components: { ReportHyperguest }
}
</script>

<style lang="stylus">
</style>


