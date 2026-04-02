<template>
    <form @submit="submit">
        <div class="row">
            <div class="col-sm-12">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{$t('permissions.forms.name')}}</label>
                        <div class="col-sm-5">
                            <input class="form-control input"
                                   id="name"
                                   name="name"
                                   placeholder=""
                                   type="text"
                                   v-model="form.name">
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="slug">{{$t('permissions.forms.slug')}}</label>
                        <div class="col-sm-5">
                            <input class="form-control input"
                                   id="slug"
                                   name="slug"
                                   placeholder=""
                                   type="text"
                                   v-model="form.slug">
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label"
                               for="description">{{$t('permissions.forms.description')}}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="description" name="description"
                                   placeholder=""
                                   type="text"
                                   v-model="form.description">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div slot="footer">
                    <img src="/images/loading.svg" v-if="loading" width="40px"/>
                    <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{$t('global.buttons.submit')}}
                    </button>
                    <router-link :to="{ name: 'PermissionsList' }" v-if="!loading">
                        <button class="btn btn-danger" type="reset">
                            {{$t('global.buttons.cancel')}}
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
  import { API } from './../../api'

  export default {
    data: () => {
      return {
        loading: false,
        formAction: 'post',
        form: {
          name: '',
          slug: '',
          description: ''
        }
      }
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/permissions/' + this.$route.params.id)
          .then((result) => {
            this.form = result.data.data
            this.formAction = 'put'
          })
      }
    },
    methods: {
      submit () {
        this.loading = true

        API({
          method: this.formAction,
          url: 'permissions/' + (this.$route.params.id !== undefined ? this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push({ name: 'PermissionsList' })
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Permisos',
                text: result.data.message
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>

<style lang="stylus">

</style>
