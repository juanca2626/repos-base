<template>
    <div class="container-profile">
        <div class="content-profile media">
            <div class="row d-flex justify-content-between">
                <div class="photo-profile">
                    <div class="foto">
                        <img v-bind:src="photo" class="align-self-center mx-5 object-fit_cover" alt="..." v-if="photo != '' && photo != null">
                    </div>
                </div>
                <div class="media-body">
                    <div class="d-block w-100 my-4"><h2>Bienvenido a tu perfil {{ username }}!</h2></div>
                    <div class="form">
                        <div class="form-group">
                            <label for="file" class="label-profile">Selecciona una foto para tu perfil</label>
                            <input type="file" class="form-control-file" ref="file" id="file" v-on:change="onChangeFileUpload()">
                        </div>
                        <div class="my-5">
                            <button type="button" v-on:click="update()" v-bind:disabled="loading" class="btn btn-primary"><i class="far fa-image mx-3"></i>Añadir foto</button>
                            <button type="button" v-on:click="destroy()" v-if="photo != '' && photo != null" v-bind:disabled="loading" class="btn btn-lg"><i class="far fa-trash-alt mx-2 mt-3"></i>Eliminar foto</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        data: () => {
            return {
                user: '',
                username: '',
                lang: '',
                password: '',
                new_password: '',
                confirm_password: '',
                loading: false,
                file: '',
                photo: ''
            }
        },
        created: function () {
            this.user = document.querySelector('meta[name=\'code\']').getAttribute('content')
            this.username = document.querySelector('meta[name=\'username\']').getAttribute('content')
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.loadPhoto()
        },
        computed: {

        },
        methods: {
            loadPhoto: function () {
                axios.get(
                    baseURL + 'account/find_photo'
                )
                .then((result) => {
                    this.photo = result.data.photo
                })
                .catch((e) => {
                    if(e.message == 'Unauthenticated.')
                    {
                        window.location.reload()
                    }
                })
            },
            onChangeFileUpload(){
                this.file = this.$refs.file.files[0];
            },
            update: function () {
                this.loading = true

                if(this.file != '' && this.file != null)
                {
                    let formData = new FormData();
                    formData.append('file', this.file);

                    axios.post(baseURL + 'account/change_photo',
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        }
                    )
                    .then((result) => {
                        this.loading = false

                        console.log(result)

                        if(result.data.type == 'success')
                        {
                            this.photo = result.data.path
                            this.file = ''

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
                        this.loading = false
                        console.log(e)
                    })
                }
            },
            destroy: function () {
                this.loading = true

                    axios.post(
                        baseURL + 'account/delete_photo'
                    )
                    .then((result) => {
                        this.loading = false

                        if(result.data.type == 'success')
                        {
                            this.photo = result.data.path
                            this.file = ''

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
                        this.loading = false
                        console.log(e)
                    })
            },
        }
    };
</script>
