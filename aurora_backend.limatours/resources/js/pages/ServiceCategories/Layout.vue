<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1"/>
            {{ $t('global.modules.serviceCategory') }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'ServiceCategoryAdd' }" v-if="showAddCategory">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
                <router-link :to="{ name: 'ServiceSubCategoriesAdd' }" v-if="showAddSubCategory">
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
      return {}
    },
    computed: {
      showAddCategory () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'servicecategories')
      },
      showAddSubCategory () {
        return this.$route.meta.breadcrumb === 'Lista.' && this.$ability.can('create', 'servicesubcategories')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style scoped>

</style>