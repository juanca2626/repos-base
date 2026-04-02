<?php
 
namespace Src\Modules\File\Presentation\Http\Controllers\Download;

use App\Http\Traits\ApiResponse; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory; 
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpWord\SimpleType\JcTable; 
use Src\Modules\File\Application\UseCases\Queries\SearchInA2DetailsServiceQuery;
use Src\Modules\File\Domain\ValueObjects\File\FileItineraryDetails; 
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use File;
use Src\Modules\File\Application\UseCases\Queries\FindFileByIdAllQuery;

class ItineraryWordController extends Controller
{
    use ApiResponse;

    public function __invoke(Request $request, $file_id)
    {         
 
        try
        {   
            $lang = $request->input('lang');
            $portadaURL = $request->input('portada'); 
            $dataLang = File::get(resource_path() . "/lang/" . $lang . "/itinerary.json");
            $trad = json_decode($dataLang);
        
            $file = (new FindFileByIdAllQuery($file_id))->handle();   
            $services_info_aurora = (new SearchInA2DetailsServiceQuery($this->getCodes($file)))->handle();   
             
            $itineraries = (new FileItineraryDetails($file, $services_info_aurora, $lang))->jsonSerialize(); 
 
        } catch (\Exception $domainException) {
                    
            return $this->errorResponse($domainException->getMessage(), ResponseAlias::HTTP_NOT_FOUND);
        }  


        $pathToFile = storage_path('app/exportedfile.docx');
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Juan Carlos');
        $properties->setCompany('Limatours');
        $properties->setTitle('Itinerario');
        $phpWord->setDefaultFontName('Calibri');        

        $firstPageSeccion = $phpWord->addSection();
        
        $portadaURL = config('services.quotes_ms.endpoint') . "/images/portadas/create/". $portadaURL;
        
        if (@getimagesize($portadaURL)) {
            $firstPageSeccion->addImage($portadaURL, [
                'widthwordSkeleton' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(16),
                'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(22.5),
                'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(15.5),
                'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.55),
                'wrappingStyle' => \PhpOffice\PhpWord\Style\Image::WRAP_BEHIND,
            ]);
        }

        

        $linestyle = [
            'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(12),
            'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(0),
            'weight' => 2,
            'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1),
            'color' => '#aea792',
        ];

        $tours = $itineraries['itineraries'];
        $phpWord->addParagraphStyle('spaceAfter0', [
            'spaceAfter' => Converter::pointToTwip(0),
        ]);

        //start building document
        $section = $phpWord->addSection();
        $section->addText(htmlspecialchars($trad->dayToday),array('name' => 'Calibri', 'size' => 11, 'color' => '5a5a58', 'bold' => true, 'wrappingStyle' => 'infront'),'title'); 
        $section->addLine($linestyle);
        foreach ($tours as $index => $tour) {
            $textRun = $section->addTextRun();
            $textRun->addText(htmlspecialchars($trad->day . ' ' . ($tour['day']).' | '. $tour['city_in_name'] . "-" . $tour['city_out_name']), ['color' => 'B3B182', 'bold' => true, 'size' => 11]);
            $textRun->addTextBreak(1);
            $textRun->addText(htmlspecialchars($tour['date']), ['color' => 'B3B182', 'bold' => true, 'size' => 11]);

            foreach ($tour['itineraries'] as $item) {

                if($item['entity'] == 'hotel'){
                    $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
                    $listItem->addText(htmlspecialchars($trad->accommodation . ' ' . $item['name']), ['size' => 9, 'bold' => true, 'color' => '545454']);
                    if ($item['hotel_detail']['url']) {
                        $listItem->addLink($item['hotel_detail']['url'], '('.$item['name'].')', 
                            ['size' => 9, 'bold' => true, 'color' => '0000FF', 'underline' => 'single']);
                    }

                    $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
                    $listItem->addText(htmlspecialchars('CHECKIN: ' . $item['start_time'].' CHECKOUT: ' . $item['departure_time']), ['size' => 9, 'bold' => true, 'color' => '545454']);
                    
                    $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
                    $listItem->addText(htmlspecialchars($trad->supplyIncluded . ' ' . $item['hotel_detail']['meal']), ['size' => 9, 'bold' => true, 'color' => '545454']);

                }else if($item['entity'] == 'flight'){

                    $section->addText(htmlspecialchars($item['start_time'].' '.$item['city_in_name'] . " / " . $item['city_out_name'] . " - " . $item['airline_name'] . " " . $item['airline_number']) , ['size' => 10, 'color' => '808080']);

                }else{
                    $section->addText(htmlspecialchars($item['start_time'].' '.$item['description']), ['size' => 10, 'color' => '808080']);
                }

                // $section->addTextBreak(1);
            }

            $section->addTextBreak(2);
        }
        $phpWord->addTableStyle('Table', ['borderSize' => 4, 'borderColor' => '85837f', 'cellMargin' => 80, 'alignment' => JcTable::CENTER, 'align' => 'center']);

        $table = $section->addTable();
        $table->addRow(300, ['exactHeight' => true]);
        $cell = $table->addCell(9500, [
            'align' => 'center',
            'borderTopSize' => 15,
            'borderBottomSize' => 15,
            'borderRightColor' => 'ffffff',
            'borderLeftColor' => 'ffffff',
            'borderTopColor' => 'b3b182',
            'borderBottomColor' => 'b3b182',
            'bgColor' => '#dad7c6'
        ]);
        $cell->addText(htmlspecialchars($trad->endService), ['size' => 9, 'color' => 'b3b182', 'bold' => true], ['alignment' => 'center']);

        $section->addTextBreak(1, 'space1');
 
        $hotels = $itineraries['hotels'];
        if (count($hotels) > 0) {

            $section->addText(htmlspecialchars($trad->tltHotel), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true]);
            $table = $section->addTable('Table');
            $table->addRow(300, ['exactHeight' => true]);
            $table->addCell(1000, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thCity), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(2650, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thHotel), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(2650, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thConfirmation), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(2650, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thtipHab), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(1500, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thDel), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(1500, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thAl), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(700, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thStatus), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            foreach ($hotels as $hotel)
            {
                $table->addRow(340, array('exactHeight' => false));
                $table->addCell(1000)->addText(htmlspecialchars(strtoupper(trim($hotel['city']))));
                $table->addCell(2650)->addText(htmlspecialchars(strtoupper(trim($hotel['hotel']))));
                $table->addCell(2650)->addText(htmlspecialchars(strtoupper(trim($hotel['confirmation']))));
                $table->addCell(2650)->addText(htmlspecialchars(strtoupper(trim($hotel['room']))));
                $table->addCell(1500)->addText(htmlspecialchars(strtoupper(trim($hotel['date_in']))));
                $table->addCell(1500)->addText(htmlspecialchars(strtoupper(trim($hotel['date_out']))));
                $table->addCell(700)->addText(htmlspecialchars(strtoupper(trim($hotel['status']))));
            }
            $section->addTextBreak(2);
        }
 
        $trains = $itineraries['trains'];

        if (count($trains) > 0) {

            $section->addText(htmlspecialchars($trad->tltTrain), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true]);
            $table = $section->addTable('Table');
            $table->addRow(340, ['exactHeight' => true]);
            $table->addCell(170, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thCity), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(2650, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thService), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(2650, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thConfirmation), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(100, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thCpax), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(1500, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thDeparture), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(800, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thHour), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(1500, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thArrival), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(800, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thHour), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            $table->addCell(700, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thStatus), ['bold' => true, 'color' => 'ffffff'], ['alignment' => 'center']);
            foreach ($trains as $train)
            {
                $table->addRow(340);
                $table->addCell(170)->addText(htmlspecialchars(strtoupper(trim($train['city']))));
                $table->addCell(2650)->addText(htmlspecialchars(strtoupper(trim($train['name']))));
                $table->addCell(2650)->addText(htmlspecialchars(strtoupper(trim($train['confirmation']))));
                $table->addCell(100)->addText(htmlspecialchars(strtoupper(trim($train['pax']))));
                $table->addCell(1500)->addText(htmlspecialchars(strtoupper(trim($train['date_in']))));
                $table->addCell(800)->addText(htmlspecialchars(strtoupper(trim($train['start_time']))));
                $table->addCell(1500)->addText(htmlspecialchars(strtoupper(trim($train['date_out']))));
                $table->addCell(800)->addText(htmlspecialchars(strtoupper(trim($train['departure_time']))));
                $table->addCell(700)->addText(htmlspecialchars(strtoupper(trim($train['status']))));
            }
            $section->addTextBreak(2);
        }

        $section->addText(htmlspecialchars($trad->titleNotInclude), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line1), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line2), ['size' => 10, 'color' => '5A5A58']);
        
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line3), ['size' => 10, 'color' => '5A5A58']);
        
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line4), ['size' => 10, 'color' => '5A5A58']);
        
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line5), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line6), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->textNotInclude_line7), ['size' => 10, 'color' => '5A5A58']);


        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->titleinfoImportant), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->textinfoImportant), ['size' => 10, 'color' => '808080']);

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->titleGeneralImportant), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->textGeneralImportant), ['size' => 10, 'color' => '808080']);
        $textRun = $section->addTextRun();
        $textRun->addText(htmlspecialchars($trad->textGeneralImportant_2), ['color' => '808080']);
        $textRun->addLink($trad->linkGeneraldomation1, htmlspecialchars($trad->textGeneraldomation1), ['bold' => true, 'color' => '0000FF']);

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->titleRecommendationForTransfers), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->subtitleRecommendationForTransfers), ['size' => 10, 'color' => '808080']);
        $section->addListItem(htmlspecialchars($trad->textRecommendationForTransfers_1), 0, ['size' => 10, 'color' => '5A5A58']);
        
        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->titleImportant), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->textImportant), ['size' => 10, 'color' => '808080']);
        $section->addListItem(htmlspecialchars($trad->textImportant_data1), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data2), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data3), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data4), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);

        $section->addText(htmlspecialchars($trad->textImportant_2), ['size' => 10, 'color' => '808080']);
        $section->addListItem(htmlspecialchars($trad->textImportant_data6), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data7), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data8), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);
        $section->addListItem(htmlspecialchars($trad->textImportant_data9), 0, ['size' => 10, 'color' => '5A5A58', 'lineHeight' => 1.0]);

        $section->addTextBreak(1);

        $section->addImage(
            storage_path('bag.jpeg'),
            array(
                'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.68),
                'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(2.28),
                'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_MARGIN,
                'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
            )
        );

        $styleTable = [
            'borderSize' => 6,
            'borderColor' => 'A69F88',
            'cellMargin' => 80,
            'align' => 'center',
            'marginRight' => 10
        ];
        $styleFirstRow = ['borderBottomColor' => 'A69F88', 'bgColor' => 'A69F88'];
        $phpWord->addTableStyle('tarifas', $styleTable, $styleFirstRow);
        $table = $section->addTable('tarifas');
        $table->addRow(300, ['exactHeight' => true]);
        $table->addCell(1900, ['bgColor' => 'A69F88'])->addText('', ['bold' => true, 'color' => 'ffffff']);
        $table->addCell(1300, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->thPeso), ['bold' => true, 'color' => 'ffffff']);
        $table->addCell(2800, ['bgColor' => 'A69F88'])->addText(htmlspecialchars($trad->size), ['bold' => true, 'color' => 'ffffff']);
        $table->addRow(300, ['exactHeight' => true]);
        $table->addCell(1900)->addText(htmlspecialchars($trad->tdbolso));
        $table->addCell(1300)->addText('5Kg/11lb');
        $table->addCell(2800)->addText(htmlspecialchars($trad->size_value));

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->titleRecommendationForTraveling), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->textRecommendationForTraveling_1), ['size' => 10, 'color' => '808080']);
        $textRun = $section->addTextRun();
        $textRun->addText(htmlspecialchars($trad->textRecommendationForTraveling_2), ['color' => '808080']);
        $textRun->addLink($trad->textRecommendationForTraveling_link, htmlspecialchars($trad->textRecommendationForTraveling_here), ['bold' => true, 'color' => '0000FF']);

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->masi_title), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->masi_parrafo_1), ['size' => 10, 'color' => '808080']);
                
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_1), ['size' => 10, 'color' => '5A5A58']);
        
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_2), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_3), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_4), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_5), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_texto_6), ['size' => 10, 'color' => '5A5A58']);

        
                
        $section->addTextBreak(1);
        $section->addText(htmlspecialchars('**********************************************'), ['size' => 10, 'color' => '5A5A58']);

        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->masi_title_numbers), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        
        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_numbers_1), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_numbers_2), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_numbers_3), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_numbers_4), ['size' => 10, 'color' => '5A5A58']);

        $listItem = $section->addListItemRun(0, null, 'spaceAfter0');
        $listItem->addText(htmlspecialchars($trad->masi_numbers_5), ['size' => 10, 'color' => '5A5A58']);


        $section->addTextBreak(1);
        $section->addText(htmlspecialchars($trad->disclaimer_title), ['align' => Jc::BOTH, 'size' => 11, 'color' => '5A5A58', 'bold' => true], 'spaceAfter0');
        // $section->addText(str_repeat('_', 82), ['bold' => true, 'size' => 11, 'color' => 'aea792'], ['alignment' => 'center', 'spaceAfter' => Converter::pointToTwip(4)]);
        $section->addLine($linestyle);
        $section->addText(htmlspecialchars($trad->disclaimer_text), ['size' => 10, 'color' => '808080']);
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($pathToFile);
        // return response()->json(['success' => true, 'message' => 'Exported file']);
        return Response::download($pathToFile, 'itinerary.docx');
    }

    public function getCodes($file): array {

        $data = [
            'services' => [],
            'hotels' => [],
            'flights' => []
        ];

        foreach($file['itineraries'] as $itinerary){

            if(in_array($itinerary['entity'], ['service', 'service-temporary'])){  
                array_push($data['services'], $itinerary['object_code']);
            }

            if($itinerary['entity'] == 'hotel'){ 
                array_push($data['hotels'], [
                    'itinerary_id' => $itinerary['id'],
                    'hotel_id' => $itinerary['object_id'],
                    'hotel_code' => $itinerary['object_code'],
                    'hotel_rate' => $itinerary['rooms'][0]['rate_plan_id']
                ] );
            }

            if($itinerary['entity'] == 'flight'){ 

                if($itinerary['city_in_iso'] and !in_array($itinerary['city_in_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_in_iso']);
                }
                if($itinerary['city_out_iso'] and !in_array($itinerary['city_out_iso'], $data['flights'])){
                    array_push($data['flights'], $itinerary['city_out_iso']);
                }                
            }

        }

        return $data ;
    }

}
