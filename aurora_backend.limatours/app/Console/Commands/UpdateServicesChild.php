<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceChild;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateServicesChild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:update_age_child';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar masivamente edades de niños';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $services = Service::select('id')->whereIn('aurora_code',['LIN438', 'URUX06', 'PUV118', 'LIN59C', 'LIN620', 'LINGL2', 'CUG201', 'CUZPQ6',
'CUZ466', 'CUZ575', 'PUVP06', 'MPIGL8', 'AQVP32', 'CUZ468', 'UR1443', 'MPIGL7', 'CUZPQR', 'CUZPQO', 'CUZP31', 'CUZ440', 'AQPTR6',
'PUV116', 'UR1334', 'CUZTL6', 'CUZ302', 'LIN303', 'MPIGL1', 'UR1319', 'CUZPQ7', 'LIN114', 'UR1507', 'MPI501', 'PUV120', 'CUZ465',
 'UR1350', 'CUZ316', 'CUZ307', 'CUZ360', 'CUZ242', 'CUZ11A', 'AQV241', 'AQV111', 'LINXCT', 'LINXCR', 'LIN543', 'LIN405', 'LIN482',
 'LIMXTM', 'LIN450', 'CUZPQE', 'LINXSL', 'AQVP15', 'CUZPQH', 'CUZ584', 'CUZ574', 'CUZ573', 'LINP34', 'CUZXTF', 'AQV344', 'LIN542',
 'CUZ527', 'CUZ5S3', 'PCS308', 'UR1X13', 'AQVP26', 'LIN505', 'LINP42', 'PUVP19', 'PUVP18', 'PUVP0A', 'PUVP10', 'PUVP16', 'PUVP08',
 'UR15I9', 'UR1406', 'CUZ464', 'UR1TLL', 'UR1309', 'UR1303', 'CUZ3A6', 'CUZGLB', 'CUZ2H1', 'CUZ321', 'CUZ221', 'LIN51O', 'LIN436',
 'LINGL0', 'LIN215', 'CUZ400', 'UR1Q16', 'PUVP09', 'URU553', 'UR1461', 'AQVP1C', 'LINXMP', 'AQVP29', 'LIN498', 'LINTL8', 'CUZ438',
 'CUZP36', 'UR1402', 'MPI405', 'PUV124', 'URU555', 'URU554', 'CUZ554', 'UR1437', 'CUZ55H', 'AQVTL1', 'LIN2SI', 'UR1X12', 'UR1X28',
 'UR1401', 'CUZ424', 'CUZXPL', 'CUZXPC', 'CUZP86', 'PUV30I', 'LIN40P', 'CUZGLE', 'CUZ511', 'LIN5GA', 'CUZTL1', 'CUZ413', 'UR1571',
 'CUZX14', 'CUZXLC', 'AQVP0E', 'LIN2A5', 'LIN4AC', 'LIN1A5', 'CUZ507', 'CUZXRT', 'CUZ245', 'UR1370', 'CUZBT1', 'PUV538', 'CUZX30',
 'LIN487', 'LIN151', 'PUVP05', 'LINX13', 'CUZ506', 'LINX19', 'CUZ510', 'LINPA4', 'LIN4AD', 'CUZ2A5', 'CUZ4A8', 'UR151A', 'PUV3A6',
 'AQVP14', 'AQV4A1', 'PUV5A1', 'LINPA2', 'AQV3A3', 'CUZ570', 'CUZPQV', 'UR1528', 'CUZ528', 'CUZ5P9', 'CUZ538', 'CUZ550', 'CUZ5P8',
 'CUZ5P6', 'LINP08', 'CUZ5P7', 'CUZ1T1', 'CUZ534', 'UR1X35', 'UR1403', 'UR1405', 'LIN251', 'CUZ521', 'LIN507', 'CUZX3B', 'UR130A',
 'PUV220', 'CUZX3A', 'CUZGL3', 'CUZGL7', 'CUZ463', 'LINX21', 'CUZ5Y1', 'CUZ5X1', 'CUZX91', 'CUZX90', 'AQV245', 'AQV145', 'AQV141',
 'AQVGL4', 'CUZ121', 'CUZ508', 'UR1419', 'CUZXCE', 'CUZ5I0', 'CUZ552', 'LIN5CB', 'PCS4I3', 'LIN40J', 'LIN40K', 'LIN40E', 'AQVX02',
 'PUVX12', 'CUZ582', 'CUZ5P2', 'AQVGL7', 'AQVGL6', 'AQVTL2', 'AQVP01', 'PUV301', 'PUV407', 'CUZGL1', 'CUZGL2', 'CUZ533', 'CUZ5P3',
 'CUZ5P1', 'CUZ5P4', 'UR1355', 'UR1302', 'LIN4A0', 'LIN50A', 'UR1367', 'AQV343', 'AQV342', 'LINP38', 'LINGL1', 'LINTL2', 'LINTL1',
 'LINX20', 'LIN612', 'UR1529', 'LINX34', 'LIN226', 'LIN218', 'LIN118', 'CUZ436', 'LINX33', 'CUZ5O3', 'PUV405', 'PUV404', 'CUZ435',
 'CUZ437', 'LIN471', 'LIN515', 'LIN521', 'CUZX25', 'CUZ1H4', 'CUZ1H2', 'CUZ2H2', 'CUZ1H1', 'LIN2H1', 'LIN1H1', 'LIN2H2', 'LIN1H2',
 'UR1307', 'UR1333', 'UR1312', 'UR1557', 'UR1X34', 'URU526', 'CUZ5I3', 'CUZ529', 'CUZ536', 'UR1526', 'UR1506', 'AQVP27', 'AQVP28',
 'AQVP25', 'AQVP20', 'AQVP18', 'AQVP24', 'AQVP23', 'AQVP22', 'AQVP21', 'AQVP13', 'AQVP19', 'AQVP17', 'PUVP02', 'LINP49', 'LINP01',
 'CUZPQB', 'CUZPQA', 'CUZPQF', 'CUZPQG', 'LIN241', 'PIU126', 'UR1304', 'UR1301', 'TYL226', 'TYL111', 'TBP125', 'PUV241', 'PUV222',
 'PUV221', 'PUV216', 'PUV215', 'PUV211', 'PUV142', 'PUV141', 'PUV122', 'PUV121', 'PUV115', 'PUV111', 'LIN308', 'LIN307', 'LIN302',
 'LIN214', 'LIN211', 'LIN141', 'LIN115', 'LIN111', 'CUZ306', 'CUZ368', 'CUZ320', 'CUZ317', 'CUZ310', 'CUZ303', 'CUZ301', 'CUZ300',
 'CUZ243', 'CUZ21A', 'CUZ211', 'CUZ143', 'CUZ142', 'CUZ141', 'CUZ111', 'AQV302', 'AQV301', 'AQV211', 'AQV144', 'CUZ322', 'MPIX25',
 'MPIGL2', 'UR1542', 'UR1522', 'UR1519', 'UR1509', 'UR1502', 'UR1436', 'UR1435', 'UR1408', 'UR1416', 'PUV532', 'PUV508', 'PUV518',
 'PUV509', 'PUV506', 'PUV410', 'PUV402', 'PUV401', 'LIN520', 'LIN599', 'LIN596', 'LIN589', 'LIN533', 'LIN493', 'LIN435', 'LIN448',
 'LIN424', 'LIN406', 'LIN404', 'CUZ557', 'CUZ523', 'CUZ516', 'CUZ512', 'CUZ504', 'CUZ446', 'CUZ441', 'CUZ431', 'CUZ418', 'CUZ417',
 'CUZ414', 'CUZ412', 'CUZ411', 'CUZ407', 'CUZ403', 'CUZ402', 'CUZ401', 'AQV518', 'AQV502', 'AQV418', 'AQV415', 'AQV414', 'AQV413',
 'AQV416', 'AQV409', 'AQV405', 'AQV401', 'PUV119', 'CUZ467', 'UR1373', 'CUZ448', 'LIN576', 'CUZP29', 'CUZ117', 'CUZ217', 'LIN41U',
 'PUVP0B', 'PUVP17', 'UR1556', 'UR1317', 'CUZ369', 'LIN41Z', 'TRNE35', 'LIN128', 'LIN228', 'CUZ5G1', 'CUZ5G2', 'LINTLR', 'UR1310',
 'MPIGL3', 'CUZGLA', 'AQVTL4', 'LINGL8', 'CUZGL0', 'LINGL7', 'LINGL9', 'LINTL4', 'UR1306', 'PUVGL2', 'PUV302', 'CUZP30', 'CUZGL5',
 'LIN220', 'LINTL0', 'LIN120', 'AQV114', 'CUZGL6', 'MPIGL5', 'LIN518', 'AQV214', 'CUZ308', 'UR1332', 'PUVGL1', 'LIN12A', 'CUZ146',
 'LINTLM', 'CUZTL2', 'AQVX05', 'CUZP45', 'CUZGL9', 'LINP04', 'TRNV08', 'CUZ246', 'AQVP30', 'AQVP31', 'CUZ128', 'CUZ228', 'CUZ149',
 'CUZ249', 'CUZ374', 'UR1374', 'UR1335', 'UR1331', 'LIN5S9', 'CUZ326', 'CUZ311', 'UR1347', 'UR1353', 'CUZ319', 'MPI502', 'MPI503',
 'AQVP33', 'AQVP34', 'LIN4MC', 'CUZ4MC', 'AQV30A', 'PUV343'])->get();


        foreach ($services as $service){

            \DB::table('service_children')->insert([
                'min_age'=>2,
                'max_age'=>11,
                'service_id'=>$service["id"],
                'status'=>1,
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now()
            ]);
        }
    }
}
