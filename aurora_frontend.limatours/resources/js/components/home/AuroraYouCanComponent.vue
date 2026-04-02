<template>
    <section>
        <div class="container">
            <div class="aurora__video d-flex justify-content-between align-items-center">
                <video controls crossorigin playsinline
                       poster="https://res.cloudinary.com/litomarketing/image/upload/v1619537752/Thumbnails/Aurora/Aurora.jpg"
                       v-if="lang === 'es'" id="es">
                    <!-- Video files -->
                    <source
                        src="https://res.cloudinary.com/litomarketing/video/upload/v1614700414/vimeo/aurora/01_Aurora_ESP.mp4"
                        type="video/mp4" size="720">
                </video>
                <video controls crossorigin playsinline
                       poster="https://res.cloudinary.com/litomarketing/image/upload/v1619537752/Thumbnails/Aurora/Aurora.jpg"
                       v-else id="en">
                    <!-- Video files -->
                    <source
                        src="https://res.cloudinary.com/litomarketing/video/upload/v1614700416/vimeo/aurora/02._Aurora_ENG.mp4"
                        type="video/mp4" size="720">
                </video>
                <div class="aurora__list">
                    <h2 class="mb-4 ml-4">{{ translations.label.with_aurora_you_can }}</h2>
                    <ol>
                        <li>{{ translations.label.book_and_customize_your_programmes }}.</li>
                        <li>{{ translations.label.check_availability_of_hotels }}.</li>
                        <li>{{ translations.label.get_information_about_the_operation_of_our_services }}.</li>
                        <li>{{ translations.label.follow_the_hotel_itinerary_in_real_time }}.</li>
                        <li>{{ translations.label.review_billing_issues }}.</li>
                        <li>{{ translations.label.find_graphic_and_audiovisual_materials }}.</li>
                    </ol>
                </div>
            </div>
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
