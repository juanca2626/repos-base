<?php

use App\OpeTemplate;
use App\OpeTemplateContent;
use Illuminate\Database\Seeder;

class OpeTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try
        {
            $templates = [
                "Atractivos Turísticos - Huelga" => [
                    "es" => "HX7ddb7983fd71f3b5cccacb77b194293f",
                    'en' => "HXecd30d817f9e7785c483defa230b2a1b",
                    "pt" => "HX3a15086edc8e2084ebde6c16ccf22b2a",
                ],
                "Atractivos Turísticos - Desastres Naturales" => [
                    "es" => "HXa1ea9abd6f09d579c1f5684a485e0fad",
                    "en" => "HX6ef5b7a85cc08679fa7d625c4efee983",
                    "pt" => "HXf664f17a85badf5d8ffe23648faab72e",
                ],
                "Atractivos Turísticos - Mantenimiento" => [
                    "es" => "HXa6c4b8ac46d29e25cfce0cb08aef64ee",
                    "en" => "HX00ccf2f26f7e4f68e8d490eb8e6d1067",
                    "pt" => "HX9bc3aef38d231a57df124faf014496ad",
                ],
                "Vias de Acceso - Huelga" => [
                    "es" => "HX6293f547d3eea88f671e4452ee83524a",
                    "en" => "HXdbed1bab0269d1c2c6ac4a7622b47636",
                    "pt" => "HX6bbaeb198d08fbc98a6e480601ff1c1f",
                ],
                "Vias de Acceso - Desastres Naturales" => [
                    "es" => "HX49435f0a243ac371cf086a6e3c29cbd0",
                    "en" => "HXd3cb971f5c043cd8400c3a86ca8ebf71",
                    "pt" => "HXfb774d777e9e21618ca836dbc0ee4d33",
                ],
                "Vias de Acceso - Mantenimiento" => [
                    "es" => "HX3294ddcbb7227bb4ffcf2b9aa80a19c7",
                    "en" => "HXd9257fe750f95df6dcc24d612332321d",
                    "pt" => "HX01430c4c9b4489a06bb3eb2c4213d4e4",
                ],
                "Aeropuerto - Huelga" => [
                    "es" => "HXa54f56f507a31707f07e9ce172febda1",
                    "en" => "HX183dd54228db704b18f3d6f51c7cf089",
                    "pt" => "HXd4d441eaf53566affa511aa0910bb4ae",
                ],
                "Aeropuerto - Desastres Naturales" => [
                    "es" => "HX186a56b99257adf508f0214ab1f8b905",
                    "en" => "HX2ce3dd5eaf235a49e8e7ec4736460da6",
                    "pt" => "HX1efc020813920843a132c49ac5f5aaa3",
                ],
                "Aeropuerto - Mantenimiento" => [
                    "es" => "HX4806410bf845b521ef32bd1444aff672",
                    "en" => "HXa327b8d4fea2568aa9bd78e113c7ab34",
                    "pt" => "HX6da522af145e1bd51713c327ccdf1fdb",
                ],
                "Trenes - Huelga" => [
                    "es" => "HXc6b7360b4ca5889c785a619757b13aa3",
                    "en" => "HXe995c23d6eb21c297c1e90a37b7a0aea",
                    "pt" => "HXb2257d085593c641544a5d8bbbd0f6a1",
                ],
                "Trenes - Desastres Naturales" => [
                    "es" => "HX89f8709aaf510f9892c516c55df33c49",
                    "en" => "HX2d920927ca3874090395436e660d572e",
                    "pt" => "HXab2fe314971bb1c1649d846f4fa91fe4",
                ],
                "Trenes - Mantenimiento" => [
                    "es" => "HX8f800e47b73096c991ff4684525dbdd8",
                    "en" => "HX4097a5bf2b0e8f635600689611e21b3c",
                    "pt" => "HX12906b2b42676c206f04828d517bd915",
                ],
                "Modificación de horario" => [
                    "es" => "HX05318c36dab464fe195f8f0f69a4f86a",
                    "en" => "HXaf71d66b8e63ab0eae6078775e2877d2",
                    "pt" => "HXa3167d57325920e3c67c02ce03a16270",
                ],
                "Caída de App" => [
                    "es" => "HX164b64af2a3d962f2b8abfeb8b015a41",
                    "en" => "HXa07877fecb2be85eaf5e358cf4573635",
                    "pt" => "HX4559d1fc1138aefa2836c1cb5788cdd6",
                ]
            ];

            $langs = ['', 'es', 'en', 'pt'];

            foreach($templates as $key => $template)
            {
                $ope_template = OpeTemplate::where('name', '=', $key)->first();

                foreach($langs as $lang)
                {
                    if(!empty($lang))
                    {
                        $content = OpeTemplateContent::where('ope_template_id', '=', $ope_template->id)
                            ->whre('language_id', '=', $lang)->first();
                        $content->twilio_template_id = $template[$lang];
                        $content->save();
                    }
                }
            }
        }
        catch(\Exception $ex)
        {
            app('sentry')->captureException($ex);
        }
        finally
        {
            return;
        }
    }
}
