<template>
    <div class="container-profile">
        <div class="content-profile">
            <div class="form">
                <div class="form-group">
                    <label for="password">Contraseña Actual</label>
                    <input type="password" class="form-control" id="password" v-model="password" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password" v-model="new_password" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" v-model="confirm_password" placeholder="" />
                </div>
                <button type="button" v-on:click="update()" v-bind:disabled="loading" class="btn btn-primary">Cambiar Contraseña</button>
            </div>
        </div>
    </div>

</template>
<script>
    export default {
        data: () => {
            return {
                lang: '',
                password: '',
                new_password: '',
                confirm_password: '',
                loading: false
            }
        },
        created: function () {

        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
        },
        computed: {

        },
        methods: {
            update: function () {
                this.loading = true

                if(this.new_password == '' || this.new_password == null)
                {
                    this.loading = false
                    this.$toast.error('Ingrese una nueva contraseña.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                if(this.confirm_password.trim() != this.new_password.trim())
                {
                    this.loading = false
                    this.$toast.error('La confirmación de contraseña no coincide con la nueva contraseña. Por favor, revise los datos e intente nuevamente.', {
                        // override the global option
                        position: 'top-right'
                    })
                    return false
                }

                axios.post(
                    baseURL + 'account/change_password', {
                        lang: this.lang,
                        password: this.password,
                        new_password: this.new_password,
                        confirm_password: this.confirm_password
                    }
                )
                .then((result) => {

                    this.loading = false

                    if(result.data.type == 'success')
                    {
                        this.password = ''
                        this.new_password = ''
                        this.confirm_password = ''

                        this.$toast.success(result.data.content, {
                            // override the global option
                            position: 'top-right'
                        })
                    }
                    else
                    {
                        this.$toast.error(result.data.content, {
                            // override the global option
                            position: 'top-right'
                        })
                    }
                })
                .catch((e) => {
                    if(e.message == 'Unauthenticated.')
                    {
                        window.location.reload()
                    }
                })
            },
        }
    };
</script>
