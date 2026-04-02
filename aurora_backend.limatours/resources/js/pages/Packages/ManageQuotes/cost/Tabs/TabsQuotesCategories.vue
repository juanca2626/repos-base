<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status ==='active'">
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
  import { API } from '../../../../../api'

  export default {
    data: () => {
      return {
        items: []
      }
    },
    mounted () {
      this.getCategories()
    },
    created: function () {
    },
    methods: {
      getCategories: function () {
        API({
          method: 'get',
          url: '/package/plan_rates/' + this.$route.params.package_plan_rate_id + '?lang=' + localStorage.getItem('lang')
        })
          .then((result) => {
            if (result.data.success === true) {
              let categories = result.data.data.plan_rate_categories
              for (let i = 0; i < categories.length; i++) {
                if (this.$route.params.category_id == categories[i].id) {
                  this.items.push({
                    id: categories[i].id,
                    title: categories[i].category.translations[0].value,
                    link: '/quotes/cost/' + categories[i].package_plan_rate_id + '/category/' + categories[i].id,
                    icon: 'dot-circle',
                    status: 'active'
                  })
                } else {
                  this.items.push({
                    id: categories[i].id,
                    title: categories[i].category.translations[0].value,
                    link: '/quotes/cost/' + categories[i].package_plan_rate_id + '/category/' + categories[i].id,
                    icon: 'dot-circle',
                    status: ''
                  })
                }

              }
            } else {

            }
          }).catch((e) => {
          console.log(e)
        })
      },
      tabsStatus: function (link, id) {
        for (var i = this.items.length - 1; i >= 0; i--) {
          if (id == this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$root.$emit('updateCategory', { categoryId: id })
        this.$root.$emit('updateCategoryListServices', { categoryId: id })
        this.$router.push('/packages/' + this.$route.params.package_id + link)
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
