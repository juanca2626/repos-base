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
                <h2 class="mb-4 ml-4">@{{ translations.label.with_aurora_you_can }}</h2>
                <ol>
                    <li>Reservar y personalizar tus programas.</li>
                    <li>Consultar diponibilidad de hoteles.</li>
                    <li>Informarte sobre la operatividad de nuestros servicios.</li>
                    <li>Seguir el itinerario de hoteles en tiempo real.</li>
                    <li>Revisar temas de facturación.</li>
                    <li>Encontrar materiales gráficos y audiovisuales.</li>
                </ol>

            </div>
        </div>
    </div>
</section>
