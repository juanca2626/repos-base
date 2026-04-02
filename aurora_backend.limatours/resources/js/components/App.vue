<template>
    <div>
        <transition mode="out-in" name="fade">
            <router-view></router-view>
        </transition>
        <notifications group="main"></notifications>
    </div>
</template>

<script>
  import axios from 'axios'

  export default {
    created: function () {
      axios.get('/api/translations/json')
        .then((result) => {
          Object.keys(result.data.data).map(e => {
            this.$i18n.setLocaleMessage(e, result.data.data[e])
          })

          if (localStorage.getItem('lang') === null) {
            let language = (window.navigator.language).substring(0, 2)
            this.$i18n.locale = language
            localStorage.setItem('lang', language)
          } else {
            this.$i18n.locale = localStorage.getItem('lang')
          }
        })
    },
    methods: {}
  }
</script>

<style lang="stylus">
    .fade-enter-active,
    .fade-leave-active
        transition opacity .2s

    .fade-enter,
    .fade-leave-to
        opacity 0

</style>
