<template>
    <div class="container-fluid">
        <manage-navegator/>
        <router-view></router-view>
    </div>
</template>

<script>
  import ManageNav from './ManageClientNav'
  import { API } from './../../../api'

  export default {
    components: {
      'manage-navegator': ManageNav
    },
    data () {
      return {
        client:[],
      }
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista'
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
      // this.fetchData(this.$i18n.locale)
      console.log("me ejecutaste region");
    },
    methods: {
      fetchData: function (lang) {

        if (this.$route.params.client_id !== undefined) {
          API.get('/clients/' + this.$route.params.client_id + '?lang=' + localStorage.getItem('lang'))
            .then((result) => {
              this.client = result.data.data

              this.changeName(this.client.name)
            }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error')
              })
            })
        }
      },
    },
    changeName(name) {
      this.$emit('changeStatus', name)
    },
  }
</script>

<style lang="stylus">

</style>


