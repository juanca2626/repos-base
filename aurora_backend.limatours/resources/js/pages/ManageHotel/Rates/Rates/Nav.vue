<template>
    <div class="w-100">
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
    </div>
</template>

<script>

  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'Costo',
            link: '/manage_hotels/rates/rates/cost',
            icon: 'dot-circle',
            status: 'active'
          },
          {
            id: 2,
            title: 'Venta',
            link: '/manage_hotels/rates/rates/sale',
            status: ''
          }
        ]
      }
    },
    methods: {
      tabsStatus: function (link, id) {
        for (let i = this.items.length - 1; i >= 0; i--) {
          if (id === this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$router.push(link)
      }
    }
  }
</script>

<style lang="stylus">
    .s-color
        color red

    .fondo-nav
        background-color #f9fbfc
</style>
