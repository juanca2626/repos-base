<template>
    <div class="row">
        <div class="col-sm-12">
            <div>
                <form class="row">
                    <div class="col-8">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="name">{{$t('roles.name')}}</label>
                                <div class="col-sm-10">
                                    <input class="form-control input" id="name" name="name" placeholder=""
                                           type="text"
                                           v-model="form.name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="slug">{{$t('roles.slug')}}</label>
                                <div class="col-sm-5">
                                    <input class="form-control input" id="slug" name="slug" placeholder=""
                                           type="text"
                                           v-model="form.slug">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label"
                                       for="description">{{$t('roles.description')}}</label>
                                <div class="col-sm-10">
                                    <input class="form-control input" id="description" name="description"
                                           placeholder=""
                                           type="text"
                                           v-model="form.description">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label" for="level">{{$t('roles.level')}}</label>
                                <div class="col-sm-5">
                                    <input class="form-control input" id="level" name="level"
                                           placeholder=""
                                           type="number"
                                           v-model="form.level">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-8">
                        <div class="b-form-group form-group">
                            <div class="form-row">
                                <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
                                <div class="col-sm-5">
                                    <c-switch :value=true class="mx-1" color="success"
                                              v-model="form.status"
                                              variant="pill">
                                    </c-switch>
                                </div>
                            </div>
                        </div> 
                    </div>                     
                </form>
            </div>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'RolesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>

  import { API } from './../../api'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {     
      cSwitch,
    },
    data: () => {
      return {
        loading: false,
        formAction: 'post',
        form: {
          name: '',
          slug: '',
          description: '',
          level: 1,
          permissions: [],
          status : true
        },
        permissions: []
      }
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/roles/' + this.$route.params.id)
          .then((result) => {
            this.form = result.data.data
            this.form.status = !!result.data.data.status
            this.formAction = 'put'
          })
      }
    },
    methods: {
      submit () {
        this.loading = true
        this.form.status = (this.form.status == false ? 0 : 1)
        this.form.slug = this.form.slug.toLowerCase()

        API({
          method: this.formAction,
          url: 'roles/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/roles/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Roles',
                text: result.data.message
              })

              this.loading = false
            }
          })
          .catch(() => {
            this.loading = false
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Roles',
              text: 'Error al regitrar el Rol'
            })
          })
      }
    }
  }
</script>

<style lang="stylus">
</style>
