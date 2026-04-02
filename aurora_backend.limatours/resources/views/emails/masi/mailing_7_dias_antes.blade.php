<?php
$data = $items['data'];
$lang = $data['idioma'];
$has_certification = $items['certification_carbon'];
$colorText = ($data['color'] !== '#C38D22') ? $data['color'] : '#bd0a10';
$textFontSize = 'font-size:12px;';

$enlaces_bio = [
    'es' => 'https://drive.google.com/file/d/1woXlswNjULFsYws_An4eEq2IR0vB4qwD/view?usp=sharing',
    'en' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
    'pt' => 'https://drive.google.com/file/d/1tZcBoZtSNA4jE_r_sT5Olu02fQjyYw9O/view?usp=sharing',
    'it' => 'https://drive.google.com/file/d/10YueILA5WclayjNHXpUBL-B3y_2MBBC4/view?usp=sharing',
];

$enlaces_info = [
    'es' => 'https://drive.google.com/file/d/1gv_ztGee2nk6QFvu-C0n_5Rak58zzV9p/view?usp=sharing',
    'en' => 'https://drive.google.com/file/d/1HJqhBlHaFHNd23UfYi_QLvn2VrDlsUcr/view?usp=sharing',
    'pt' => 'https://drive.google.com/file/d/1ZBnc68dx_vqcJHSTJHmGa97nfyQM5O2-/view?usp=sharing',
    'it' => 'https://drive.google.com/file/d/1VMPO1wsqt_IDhctfPeQRlfv2ohvcCwmV/view?usp=sharing',
    'fr' => 'https://drive.google.com/file/d/1RQDXvdaPjHmhOIeWgJgdkcXr1sRM8bTa/view?usp=sharing',
];

$pasaporte = [
    'es' => 'https://drive.google.com/file/d/10BNnAEJ7ixsy0q2lBO51wyC35FnxY5Ke/view?usp=share_link',
    'en' => 'https://drive.google.com/file/d/1gExZsenAaZgNPBq-LcdUz5NtcR6q27IT/view?usp=share_link',
    'pt' => 'https://drive.google.com/file/d/1W78g9_4S4q5bg0JE7QKE9jJaKar44PDl/view?usp=share_link',
    'it' => 'https://drive.google.com/file/d/185BdNwyBuGJb8X-OR75kZs6cdxyz5qAU/view?usp=share_link',
];

?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
>

<head>
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml><![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <title></title>


    <style type="text/css" id="media-query">
        body {
            margin: 0;
            padding: 0;
        }

        table,
        tr,
        td {
            vertical-align: top;
            border-collapse: collapse;
        }

        .ie-browser table,
        .mso-container table {
            table-layout: fixed;
        }

        * {
            line-height: inherit;
        }

        a[x-apple-data-detectors=true] {
            color: inherit !important;
            text-decoration: none !important;
        }

        [owa] .img-container div,
        [owa] .img-container button {
            display: block !important;
        }

        [owa] .fullwidth button {
            width: 100% !important;
        }

        [owa] .block-grid .col {
            display: table-cell;
            float: none !important;
            vertical-align: top;
        }

        .ie-browser .num12,
        .ie-browser .block-grid,
        [owa] .num12,
        [owa] .block-grid {
            width: 600px !important;
        }

        .ExternalClass,
        .ExternalClass p,
        .ExternalClass span,
        .ExternalClass font,
        .ExternalClass td,
        .ExternalClass div {
            line-height: 100%;
        }

        .ie-browser .mixed-two-up .num4,
        [owa] .mixed-two-up .num4 {
            width: 200px !important;
        }

        .ie-browser .mixed-two-up .num8,
        [owa] .mixed-two-up .num8 {
            width: 400px !important;
        }

        .ie-browser .block-grid.two-up .col,
        [owa] .block-grid.two-up .col {
            width: 300px !important;
        }

        .ie-browser .block-grid.three-up .col,
        [owa] .block-grid.three-up .col {
            width: 200px !important;
        }

        .ie-browser .block-grid.four-up .col,
        [owa] .block-grid.four-up .col {
            width: 150px !important;
        }

        .ie-browser .block-grid.five-up .col,
        [owa] .block-grid.five-up .col {
            width: 120px !important;
        }

        .ie-browser .block-grid.six-up .col,
        [owa] .block-grid.six-up .col {
            width: 100px !important;
        }

        .ie-browser .block-grid.seven-up .col,
        [owa] .block-grid.seven-up .col {
            width: 85px !important;
        }

        .ie-browser .block-grid.eight-up .col,
        [owa] .block-grid.eight-up .col {
            width: 75px !important;
        }

        .ie-browser .block-grid.nine-up .col,
        [owa] .block-grid.nine-up .col {
            width: 66px !important;
        }

        .ie-browser .block-grid.ten-up .col,
        [owa] .block-grid.ten-up .col {
            width: 60px !important;
        }

        .ie-browser .block-grid.eleven-up .col,
        [owa] .block-grid.eleven-up .col {
            width: 54px !important;
        }

        .ie-browser .block-grid.twelve-up .col,
        [owa] .block-grid.twelve-up .col {
            width: 50px !important;
        }

        @media only screen and (min-width: 620px) {
            .block-grid {
                width: 600px !important;
            }

            .block-grid .col {
                vertical-align: top;
            }

            .block-grid .col.num12 {
                width: 600px !important;
            }

            .block-grid.mixed-two-up .col.num4 {
                width: 200px !important;
            }

            .block-grid.mixed-two-up .col.num8 {
                width: 400px !important;
            }

            .block-grid.two-up .col {
                width: 300px !important;
            }

            .block-grid.three-up .col {
                width: 200px !important;
            }

            .block-grid.four-up .col {
                width: 150px !important;
            }

            .block-grid.five-up .col {
                width: 120px !important;
            }

            .block-grid.six-up .col {
                width: 100px !important;
            }

            .block-grid.seven-up .col {
                width: 85px !important;
            }

            .block-grid.eight-up .col {
                width: 75px !important;
            }

            .block-grid.nine-up .col {
                width: 66px !important;
            }

            .block-grid.ten-up .col {
                width: 60px !important;
            }

            .block-grid.eleven-up .col {
                width: 54px !important;
            }

            .block-grid.twelve-up .col {
                width: 50px !important;
            }
        }

        @media (max-width: 620px) {
            .block-grid,
            .col {
                min-width: 320px !important;
                max-width: 100% !important;
                display: block !important;
            }

            .block-grid {
                width: calc(100% - 40px) !important;
            }

            .col {
                width: 100% !important;
            }

            .col > div {
                margin: 0 auto;
            }

            img.fullwidth,
            img.fullwidthOnMobile {
                max-width: 100% !important;
            }

            .no-stack .col {
                min-width: 0 !important;
                display: table-cell !important;
            }

            .no-stack.two-up .col {
                width: 50% !important;
            }

            .no-stack.mixed-two-up .col.num4 {
                width: 33% !important;
            }

            .no-stack.mixed-two-up .col.num8 {
                width: 66% !important;
            }

            .no-stack.three-up .col.num4 {
                width: 33% !important;
            }

            .no-stack.four-up .col.num3 {
                width: 25% !important;
            }

            .mobile_hide {
                min-height: 0px;
                max-height: 0px;
                max-width: 0px;
                display: none;
                overflow: hidden;
                font-size: 0px;
            }

            .icono_atencion{
                text-align: center !important;
            }

            .btn_info{
                font-size: 13px !important;
            }
        }

        .background-clouds {
            background-image: url('https://res.cloudinary.com/litomarketing/image/upload/v1678740118/clouds-masi_kk2taj.jpg');
            background-size: cover;
            background-repeat: none;
        }
    </style>
</head>

<body class="clean-body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #FFFFFF">
<style type="text/css" id="media-query-bodytag">
    @media (max-width: 520px) {
        .block-grid {
            min-width: 320px !important;
            max-width: 100% !important;
            width: 100% !important;
            display: block !important;
        }

        .col {
            min-width: 320px !important;
            max-width: 100% !important;
            width: 100% !important;
            display: block !important;
        }

        .col > div {
            margin: 0 auto;
        }

        img.fullwidth {
            max-width: 100% !important;
        }

        img.fullwidthOnMobile {
            max-width: 100% !important;
        }

        .no-stack .col {
            min-width: 0 !important;
            display: table-cell !important;
        }

        .no-stack.two-up .col {
            width: 50% !important;
        }

        .no-stack.mixed-two-up .col.num4 {
            width: 33% !important;
        }

        .no-stack.mixed-two-up .col.num8 {
            width: 66% !important;
        }

        .no-stack.three-up .col.num4 {
            width: 33% !important;
        }

        .no-stack.four-up .col.num3 {
            width: 25% !important;
        }

        .mobile_hide {
            min-height: 0px !important;
            max-height: 0px !important;
            max-width: 0px !important;
            display: none !important;
            overflow: hidden !important;
            font-size: 0px !important;
        }
    }
</style>
<!--[if IE]>
<div class="ie-browser"><![endif]-->
<!--[if mso]>
<div class="mso-container"><![endif]-->
<table class="nl-container"
       style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #FFFFFF;width: 100%"
       cellpadding="0" cellspacing="0">
    <tbody>
    <tr style="vertical-align: top">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top"><!--[if (mso)|(IE)]>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center" style="background-color: #FFFFFF;"><![endif]-->
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:transparent;" align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 5px; padding-left: 5px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 5px;
                                padding-left: 5px;">
                                    <!--<![endif]-->


                                    <div align="center" class="img-container center  autowidth  "
                                         style="padding-right: 0px;  padding-left: 0px;">
                                        <!--[if mso]>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr style="line-height:0px;line-height:0px;">
                                                <td style="padding-right: 0px; padding-left: 0px;" align="center">
                                        <![endif]-->
                                        <img class="center  autowidth " align="center" border="0"
                                             src="<?php echo $data['logo']; ?>"
                                             alt="<?php echo $data['logo']; ?>" title="Image"
                                             style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: 0;height: auto;float: none;width: 100%;max-width: 260px"
                                             width="260">
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 25px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                          padding-left: 10px;
                                           padding-top: 20px;
                                            padding-bottom: 10px;">
                                            <div style="font-size:13px;
                                            line-height:14px;
                                            color:#C38D22;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:left;">
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 17px;
                                                    text-align: center">
                                                    <span style="font-size: 20px; line-height: 24px;"><strong>{{ __('masi.texto_quedan_7_dias') }}</strong></span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider "
                                           style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 100%;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                        <tbody>
                                        <tr style="vertical-align: top">
                                            <td class="divider_inner"
                                                style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-right: 10px;padding-left: 10px;padding-top: 10px;padding-bottom: 10px;min-width: 100%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                <table class="divider_content" height="0px" align="center" border="0"
                                                       cellpadding="0" cellspacing="0" width="10%"
                                                       style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 2px solid #C38D22;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                    <tbody>
                                                    <tr style="vertical-align: top">
                                                        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                            <span>&#160;</span>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                 padding-top: 10px;
                                                  padding-bottom: 10px;">
                                        <![endif]-->

                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 0px;
                                          padding-left: 0px;
                                           padding-top: 10px;
                                            padding-bottom: 0px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b5f;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 14px;
                                                    text-align: center">
                                                        <span style="<?php echo $textFontSize; ?>line-height: 15px;">
                                                            <span style="line-height: 15px;<?php echo $textFontSize; ?>">
                                                                 <strong>{{ __('masi.text_hola') }} <?php echo urldecode($items['paxName']); ?></strong>, {{ __('masi.texto_te_damos_bienvenida') }}<span style="color: #C38D22;line-height: 15px;<?php echo $textFontSize; ?>"> <strong>{{ __('masi.masi') }}</strong></span><span>{{ __('masi.texto_tu_comanero') }}</span>
                                                            </span>
                                                        </span>
                                                </p>
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 18px;
                                                    text-align: center;padding-top: 20px">
                                                        <span style="<?php echo $textFontSize; ?>line-height: 15px;">
                                                            <span style="color: #5b5b75;">{{ __('masi.texto_esta_herramienta') }}</span>
                                                        </span>
                                                </p>
                                                <p style="margin: 0;
                                                    font-size: <?php echo $textFontSize; ?>;
                                                    line-height: 18px;
                                                    text-align: center;padding-top: 20px">
                                                    <span style="<?php echo $textFontSize; ?>
                                                        line-height: 15px;">
                                                    {{ __('masi.texto_aqui_enviamos') }}
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>
                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                                    <span style="font-size: 20px;
                                                            line-height: 24px;
                                                            background-color: rgb(255, 255, 255);
                                                            color: #C38D22;">
                                                        <strong>{{ __('masi.title_datos_generales_del_viaje') }}</strong>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid four-up ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="150"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 320px;min-width: 150px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                padding-left: 10px;
                                                 padding-top: 10px;
                                                  padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                          padding-left: 10px;
                                           padding-top: 10px;
                                            padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b5f;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:left;">
                                                <p style="margin: 0;
                                                font-size: 14px;
                                                line-height: 17px;
                                                text-align: center">
                                                    <strong><span
                                                            style="color: #C38D22;
                                                                 font-size: 13px;
                                                                  line-height: 16px;">{{ __('masi.tlt_reserva') }}</span></strong>
                                                </p>
                                                <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                    <span style="color: #5b5b5f; font-size: 13px; line-height: 16px;"><?php echo $data['file']['reserva']['dias']; ?> {{ __('masi.text_dias') }} / <?php echo $data['file']['reserva']['noches']; ?> {{ __('masi.text_noches') }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td>
                        <td align="center" width="150"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 320px;min-width: 150px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                        padding-right: 10px;
                                         padding-left: 10px;
                                          padding-top: 10px;
                                           padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b5f;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:left;">
                                                <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center">
                                                    <strong><span
                                                            style="color:#C38D22;font-size: 13px; line-height: 16px;">{{ __('masi.tlt_destinos') }}</span></strong>
                                                </p>
                                                <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                    <span style="color: #5b5b5f; font-size: 13px; line-height: 16px;"><?php echo $data['file']['destinos']; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td>
                        <td align="center" width="150"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 320px;min-width: 150px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                 border-left: 0px solid transparent;
                                  border-bottom: 0px solid transparent;
                                   border-right: 0px solid transparent;
                                    padding-top:5px; padding-bottom:5px;
                                     padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                 padding-top: 10px;
                                                 padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                          padding-left: 10px;
                                          padding-top: 10px;
                                          padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b5f;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:left;">
                                                <p style="margin: 0;
                                                font-size: 14px;
                                                line-height: 17px;
                                                text-align: center">
                                                    <span style="color: #C38D22;
                                                     font-size: 13px;
                                                      line-height: 16px;"><strong>{{ __('masi.tlt_llegada') }}</strong></span>
                                                </p>
                                                <p style="margin: 0;
                                                font-size: 13px;
                                                line-height: 17px;
                                                text-align: center">
                                                    <span style="color: #5b5b5f;
                                                     font-size: 13px;
                                                     line-height: 16px;"><?php echo $data['file']['llegada']; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td>
                        <td align="center" width="150"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 320px;min-width: 150px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                 border-right: 0px solid transparent;
                                 padding-top:5px; padding-bottom:5px;
                                  padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                         padding-left: 10px;
                                         padding-top: 10px;
                                         padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b5f;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:left;">
                                                <p style="margin: 0;
                                                font-size: 13px;
                                                line-height: 17px;
                                                text-align: center">
                                                    <span style="color:#C38D22;
                                                     font-size: 13px;
                                                      line-height: 16px;"><strong>{{ __('masi.tlt_salida') }}</strong></span>
                                                </p>
                                                <p style="margin: 0;
                                                font-size: 13px;
                                                line-height: 17px;
                                                text-align: center">
                                                    <span style="color: #5b5b5f;
                                                     font-size: 13px;
                                                      line-height: 16px;"><?php echo $data['file']['salida']; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                 border-left: 0px solid transparent;
                                  border-bottom: 0px solid transparent;
                                   border-right: 0px solid transparent;
                                    padding-top:5px;
                                     padding-bottom:5px;
                                     padding-right: 0px;
                                     padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div align="center" class="img-container center  autowidth  fullwidth "
                                         style="padding-right: 0px;
                                           padding-left: 0px;">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr style="line-height:0px;
                                            line-height:0px;">
                                                <td style="padding-right: 0px; padding-left: 0px;" align="center">
                                        <![endif]-->
                                        <img class="center  autowidth  fullwidth" align="center" border="0"
                                             src="<?php echo $data['mapa']; ?>"
                                             alt="Image" title="Image"
                                             style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: 0;height: auto;float: none;width: 100%;max-width: 600px"
                                             width="600">
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>


                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="margin: 0px auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 5px;
                                padding-bottom: 0px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 17px;
                                                    text-align: center">
                                                <p style="padding: 0px;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 18px;
                                                    background-color: rgb(255, 255, 255);text-align: center;">
                                                    <span style="color: #5B5B75;">{{ __('masi.texto_he_adjuntado_info_1') }}</span>
                                                    <span style="color: #5B5B75;"><span>{{ __('masi.texto_he_adjuntado_info_2') }}</span></span>
                                                    <span style="color: #5B5B75;">{{ __('masi.texto_he_adjuntado_info_3') }}</span>
                                                    <span style="color: #5B5B75;"><span>{{ __('masi.texto_he_adjuntado_info_4') }}</span></span>
                                                </p>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:center;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                                <p style="margin: 0;line-height: 17px; text-align:center;">
                                                    <a href="<?php echo (isset($enlaces_info[$lang]) ? $enlaces_info[$lang] : $enlaces_info['en']); ?>" style="text-decoration: none;">
                                                        <span class="btn_info" style="font-size: 18px; line-height: 12px;
                                                        color: white;background-color:#C38D22;padding: 10px;
                                                        padding-left: 20px;padding-right: 20px;
                                                        border-radius: 6px;font-weight: 600;">
                                                            {{ __('masi.btn_info_de_viaje') }}
                                                        </span>
                                                    </a>
                                                </p>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider "
                   style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 100%;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                <tbody>
                <tr style="vertical-align: top">
                    <td class="divider_inner"
                        style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-right: 10px;padding-left: 10px;padding-top: 10px;padding-bottom: 10px;min-width: 100%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                        <table class="divider_content" height="0px" align="center" border="0"
                               cellpadding="0" cellspacing="0" width="10%"
                               style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 2px solid #C38D22;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                            <tbody>
                            <tr style="vertical-align: top">
                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                    <span>&#160;</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php /* ?>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                                    <span style="font-size: 20px;
                                                            line-height: 24px;
                                                            background-color: rgb(255, 255, 255);
                                                            color: #C38D22;">
                                                        <strong>
                                                            {{ __('masi.tit_protocolos') }}
                                                        </strong>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:#ffffff;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->

                                        <div style="font-size: 14px; margin-bottom: 0rem;">
                                            <!-- p style="font-weight: 600; text-align: center; margin-top: 0; color: #d6971a">
                                                {!! __('masi.text_protocolos') !!}
                                            </p>
                                            <p style="font-weight: 600; text-align: center; margin-top: 0; color: #d6971a">
                                                {!! __('masi.text_protocolos_2') !!}
                                            </p -->
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="full_width" style="font-size: 14px; text-align: center;" >
                                                <tr align="center">
                                                    <td style="padding-left: 2rem; padding-right: 1rem;">
                                                        <img src="{{ asset('/images/guia-1.png') }}" alt="guia" width="150px">
                                                    </td>
                                                    <td style="padding-right: 2rem;">
                                                        <p style="text-align: center; margin-top: 0">
                                                            {!! __('masi.text_protocolos') !!}
                                                        </p>
                                                        <p style="text-align: center; margin-top: 0">
                                                            {!! __('masi.text_protocolos_2') !!}
                                                        </p>
                                                        <small style="text-align: center; color: #d6971a"> {!! __('masi.detalle_protocolos') !!} </p>
                                                        <!-- p style="margin: 0;line-height: 17px;text-align: center;">
                                                            <a href="<?php echo (isset($enlaces_bio[$lang])) ? $enlaces_bio[$lang] : $enlaces_bio['en']; ?>" style="text-decoration: none;">
                                                        <span class="btn_info" style="font-size: 18px; line-height: 12px;
                                                        color: white;background-color:#C38D22;padding: 10px;
                                                        padding-left: 20px;padding-right: 20px;
                                                        border-radius: 6px;font-weight: 600;">
                                                            {{ __('masi.boton_protocolos') }}
                                                        </span>
                                                            </a>
                                                        </p -->
                                                    </td>
                                                </tr>
                                            </table>
                                            <hr style="width: 50px; border: 1px #d6971a solid; margin-top: 1rem;">
                                        </div>

                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <?php */ ?>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid four-up">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <div class="col num3 background-clouds"
                             style="max-width: 500px;min-width: 500px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                 border-right: 0px solid transparent;
                                 padding-top:10px; padding-bottom:10px;
                                  padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                         padding-left: 10px;
                                         padding-top: 10px;
                                         padding-bottom: 10px;">
                                            <div style="color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <div style="margin: 0px;text-align: center;">
                                                    <strong style="color: #C38D22; font-size: 20px; line-height: 24px;">
                                                        {{ __('masi.texto_porque_pasaporte') }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                         padding-left: 10px;
                                         padding-top: 10px;
                                         padding-bottom: 10px; display:inline;">
                                            <div style="color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center; display:inline;">
                                                <p style="font-size: 13px;line-height: 17px;text-align: center;">
                                                    <a href="{{ (isset($pasaporte[$lang])) ? $pasaporte[$lang] : $pasaporte['en'] }}" style="text-decoration: none;">
                                                        <span class="btn_info" style="font-size: 18px; line-height: 12px;
                                                        color: white;background-color:#C38D22;padding: 10px;
                                                        padding-left: 20px;padding-right: 20px;
                                                        border-radius: 6px;font-weight: 600;">
                                                            {{ __('masi.btn_info_pasaportes') }}
                                                        </span>
                                                    </a>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid four-up">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="300"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 70px;min-width: 70px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                padding-left: 10px;
                                                 padding-top: 10px;
                                                  padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                          padding-left: 10px;
                                           padding-top: 10px;
                                            padding-bottom: 10px;">
                                            <div class="icono_atencion" style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <img src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_60/v1560430023/aurora/mailing/icono_atencion.png"
                                                     alt="">
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td>
                        <td align="center" width="300"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 500px;min-width: 500px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                 border-right: 0px solid transparent;
                                 padding-top:5px; padding-bottom:5px;
                                  padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                         padding-left: 10px;
                                         padding-top: 10px;
                                         padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <p style="margin: 0px;font-size: 13px;line-height: 17px;text-align: left;">
                                                    <span style="color: #C38D22; font-size: 12px; line-height: 16px;">
                                                        {{ __('masi.texto_atencion') }}
                                                    </span>
                                                    <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                        {{ __('masi.texto_atencion_1') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider "
                   style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 100%;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                <tbody>
                <tr style="vertical-align: top">
                    <td class="divider_inner"
                        style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-right: 10px;padding-left: 10px;padding-top: 10px;padding-bottom: 10px;min-width: 100%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                        <table class="divider_content" height="0px" align="center" border="0"
                               cellpadding="0" cellspacing="0" width="10%"
                               style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 2px solid #C38D22;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                            <tbody>
                            <tr style="vertical-align: top">
                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                    <span>&#160;</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php /* ?>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                                    <span style="font-size: 20px;
                                                            line-height: 24px;
                                                            background-color: rgb(255, 255, 255);
                                                            color: #C38D22;">
                                                        <strong>
                                                            <?php if($has_certification){ ?>
                                                                {{ __('masi.title_con_compensacion_tu_huella_de_carbono') }}
                                                            <?php }else{ ?>
                                                                {{ __('masi.title_sin_compensacion_tu_huella_de_carbono') }}
                                                            <?php } ?>
                                                        </strong>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid four-up">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="300"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 70px;min-width: 70px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                padding-left: 10px;
                                                 padding-top: 10px;
                                                  padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                          padding-left: 10px;
                                           padding-top: 10px;
                                            padding-bottom: 10px;">
                                            <div class="icono_atencion" style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <img src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,q_100,w_86/v1609280572/aurora/mailing/carbon_neutral.jpg"
                                                     alt="">
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td>
                        <td align="center" width="300"
                            style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num3"
                             style="max-width: 500px;min-width: 500px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                 border-right: 0px solid transparent;
                                 padding-top:5px; padding-bottom:5px;
                                  padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                                 padding-left: 10px;
                                                  padding-top: 10px;
                                                   padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;
                                        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                        line-height:120%;
                                         padding-right: 10px;
                                         padding-left: 10px;
                                         padding-top: 10px;
                                         padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                            line-height:14px;
                                            color:#5b5b75;
                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                            text-align:center;">
                                                <?php if($has_certification){ ?>
                                                <p style="margin: 0px;font-size: 13px;line-height: 17px;text-align: left;margin-top: 20px;">
                                                        <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                            {{ __('masi.texto_con_certificado') }}<br>
                                                        </span>
                                                </p>
                                                <?php }else{ ?>
                                                <p style="margin: 0px;font-size: 13px;line-height: 17px;text-align: left;margin-top: 5px;">
                                                        <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                            {{ __('masi.texto_sin_certificado') }}<br>
                                                        </span>
                                                </p>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 10px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:center;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                                <p style="margin: 0;line-height: 17px;text-align:center;">
                                                    <?php if($has_certification){ ?>
                                                    <a href="https://www.masi.pe/carbo_certification/" style="text-decoration: none;">
                                                            <span class="btn_info" style="font-size: 18px; line-height: 12px;
                                                            color: white;background-color:#C38D22;padding: 10px;
                                                            padding-left: 20px;padding-right: 20px;
                                                            border-radius: 6px;font-weight: 600;">
                                                                {{ __('masi.descarga_tu_certificado') }}
                                                            </span>
                                                    </a>
                                                    <?php }else{ ?>
                                                    <a href="#" style="text-decoration: none;">
                                                            <span class="btn_info" style="font-size: 18px; line-height: 12px;
                                                            color: white;background-color:#C38D22;padding: 10px;
                                                            padding-left: 20px;padding-right: 20px;
                                                            border-radius: 6px;font-weight: 600;">
                                                                {{ __('masi.obten_mas_informacion_certificado') }}
                                                            </span>
                                                    </a>
                                                    <?php } ?>

                                                </p>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <?php */ ?>

            <div style="background-color:transparent;">
                <div style="margin: 0px auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;"
                                    align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                    border-left: 0px solid transparent;
                     border-bottom: 0px solid transparent;
                     border-right: 0px solid transparent;
                     padding-top:5px; padding-bottom:5px;
                      padding-right: 0px;
                      padding-left: 0px;">
                                    <!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 5px;
                                padding-bottom: 0px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 17px;
                                                    text-align: center">
                                                <p style="margin: 0px;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 18px;
                                                    background-color: rgb(255, 255, 255);text-align: center;">
                                                    <span style="color: #5B5B75;">
                                                        {{ __('masi.texto_estamos_contacto_1') }}<br>
                                                        {{ __('masi.texto_estamos_contacto_2') }}<br>
                                                    </span>
                                                </p>

                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;" align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px;
                 padding-right: 0px;
                  padding-left: 0px;
                   padding-top:5px;
                    padding-bottom:5px;
                     border-top: 0px solid transparent;
                      border-left: 0px solid transparent;
                       border-bottom: 0px solid transparent;
                        border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->


                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider"
                                           style="border-collapse: collapse;
                                           table-layout: fixed;
                                           border-spacing: 0;
                                           mso-table-lspace: 0pt;
                                           mso-table-rspace: 0pt;
                                           vertical-align: top;
                                           min-width: 100%;
                                           -ms-text-size-adjust: 100%;
                                           -webkit-text-size-adjust: 100%">
                                        <tbody>
                                        <tr style="vertical-align: top">
                                            <td class="divider_inner"
                                                style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;padding-right: 10px;padding-left: 10px;padding-top: 0px;padding-bottom: 0px;min-width: 100%;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                <table class="divider_content" height="0px" align="center" border="0"
                                                       cellpadding="0" cellspacing="0" width="100%"
                                                       style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                    <tbody>
                                                    <tr style="vertical-align: top">
                                                        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                            <span>&#160;</span>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <!--[if (!mso)&(!IE)]><!-->
                                </div>
                                <!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:transparent;" align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;">
                        <![endif]-->
                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style="width:600px;
                 padding-right: 0px;
                  padding-left: 0px;
                   padding-top:5px;
                    padding-bottom:5px;
                     border-top: 0px solid transparent;
                      border-left: 0px solid transparent;
                       border-bottom: 0px solid transparent;
                        border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 100px;max-width: 100px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->
                                    <div class=""><!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                     padding-left: 10px;
                                      padding-top: 10px;
                                       padding-bottom: 0px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 0px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: center">
                                        <span style="font-size: 9px;
                                        line-height: 13px;">
                                            <img src="https://res.cloudinary.com/litomarketing/image/upload/c_scale,w_80/v1560430028/aurora/mailing/icono_masi.png" alt="">
                                        </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]>
                                        </td>
                                        </tr>
                                        </table><![endif]-->
                                    </div><!--[if (!mso)&(!IE)]><!-->
                                </div><!--<![endif]-->
                            </div>
                        </div>
                        <div class="col num12"
                             style="min-width: 480px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->
                                    <div class=""><!--[if mso]>
                                        <table width="100%"
                                               cellpadding="0"
                                               cellspacing="0"
                                               border="0">
                                            <tr>
                                                <td style="padding-right: 10px;
                                     padding-left: 10px;
                                      padding-top: 10px;
                                       padding-bottom: 0px;">
                                        <![endif]-->
                                        <div style="color:#5b5b5f;
                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                            line-height:120%;
                             padding-right: 10px;
                              padding-left: 10px;
                               padding-top: 10px;
                                padding-bottom: 0px;">
                                            <div style="font-size:12px;
                                line-height:14px;
                                color:#5b5b5f;
                                font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                text-align:left;">
                                                <p style="margin: 0;
                                    font-size: 14px;
                                    line-height: 17px;
                                    text-align: justify">
                                        <span style="font-size: 10px;
                                        line-height: 13px;">
                                            {{ __('masi.text_ley') }} {{ __('masi.text_ley_correo') }}
                                        </span>
                                                </p>
                                            </div>
                                        </div>
                                        <!--[if mso]>
                                        </td>
                                        </tr>
                                        </table><![endif]-->
                                    </div><!--[if (!mso)&(!IE)]><!-->
                                </div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]>
                        </td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        </table><![endif]-->
                    </div>
                </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
                     class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%"
                               cellpadding="0"
                               cellspacing="0"
                               border="0">
                            <tr>
                                <td style="background-color:transparent;" align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width"
                                            style="background-color:transparent;">
                        <![endif]-->
                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;
                 max-width: 600px;
                 display: table-cell;
                 vertical-align: top;">
                            <div style="background-color: transparent;
                 width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent;
                                border-left: 0px solid transparent;
                                border-bottom: 0px solid transparent;
                                border-right: 0px solid transparent;
                                padding-top:5px; padding-bottom:5px;
                                padding-right: 0px;
                                padding-left: 0px;">
                                    <!--<![endif]-->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider"
                                           style="border-collapse: collapse;
                                           table-layout: fixed;
                                           border-spacing: 0;
                                           mso-table-lspace: 0pt;
                                           mso-table-rspace: 0pt;
                                           vertical-align: top;
                                           min-width: 100%;
                                           -ms-text-size-adjust: 100%;
                                           -webkit-text-size-adjust: 100%">
                                        <tbody>
                                        <tr style="vertical-align: top">
                                            <td class="divider_inner"
                                                style="word-break: break-word;
                                    border-collapse: collapse !important;
                                    vertical-align: top;
                                    padding-right: 0px;
                                    padding-left: 0px;
                                    padding-top: 0px;
                                    padding-bottom: 0px;
                                    min-width: 100%;
                                    mso-line-height-rule: exactly;
                                    -ms-text-size-adjust: 100%;
                                    -webkit-text-size-adjust: 100%">
                                                <table class="divider_content" height="0px" align="center" border="0"
                                                       cellpadding="0" cellspacing="0" width="100%"
                                                       style="border-collapse: collapse;
                                                               table-layout: fixed;
                                                               border-spacing: 0;
                                                               mso-table-lspace: 0pt;
                                                               mso-table-rspace: 0pt;
                                                               vertical-align: top;
                                                               border-top: 14px solid #C38D22;
                                                               -ms-text-size-adjust: 100%;
                                                               -webkit-text-size-adjust: 100%">
                                                    <tbody>
                                                    <tr style="vertical-align: top">
                                                        <td style="word-break: break-word;
                                            border-collapse: collapse !important;
                                            vertical-align: top;
                                            font-size: 0px;
                                            line-height: 0px;
                                            mso-line-height-rule: exactly;
                                            -ms-text-size-adjust: 100%;
                                            -webkit-text-size-adjust: 100%">
                                                            <span>&#160;</span>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table><!--[if (!mso)&(!IE)]><!-->
                                </div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]>
                        </td>
                        </tr>
                        </table>
                        </td>
                        </tr>
                        </table><![endif]-->
                    </div>
                </div>
            </div>
            <!--[if (mso)|(IE)]>
            </td>
            </tr>
            </table><![endif]-->
        </td>
    </tr>
    </tbody>
</table>
<!--[if (mso)|(IE)]>
</div><![endif]-->
</body>
</html>
