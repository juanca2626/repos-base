<template>
    <div class="row">
        <div class="col-sm-12">
            <h1> request</h1>
            <pre>{{ log.request }}</pre>
            <h1>response</h1>
            <pre>{{ log.response }}</pre>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <router-link :to="{ name: 'ChannelLogsList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../api'
  import MenuEdit from './../../../components/MenuEdit'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      'menu-edit': MenuEdit,
      cSwitch
    },
    data: () => {
      return {
        invalidError: false,
        invalidErrorCode: false,
        countError: 0,
        countErrorCode: 0,
        showError: false,
        loading: false,
        log: []
      }
    },
    mounted () {
      this.fetchData()
    },
    methods: {
      fetchData: function () {
        this.loading = true
        API.get('channels-logs/' + this.$route.params.channel_id + '/show/' + this.$route.params.id).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.log = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: 'Fetch Error',
            text: 'Cannot load data'
          })
        })
      }
    }
  }
</script>

<style lang="stylus">

</style>


