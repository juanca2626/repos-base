<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'globe']" class="mr-1"/>
            {{ showTitle }}
        </div>
        <div class="card-body">
            <manage-navegator/>
            <div class="mt-3">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import Nav from './Nav'

  export default {
    components: {
      'manage-navegator': Nav
    },
    data () {
      return {}
    },
    computed: {
      showTitle: function () {
        if (this.$route.path.indexOf('manage_package') !== -1) {
          return this.$i18n.t('manage_package')
        } else {
          return this.$i18n.t('title')
        }
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>

<i18n src="./reservation_center.json"></i18n>
