<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'list-alt']" class="mr-1"/>
            {{ title }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'ChannelsAdd' }" v-if="showAdd">
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
        user:'',
        adminUser:''
      }
    },
    created: function () {
      this.user = this.$i18n.t('channels.title'),
        this.adminUser = this.$i18n.t('channels.manage_user')
      this.$root.$on('updateTitle', (payload) => {
        this.showTitle();
      })
    },
    computed: {
      showAdd () {
        return this.$route.name === 'ChannelsList' && this.$can('create', 'channels')
      },
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      showTitle: function () {
        if (this.$route.path.indexOf('users') !== -1) {
          this.title = this.adminUser + " : " +localStorage.getItem("usersname")
        } else if (this.$route.path.indexOf('logs') !== -1) {
          this.title = this.adminUser + ' : ' + localStorage.getItem('usersname')
        } else {
          this.title = this.user
        }
      },
    }
  }
</script>

<style lang="stylus">

</style>

