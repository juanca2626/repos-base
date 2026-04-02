<?php

namespace App\Imports;

use App\Language;
use App\OpeTemplate;
use App\OpeTemplateContent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TemplatesOpeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $rows = $rows->toArray(); $templates = [];

        foreach($rows as $key => $value)
        {
            if($key > 0)
            {
                if(!empty($value[0]))
                {
                    $template = OpeTemplate::where('name', '=', $value[0])->first();

                    if(!$template)
                    {
                        $template = new OpeTemplate;
                        $template->name = $value[0];
                        $template->save();
                    }

                    $languages = ['', 'en', 'es', 'pt']; $contents = [];

                    foreach($languages as $k => $v)
                    {
                        if(!empty($v))
                        {
                            $language = Language::where('iso', '=', $v)->first();

                            $content = OpeTemplateContent::where('language_id', '=', $language->id)
                                ->where('ope_template_id', '=', $template->id)->first();

                            if(!$content)
                            {
                                $content = new OpeTemplateContent;
                                $content->ope_template_id = $template->id;
                                $content->language_id = $language->id;
                            }

                            $value[$k] = str_replace("<br />", "<br>", $value[$k]);

                            $html = strip_tags($value[$k], '<br>');
                            $lines = explode("<br>", $html);
                            // Contents..
                            $html_wsp = ''; $html_content = '';

                            foreach($lines as $line)
                            {
                                $line = strip_tags($line); // Content Reset..

                                if(!empty($line))
                                {
                                    $html_wsp .= $line . "\n";
                                }

                                $html_content .= ((!empty($line)) ? $line : '') . "<br>";
                            }

                            $content->content_email = trim($html_content);
                            $content->content_wsp = trim($html_wsp);
                            $content->save();

                            $contents[] = $content;
                        }
                    }

                    $templates[] = [
                        'template' => $template,
                        'contents' => $contents,
                    ];
                }
            }
        }

        return $templates;
    }
}
