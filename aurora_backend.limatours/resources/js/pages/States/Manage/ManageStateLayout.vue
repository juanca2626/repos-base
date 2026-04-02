<template>
    <div class="container-fluid">
        <manage-navegator/>
        <router-view></router-view>
    </div>
</template>

<script>
  import ManageNav from './ManageStatesNav'
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
    },
    methods: {

    },
    changeName(name) {
      this.$emit('changeStatus', name)
    },
  }
</script>

<style lang="stylus">

</style>


