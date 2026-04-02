<template>
    <footer class="site-footer py-5">
        <div class="container">
            <div class="d-flex justify-content-center">
                <!--                <a class="link-footer" href="#">{{ translations.label.faqs }}</a>-->
                <!-- <a class="link-footer" href="/biosafety-protocols">{{ translations.label.biosafety_protocols }}</a> -->
                <a class="link-footer" href="https://limatours.com.pe/es/nosotros"
                   target="_blank">{{ translations.label.about_us }}</a>
                <a class="navbar-brand-client" href="/">
                    <img src="/images/logo/logo_nav.jpg">
                </a>
                <!--                <a class="link-footer" href="#">{{ translations.label.aurora_user_guide }}</a>-->
                <a class="link-footer" href="/contact-us">{{ translations.label.contact_us }}</a>
                <a class="link-footer" href="/cancellation-policies">{{ translations.label.privacy_policy }}</a>
            </div>
            <hr>
        </div>
        <div class="container-fluid pt-4">
            <div class="container bg-white">
                <div class="row">
                    <div class="col-md-3">
                        <div>
                            <a class="navbar-brand-client" href="#"><img src="/images/marca-peru.jpg"></a>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <p style="font-size: 14px;text-align: center">Av. Juan de Arona 755 Piso 3, San Isidro 15046 -
                            Oficinas de WorkTown - Perú</p>
                        <p style="font-size: 14px;text-align: center">{{ translations.label.business_name }}: LimaTours SAC | RUC: 20536830376</p>
                        <br>
                        <p style="font-size: 12px;text-align: center">Copyright © {{ year }} - {{ translations.label.all_rights_reserved }}</p>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="icons-redes">
                            <a href="https://www.linkedin.com/company/limatoursperu" target="_blank"><span
                                class="icon-ac-in"></span></a>
                            <a href="https://www.facebook.com/limatoursperu" target="_blank"><span
                                class="icon-ac-Facebook"></span></a>
                            <a href="https://www.instagram.com/limatours/" target="_blank"><span
                                class="icon-ac-Instagram"></span></a>
                            <a href="https://vimeo.com/ultimatejourneys" target="_blank"><span
                                class="icon-ac-vimeo-1"></span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
<script>
export default {
    data: () => {
        return {
            year: new Date().getFullYear(),
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
        setTranslations() {
            axios.get(baseURL + 'translation/' + localStorage.getItem('lang') + '/slug/global').then((data) => {
                this.translations = data.data
            })
        },
    }
}
</script>
