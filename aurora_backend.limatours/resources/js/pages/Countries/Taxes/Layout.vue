<template>
    <div style="margin-top: 0.2rem">
        <h3>{{ country_name }}</h3>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
        <router-view></router-view>
    </div>
</template>
<script>
  export default {
    data () {
      return {
        country_name: '',
        items: [
          {
            id: 1,
            title: 'Impuestos',
            link: '/taxes/list',
            icon: 'dot-circle',
            status: ''
          },
          {
            id: 2,
            title: 'Servicios',
            link: '/services/list',
            status: ''
          },
        ]
      }
    },
    mounted: function () {
      this.country_name = localStorage.getItem('country_name')
      this.$i18n.locale = localStorage.getItem('lang')
    },
    created: function () {
      console.log(this.$route.name)
      if (this.$route.name === 'TaxesList') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'TaxesAdd') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'TaxesEdit') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'ServicesList') {
        this.items[1].status = 'active'
      }
      if (this.$route.name === 'ServicesAdd') {
        this.items[1].status = 'active'
      }
      if (this.$route.name === 'ServicesEdit') {
        this.items[1].status = 'active'
      }
    },
    methods: {
      tabsStatus: function (link, id) {
        for (var i = this.items.length - 1; i >= 0; i--) {
          if (id == this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$router.push('/countries/taxes/' + this.$route.params.country_id + link)
      }
    }
  }
</script>

<style lang="stylus">

</style>
