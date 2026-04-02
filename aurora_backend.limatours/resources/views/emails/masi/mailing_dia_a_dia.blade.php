<?php
$data = $items['data'];
$lang = $data['idioma'];
$docItinerario = $items['docItinerario'];
$colorText = ($data['color'] !== '#D68F02') ? $data['color'] : '#5b5b75';
$textFontSize = 'font-size:12px;';

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

    .bg-white {
        background-color: #FFF!important;
    }

    .text-white {
        color: #FFF!important;
    }

    .bg-warning {
        background-color: #e89b00!important;
    }

    .bg-danger {
        background-color: #c31b1e!important;
    }

    .text-danger {
        color: #c31b1e!important;
    }
</style>
<!--[if IE]>
<div class="ie-browser"><![endif]-->
<!--[if mso]>
<div class="mso-container"><![endif]-->
<table class="nl-container bg-white"
       style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #FFFFFF;width: 100%"
       cellpadding="0" cellspacing="0">
    <tbody>
    <tr style="vertical-align: top">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
            <!--[if (mso)|(IE)]>
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
                                        <tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="600"
                            style=" width:600px; padding-right: 5px; padding-left: 5px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num12"
                             style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 5px; padding-left: 5px;">
                                    <!--<![endif]-->


                                    <div align="center" class="img-container center  autowidth  "
                                         style="padding-right: 0px;  padding-left: 0px;">
                                        <!--[if mso]>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr style="line-height:0px;line-height:0px;">
                                                <td style="padding-right: 0px; padding-left: 0px;" align="center">
                                        <![endif]-->
                                        <img class="center  autowidth " align="center" border="0"
                                             src="<?php echo $data['logo'] ?>"
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
                                                    <span style="font-size: 20px; line-height: 24px;"><strong>{{ __('masi.tus_servicios_de') }} <?php echo $data['file']['dia']; ?></strong></span>
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
                                            <div style="<?php echo $textFontSize; ?>
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
                                                                <strong>{{ __('masi.text_hola') }} <?php echo urldecode($items['paxName']); ?></strong>, <span>{{ __('masi.esperamos_que_hayas_tenido') }}</span>
                                                            </span>
                                                        </span>
                                                </p>
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 14px;
                                                    text-align: center">
                                                        <span style="<?php echo $textFontSize; ?>line-height: 15px;">
                                                            <span style="line-height: 15px;<?php echo $textFontSize; ?>">
                                                                <span>{{ __('masi.encontraras_abajo_los_servicio') }}</span>
                                                            </span>
                                                        </span>
                                                </p>
                                                <p style="margin: 0;
                                                <?php echo $textFontSize; ?>
                                                    line-height: 14px;
                                                    text-align: center">
                                                        <span style="<?php echo $textFontSize; ?>line-height: 15px;">
                                                            <span style="line-height: 15px;<?php echo $textFontSize; ?>">
                                                                <span>{{ __('masi.services_shared_text') }}</span>
                                                            </span>
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
                     class="block-grid three-up ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background-color:transparent;" align="center">
                                    <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                                        <tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]>
                        <td align="center" width="200"
                            style=" width:200px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:15px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                            valign="top"><![endif]-->
                        <div class="col num4"
                             style="max-width: 600px;min-width: 600px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!-->
                                <div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:5px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;">
                                    <!--<![endif]-->
                                    <div class="">
                                        <!--[if mso]>
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                        <![endif]-->
                                        <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 0px;">
                                            <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:center;">
                                                <?php if ($data['file']['vuelo']['nrovuelo'] !== '') { ?>
                                                <p style="margin: 0;font-size: 12px;line-height: 17px;text-align: center">
                                                    <strong>
                                                            <span style="color: #C38D22; font-size: 13px; line-height: 16px;">
                                                                {{ __('masi.texto_vuelo') }}</span>
                                                    </strong>
                                                </p>

                                                <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 5px; padding-bottom: 10px;">
                                                    <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:left;">
                                                        <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center">
                                                            <span style="font-family: inherit; background-color: transparent; font-size: 14px; line-height: 16px;"><span
                                                                    style="color: #5b5b75; font-size: 13px; line-height: 16px;">
                                                                    {{ __('masi.texto_nrovuelo') }} <?php echo $data['file']['vuelo']['nrovuelo']; ?> <?php echo ucwords(strtolower($data['file']['vuelo']['companyair'])); ?> | {{ __('masi.tlt_salida') }} <?php echo $data['file']['vuelo']['horain']; ?> - {{ __('masi.tlt_llegada') }} <?php echo $data['file']['vuelo']['horaout']; ?>
                                                               </span>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 5px; padding-bottom: 10px;">
                                                    <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:left;">
                                                        <p style="margin: 0;font-size: 11px;line-height: 17px;text-align: center">
                                                            <span style="color: #C38D22; font-size: 11px; line-height: 16px;">
                                                                {{ __('masi.texto_vuelo_domestico') }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>

                                                <?php } ?>

                                                <?php if(count($data['file']['skeleton']) > 0){?>
                                                <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 0px; padding-bottom: 10px;">
                                                    <p style="margin: 0;font-size: 12px;line-height: 17px;text-align: center;margin-bottom: 5px; ">
                                                        <strong><span
                                                                style="color: #C38D22; font-size: 13px; line-height: 16px;">{{ __('masi.texto_servicio') }}</span></strong>
                                                    </p>
                                                    <?php for ($i = 0; $i < count($data['file']['skeleton']); $i++) { ?>
                                                    <span style="color: #5b5b75;">
                                                        <span style="font-size: 13px; line-height: 16px;">
                                                            <?php echo $data['file']['skeleton'][$i]['horin']; ?> <?php echo $data['file']['skeleton'][$i]['descrip'].'<br>'; ?>
                                                        </span>
                                                    </span>
                                                    <?php } ?>
                                                </div>
                                                <?php } ?>
                                                <?php if($data['file']['hotel'] !== ''){?>
                                                <p style="margin: 0;font-size: 12px;line-height: 17px;text-align: center">
                                                    <strong>
                                                            <span style="color: #C38D22; font-size: 13px; line-height: 16px;">
                                                                {{ __('masi.texto_hotel') }}</span>
                                                    </strong>
                                                </p>
                                                <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 5px; padding-bottom: 10px;">
                                                    <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:left;">
                                                        <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center">
                                                            <span style="background-color: transparent; font-size: 14px; line-height: 16px;">
                                                                <span style="color: #5b5b75; font-size: 13px; line-height: 16px;">
                                                                        <?php echo $data['file']['hotel']; ?>
                                                                </span>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                                <?php if(count($data['file']['pronostico']) > 0 AND $data['file']['pronostico'][0]['clima']['icon'] != ''){?>
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
                                                                 style="max-width: 300px;min-width: 300px;display: table-cell;vertical-align: top;">
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
                                            text-align:right;">
                                                                                    <img src="<?php echo $data['file']['pronostico'][0]['clima']['icon']; ?>">

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
                                                                 style="max-width: 320px;min-width: 320px;display: table-cell;vertical-align: top;">
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
                                                                                    <span style="font-size: 24px;
                                                                                        line-height: 28px;
                                                                                        color: rgb(153, 153, 153);">
                                                                                        <strong>
                                                                                            <span style="line-height: 28px; font-size: 28px;">
                                                                                                <?php echo $data['file']['pronostico'][0]['clima']['clima_min']; ?>° C
                                                                                            </span>
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
                        <!--[if (mso)|(IE)]></td>
                        </tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>

<?php /* ?>
@if(@$data['days_diff'] >= 0 AND @$data['days_diff'] <= 3)
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
                                    <p style="padding: 0px;
                                    <?php echo $textFontSize; ?>
                                        line-height: 18px;
                                        margin: 0px;
                                        background-color: rgb(255, 255, 255);text-align: center;">
                                        <strong style="color: #5B5B75;">{{ __('masi.return_home_title') }}</strong>
                                    </p>
                                    <p style="padding: 0px;
                                    <?php echo $textFontSize; ?>
                                        line-height: 18px;
                                        margin: 0px;
                                        background-color: rgb(255, 255, 255);text-align: center;">
                                        <span style="color: #5B5B75;">{{ __('masi.return_home_subtitle') }}</span>
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
@endif
<?php */ ?>


<div style="background-color:transparent;">
    <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;"
         class="block-grid ">
        <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
            <!--[if (mso)|(IE)]>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background-color:transparent;" align="center">
                        <table cellpadding="0" cellspacing="0" border="0"
                               style="width: 600px;">
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
                                padding-right: 0px; padding-left: 0px;">
                        <!--<![endif]-->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider "
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
                                                padding-right: 10px;
                                                padding-left: 10px;
                                                padding-top: 20px;
                                                padding-bottom: 10px;
                                                min-width: 100%;
                                                mso-line-height-rule: exactly;
                                                -ms-text-size-adjust: 100%;
                                                -webkit-text-size-adjust: 100%">
                                    <table class="divider_content" height="0px" align="center" border="0"
                                           cellpadding="0" cellspacing="0" width="20%"
                                           style="border-collapse: collapse;
                                                   table-layout: fixed;border-spacing: 0;
                                                   mso-table-lspace: 0pt;
                                                   mso-table-rspace: 0pt;
                                                   vertical-align: top;
                                                   border-top: 2px solid #d68f02;
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
                        </table>
                        <div class="">
                            <!--[if mso]>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                            <![endif]-->
                            <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 20px; padding-bottom: 0px;">
                                <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:left;">
                                    <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center">
                                        <strong>
                                            <span style="color: #C38D22; font-size: 20px; line-height: 16px;">
                                                {{ __('masi.tlt_contacto') }} {{ __('masi.tlt_contacto_24_7') }}
                                            </span>
                                        </strong>
                                    </p>
                                </div>
                            </div>
                            <!--[if mso]></td></tr></table><![endif]-->
                        </div>
                        <div class="">
                            <!--[if mso]>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right: 10px; padding-left: 10px; padding-top: 0px; padding-bottom: 0px;">
                            <![endif]-->
                            <div style="color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica,
                                         sans-serif;line-height:120%;
                                         padding-right: 0px;
                                         padding-left: 0px;
                                          padding-top: 10px;
                                          padding-bottom: 10px;">
                                <div style="<?php echo $textFontSize; ?>line-height:14px;color:#5b5b75;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;text-align:left;">
                                    <p style="margin: 0;font-size: 12px;line-height: 17px;text-align: center">
                                                    <span style="color: rgb(188, 10, 16); font-size: 12px; line-height: 15px;">
                                                        <span style="color: #5b5b75; line-height: 15px; font-size: 12px;">
                                                            {{ __('masi.text_contacto') }}
                                                        </span>
                                                    </span>
                                    </p>
                                </div>
                            </div>
                            <!--[if mso]></td></tr></table><![endif]-->
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
                                         style="max-width: 320px;min-width: 162px;display: table-cell;vertical-align: top;">
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
                                          border-right: solid 1px #CBCBCC;">
                                                        <div style="<?php echo $textFontSize; ?>
                                                            line-height:14px;
                                                            color:#5b5b75;
                                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                                            text-align:left;padding-top: 10px;">
                                                            <p style="margin: 0;text-align: center;margin-bottom: 0px;">
                                                                <a href="{{ __('masi.text_chatbot') }}"
                                                                   style="color: #5b5b75!important;text-decoration: none">
                                                                    <img src="https://res.cloudinary.com/litodti/image/upload/v1725471225/MASI/Icon_Chat.png"
                                                                         alt="">
                                                                </a>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <b style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    {{ __('masi.legend_text_chatbot') }}
                                                                </b>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    <a href="{{ __('masi.text_chatbot') }}"
                                                                       style="color: #5b5b75!important;text-decoration: none">
                                                                        {{ __('masi.text_chatbot') }}
                                                                    </a>
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
                                    <!--[if (mso)|(IE)]></td>
                                    <td align="center" width="150"
                                        style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                                        valign="top"><![endif]-->
                                    <div class="col num3"
                                         style="max-width: 320px;min-width: 200px;display: table-cell;vertical-align: top;">
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
                                        border-right: solid 1px #CBCBCC;">
                                                        <div style="<?php echo $textFontSize; ?>
                                                            line-height:14px;
                                                            color:#5b5b75;
                                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                                            text-align:left;padding-top: 10px;">
                                                            <p style="margin: 0;text-align: center;margin-bottom: 0px;">
                                                                <a href="mailto:{{ __('masi.text_email') }}"
                                                                   style="text-decoration: none">
                                                                    <img src="https://res.cloudinary.com/litodti/image/upload/v1725471281/MASI/Icon_Mail.png"
                                                                         alt="">
                                                                </a>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <b style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    {{ __('masi.legend_text_email') }}
                                                                </b>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    <a href="mailto:{{ __('masi.text_email') }}"
                                                                       style="color:#C38D22 !important;text-decoration: none">
                                                                        {{ __('masi.text_email') }}
                                                                    </a>
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
                                    <!--[if (mso)|(IE)]></td>
                                    <td align="center" width="150"
                                        style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                                        valign="top"><![endif]-->
                                    <div class="col num3"
                                         style="max-width: 320px;min-width: 110px;display: table-cell;vertical-align: top;">
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
                                          border-right: solid 1px #CBCBCC;">
                                                        <div style="<?php echo $textFontSize; ?>
                                                            line-height:14px;
                                                            color:#5b5b75;
                                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                                            text-align:left;padding-top: 10px;">
                                                            <p style="margin: 0;text-align: center;margin-bottom: 0px;">
                                                                <a href="https://api.whatsapp.com/send?phone={{ __('masi.text_whatsapp_alt') }}"
                                                                   style="text-decoration: none;">
                                                                    <img src="https://res.cloudinary.com/litodti/image/upload/v1725471283/MASI/Icon_WhatsApp.png"
                                                                         alt="">
                                                                </a>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <b style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    {{ __('masi.legend_text_whatsapp') }}
                                                                </b>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    <a href="https://api.whatsapp.com/send?phone={{ __('masi.text_whatsapp_alt') }}"
                                                                       style="color:#5EAC77 !important;text-decoration: none">
                                                                        {{ __('masi.text_whatsapp') }}
                                                                    </a>
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
                                    <!--[if (mso)|(IE)]></td>
                                    <td align="center" width="150"
                                        style=" width:150px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;"
                                        valign="top"><![endif]-->
                                    <div class="col num3"
                                         style="max-width: 320px;min-width: 110px;display: table-cell;vertical-align: top;">
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
                                         padding-left: 10px;">
                                                        <div style="<?php echo $textFontSize; ?>
                                                            line-height:14px;
                                                            color:#5b5b75;
                                                            font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
                                                            text-align:left;padding-top: 10px;">
                                                            <p style="margin: 0;text-align: center;margin-bottom: 0px;">
                                                                <a href="tel:{{ __('masi.text_celular_alt') }}"
                                                                   style="text-decoration: none;">
                                                                    <img src="https://res.cloudinary.com/litodti/image/upload/v1725471281/MASI/Icon_Phone.png"
                                                                         alt="">
                                                                </a>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <b style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    {{ __('masi.legend_text_telefono') }}
                                                                </b>
                                                            </p>
                                                            <p style="margin: 0;font-size: 13px;line-height: 17px;text-align: center">
                                                                <span style="color: #5b5b75; font-size: 12px; line-height: 16px;">
                                                                    <a href="tel:{{ __('masi.text_celular_alt') }}"
                                                                       style="color:#5B5B5F !important;text-decoration: none">
                                                                        {{ __('masi.text_celular') }}
                                                                    </a>
                                                                    <br>
                                                                    <a href="tel:{{ __('masi.text_telefono_alt') }}"
                                                                       style="color:#5B5B5F !important;text-decoration: none">
                                                                        {{ __('masi.text_telefono') }}
                                                                    </a>
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
        <div style="border-collapse: collapse;
                    display: table;
                    width: 100%;
                    background-color:transparent;"><!--[if (mso)|(IE)]>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="background-color:transparent;" align="center">
                        <table cellpadding="0" cellspacing="0" border="0" style="width: 600px;">
                            <tr class="layout-full-width" style="background-color:transparent;"><![endif]-->


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
                 style="min-width: 320px;max-width: 600px;
                             display: table-cell;
                             vertical-align: top;">
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
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="divider "
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
                                                padding-right: 10px;
                                                padding-left: 10px;
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
                                                       border-top: 1px solid #BBBBBB;
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
</td>
</tr>
</tbody>
</table>
<!--[if (mso)|(IE)]></div><![endif]-->


</body>

</html>
