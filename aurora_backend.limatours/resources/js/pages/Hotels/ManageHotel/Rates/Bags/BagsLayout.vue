<template>
    <div class="container">
        <div style="text-align: right;">
            <router-link :to="getRouteBagsAdd()" v-if="showButtonAdd"
                         class="btn btn-danger text-right">
                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                {{ $t('global.buttons.add') }}
            </router-link>
        </div>
        <router-view :key="Bag"></router-view>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        Bag:false
      }
    },
    created: function () {
      this.$root.$on('updateBag', (payload) => {
        this.Bag = payload.id
      })
    },
    computed: {
      showButtonAdd: function () {
        if (this.$route.params.bag_id === undefined && this.$route.name === 'BagsList') {
          return true
        } else {

          return false
        }
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      getRouteBagsAdd: function () {

        return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rates/bags/add'

      },

    }
  }
</script>