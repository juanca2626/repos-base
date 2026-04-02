<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items" v-if="$can('read', item.permission_slug)">
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
    </div>
</template>
<script>

  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'TourCMS',
            link: 'tourcms',
            icon: 'dot-circle',
            status: '',
            permission_slug:'reservationcentertourcms'
          },
          {
            id: 2,
            title: 'Despegar',
            link: 'despegar',
            icon: 'dot-circle',
            status: '',
            permission_slug:'reservationcenterdespegar'
          },
          {
            id: 3,
            title: 'Expedia',
            link: 'expedia',
            icon: 'dot-circle',
            status: '',
            permission_slug:'reservationcenterexpedia'
          }
           {
            id: 4,
            title: 'Pentagrama',
            link: 'pentagramma',
            icon: 'dot-circle',
            status: '',
            permission_slug:'reservationcenterpentagrama'
          }
        ]
      }
    },
    mounted(){
    },
    created: function () {
      // console.log(this.$route.name)
      if (this.$route.name === 'TourcmsList') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'DespegarList') {
        this.items[1].status = 'active'
      }
      if (this.$route.name === 'ExpediaList') {
        this.items[2].status = 'active'
      }
      if (this.$route.name === 'PentagramaList') {
        this.items[3].status = 'active'
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
        this.$router.push('/reservation_center/' + link)
      }
    }
  }
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

</style>

<i18n src="./reservation_center.json"></i18n>
