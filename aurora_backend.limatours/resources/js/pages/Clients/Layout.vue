<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'hotel']" class="mr-1"/>
            {{ title }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'ClientsAdd' }" v-if="showAdd" >
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
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
         title:'',
         client:'',
         adminclient:''
      }
    },
    created: function () {
      this.client = this.$i18n.t('clients.title'),
        this.adminclient = this.$i18n.t('clients.manage_client')
      this.$root.$on('updateTitle', (payload) => {
        this.showTitle();
      })
    },
    computed: {
      showAdd () {
        return this.$route.name === 'ClientsList' && this.$can('create', 'clients')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      showTitle: function () {
        if (this.$route.path.indexOf('manage_client') !== -1) {
          this.title = this.adminclient + " : " +localStorage.getItem("clientnamemanage")
        } else {
          this.title = this.client
        }
      }
    }
  }
</script>

<style lang="stylus">
</style>


