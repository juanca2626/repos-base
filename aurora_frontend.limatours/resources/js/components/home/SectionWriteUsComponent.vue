<template>
    <section>
        <div class="hero__secondary d-flex justify-content-around align-items-center">
            <p>{{translations.label.have_any_questions}},
                <br>
                {{translations.label.do_not_hesitate_to_contact_us}}</p>

            <a href="/contact-us" class="btn-primary" >{{translations.label.write_us }}</a>
        </div>
    </section>
</template>>


<script>
    export default {
        data: () => {
            return {
                lang: 'es',
                translations: {
                    label: {},
                    validations: {},
                    messages: {}
                }
            }
        },
        created: function () {
            this.lang = localStorage.getItem('lang')
        },
        mounted: function () {
            this.setTranslations()
        },
        computed: {},
        methods: {
            setTranslations () {
                axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                    this.translations = data.data
                })
            },
        }
    }
</script>
<style>
    .loading-backdrop2 {
        background: rgba(75, 75, 75, 0.87) !important;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        z-index: 9998;
    }

    .customColor2 {
        color: #FFFFFF !important;
    }
</style>
