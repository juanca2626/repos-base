<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items" v-show="$can('read', item.permission)">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/> <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/> <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
        <div class="container-fluid">
            <div class="mt-1">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
      data: () => {
          return {
              items: [],
          };
      },
      mounted: function () {
          this.$i18n.locale = localStorage.getItem('lang')
      },
      created: function() {
        this.items = [
            {
                id: 1,
                title: 'Administracion',
                link: `/manage_client/regions/${this.$route.params.region_id}/services`,
                icon: 'route',
                status: '',
                permission: 'clientservices',
                activeRoutes: ['ServiceLayout', 'Service'] // Rutas que activan este item
            },
            {
                id: 2,
                title: 'Ofertas',
                link: `/manage_client/regions/${this.$route.params.region_id}/services/offer`,
                icon: 'hand-holding-usd',
                status: '',
                permission: 'clientserviceoffer',
                activeRoutes: ['ServiceLayout', 'ServiceOffer']
            },
            {
                id: 3,
                title: 'Valoración',
                link: `/manage_client/regions/${this.$route.params.region_id}/services/rated`,
                icon: 'star-half-alt',
                status: '',
                permission: 'clientservicerated',
                activeRoutes: ['ServiceLayout', 'ServiceRated']
            },
            {
                id: 4,
                title: 'Configuración',
                link: `/manage_client/regions/${this.$route.params.region_id}/services/configuration`,
                icon: 'cogs',
                status: '',
                permission: 'clientservicerated',
                activeRoutes: ['ServiceLayout', 'ServiceConfig']
            }
        ];

        // Actualizar el estado 'active' basado en la ruta actual
        this.items.forEach(item => {
            item.status = item.activeRoutes.includes(this.$route.name) ? 'active' : '';
        });
      },
      methods: {
          tabsStatus: function(link, id) {
              for (var i = this.items.length - 1; i >= 0; i--) {
                  if (id == this.items[i].id) {
                      this.items[i].status = 'active';
                  } else {
                      this.items[i].status = '';
                  }
              }
              this.$router.push('/clients/' + this.$route.params.client_id + link);
          },
      },
  };
</script>

<style lang="stylus">

</style>

<i18n src="./services.json"></i18n>
