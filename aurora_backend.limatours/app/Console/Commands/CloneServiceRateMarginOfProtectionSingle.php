<?php

namespace App\Console\Commands;

use App\Service;
use App\ServiceRatePlan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneServiceRateMarginOfProtectionSingle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:clone-rate-margin-of-protection-single
                            {--year= : Anio origen (por defecto ano actual)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clona tarifas de servicios por codigo aplicando un margen de proteccion por servicio.';

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
        $year = (int) ($this->option('year') ?: Carbon::now()->year);
        $year_to = $year + 1;

        // Usar array manual (sin archivo)
        // Pegue aqui su array $data = [ ['codigo' => 'XXX', 'proteccion' => '5%'], ... ];
        $data = [
            ['codigo' => 'LIN111', 'proteccion' => '3%'],
            ['codigo' => 'MPI8MP', 'proteccion' => '5%'],
            ['codigo' => 'CUZ211', 'proteccion' => '0%'],
            ['codigo' => 'CUZMBT', 'proteccion' => '3%'],
            ['codigo' => 'CUZ111', 'proteccion' => '0%'],
            ['codigo' => 'CUZ431', 'proteccion' => '5%'],
            ['codigo' => 'UR1319', 'proteccion' => '0%'],
            ['codigo' => 'LIN40I', 'proteccion' => '7%'],
            ['codigo' => 'LIN211', 'proteccion' => '3%'],
            ['codigo' => 'LIN228', 'proteccion' => '5%'],
            ['codigo' => 'CUZ557', 'proteccion' => '5%'],
            ['codigo' => 'UR1310', 'proteccion' => '0%'],
            ['codigo' => 'MPI500', 'proteccion' => '5%'],
            ['codigo' => 'TRNMP4', 'proteccion' => '7%'],
            ['codigo' => 'CUZ401', 'proteccion' => '5%'],
            ['codigo' => 'UR1303', 'proteccion' => '0%'],
            ['codigo' => 'TRNE83', 'proteccion' => '7%'],
            ['codigo' => 'LIN40A', 'proteccion' => '5%'],
            ['codigo' => 'MPI515', 'proteccion' => '5%'],
            ['codigo' => 'PUV508', 'proteccion' => '7%'],
            ['codigo' => 'UR1304', 'proteccion' => '0%'],
            ['codigo' => 'PUNTR3', 'proteccion' => '5%'],
            ['codigo' => 'AQV111', 'proteccion' => '0%'],
            ['codigo' => 'TRNE34', 'proteccion' => '7%'],
            ['codigo' => 'MPIPI0', 'proteccion' => '5%'],
            ['codigo' => 'CUZ300', 'proteccion' => '0%'],
            ['codigo' => 'AQV416', 'proteccion' => '3%'],
            ['codigo' => 'MPIT13', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5PS', 'proteccion' => '5%'],
            ['codigo' => 'LIN214', 'proteccion' => '0%'],
            ['codigo' => 'MPIMMP', 'proteccion' => '5%'],
            ['codigo' => 'CUZ319', 'proteccion' => '0%'],
            ['codigo' => 'AQVP26', 'proteccion' => '5%'],
            ['codigo' => 'UR1TLL', 'proteccion' => '0%'],
            ['codigo' => 'CUZ441', 'proteccion' => '5%'],
            ['codigo' => 'URU8HK', 'proteccion' => '5%'],
            ['codigo' => 'PUV211', 'proteccion' => '0%'],
            ['codigo' => 'PUV509', 'proteccion' => '7%'],
            ['codigo' => 'CUZMRU', 'proteccion' => '3%'],
            ['codigo' => 'MPIPI5', 'proteccion' => '5%'],
            ['codigo' => 'CUZTR2', 'proteccion' => '5%'],
            ['codigo' => 'CUZ528', 'proteccion' => '0%'],
            ['codigo' => 'UR1302', 'proteccion' => '0%'],
            ['codigo' => 'TRNE37', 'proteccion' => '7%'],
            ['codigo' => 'AQV401', 'proteccion' => '0%'],
            ['codigo' => 'CUZ5P6', 'proteccion' => '5%'],
            ['codigo' => 'LINX33', 'proteccion' => '5%'],
            ['codigo' => 'CUZMSU', 'proteccion' => '3%'],
            ['codigo' => 'CUZX30', 'proteccion' => '5%'],
            ['codigo' => 'URU8MU', 'proteccion' => '5%'],
            ['codigo' => 'CUZ306', 'proteccion' => '0%'],
            ['codigo' => 'CUZ21A', 'proteccion' => '0%'],
            ['codigo' => 'UR1301', 'proteccion' => '0%'],
            ['codigo' => 'CUZ303', 'proteccion' => '0%'],
            ['codigo' => 'LIN302', 'proteccion' => '0%'],
            ['codigo' => 'CUZ5HU', 'proteccion' => '5%'],
            ['codigo' => 'LIN114', 'proteccion' => '0%'],
            ['codigo' => 'MPIX26', 'proteccion' => '5%'],
            ['codigo' => 'LINP07', 'proteccion' => '5%'],
            ['codigo' => 'MPIGL1', 'proteccion' => '5%'],
            ['codigo' => 'UR1542', 'proteccion' => '5%'],
            ['codigo' => 'PCS402', 'proteccion' => '8%'],
            ['codigo' => 'CUZ418', 'proteccion' => '0%'],
            ['codigo' => 'CUZ221', 'proteccion' => '0%'],
            ['codigo' => 'UR1528', 'proteccion' => '0%'],
            ['codigo' => 'MP2MPH', 'proteccion' => '3%'],
            ['codigo' => 'CUS9TU', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5G2', 'proteccion' => '0%'],
            ['codigo' => 'LIN241', 'proteccion' => '0%'],
            ['codigo' => 'LINX20', 'proteccion' => '5%'],
            ['codigo' => 'ICAXAR', 'proteccion' => '8%'],
            ['codigo' => 'UR1522', 'proteccion' => '3%'],
            ['codigo' => 'AQV211', 'proteccion' => '0%'],
            ['codigo' => 'TRNE91', 'proteccion' => '7%'],
            ['codigo' => 'AQVP20', 'proteccion' => '5%'],
            ['codigo' => 'MPIPI8', 'proteccion' => '5%'],
            ['codigo' => 'CUZ311', 'proteccion' => '0%'],
            ['codigo' => 'URU8UT', 'proteccion' => '5%'],
            ['codigo' => 'UR1408', 'proteccion' => '0%'],
            ['codigo' => 'CUZ527', 'proteccion' => '0%'],
            ['codigo' => 'MPI519', 'proteccion' => '5%'],
            ['codigo' => 'URU8GT', 'proteccion' => '5%'],
            ['codigo' => 'CUZ463', 'proteccion' => '5%'],
            ['codigo' => 'LIN128', 'proteccion' => '5%'],
            ['codigo' => 'CUZ143', 'proteccion' => '0%'],
            ['codigo' => 'UR1557', 'proteccion' => '5%'],
            ['codigo' => 'URU8HU', 'proteccion' => '5%'],
            ['codigo' => 'URU8HG', 'proteccion' => '5%'],
            ['codigo' => 'UR1509', 'proteccion' => '3%'],
            ['codigo' => 'URU8AR', 'proteccion' => '5%'],
            ['codigo' => 'PCO400', 'proteccion' => '8%'],
            ['codigo' => 'TRNMP6', 'proteccion' => '7%'],
            ['codigo' => 'PUV122', 'proteccion' => '0%'],
            ['codigo' => 'MPI503', 'proteccion' => '5%'],
            ['codigo' => 'LINX56', 'proteccion' => '5%'],
            ['codigo' => 'CUZ301', 'proteccion' => '0%'],
            ['codigo' => 'UR1309', 'proteccion' => '0%'],
            ['codigo' => 'CUZ516', 'proteccion' => '0%'],
            ['codigo' => 'URU8PI', 'proteccion' => '5%'],
            ['codigo' => 'TRNE36', 'proteccion' => '7%'],
            ['codigo' => 'CUZ368', 'proteccion' => '0%'],
            ['codigo' => 'MPIGL5', 'proteccion' => '5%'],
            ['codigo' => 'AQVP28', 'proteccion' => '5%'],
            ['codigo' => 'URU8CT', 'proteccion' => '5%'],
            ['codigo' => 'UR1519', 'proteccion' => '0%'],
            ['codigo' => 'CUZP29', 'proteccion' => '5%'],
            ['codigo' => 'CUZMCA', 'proteccion' => '3%'],
            ['codigo' => 'CUZMKO', 'proteccion' => '3%'],
            ['codigo' => 'CUZPQ2', 'proteccion' => '7%'],
            ['codigo' => 'AQV405', 'proteccion' => '0%'],
            ['codigo' => 'URU8TU', 'proteccion' => '5%'],
            ['codigo' => 'CUZ508', 'proteccion' => '0%'],
            ['codigo' => 'LINX35', 'proteccion' => '0%'],
            ['codigo' => 'CUZ529', 'proteccion' => '0%'],
            ['codigo' => 'COL9CL', 'proteccion' => '5%'],
            ['codigo' => 'CUZ424', 'proteccion' => '0%'],
            ['codigo' => 'MPI8SL', 'proteccion' => '5%'],
            ['codigo' => 'TRNE94', 'proteccion' => '7%'],
            ['codigo' => 'PUV30I', 'proteccion' => '0%'],
            ['codigo' => 'COL9AC', 'proteccion' => '5%'],
            ['codigo' => 'PCST05', 'proteccion' => '8%'],
            ['codigo' => 'TRNMP5', 'proteccion' => '7%'],
            ['codigo' => 'CUZBT1', 'proteccion' => '0%'],
            ['codigo' => 'CUZPQ1', 'proteccion' => '7%'],
            ['codigo' => 'PCS120', 'proteccion' => '8%'],
            ['codigo' => 'NAZ409', 'proteccion' => '8%'],
            ['codigo' => 'PCS302', 'proteccion' => '8%'],
            ['codigo' => 'CUZXMW', 'proteccion' => '0%'],
            ['codigo' => 'ICA402', 'proteccion' => '8%'],
            ['codigo' => 'URU8ML', 'proteccion' => '5%'],
            ['codigo' => 'LIN141', 'proteccion' => '0%'],
            ['codigo' => 'LIMTU6', 'proteccion' => '7%'],
            ['codigo' => 'LIMTUB', 'proteccion' => '7%'],
            ['codigo' => 'LIN303', 'proteccion' => '0%'],
            ['codigo' => 'UR1402', 'proteccion' => '0%'],
            ['codigo' => 'LINTL1', 'proteccion' => '0%'],
            ['codigo' => 'URU9SQ', 'proteccion' => '5%'],
            ['codigo' => 'CUZ403', 'proteccion' => '0%'],
            ['codigo' => 'CUZ5G1', 'proteccion' => '0%'],
            ['codigo' => 'PUV401', 'proteccion' => '5%'],
            ['codigo' => 'AQV114', 'proteccion' => '0%'],
            ['codigo' => 'MPI518', 'proteccion' => '5%'],
            ['codigo' => 'PCS220', 'proteccion' => '8%'],
            ['codigo' => 'CUZ5S3', 'proteccion' => '5%'],
            ['codigo' => 'LIM8CM', 'proteccion' => '5%'],
            ['codigo' => 'MPIGL2', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5P8', 'proteccion' => '5%'],
            ['codigo' => 'AQVP25', 'proteccion' => '5%'],
            ['codigo' => 'CUZ121', 'proteccion' => '0%'],
            ['codigo' => 'CUZ51B', 'proteccion' => '5%'],
            ['codigo' => 'URU8CH', 'proteccion' => '5%'],
            ['codigo' => 'AQV141', 'proteccion' => '0%'],
            ['codigo' => 'LIM9HP', 'proteccion' => '5%'],
            ['codigo' => 'COL9ER', 'proteccion' => '5%'],
            ['codigo' => 'LIN215', 'proteccion' => '0%'],
            ['codigo' => 'LIM8CX', 'proteccion' => '5%'],
            ['codigo' => 'CUZ523', 'proteccion' => '0%'],
            ['codigo' => 'URU8HY', 'proteccion' => '5%'],
            ['codigo' => 'LIN423', 'proteccion' => '5%'],
            ['codigo' => 'ICAGL6', 'proteccion' => '8%'],
            ['codigo' => 'CUZ411', 'proteccion' => '0%'],
            ['codigo' => 'LINP01', 'proteccion' => '5%'],
            ['codigo' => 'LIN115', 'proteccion' => '0%'],
            ['codigo' => 'LINTL8', 'proteccion' => '0%'],
            ['codigo' => 'URU9AD', 'proteccion' => '5%'],
            ['codigo' => 'PCS304', 'proteccion' => '8%'],
            ['codigo' => 'ICA241', 'proteccion' => '8%'],
            ['codigo' => 'PCS401', 'proteccion' => '8%'],
            ['codigo' => 'URU8IM', 'proteccion' => '5%'],
            ['codigo' => 'CUZ310', 'proteccion' => '0%'],
            ['codigo' => 'PCOX02', 'proteccion' => '8%'],
            ['codigo' => 'URU8MN', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQ3', 'proteccion' => '7%'],
            ['codigo' => 'CUZ436', 'proteccion' => '0%'],
            ['codigo' => 'UR1331', 'proteccion' => '0%'],
            ['codigo' => 'URU8GH', 'proteccion' => '5%'],
            ['codigo' => 'URU9TW', 'proteccion' => '5%'],
            ['codigo' => 'UR1367', 'proteccion' => '0%'],
            ['codigo' => 'LINP19', 'proteccion' => '5%'],
            ['codigo' => 'MPI8SQ', 'proteccion' => '5%'],
            ['codigo' => 'UR1556', 'proteccion' => '0%'],
            ['codigo' => 'CUZ451', 'proteccion' => '0%'],
            ['codigo' => 'NAZ242', 'proteccion' => '8%'],
            ['codigo' => 'MP1MPH', 'proteccion' => '3%'],
            ['codigo' => 'ICA410', 'proteccion' => '8%'],
            ['codigo' => 'TRNE84', 'proteccion' => '7%'],
            ['codigo' => 'MPIGL3', 'proteccion' => '5%'],
            ['codigo' => 'CUZ446', 'proteccion' => '5%'],
            ['codigo' => 'LIN307', 'proteccion' => '0%'],
            ['codigo' => 'LIN40P', 'proteccion' => '5%'],
            ['codigo' => 'PCS417', 'proteccion' => '8%'],
            ['codigo' => 'PCSXAR', 'proteccion' => '8%'],
            ['codigo' => 'MPI5OO', 'proteccion' => '5%'],
            ['codigo' => 'NAZ142', 'proteccion' => '8%'],
            ['codigo' => 'PUV222', 'proteccion' => '0%'],
            ['codigo' => 'ICATL2', 'proteccion' => '8%'],
            ['codigo' => 'NAZX01', 'proteccion' => '8%'],
            ['codigo' => 'MPIMPH', 'proteccion' => '5%'],
            ['codigo' => 'ICA141', 'proteccion' => '8%'],
            ['codigo' => 'PM1P10', 'proteccion' => '10%'],
            ['codigo' => 'PCSTU3', 'proteccion' => '8%'],
            ['codigo' => 'UR1312', 'proteccion' => '0%'],
            ['codigo' => 'LIN41W', 'proteccion' => '5%'],
            ['codigo' => 'PM1P14', 'proteccion' => '10%'],
            ['codigo' => 'URU8PY', 'proteccion' => '5%'],
            ['codigo' => 'PCS241', 'proteccion' => '8%'],
            ['codigo' => 'LIN615', 'proteccion' => '5%'],
            ['codigo' => 'URU8WY', 'proteccion' => '5%'],
            ['codigo' => 'AQVP18', 'proteccion' => '5%'],
            ['codigo' => 'PCS141', 'proteccion' => '8%'],
            ['codigo' => 'LIN498', 'proteccion' => '5%'],
            ['codigo' => 'PCST02', 'proteccion' => '8%'],
            ['codigo' => 'NAZTU7', 'proteccion' => '8%'],
            ['codigo' => 'LINXMW', 'proteccion' => '0%'],
            ['codigo' => 'ICATU5', 'proteccion' => '8%'],
            ['codigo' => 'LIN612', 'proteccion' => '0%'],
            ['codigo' => 'LIN50C', 'proteccion' => '5%'],
            ['codigo' => 'COL9AW', 'proteccion' => '5%'],
            ['codigo' => 'CUZ538', 'proteccion' => '0%'],
            ['codigo' => 'PUV518', 'proteccion' => '7%'],
            ['codigo' => 'CUZGL7', 'proteccion' => '5%'],
            ['codigo' => 'CUZ574', 'proteccion' => '5%'],
            ['codigo' => 'MP2MIP', 'proteccion' => '3%'],
            ['codigo' => 'AQVTL1', 'proteccion' => '5%'],
            ['codigo' => 'LINGL0', 'proteccion' => '5%'],
            ['codigo' => 'URU9AQ', 'proteccion' => '5%'],
            ['codigo' => 'URU8SQ', 'proteccion' => '5%'],
            ['codigo' => 'CUZX41', 'proteccion' => '5%'],
            ['codigo' => 'CUZGME', 'proteccion' => '5%'],
            ['codigo' => 'LIN40F', 'proteccion' => '5%'],
            ['codigo' => 'PM6P11', 'proteccion' => '10%'],
            ['codigo' => 'URU8WA', 'proteccion' => '5%'],
            ['codigo' => 'LIN536', 'proteccion' => '5%'],
            ['codigo' => 'MPI505', 'proteccion' => '5%'],
            ['codigo' => 'PCS306', 'proteccion' => '8%'],
            ['codigo' => 'LIN507', 'proteccion' => '0%'],
            ['codigo' => 'URU8HC', 'proteccion' => '5%'],
            ['codigo' => 'AQVP19', 'proteccion' => '5%'],
            ['codigo' => 'AQP8GB', 'proteccion' => '5%'],
            ['codigo' => 'CUZXRT', 'proteccion' => '5%'],
            ['codigo' => 'AQP8SB', 'proteccion' => '5%'],
            ['codigo' => 'UR1526', 'proteccion' => '0%'],
            ['codigo' => 'PUV215', 'proteccion' => '0%'],
            ['codigo' => 'PUV111', 'proteccion' => '0%'],
            ['codigo' => 'CUZ360', 'proteccion' => '0%'],
            ['codigo' => 'CUZ370', 'proteccion' => '0%'],
            ['codigo' => 'CUZGL1', 'proteccion' => '5%'],
            ['codigo' => 'UR1355', 'proteccion' => '0%'],
            ['codigo' => 'PM6P13', 'proteccion' => '10%'],
            ['codigo' => 'CUZ576', 'proteccion' => '0%'],
            ['codigo' => 'TRNE92', 'proteccion' => '7%'],
            ['codigo' => 'PCSTU2', 'proteccion' => '8%'],
            ['codigo' => 'ICA8DU', 'proteccion' => '5%'],
            ['codigo' => 'URU8LF', 'proteccion' => '5%'],
            ['codigo' => 'PCS407', 'proteccion' => '8%'],
            ['codigo' => 'URU555', 'proteccion' => '0%'],
            ['codigo' => 'CUZ243', 'proteccion' => '0%'],
            ['codigo' => 'AQVP22', 'proteccion' => '5%'],
            ['codigo' => 'LIMTU3', 'proteccion' => '7%'],
            ['codigo' => 'CUZ507', 'proteccion' => '0%'],
            ['codigo' => 'CUZTL1', 'proteccion' => '0%'],
            ['codigo' => 'CUZ414', 'proteccion' => '0%'],
            ['codigo' => 'UR1XSP', 'proteccion' => '5%'],
            ['codigo' => 'CUZ321', 'proteccion' => '0%'],
            ['codigo' => 'MPIT15', 'proteccion' => '5%'],
            ['codigo' => 'PCSTU1', 'proteccion' => '8%'],
            ['codigo' => 'LIN308', 'proteccion' => '0%'],
            ['codigo' => 'CUZPQ4', 'proteccion' => '7%'],
            ['codigo' => 'LIN510', 'proteccion' => '5%'],
            ['codigo' => 'TRNMP7', 'proteccion' => '7%'],
            ['codigo' => 'UR1307', 'proteccion' => '0%'],
            ['codigo' => 'LINP00', 'proteccion' => '5%'],
            ['codigo' => 'UR1X32', 'proteccion' => '5%'],
            ['codigo' => 'LIN571', 'proteccion' => '0%'],
            ['codigo' => 'COL8CL', 'proteccion' => '5%'],
            ['codigo' => 'UR1507', 'proteccion' => '0%'],
            ['codigo' => 'TRU111', 'proteccion' => '7%'],
            ['codigo' => 'PUVP05', 'proteccion' => '7%'],
            ['codigo' => 'CUZ402', 'proteccion' => '5%'],
            ['codigo' => 'CUZ11A', 'proteccion' => '0%'],
            ['codigo' => 'PUV221', 'proteccion' => '0%'],
            ['codigo' => 'CUS8RC', 'proteccion' => '5%'],
            ['codigo' => 'NAZTU3', 'proteccion' => '8%'],
            ['codigo' => 'LIM9CR', 'proteccion' => '5%'],
            ['codigo' => 'UR1462', 'proteccion' => '0%'],
            ['codigo' => 'AQV422', 'proteccion' => '5%'],
            ['codigo' => 'LINGL9', 'proteccion' => '5%'],
            ['codigo' => 'AQV214', 'proteccion' => '0%'],
            ['codigo' => 'UR1586', 'proteccion' => '5%'],
            ['codigo' => 'PCSTU4', 'proteccion' => '8%'],
            ['codigo' => 'URU8SG', 'proteccion' => '5%'],
            ['codigo' => 'LINP50', 'proteccion' => '5%'],
            ['codigo' => 'UR1334', 'proteccion' => '0%'],
            ['codigo' => 'URU8WG', 'proteccion' => '5%'],
            ['codigo' => 'TRNE85', 'proteccion' => '7%'],
            ['codigo' => 'URU8AL', 'proteccion' => '5%'],
            ['codigo' => 'LINP08', 'proteccion' => '5%'],
            ['codigo' => 'CUZGL2', 'proteccion' => '5%'],
            ['codigo' => 'CUZ141', 'proteccion' => '0%'],
            ['codigo' => 'CUS8IG', 'proteccion' => '5%'],
            ['codigo' => 'COL8BA', 'proteccion' => '5%'],
            ['codigo' => 'MPI8SM', 'proteccion' => '5%'],
            ['codigo' => 'LIN419', 'proteccion' => '5%'],
            ['codigo' => 'MPI504', 'proteccion' => '5%'],
            ['codigo' => 'TRU503', 'proteccion' => '7%'],
            ['codigo' => 'CUZ371', 'proteccion' => '0%'],
            ['codigo' => 'MPIMIP', 'proteccion' => '5%'],
            ['codigo' => 'AQVP14', 'proteccion' => '5%'],
            ['codigo' => 'MPI726', 'proteccion' => '7%'],
            ['codigo' => 'LIM9M1', 'proteccion' => '5%'],
            ['codigo' => 'MPIM02', 'proteccion' => '3%'],
            ['codigo' => 'URI717', 'proteccion' => '7%'],
            ['codigo' => 'PUV141', 'proteccion' => '0%'],
            ['codigo' => 'MPIPI9', 'proteccion' => '5%'],
            ['codigo' => 'CUZ704', 'proteccion' => '7%'],
            ['codigo' => 'LIM8CR', 'proteccion' => '5%'],
            ['codigo' => 'LIN435', 'proteccion' => '5%'],
            ['codigo' => 'CUZ417', 'proteccion' => '0%'],
            ['codigo' => 'AQV301', 'proteccion' => '0%'],
            ['codigo' => 'UR1375', 'proteccion' => '0%'],
            ['codigo' => 'PUV532', 'proteccion' => '7%'],
            ['codigo' => 'PUN9PI', 'proteccion' => '5%'],
            ['codigo' => 'ICATU2', 'proteccion' => '8%'],
            ['codigo' => 'URU9VU', 'proteccion' => '5%'],
            ['codigo' => 'PM6P10', 'proteccion' => '10%'],
            ['codigo' => 'UR1370', 'proteccion' => '0%'],
            ['codigo' => 'NAZX10', 'proteccion' => '8%'],
            ['codigo' => 'TRU509', 'proteccion' => '7%'],
            ['codigo' => 'UR1407', 'proteccion' => '5%'],
            ['codigo' => 'LIM8HP', 'proteccion' => '5%'],
            ['codigo' => 'PUVP12', 'proteccion' => '5%'],
            ['codigo' => 'LIN492', 'proteccion' => '0%'],
            ['codigo' => 'LIN40Q', 'proteccion' => '5%'],
            ['codigo' => 'LIM9CM', 'proteccion' => '5%'],
            ['codigo' => 'MPI517', 'proteccion' => '5%'],
            ['codigo' => 'CUZ533', 'proteccion' => '0%'],
            ['codigo' => 'CUZ302', 'proteccion' => '0%'],
            ['codigo' => 'PUV118', 'proteccion' => '0%'],
            ['codigo' => 'LIN511', 'proteccion' => '5%'],
            ['codigo' => 'ICA8DN', 'proteccion' => '5%'],
            ['codigo' => 'LINGL1', 'proteccion' => '5%'],
            ['codigo' => 'PM5P02', 'proteccion' => '10%'],
            ['codigo' => 'PUVP06', 'proteccion' => '7%'],
            ['codigo' => 'MP1MIP', 'proteccion' => '3%'],
            ['codigo' => 'MPIP23', 'proteccion' => '5%'],
            ['codigo' => 'CUZGLE', 'proteccion' => '5%'],
            ['codigo' => 'LINP42', 'proteccion' => '5%'],
            ['codigo' => 'COL8RF', 'proteccion' => '5%'],
            ['codigo' => 'LINGAD', 'proteccion' => '5%'],
            ['codigo' => 'MPIT12', 'proteccion' => '5%'],
            ['codigo' => 'AQP9ZZ', 'proteccion' => '5%'],
            ['codigo' => 'CUS8PX', 'proteccion' => '5%'],
            ['codigo' => 'CUZGL0', 'proteccion' => '5%'],
            ['codigo' => 'CUZXGC', 'proteccion' => '5%'],
            ['codigo' => 'LIMTU2', 'proteccion' => '7%'],
            ['codigo' => 'UR1419', 'proteccion' => '5%'],
            ['codigo' => 'CUS8AR', 'proteccion' => '5%'],
            ['codigo' => 'TRNE33', 'proteccion' => '7%'],
            ['codigo' => 'LIN40K', 'proteccion' => '5%'],
            ['codigo' => 'LIN562', 'proteccion' => '5%'],
            ['codigo' => 'IQ1111', 'proteccion' => '10%'],
            ['codigo' => 'MPIMMC', 'proteccion' => '3%'],
            ['codigo' => 'TRU401', 'proteccion' => '7%'],
            ['codigo' => 'AQV343', 'proteccion' => '0%'],
            ['codigo' => 'CUZ369', 'proteccion' => '0%'],
            ['codigo' => 'UR130A', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ317', 'proteccion' => '0%'],
            ['codigo' => 'LINXBH', 'proteccion' => '5%'],
            ['codigo' => 'TRNE40', 'proteccion' => '7%'],
            ['codigo' => 'CUZ437', 'proteccion' => '5%'],
            ['codigo' => 'CUZ412', 'proteccion' => '0%'],
            ['codigo' => 'PCS305', 'proteccion' => '8%'],
            ['codigo' => 'LINP09', 'proteccion' => '5%'],
            ['codigo' => 'CUZ316', 'proteccion' => '0%'],
            ['codigo' => 'LIN50A', 'proteccion' => '5%'],
            ['codigo' => 'PUN9GH', 'proteccion' => '5%'],
            ['codigo' => 'PM4P23', 'proteccion' => '10%'],
            ['codigo' => 'CUS9MC', 'proteccion' => '5%'],
            ['codigo' => 'TYL111', 'proteccion' => '7%'],
            ['codigo' => 'AQV342', 'proteccion' => '0%'],
            ['codigo' => 'CUZ536', 'proteccion' => '0%'],
            ['codigo' => 'PM2P03', 'proteccion' => '10%'],
            ['codigo' => 'CUS9SO', 'proteccion' => '5%'],
            ['codigo' => 'LIM8MO', 'proteccion' => '5%'],
            ['codigo' => 'UR1471', 'proteccion' => '0%'],
            ['codigo' => 'CIX505', 'proteccion' => '7%'],
            ['codigo' => 'LIN251', 'proteccion' => '0%'],
            ['codigo' => 'PM6P12', 'proteccion' => '10%'],
            ['codigo' => 'AQV302', 'proteccion' => '0%'],
            ['codigo' => 'LIN151', 'proteccion' => '0%'],
            ['codigo' => 'PUV402', 'proteccion' => '5%'],
            ['codigo' => 'UR1X34', 'proteccion' => '5%'],
            ['codigo' => 'AQV516', 'proteccion' => '5%'],
            ['codigo' => 'MPI8CH', 'proteccion' => '5%'],
            ['codigo' => 'CUS9AR', 'proteccion' => '5%'],
            ['codigo' => 'LINGES', 'proteccion' => '5%'],
            ['codigo' => 'MPI8MI', 'proteccion' => '5%'],
            ['codigo' => 'TRU211', 'proteccion' => '7%'],
            ['codigo' => 'LIN40S', 'proteccion' => '5%'],
            ['codigo' => 'UR1420', 'proteccion' => '5%'],
            ['codigo' => 'LIM8GM', 'proteccion' => '5%'],
            ['codigo' => 'LIN493', 'proteccion' => '5%'],
            ['codigo' => 'LIM9MG', 'proteccion' => '5%'],
            ['codigo' => 'PM4P08', 'proteccion' => '10%'],
            ['codigo' => 'CUSXOX', 'proteccion' => '5%'],
            ['codigo' => 'UR1443', 'proteccion' => '5%'],
            ['codigo' => 'AQV415', 'proteccion' => '5%'],
            ['codigo' => 'PUV121', 'proteccion' => '0%'],
            ['codigo' => 'AQP8SA', 'proteccion' => '5%'],
            ['codigo' => 'CUS8TU', 'proteccion' => '5%'],
            ['codigo' => 'CUS9CC', 'proteccion' => '5%'],
            ['codigo' => 'MPIP22', 'proteccion' => '5%'],
            ['codigo' => 'TR2HB1', 'proteccion' => '7%'],
            ['codigo' => 'TRNVD1', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZTL2', 'proteccion' => '0%'],
            ['codigo' => 'LIN533', 'proteccion' => '5%'],
            ['codigo' => 'AQVTL4', 'proteccion' => '0%'],
            ['codigo' => 'LINP38', 'proteccion' => '5%'],
            ['codigo' => 'CUS8LM', 'proteccion' => '5%'],
            ['codigo' => 'UR1X30', 'proteccion' => '5%'],
            ['codigo' => 'CUZ503', 'proteccion' => '0%'],
            ['codigo' => 'ICA414', 'proteccion' => '8%'],
            ['codigo' => 'LIN570', 'proteccion' => '5%'],
            ['codigo' => 'TRNMP1', 'proteccion' => '7%'],
            ['codigo' => 'AQV3A3', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN424', 'proteccion' => '0%'],
            ['codigo' => 'PCS411', 'proteccion' => '8%'],
            ['codigo' => 'LIN4A0', 'proteccion' => '5%'],
            ['codigo' => 'LINP52', 'proteccion' => '5%'],
            ['codigo' => 'PUV119', 'proteccion' => '0%'],
            ['codigo' => 'CUZXA7', 'proteccion' => '5%'],
            ['codigo' => 'AQV144', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXCT', 'proteccion' => '5%'],
            ['codigo' => 'PLANW0', 'proteccion' => '0%'],
            ['codigo' => 'TRNI15', 'proteccion' => '7%'],
            ['codigo' => 'CUS8CX', 'proteccion' => '5%'],
            ['codigo' => 'LINP10', 'proteccion' => '5%'],
            ['codigo' => 'CUZ518', 'proteccion' => '5%'],
            ['codigo' => 'PUVP0B', 'proteccion' => '0%'],
            ['codigo' => 'UR1333', 'proteccion' => '0%'],
            ['codigo' => 'AQV518', 'proteccion' => '3%'],
            ['codigo' => 'CUZ491', 'proteccion' => '5%'],
            ['codigo' => 'CUZX03', 'proteccion' => '5%'],
            ['codigo' => 'PUV241', 'proteccion' => '0%'],
            ['codigo' => 'LIN41O', 'proteccion' => '5%'],
            ['codigo' => 'MPI506', 'proteccion' => '5%'],
            ['codigo' => 'PUVXMW', 'proteccion' => '5%'],
            ['codigo' => 'CUS8RP', 'proteccion' => '5%'],
            ['codigo' => 'PM5P01', 'proteccion' => '10%'],
            ['codigo' => 'CUS9IG', 'proteccion' => '5%'],
            ['codigo' => 'CUZ407', 'proteccion' => '0%'],
            ['codigo' => 'LIN613', 'proteccion' => '5%'],
            ['codigo' => 'PM3P03', 'proteccion' => '10%'],
            ['codigo' => 'AQP8ZZ', 'proteccion' => '5%'],
            ['codigo' => 'PCSX05', 'proteccion' => '8%'],
            ['codigo' => 'PUVP10', 'proteccion' => '5%'],
            ['codigo' => 'PUN704', 'proteccion' => '7%'],
            ['codigo' => 'CIX211', 'proteccion' => '7%'],
            ['codigo' => 'CUS8CC', 'proteccion' => '5%'],
            ['codigo' => 'CUZ308', 'proteccion' => '0%'],
            ['codigo' => 'URU9LI', 'proteccion' => '5%'],
            ['codigo' => 'URU9SR', 'proteccion' => '5%'],
            ['codigo' => 'PUV318', 'proteccion' => '0%'],
            ['codigo' => 'AQVP15', 'proteccion' => '5%'],
            ['codigo' => 'PCS413', 'proteccion' => '8%'],
            ['codigo' => 'URU9IU', 'proteccion' => '5%'],
            ['codigo' => 'CUZX91', 'proteccion' => '7%'],
            ['codigo' => 'PUVP17', 'proteccion' => '5%'],
            ['codigo' => 'CUZ51A', 'proteccion' => '5%'],
            ['codigo' => 'UR1X28', 'proteccion' => '5%'],
            ['codigo' => 'CUZXPC', 'proteccion' => '5%'],
            ['codigo' => 'CUZ413', 'proteccion' => '5%'],
            ['codigo' => 'AQV145', 'proteccion' => '0%'],
            ['codigo' => 'MPI509', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPIPI7', 'proteccion' => '5%'],
            ['codigo' => 'MPI8AY', 'proteccion' => '5%'],
            ['codigo' => 'URU9CY', 'proteccion' => '5%'],
            ['codigo' => 'HAZ141', 'proteccion' => '7%'],
            ['codigo' => 'LIN51O', 'proteccion' => '5%'],
            ['codigo' => 'PM4P20', 'proteccion' => '10%'],
            ['codigo' => 'CUZ142', 'proteccion' => '0%'],
            ['codigo' => 'CUZ530', 'proteccion' => '5%'],
            ['codigo' => 'CUZ440', 'proteccion' => '5%'],
            ['codigo' => 'CUS9RP', 'proteccion' => '5%'],
            ['codigo' => 'AQV245', 'proteccion' => '0%'],
            ['codigo' => 'CUZ5P1', 'proteccion' => '5%'],
            ['codigo' => 'CUS9LI', 'proteccion' => '5%'],
            ['codigo' => 'MPI714', 'proteccion' => '7%'],
            ['codigo' => 'CUZ588', 'proteccion' => '5%'],
            ['codigo' => 'HAZ241', 'proteccion' => '7%'],
            ['codigo' => 'ICA245', 'proteccion' => '8%'],
            ['codigo' => 'TRNV08', 'proteccion' => '7%'],
            ['codigo' => 'UR1506', 'proteccion' => '3%'],
            ['codigo' => 'CUSXCM', 'proteccion' => '5%'],
            ['codigo' => 'PCS8HA', 'proteccion' => '5%'],
            ['codigo' => 'UR1502', 'proteccion' => '5%'],
            ['codigo' => 'CIX111', 'proteccion' => '7%'],
            ['codigo' => 'LIN471', 'proteccion' => '5%'],
            ['codigo' => 'CUZPSK', 'proteccion' => '7%'],
            ['codigo' => 'AQV533', 'proteccion' => '0%'],
            ['codigo' => 'ICA145', 'proteccion' => '8%'],
            ['codigo' => 'LIM9TB', 'proteccion' => '5%'],
            ['codigo' => 'LIN520', 'proteccion' => '5%'],
            ['codigo' => 'UR1547', 'proteccion' => '5%'],
            ['codigo' => 'MPI8PG', 'proteccion' => '5%'],
            ['codigo' => 'CUZGMD', 'proteccion' => '7%'],
            ['codigo' => 'CUZXRK', 'proteccion' => '5%'],
            ['codigo' => 'URU8TK', 'proteccion' => '5%'],
            ['codigo' => 'CUZXNA', 'proteccion' => '5%'],
            ['codigo' => 'LINX21', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN404', 'proteccion' => '5%'],
            ['codigo' => 'UR1505', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5P3', 'proteccion' => '5%'],
            ['codigo' => 'LIM8TS', 'proteccion' => '5%'],
            ['codigo' => 'PUV343', 'proteccion' => '3%'],
            ['codigo' => 'TYL226', 'proteccion' => '7%'],
            ['codigo' => 'UR1739', 'proteccion' => '7%'],
            ['codigo' => 'LIN543', 'proteccion' => '5%'],
            ['codigo' => 'PUV220', 'proteccion' => '0%'],
            ['codigo' => 'CUZ435', 'proteccion' => '5%'],
            ['codigo' => 'IQTP15', 'proteccion' => '10%'],
            ['codigo' => 'LIN405', 'proteccion' => '0%'],
            ['codigo' => 'LIN40J', 'proteccion' => '5%'],
            ['codigo' => 'MPIMUP', 'proteccion' => '5%'],
            ['codigo' => 'CUZ400', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ5P5', 'proteccion' => '5%'],
            ['codigo' => 'PM1P15', 'proteccion' => '10%'],
            ['codigo' => 'PUV50I', 'proteccion' => '7%'],
            ['codigo' => 'UR1332', 'proteccion' => '0%'],
            ['codigo' => 'URU554', 'proteccion' => '3%'],
            ['codigo' => 'PUV142', 'proteccion' => '0%'],
            ['codigo' => 'AQV502', 'proteccion' => '0%'],
            ['codigo' => 'AQVP27', 'proteccion' => '5%'],
            ['codigo' => 'LIN415', 'proteccion' => '5%'],
            ['codigo' => 'PCS8LI', 'proteccion' => '5%'],
            ['codigo' => 'CUZ542', 'proteccion' => '3%'],
            ['codigo' => 'CUZ490', 'proteccion' => '5%'],
            ['codigo' => 'ICATL3', 'proteccion' => '8%'],
            ['codigo' => 'CUZ5P9', 'proteccion' => '5%'],
            ['codigo' => 'IQ1P04', 'proteccion' => '10%'],
            ['codigo' => 'CUZ553', 'proteccion' => '5%'],
            ['codigo' => 'PUVP19', 'proteccion' => '5%'],
            ['codigo' => 'PM1P11', 'proteccion' => '10%'],
            ['codigo' => 'PCS8RP', 'proteccion' => '5%'],
            ['codigo' => 'AQV241', 'proteccion' => '0%'],
            ['codigo' => 'CUS8KI', 'proteccion' => '5%'],
            ['codigo' => 'LIM9SS', 'proteccion' => '5%'],
            ['codigo' => 'LIN527', 'proteccion' => '5%'],
            ['codigo' => 'NAZ415', 'proteccion' => '8%'],
            ['codigo' => 'LIN438', 'proteccion' => '5%'],
            ['codigo' => 'CUZ322', 'proteccion' => '0%'],
            ['codigo' => 'CUZ521', 'proteccion' => '0%'],
            ['codigo' => 'URU9PI', 'proteccion' => '5%'],
            ['codigo' => 'CUS9UH', 'proteccion' => '5%'],
            ['codigo' => 'CUZ241', 'proteccion' => '0%'],
            ['codigo' => 'LINXEX', 'proteccion' => '5%'],
            ['codigo' => 'UR1350', 'proteccion' => 'N.A.'],
            ['codigo' => 'COL8AW', 'proteccion' => '5%'],
            ['codigo' => 'LIN461', 'proteccion' => '5%'],
            ['codigo' => 'LINX60', 'proteccion' => '5%'],
            ['codigo' => 'PCS4I3', 'proteccion' => '8%'],
            ['codigo' => 'COL9KI', 'proteccion' => '5%'],
            ['codigo' => 'CUZXSU', 'proteccion' => '5%'],
            ['codigo' => 'LIM9GO', 'proteccion' => '5%'],
            ['codigo' => 'NAZ143', 'proteccion' => '8%'],
            ['codigo' => 'CUS8CG', 'proteccion' => '5%'],
            ['codigo' => 'UR1335', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIM8CW', 'proteccion' => '5%'],
            ['codigo' => 'LINXLG', 'proteccion' => '5%'],
            ['codigo' => 'PUV415', 'proteccion' => '5%'],
            ['codigo' => 'COL9PZ', 'proteccion' => '5%'],
            ['codigo' => 'LIN407', 'proteccion' => '5%'],
            ['codigo' => 'PCS308', 'proteccion' => '8%'],
            ['codigo' => 'PUVP02', 'proteccion' => '5%'],
            ['codigo' => 'LIN41N', 'proteccion' => '5%'],
            ['codigo' => 'LINGL7', 'proteccion' => '5%'],
            ['codigo' => 'NAZ243', 'proteccion' => '8%'],
            ['codigo' => 'PCS9HA', 'proteccion' => '5%'],
            ['codigo' => 'PCS9LI', 'proteccion' => '5%'],
            ['codigo' => 'CUZXAL', 'proteccion' => '7%'],
            ['codigo' => 'CUS9TF', 'proteccion' => '5%'],
            ['codigo' => 'PUV115', 'proteccion' => '0%'],
            ['codigo' => 'CHJ213', 'proteccion' => '7%'],
            ['codigo' => 'CUZXCE', 'proteccion' => '5%'],
            ['codigo' => 'PIU126', 'proteccion' => '7%'],
            ['codigo' => 'PM4P05', 'proteccion' => '10%'],
            ['codigo' => 'TRU513', 'proteccion' => '7%'],
            ['codigo' => 'CUS8LR', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQ6', 'proteccion' => '7%'],
            ['codigo' => 'ICAX22', 'proteccion' => '5%'],
            ['codigo' => 'LINXCR', 'proteccion' => '5%'],
            ['codigo' => 'MPIP1O', 'proteccion' => '5%'],
            ['codigo' => 'UR1373', 'proteccion' => 'N.A.'],
            ['codigo' => 'URU8IU', 'proteccion' => '5%'],
            ['codigo' => 'URUX26', 'proteccion' => '5%'],
            ['codigo' => 'CUS8MC', 'proteccion' => '5%'],
            ['codigo' => 'LIN448', 'proteccion' => '5%'],
            ['codigo' => 'CUZMMA', 'proteccion' => '3%'],
            ['codigo' => 'LINP21', 'proteccion' => '5%'],
            ['codigo' => 'TRNMP2', 'proteccion' => '7%'],
            ['codigo' => 'CUZ467', 'proteccion' => '5%'],
            ['codigo' => 'LIN436', 'proteccion' => '5%'],
            ['codigo' => 'UR1473', 'proteccion' => '0%'],
            ['codigo' => 'UR1X04', 'proteccion' => '5%'],
            ['codigo' => 'AQV344', 'proteccion' => '0%'],
            ['codigo' => 'CHJ502', 'proteccion' => '7%'],
            ['codigo' => 'CUZ5I9', 'proteccion' => '5%'],
            ['codigo' => 'URTX05', 'proteccion' => '0%'],
            ['codigo' => 'CUZX14', 'proteccion' => '7%'],
            ['codigo' => 'UR1435', 'proteccion' => '5%'],
            ['codigo' => 'MPI402', 'proteccion' => '5%'],
            ['codigo' => 'PCS211', 'proteccion' => '8%'],
            ['codigo' => 'TYL218', 'proteccion' => '7%'],
            ['codigo' => 'LIN487', 'proteccion' => '5%'],
            ['codigo' => 'LIN509', 'proteccion' => '3%'],
            ['codigo' => 'LIN618', 'proteccion' => '5%'],
            ['codigo' => 'LINGLH', 'proteccion' => '5%'],
            ['codigo' => 'MPIGLB', 'proteccion' => '5%'],
            ['codigo' => 'MPIT11', 'proteccion' => '5%'],
            ['codigo' => 'PM4P07', 'proteccion' => '10%'],
            ['codigo' => 'UR1X35', 'proteccion' => '5%'],
            ['codigo' => 'LIM9BH', 'proteccion' => '5%'],
            ['codigo' => 'TBP125', 'proteccion' => '7%'],
            ['codigo' => 'CUS8RG', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5P7', 'proteccion' => '5%'],
            ['codigo' => 'UR1543', 'proteccion' => '5%'],
            ['codigo' => 'CUZ55H', 'proteccion' => '5%'],
            ['codigo' => 'UR1416', 'proteccion' => '5%'],
            ['codigo' => 'UR1GL1', 'proteccion' => '5%'],
            ['codigo' => 'CHJX02', 'proteccion' => '7%'],
            ['codigo' => 'CUZ534', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPIP10', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPIPI6', 'proteccion' => 'N.A.'],
            ['codigo' => 'PM3P02', 'proteccion' => '10%'],
            ['codigo' => 'CUZ488', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQ7', 'proteccion' => '7%'],
            ['codigo' => 'LIM8EC', 'proteccion' => '5%'],
            ['codigo' => 'LIN40E', 'proteccion' => '5%'],
            ['codigo' => 'NAZTL1', 'proteccion' => '8%'],
            ['codigo' => 'UR1TL9', 'proteccion' => '0%'],
            ['codigo' => 'AQV419', 'proteccion' => '0%'],
            ['codigo' => 'CUS9EI', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5O3', 'proteccion' => '0%'],
            ['codigo' => 'CUZ5P4', 'proteccion' => '5%'],
            ['codigo' => 'LINX03', 'proteccion' => '5%'],
            ['codigo' => 'UR1X12', 'proteccion' => '5%'],
            ['codigo' => 'AQVP21', 'proteccion' => '5%'],
            ['codigo' => 'CUZXTM', 'proteccion' => '5%'],
            ['codigo' => 'PCOX12', 'proteccion' => '8%'],
            ['codigo' => 'TRNE90', 'proteccion' => '7%'],
            ['codigo' => 'UR1726', 'proteccion' => '7%'],
            ['codigo' => 'CIX506', 'proteccion' => '7%'],
            ['codigo' => 'HAZTUV', 'proteccion' => '7%'],
            ['codigo' => 'LIN522', 'proteccion' => '5%'],
            ['codigo' => 'LIN596', 'proteccion' => '5%'],
            ['codigo' => 'HAZ527', 'proteccion' => '7%'],
            ['codigo' => 'INCAR1', 'proteccion' => '7%'],
            ['codigo' => 'IQTP13', 'proteccion' => '10%'],
            ['codigo' => 'LINGL2', 'proteccion' => '5%'],
            ['codigo' => 'MPIP15', 'proteccion' => 'N.A.'],
            ['codigo' => 'CHJ113', 'proteccion' => '7%'],
            ['codigo' => 'CUS9PN', 'proteccion' => '5%'],
            ['codigo' => 'LIN41V', 'proteccion' => '5%'],
            ['codigo' => 'MPI501', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPI8SU', 'proteccion' => '5%'],
            ['codigo' => 'CUZMAP', 'proteccion' => '3%'],
            ['codigo' => 'ICATU6', 'proteccion' => '8%'],
            ['codigo' => 'LIM9CD', 'proteccion' => '5%'],
            ['codigo' => 'LINX13', 'proteccion' => '5%'],
            ['codigo' => 'AQP8SI', 'proteccion' => '5%'],
            ['codigo' => 'AQP8SS', 'proteccion' => '5%'],
            ['codigo' => 'AQVXMW', 'proteccion' => '5%'],
            ['codigo' => 'CUZ245', 'proteccion' => '0%'],
            ['codigo' => 'CUZPQF', 'proteccion' => '7%'],
            ['codigo' => 'PUV301', 'proteccion' => '0%'],
            ['codigo' => 'UR1470', 'proteccion' => '0%'],
            ['codigo' => 'LIN589', 'proteccion' => '5%'],
            ['codigo' => 'LIN626', 'proteccion' => '5%'],
            ['codigo' => 'MPI9CC', 'proteccion' => '5%'],
            ['codigo' => 'UR1436', 'proteccion' => '5%'],
            ['codigo' => 'AQV423', 'proteccion' => '5%'],
            ['codigo' => 'AQVP24', 'proteccion' => '5%'],
            ['codigo' => 'CUZ307', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN40B', 'proteccion' => '5%'],
            ['codigo' => 'AQVGL7', 'proteccion' => '5%'],
            ['codigo' => 'CUSXPA', 'proteccion' => '5%'],
            ['codigo' => 'CUZ421', 'proteccion' => '5%'],
            ['codigo' => 'MPI8SG', 'proteccion' => '5%'],
            ['codigo' => 'PM7P11', 'proteccion' => '10%'],
            ['codigo' => 'URI515', 'proteccion' => 'N.A.'],
            ['codigo' => 'ICATU4', 'proteccion' => '8%'],
            ['codigo' => 'LIM9HB', 'proteccion' => '5%'],
            ['codigo' => 'LIMTUV', 'proteccion' => '7%'],
            ['codigo' => 'LIN608', 'proteccion' => '5%'],
            ['codigo' => 'CUS9LM', 'proteccion' => '5%'],
            ['codigo' => 'CUZMRE', 'proteccion' => '3%'],
            ['codigo' => 'UR1406', 'proteccion' => '0%'],
            ['codigo' => 'AQP8ZG', 'proteccion' => '5%'],
            ['codigo' => 'TRNF45', 'proteccion' => '7%'],
            ['codigo' => 'CUZ242', 'proteccion' => '0%'],
            ['codigo' => 'CUZ550', 'proteccion' => '5%'],
            ['codigo' => 'LIN51A', 'proteccion' => '5%'],
            ['codigo' => 'PM7P12', 'proteccion' => '10%'],
            ['codigo' => 'AQV409', 'proteccion' => '5%'],
            ['codigo' => 'ICATU3', 'proteccion' => '8%'],
            ['codigo' => 'NAZTU8', 'proteccion' => '7%'],
            ['codigo' => 'URUAWA', 'proteccion' => '5%'],
            ['codigo' => 'CIX527', 'proteccion' => '7%'],
            ['codigo' => 'IQ1P13', 'proteccion' => '10%'],
            ['codigo' => 'LIN620', 'proteccion' => '5%'],
            ['codigo' => 'MPI701', 'proteccion' => '7%'],
            ['codigo' => 'PUVP08', 'proteccion' => '5%'],
            ['codigo' => 'UR1583', 'proteccion' => '5%'],
            ['codigo' => 'UR17O9', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ506', 'proteccion' => '5%'],
            ['codigo' => 'CUZXTP', 'proteccion' => '5%'],
            ['codigo' => 'LIN41M', 'proteccion' => '5%'],
            ['codigo' => 'UR1XPD', 'proteccion' => '5%'],
            ['codigo' => 'PIU115', 'proteccion' => '7%'],
            ['codigo' => 'CHJ505', 'proteccion' => '7%'],
            ['codigo' => 'CUZXCK', 'proteccion' => '5%'],
            ['codigo' => 'LINP04', 'proteccion' => '5%'],
            ['codigo' => 'URU8PD', 'proteccion' => '5%'],
            ['codigo' => 'AQV418', 'proteccion' => '5%'],
            ['codigo' => 'CUZP36', 'proteccion' => '5%'],
            ['codigo' => 'CUZPLX', 'proteccion' => '7%'],
            ['codigo' => 'LINXCU', 'proteccion' => '5%'],
            ['codigo' => 'PM2P04', 'proteccion' => '10%'],
            ['codigo' => 'PUN9LO', 'proteccion' => '5%'],
            ['codigo' => 'CHJ507', 'proteccion' => '7%'],
            ['codigo' => 'COL8PZ', 'proteccion' => '5%'],
            ['codigo' => 'ICA8QE', 'proteccion' => '5%'],
            ['codigo' => 'PCS121', 'proteccion' => '8%'],
            ['codigo' => 'UR1401', 'proteccion' => '0%'],
            ['codigo' => 'UR1551', 'proteccion' => '5%'],
            ['codigo' => 'LIN4AD', 'proteccion' => '5%'],
            ['codigo' => 'NAZ8DG', 'proteccion' => '5%'],
            ['codigo' => 'CUZ489', 'proteccion' => '5%'],
            ['codigo' => 'CUZ554', 'proteccion' => '0%'],
            ['codigo' => 'CUZXLT', 'proteccion' => '5%'],
            ['codigo' => 'LIN40G', 'proteccion' => '5%'],
            ['codigo' => 'LIN5S9', 'proteccion' => '5%'],
            ['codigo' => 'UR1493', 'proteccion' => '5%'],
            ['codigo' => 'UR1X33', 'proteccion' => '5%'],
            ['codigo' => 'LIN51R', 'proteccion' => '5%'],
            ['codigo' => 'LINXA0', 'proteccion' => '5%'],
            ['codigo' => 'CUZ468', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQTP33', 'proteccion' => '10%'],
            ['codigo' => 'UR1492', 'proteccion' => '5%'],
            ['codigo' => 'AQVP13', 'proteccion' => '5%'],
            ['codigo' => 'CIX212', 'proteccion' => '7%'],
            ['codigo' => 'CUZPE6', 'proteccion' => '0%'],
            ['codigo' => 'CUZXPI', 'proteccion' => '5%'],
            ['codigo' => 'UR1X13', 'proteccion' => '5%'],
            ['codigo' => 'CIX526', 'proteccion' => '7%'],
            ['codigo' => 'CUZP86', 'proteccion' => '5%'],
            ['codigo' => 'NAZ403', 'proteccion' => '8%'],
            ['codigo' => 'PUV5A1', 'proteccion' => '7%'],
            ['codigo' => 'CUS9TI', 'proteccion' => '5%'],
            ['codigo' => 'HAZ528', 'proteccion' => '7%'],
            ['codigo' => 'LIMTUA', 'proteccion' => '7%'],
            ['codigo' => 'CUS9QA', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQN', 'proteccion' => '7%'],
            ['codigo' => 'CUZPQV', 'proteccion' => '7%'],
            ['codigo' => 'HAZ532', 'proteccion' => '7%'],
            ['codigo' => 'LIM8SS', 'proteccion' => '5%'],
            ['codigo' => 'PM1P18', 'proteccion' => '10%'],
            ['codigo' => 'UR1X09', 'proteccion' => '5%'],
            ['codigo' => 'CUZ146', 'proteccion' => '0%'],
            ['codigo' => 'CUZXPL', 'proteccion' => '5%'],
            ['codigo' => 'HAZ512', 'proteccion' => '7%'],
            ['codigo' => 'ICAGLF', 'proteccion' => '5%'],
            ['codigo' => 'LIM9TC', 'proteccion' => '5%'],
            ['codigo' => 'LIN5P7', 'proteccion' => '5%'],
            ['codigo' => 'MPI725', 'proteccion' => '7%'],
            ['codigo' => 'PM2P02', 'proteccion' => '10%'],
            ['codigo' => 'URI500', 'proteccion' => 'N.A.'],
            ['codigo' => 'CHJ8GO', 'proteccion' => '5%'],
            ['codigo' => 'CHJX01', 'proteccion' => '7%'],
            ['codigo' => 'COL8CG', 'proteccion' => '5%'],
            ['codigo' => 'LIMTU8', 'proteccion' => '7%'],
            ['codigo' => 'PM6P92', 'proteccion' => '10%'],
            ['codigo' => 'TYL118', 'proteccion' => '7%'],
            ['codigo' => 'IQ4P01', 'proteccion' => '10%'],
            ['codigo' => 'LIMTUD', 'proteccion' => '7%'],
            ['codigo' => 'PM4P04', 'proteccion' => '10%'],
            ['codigo' => 'LIM8DO', 'proteccion' => '5%'],
            ['codigo' => 'LINX06', 'proteccion' => '5%'],
            ['codigo' => 'PCS8DT', 'proteccion' => '5%'],
            ['codigo' => 'CUS9LR', 'proteccion' => '5%'],
            ['codigo' => 'IQTP16', 'proteccion' => '10%'],
            ['codigo' => 'PM9P05', 'proteccion' => '10%'],
            ['codigo' => 'CHJ403', 'proteccion' => '7%'],
            ['codigo' => 'HAZ503', 'proteccion' => '7%'],
            ['codigo' => 'ICAXCA', 'proteccion' => '5%'],
            ['codigo' => 'LINOP2', 'proteccion' => '5%'],
            ['codigo' => 'PU1508', 'proteccion' => 'N.A.'],
            ['codigo' => 'PUN9HP', 'proteccion' => '5%'],
            ['codigo' => 'CIX404', 'proteccion' => '7%'],
            ['codigo' => 'IQ1231', 'proteccion' => '10%'],
            ['codigo' => 'PUV539', 'proteccion' => '7%'],
            ['codigo' => 'CIX406', 'proteccion' => '7%'],
            ['codigo' => 'CIXTL7', 'proteccion' => '7%'],
            ['codigo' => 'LIN112', 'proteccion' => '5%'],
            ['codigo' => 'TYL119', 'proteccion' => '7%'],
            ['codigo' => 'TYL219', 'proteccion' => '7%'],
            ['codigo' => 'UR1X41', 'proteccion' => '5%'],
            ['codigo' => 'AQVP29', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXPT', 'proteccion' => '5%'],
            ['codigo' => 'HAZ529', 'proteccion' => '7%'],
            ['codigo' => 'HAZTUA', 'proteccion' => '7%'],
            ['codigo' => 'LIN409', 'proteccion' => '5%'],
            ['codigo' => 'NAZ418', 'proteccion' => '8%'],
            ['codigo' => 'UR1512', 'proteccion' => '3%'],
            ['codigo' => 'URU8ME', 'proteccion' => '5%'],
            ['codigo' => 'URU9SL', 'proteccion' => '5%'],
            ['codigo' => 'CUS8UH', 'proteccion' => '5%'],
            ['codigo' => 'CUZ512', 'proteccion' => '5%'],
            ['codigo' => 'ICAT09', 'proteccion' => '7%'],
            ['codigo' => 'IQTP20', 'proteccion' => '10%'],
            ['codigo' => 'CHJ405', 'proteccion' => '7%'],
            ['codigo' => 'COL9CB', 'proteccion' => '5%'],
            ['codigo' => 'CUS8JN', 'proteccion' => '5%'],
            ['codigo' => 'CUS8TF', 'proteccion' => '5%'],
            ['codigo' => 'LIM8AF', 'proteccion' => '5%'],
            ['codigo' => 'LIN414', 'proteccion' => '5%'],
            ['codigo' => 'PCS8PS', 'proteccion' => '5%'],
            ['codigo' => 'PUV302', 'proteccion' => '0%'],
            ['codigo' => 'CUZPQG', 'proteccion' => '7%'],
            ['codigo' => 'LIMTU9', 'proteccion' => '7%'],
            ['codigo' => 'LIN576', 'proteccion' => '5%'],
            ['codigo' => 'MPI70P', 'proteccion' => '7%'],
            ['codigo' => 'MPI722', 'proteccion' => '7%'],
            ['codigo' => 'NAZTU1', 'proteccion' => '8%'],
            ['codigo' => 'TYL227', 'proteccion' => '7%'],
            ['codigo' => 'AQVTL2', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXTF', 'proteccion' => '5%'],
            ['codigo' => 'HAZ501', 'proteccion' => '7%'],
            ['codigo' => 'PCS418', 'proteccion' => '8%'],
            ['codigo' => 'PM1P13', 'proteccion' => '10%'],
            ['codigo' => 'PUV120', 'proteccion' => '0%'],
            ['codigo' => 'PUV30J', 'proteccion' => '0%'],
            ['codigo' => 'TRNE35', 'proteccion' => '7%'],
            ['codigo' => 'URU526', 'proteccion' => '5%'],
            ['codigo' => 'AQP9ZI', 'proteccion' => '5%'],
            ['codigo' => 'AQVP32', 'proteccion' => '5%'],
            ['codigo' => 'CUS8TI', 'proteccion' => '5%'],
            ['codigo' => 'PM4PL2', 'proteccion' => '10%'],
            ['codigo' => 'PUV312', 'proteccion' => '0%'],
            ['codigo' => 'CJ1211', 'proteccion' => '7%'],
            ['codigo' => 'CUZ587', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQE', 'proteccion' => 'N.A.'],
            ['codigo' => 'LINP49', 'proteccion' => '5%'],
            ['codigo' => 'LINX52', 'proteccion' => '5%'],
            ['codigo' => 'PCS405', 'proteccion' => '8%'],
            ['codigo' => 'UR1439', 'proteccion' => '5%'],
            ['codigo' => 'UR1XDC', 'proteccion' => '5%'],
            ['codigo' => 'URU9WY', 'proteccion' => '5%'],
            ['codigo' => 'CIX215', 'proteccion' => '7%'],
            ['codigo' => 'COL8CB', 'proteccion' => '5%'],
            ['codigo' => 'COL8CR', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQB', 'proteccion' => '7%'],
            ['codigo' => 'LINP43', 'proteccion' => '5%'],
            ['codigo' => 'PCS9DT', 'proteccion' => '5%'],
            ['codigo' => 'PM4P06', 'proteccion' => '10%'],
            ['codigo' => 'TRU402', 'proteccion' => '7%'],
            ['codigo' => 'AQVP17', 'proteccion' => '5%'],
            ['codigo' => 'CJ1401', 'proteccion' => '7%'],
            ['codigo' => 'IQAP38', 'proteccion' => '10%'],
            ['codigo' => 'LIN50G', 'proteccion' => '5%'],
            ['codigo' => 'MPIX03', 'proteccion' => '7%'],
            ['codigo' => 'PUN8LO', 'proteccion' => '5%'],
            ['codigo' => 'UR1XLB', 'proteccion' => '5%'],
            ['codigo' => 'AQV414', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5P2', 'proteccion' => '5%'],
            ['codigo' => 'LIM9ET', 'proteccion' => '5%'],
            ['codigo' => 'PUVGL2', 'proteccion' => '5%'],
            ['codigo' => 'UR1571', 'proteccion' => '5%'],
            ['codigo' => 'CHJ211', 'proteccion' => '7%'],
            ['codigo' => 'CHJ401', 'proteccion' => '7%'],
            ['codigo' => 'LIN1A5', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN4SF', 'proteccion' => '5%'],
            ['codigo' => 'MPI502', 'proteccion' => 'N.A.'],
            ['codigo' => 'NAZTU2', 'proteccion' => '8%'],
            ['codigo' => 'PCST09', 'proteccion' => '7%'],
            ['codigo' => 'PUV403', 'proteccion' => '5%'],
            ['codigo' => 'CIX112', 'proteccion' => '7%'],
            ['codigo' => 'CUZGLA', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQAP32', 'proteccion' => '10%'],
            ['codigo' => 'LIM8PG', 'proteccion' => '5%'],
            ['codigo' => 'NAZTU5', 'proteccion' => '8%'],
            ['codigo' => 'URU9WA', 'proteccion' => '5%'],
            ['codigo' => 'COLX03', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQL', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQ1P03', 'proteccion' => '10%'],
            ['codigo' => 'LIM9AM', 'proteccion' => '5%'],
            ['codigo' => 'LIN59C', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPIP59', 'proteccion' => 'N.A.'],
            ['codigo' => 'PM1P17', 'proteccion' => '10%'],
            ['codigo' => 'CIX409', 'proteccion' => '7%'],
            ['codigo' => 'CJ1405', 'proteccion' => '7%'],
            ['codigo' => 'CUZ589', 'proteccion' => '5%'],
            ['codigo' => 'CUZX31', 'proteccion' => '5%'],
            ['codigo' => 'IQTP21', 'proteccion' => '10%'],
            ['codigo' => 'LIM8CD', 'proteccion' => '5%'],
            ['codigo' => 'LIM8TB', 'proteccion' => '5%'],
            ['codigo' => 'PU3503', 'proteccion' => 'N.A.'],
            ['codigo' => 'PUVP16', 'proteccion' => '5%'],
            ['codigo' => 'TRU405', 'proteccion' => '7%'],
            ['codigo' => 'CIX529', 'proteccion' => '7%'],
            ['codigo' => 'CUZXTC', 'proteccion' => '5%'],
            ['codigo' => 'LIM8BO', 'proteccion' => '5%'],
            ['codigo' => 'MPIP62', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ510', 'proteccion' => '0%'],
            ['codigo' => 'CUZP39', 'proteccion' => '7%'],
            ['codigo' => 'CUZXSA', 'proteccion' => '5%'],
            ['codigo' => 'IQTP11', 'proteccion' => '10%'],
            ['codigo' => 'IQTP24', 'proteccion' => '10%'],
            ['codigo' => 'LIN2A5', 'proteccion' => 'N.A.'],
            ['codigo' => 'LINXUR', 'proteccion' => '5%'],
            ['codigo' => 'PCSGL2', 'proteccion' => '5%'],
            ['codigo' => 'PIU127', 'proteccion' => '7%'],
            ['codigo' => 'PUVP25', 'proteccion' => '7%'],
            ['codigo' => 'CHJ212', 'proteccion' => '7%'],
            ['codigo' => 'CIXT12', 'proteccion' => '7%'],
            ['codigo' => 'CUZXIS', 'proteccion' => '5%'],
            ['codigo' => 'HAZ401', 'proteccion' => '7%'],
            ['codigo' => 'HAZ531', 'proteccion' => '7%'],
            ['codigo' => 'IQ1401', 'proteccion' => '10%'],
            ['codigo' => 'IQTP17', 'proteccion' => '10%'],
            ['codigo' => 'LIM8BH', 'proteccion' => '5%'],
            ['codigo' => 'PUV3A6', 'proteccion' => 'N.A.'],
            ['codigo' => 'AQVP23', 'proteccion' => '5%'],
            ['codigo' => 'CJ1111', 'proteccion' => '7%'],
            ['codigo' => 'CUZ540', 'proteccion' => '5%'],
            ['codigo' => 'CUZXLC', 'proteccion' => '5%'],
            ['codigo' => 'CUZXOH', 'proteccion' => '5%'],
            ['codigo' => 'LIM9TA', 'proteccion' => '5%'],
            ['codigo' => 'LIN599', 'proteccion' => '5%'],
            ['codigo' => 'PM9P01', 'proteccion' => '10%'],
            ['codigo' => 'URUXDC', 'proteccion' => '5%'],
            ['codigo' => 'AQP9CB', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5I0', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQ2P05', 'proteccion' => '10%'],
            ['codigo' => 'AQPTR8', 'proteccion' => '7%'],
            ['codigo' => 'CHJ115', 'proteccion' => '7%'],
            ['codigo' => 'CUS8PT', 'proteccion' => '5%'],
            ['codigo' => 'LIM9DP', 'proteccion' => '5%'],
            ['codigo' => 'LIM9TM', 'proteccion' => '5%'],
            ['codigo' => 'LINXLH', 'proteccion' => '5%'],
            ['codigo' => 'PM9P07', 'proteccion' => '10%'],
            ['codigo' => 'PUN8FG', 'proteccion' => '5%'],
            ['codigo' => 'PUV410', 'proteccion' => '5%'],
            ['codigo' => 'UR1403', 'proteccion' => 'N.A.'],
            ['codigo' => 'URU9PO', 'proteccion' => '5%'],
            ['codigo' => 'URUAW1', 'proteccion' => '5%'],
            ['codigo' => 'AYP111', 'proteccion' => '0%'],
            ['codigo' => 'CHJ506', 'proteccion' => '7%'],
            ['codigo' => 'CIXT11', 'proteccion' => '7%'],
            ['codigo' => 'CUS9PA', 'proteccion' => '5%'],
            ['codigo' => 'HAZ111', 'proteccion' => '7%'],
            ['codigo' => 'IQ1112', 'proteccion' => '10%'],
            ['codigo' => 'LIM8GO', 'proteccion' => '5%'],
            ['codigo' => 'LIN12A', 'proteccion' => '0%'],
            ['codigo' => 'LIN621', 'proteccion' => '5%'],
            ['codigo' => 'LINTL2', 'proteccion' => '3%'],
            ['codigo' => 'MPI405', 'proteccion' => '5%'],
            ['codigo' => 'NAZ419', 'proteccion' => '8%'],
            ['codigo' => 'PM4P0B', 'proteccion' => '10%'],
            ['codigo' => 'PM4P24', 'proteccion' => '10%'],
            ['codigo' => 'PUN9HA', 'proteccion' => '5%'],
            ['codigo' => 'TRNE40', 'proteccion' => '7%'],
            ['codigo' => 'TRU501', 'proteccion' => '7%'],
            ['codigo' => 'CUS8EI', 'proteccion' => '5%'],
            ['codigo' => 'CUZGLB', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXHQ', 'proteccion' => '5%'],
            ['codigo' => 'CUZXTB', 'proteccion' => '5%'],
            ['codigo' => 'IQTP25', 'proteccion' => '10%'],
            ['codigo' => 'LINXCT', 'proteccion' => '5%'],
            ['codigo' => 'LINXPI', 'proteccion' => '5%'],
            ['codigo' => 'AQP8SQ', 'proteccion' => '5%'],
            ['codigo' => 'AQV4A1', 'proteccion' => 'N.A.'],
            ['codigo' => 'AYP211', 'proteccion' => '0%'],
            ['codigo' => 'CUZ709', 'proteccion' => '7%'],
            ['codigo' => 'HAZ211', 'proteccion' => '7%'],
            ['codigo' => 'ICA403', 'proteccion' => '8%'],
            ['codigo' => 'IQAP40', 'proteccion' => '10%'],
            ['codigo' => 'PCST08', 'proteccion' => '7%'],
            ['codigo' => 'PM4PL3', 'proteccion' => '10%'],
            ['codigo' => 'PM9P03', 'proteccion' => '10%'],
            ['codigo' => 'TBP126', 'proteccion' => '7%'],
            ['codigo' => 'TRNE93', 'proteccion' => '7%'],
            ['codigo' => 'TRU410', 'proteccion' => '7%'],
            ['codigo' => 'UR151A', 'proteccion' => 'N.A.'],
            ['codigo' => 'URU8TW', 'proteccion' => '5%'],
            ['codigo' => 'CJ1402', 'proteccion' => '7%'],
            ['codigo' => 'CUS8QA', 'proteccion' => '5%'],
            ['codigo' => 'CUS9RA', 'proteccion' => '5%'],
            ['codigo' => 'CUZ246', 'proteccion' => '0%'],
            ['codigo' => 'CUZ464', 'proteccion' => '5%'],
            ['codigo' => 'IQTP06', 'proteccion' => '10%'],
            ['codigo' => 'AQPTRA', 'proteccion' => '7%'],
            ['codigo' => 'CIX401', 'proteccion' => '7%'],
            ['codigo' => 'CUT504', 'proteccion' => '7%'],
            ['codigo' => 'CUZ480', 'proteccion' => '5%'],
            ['codigo' => 'CUZPQY', 'proteccion' => '7%'],
            ['codigo' => 'HAZ502', 'proteccion' => '7%'],
            ['codigo' => 'IQ4P03', 'proteccion' => '10%'],
            ['codigo' => 'IQTP28', 'proteccion' => '10%'],
            ['codigo' => 'LIM8ET', 'proteccion' => '5%'],
            ['codigo' => 'LINX02', 'proteccion' => '5%'],
            ['codigo' => 'LINXEO', 'proteccion' => '5%'],
            ['codigo' => 'MPIGL8', 'proteccion' => 'N.A.'],
            ['codigo' => 'NAZTU6', 'proteccion' => '8%'],
            ['codigo' => 'URU9CO', 'proteccion' => '5%'],
            ['codigo' => 'AQP8CH', 'proteccion' => '5%'],
            ['codigo' => 'CUS9CG', 'proteccion' => '5%'],
            ['codigo' => 'CUZ466', 'proteccion' => '5%'],
            ['codigo' => 'CUZXME', 'proteccion' => '5%'],
            ['codigo' => 'LINP57', 'proteccion' => '7%'],
            ['codigo' => 'MPI516', 'proteccion' => 'N.A.'],
            ['codigo' => 'PM6P91', 'proteccion' => '10%'],
            ['codigo' => 'URU9EX', 'proteccion' => '5%'],
            ['codigo' => 'AQPP13', 'proteccion' => 'N.A.'],
            ['codigo' => 'AQVP0E', 'proteccion' => '5%'],
            ['codigo' => 'CUZ4MC', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXFE', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZXMS', 'proteccion' => '5%'],
            ['codigo' => 'ICAT08', 'proteccion' => '7%'],
            ['codigo' => 'IQAP04', 'proteccion' => '10%'],
            ['codigo' => 'LIM9AI', 'proteccion' => '5%'],
            ['codigo' => 'LIM9SE', 'proteccion' => '5%'],
            ['codigo' => 'LIN4AC', 'proteccion' => 'N.A.'],
            ['codigo' => 'PCS8AS', 'proteccion' => '5%'],
            ['codigo' => 'PM4PL1', 'proteccion' => '10%'],
            ['codigo' => 'PUN8GH', 'proteccion' => '5%'],
            ['codigo' => 'TPPP16', 'proteccion' => '10%'],
            ['codigo' => 'TRU515', 'proteccion' => '7%'],
            ['codigo' => 'UR1XTC', 'proteccion' => '5%'],
            ['codigo' => 'URU8HR', 'proteccion' => '5%'],
            ['codigo' => 'URUXHT', 'proteccion' => '5%'],
            ['codigo' => 'AQP8CB', 'proteccion' => '5%'],
            ['codigo' => 'AQP9ZG', 'proteccion' => '5%'],
            ['codigo' => 'CIX510', 'proteccion' => '7%'],
            ['codigo' => 'COL8AG', 'proteccion' => '5%'],
            ['codigo' => 'CUZ504', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ5S4', 'proteccion' => '5%'],
            ['codigo' => 'CUZTL3', 'proteccion' => '5%'],
            ['codigo' => 'IQAP34', 'proteccion' => '10%'],
            ['codigo' => 'LIN473', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN614', 'proteccion' => 'N.A.'],
            ['codigo' => 'LINOP0', 'proteccion' => '7%'],
            ['codigo' => 'LINXMP', 'proteccion' => '5%'],
            ['codigo' => 'PM4P09', 'proteccion' => '10%'],
            ['codigo' => 'URU9HR', 'proteccion' => '5%'],
            ['codigo' => 'AQV30A', 'proteccion' => '0%'],
            ['codigo' => 'AQVP1C', 'proteccion' => 'N.A.'],
            ['codigo' => 'AYP501', 'proteccion' => '0%'],
            ['codigo' => 'CIXTL6', 'proteccion' => '7%'],
            ['codigo' => 'CJ1501', 'proteccion' => '7%'],
            ['codigo' => 'CUZ551', 'proteccion' => '5%'],
            ['codigo' => 'HAZ506', 'proteccion' => '7%'],
            ['codigo' => 'PM4P21', 'proteccion' => '10%'],
            ['codigo' => 'PM4P26', 'proteccion' => '10%'],
            ['codigo' => 'AQP8ZI', 'proteccion' => '5%'],
            ['codigo' => 'AQVP01', 'proteccion' => 'N.A.'],
            ['codigo' => 'AQVX05', 'proteccion' => 'N.A.'],
            ['codigo' => 'CIX8FG', 'proteccion' => '5%'],
            ['codigo' => 'CJ1403', 'proteccion' => '7%'],
            ['codigo' => 'COL8ER', 'proteccion' => '5%'],
            ['codigo' => 'CUZ3A6', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQTP29', 'proteccion' => '10%'],
            ['codigo' => 'LINPA2', 'proteccion' => '5%'],
            ['codigo' => 'MPI728', 'proteccion' => '7%'],
            ['codigo' => 'NAZ9DB', 'proteccion' => '5%'],
            ['codigo' => 'PUVP18', 'proteccion' => '5%'],
            ['codigo' => 'TYL220', 'proteccion' => '7%'],
            ['codigo' => 'UR1490', 'proteccion' => '5%'],
            ['codigo' => 'UR1P20', 'proteccion' => 'N.A.'],
            ['codigo' => 'URT715', 'proteccion' => '7%'],
            ['codigo' => 'URU553', 'proteccion' => '5%'],
            ['codigo' => 'URU8VU', 'proteccion' => '5%'],
            ['codigo' => 'AQP9CN', 'proteccion' => '5%'],
            ['codigo' => 'CUZ591', 'proteccion' => '5%'],
            ['codigo' => 'CUZXOQ', 'proteccion' => '5%'],
            ['codigo' => 'ICA244', 'proteccion' => '8%'],
            ['codigo' => 'MPT721', 'proteccion' => '7%'],
            ['codigo' => 'PCSGL3', 'proteccion' => '5%'],
            ['codigo' => 'UR152A', 'proteccion' => 'N.A.'],
            ['codigo' => 'URU8CY', 'proteccion' => '5%'],
            ['codigo' => 'AYP401', 'proteccion' => '0%'],
            ['codigo' => 'AYP502', 'proteccion' => '0%'],
            ['codigo' => 'CIX403', 'proteccion' => '7%'],
            ['codigo' => 'CUZ228', 'proteccion' => 'N.A.'],
            ['codigo' => 'ICA143', 'proteccion' => '8%'],
            ['codigo' => 'ica405', 'proteccion' => '8%'],
            ['codigo' => 'IQAP25', 'proteccion' => '10%'],
            ['codigo' => 'MPIT14', 'proteccion' => 'N.A.'],
            ['codigo' => 'PM1P19', 'proteccion' => '10%'],
            ['codigo' => 'AQPTR6', 'proteccion' => '5%'],
            ['codigo' => 'AQVXMP', 'proteccion' => '5%'],
            ['codigo' => 'CUS9II', 'proteccion' => '5%'],
            ['codigo' => 'CUZ5Y1', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZPQI', 'proteccion' => '7%'],
            ['codigo' => 'CUZPQK', 'proteccion' => '7%'],
            ['codigo' => 'LIM8PA', 'proteccion' => '5%'],
            ['codigo' => 'LIN444', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN5S8', 'proteccion' => '5%'],
            ['codigo' => 'LINP32', 'proteccion' => 'N.A.'],
            ['codigo' => 'TR2VD1', 'proteccion' => '7%'],
            ['codigo' => 'TRNHM1', 'proteccion' => '7%'],
            ['codigo' => 'UR15I9', 'proteccion' => 'N.A.'],
            ['codigo' => 'URP727', 'proteccion' => 'N.A.'],
            ['codigo' => 'URT721', 'proteccion' => '7%'],
            ['codigo' => 'AQVGL6', 'proteccion' => '5%'],
            ['codigo' => 'AQVXEA', 'proteccion' => '5%'],
            ['codigo' => 'CHJ111', 'proteccion' => '7%'],
            ['codigo' => 'CUS8RA', 'proteccion' => '5%'],
            ['codigo' => 'CUZXA9', 'proteccion' => 'N.A.'],
            ['codigo' => 'IQAP26', 'proteccion' => '10%'],
            ['codigo' => 'LIN1H2', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN541', 'proteccion' => '5%'],
            ['codigo' => 'LIN542', 'proteccion' => '5%'],
            ['codigo' => 'LINT08', 'proteccion' => '10%'],
            ['codigo' => 'NAZ410', 'proteccion' => '8%'],
            ['codigo' => 'NAZ8DB', 'proteccion' => '5%'],
            ['codigo' => 'PCS40B', 'proteccion' => '8%'],
            ['codigo' => 'TR2E83', 'proteccion' => '7%'],
            ['codigo' => 'TRNH11', 'proteccion' => '7%'],
            ['codigo' => 'URU8CO', 'proteccion' => '5%'],
            ['codigo' => 'AQVXTO', 'proteccion' => '5%'],
            ['codigo' => 'CHJ405', 'proteccion' => '7%'],
            ['codigo' => 'COL9AG', 'proteccion' => '5%'],
            ['codigo' => 'CUS9RP', 'proteccion' => '5%'],
            ['codigo' => 'HAZ530', 'proteccion' => '7%'],
            ['codigo' => 'IQTP09', 'proteccion' => '10%'],
            ['codigo' => 'LIM9OL', 'proteccion' => '5%'],
            ['codigo' => 'LIM9TT', 'proteccion' => '5%'],
            ['codigo' => 'MAN201', 'proteccion' => '10%'],
            ['codigo' => 'PUN8HP', 'proteccion' => '5%'],
            ['codigo' => 'PUVP0A', 'proteccion' => '5%'],
            ['codigo' => 'CIX407', 'proteccion' => '7%'],
            ['codigo' => 'CUZ471', 'proteccion' => 'N.A.'],
            ['codigo' => 'HAZP01', 'proteccion' => '7%'],
            ['codigo' => 'IQ1402', 'proteccion' => '10%'],
            ['codigo' => 'IQTP30', 'proteccion' => '10%'],
            ['codigo' => 'LIN4A9', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIN556', 'proteccion' => '5%'],
            ['codigo' => 'LINP59', 'proteccion' => '7%'],
            ['codigo' => 'PCSGL4', 'proteccion' => '8%'],
            ['codigo' => 'PM2P05', 'proteccion' => '10%'],
            ['codigo' => 'TRU117', 'proteccion' => '7%'],
            ['codigo' => 'AQP8HU', 'proteccion' => '5%'],
            ['codigo' => 'CIX528', 'proteccion' => '7%'],
            ['codigo' => 'CUS9ML', 'proteccion' => '5%'],
            ['codigo' => 'CUZ2A5', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZX38', 'proteccion' => '5%'],
            ['codigo' => 'CUZXTA', 'proteccion' => '5%'],
            ['codigo' => 'IQAP41', 'proteccion' => '10%'],
            ['codigo' => 'IQAP46', 'proteccion' => '10%'],
            ['codigo' => 'LIM8RA', 'proteccion' => '5%'],
            ['codigo' => 'LINX19', 'proteccion' => '5%'],
            ['codigo' => 'MPIXSY', 'proteccion' => '5%'],
            ['codigo' => 'PM1P21', 'proteccion' => '10%'],
            ['codigo' => 'PM7P01', 'proteccion' => '10%'],
            ['codigo' => 'PUV124', 'proteccion' => '0%'],
            ['codigo' => 'PUVP22', 'proteccion' => '7%'],
            ['codigo' => 'URT718', 'proteccion' => '7%'],
            ['codigo' => 'URU8LI', 'proteccion' => '5%'],
            ['codigo' => 'CIX402', 'proteccion' => '7%'],
            ['codigo' => 'CIX405', 'proteccion' => '7%'],
            ['codigo' => 'CUS8PA', 'proteccion' => '5%'],
            ['codigo' => 'CUS9CI', 'proteccion' => '5%'],
            ['codigo' => 'CUZ4A8', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ570', 'proteccion' => 'N.A.'],
            ['codigo' => 'CUZ595', 'proteccion' => '5%'],
            ['codigo' => 'CUZP21', 'proteccion' => '7%'],
            ['codigo' => 'CUZP31', 'proteccion' => '5%'],
            ['codigo' => 'CUZXJN', 'proteccion' => 'N.A.'],
            ['codigo' => 'HAZ504', 'proteccion' => '7%'],
            ['codigo' => 'IQ2P01', 'proteccion' => '10%'],
            ['codigo' => 'IQAP35', 'proteccion' => '10%'],
            ['codigo' => 'IQAP47', 'proteccion' => '10%'],
            ['codigo' => 'LIM8NA', 'proteccion' => 'N.A.'],
            ['codigo' => 'LIMTU7', 'proteccion' => '7%'],
            ['codigo' => 'LINXBO', 'proteccion' => 'N.A.'],
            ['codigo' => 'MPI9MP', 'proteccion' => '5%'],
            ['codigo' => 'PM7P02', 'proteccion' => '10%'],
            ['codigo' => 'PUNXTL', 'proteccion' => '10%'],
            ['codigo' => 'PUVP09', 'proteccion' => 'N.A.'],
            ['codigo' => 'PUVP42', 'proteccion' => '0%'],
            ['codigo' => 'TRU404', 'proteccion' => '7%'],
            ['codigo' => 'UR1P45', 'proteccion' => '0%'],
            ['codigo' => 'URU9SU', 'proteccion' => '5%'],
            ['codigo' => 'URU9UR', 'proteccion' => '5%'],
        ];

        $service_markups = $this->loadServiceMarkupsFromArray($data);

        if (count($service_markups) === 0) {
            $this->error("No se cargaron codigos con porcentaje. Pegue el array manual en el comando.");
            return 1;
        }

        $this->info('Iniciando proceso de clonado de tarifas de servicios (por codigo)');
        $this->info("Anio origen: {$year}  ->  Anio destino: {$year_to}");
        $this->info('Codigos con porcentaje: ' . count($service_markups));

        $total_servicios_procesados = 0;
        $total_servicios_omitidos = 0;
        $total_tarifas_clonadas = 0;

        $codes = array_keys($service_markups);

        Service::where('status', '=', 1)
            ->whereIn('aurora_code', $codes)
            ->with(['service_rate', 'serviceSubCategory'])
            ->orderBy('services.id', 'ASC')
            ->chunk(10, function ($services) use ($year, $year_to, $service_markups, &$total_servicios_procesados, &$total_servicios_omitidos, &$total_tarifas_clonadas) {
                foreach ($services as $service) {
                    $servicio_actualizado = false;
                    $code = strtoupper(trim((string) $service->aurora_code));

                    if (!isset($service_markups[$code])) {
                        $total_servicios_omitidos++;
                        continue;
                    }

                    $markup = $service_markups[$code];
                    $porcentaje = (string) round(($markup - 1) * 100, 2);
                    $this->info("Clonando tarifas {$porcentaje}% de SERVICIO {$code} para {$year_to} - Markup: {$markup}");

                    foreach ($service->service_rate as $rate_plan) {
                        $markup_actual = $markup;

                        // Si el nombre contiene dinamica o dinamica con acento (mal codificado), usar 0% incremento
                        if (
                            stripos($rate_plan->name, 'dinamica') !== false ||
                            stripos($rate_plan->name, 'dinÃ¡mica') !== false
                        ) {
                            $markup_actual = 1.0;
                        }

                        if (!($rate_plan->promotions)) {
                            $has_migrate = DB::table('service_rate_plans')
                                ->where('service_rate_id', $rate_plan->id)
                                ->where('flag_migrate', '<>', 1)
                                ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                ->whereNull('deleted_at')
                                ->orderBy('created_at', 'desc')
                                ->count();

                            if ($has_migrate == 0) {
                                $date_range_services = DB::table('service_rate_plans')
                                    ->where('service_rate_id', $rate_plan->id)
                                    ->where(function ($query) {
                                        $query->orWhere('flag_migrate', '=', 1);
                                        $query->orWhereNull('flag_migrate');
                                    })
                                    ->whereNull('deleted_at')
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                    ->orderBy('created_at', 'desc')
                                    ->get();

                                if ($date_range_services->count() === 0) {
                                    $date_range_services = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('flag_migrate', '=', 0)
                                        ->whereNull('deleted_at')
                                        ->where('date_from', 'LIKE', "%" . $year . "%")
                                        ->get();

                                    if (count($date_range_services) > 0) {
                                        foreach ($date_range_services as $date_range_service) {
                                            $new_date_from = str_replace($year, $year_to, $date_range_service->date_from);
                                            $new_date_to = str_replace($year, $year_to, $date_range_service->date_to);

                                            if ($date_range_service->date_from == $year . '-02-29') {
                                                $fecha = Carbon::parse($year_to . '-02-01');
                                                $new_date_from = $fecha->endOfMonth();
                                            }

                                            if ($date_range_service->date_to == $year . '-02-29') {
                                                $fecha = Carbon::parse($year_to . '-02-01');
                                                $new_date_to = $fecha->endOfMonth();
                                            }

                                            $service_rate_plan = new ServiceRatePlan();
                                            $service_rate_plan->service_rate_id = $date_range_service->service_rate_id;
                                            $service_rate_plan->date_from = $new_date_from;
                                            $service_rate_plan->date_to = $new_date_to;
                                            $service_rate_plan->price_adult = $date_range_service->price_adult * $markup_actual;
                                            $service_rate_plan->price_child = $date_range_service->price_child * $markup_actual;
                                            $service_rate_plan->price_infant = $date_range_service->price_infant * $markup_actual;
                                            $service_rate_plan->price_guide = $date_range_service->price_guide * $markup_actual;
                                            $service_rate_plan->pax_from = $date_range_service->pax_from;
                                            $service_rate_plan->pax_to = $date_range_service->pax_to;
                                            $service_rate_plan->monday = $date_range_service->monday;
                                            $service_rate_plan->tuesday = $date_range_service->tuesday;
                                            $service_rate_plan->wednesday = $date_range_service->wednesday;
                                            $service_rate_plan->thursday = $date_range_service->thursday;
                                            $service_rate_plan->friday = $date_range_service->friday;
                                            $service_rate_plan->saturday = $date_range_service->saturday;
                                            $service_rate_plan->sunday = $date_range_service->sunday;
                                            $service_rate_plan->user_id = $date_range_service->user_id;
                                            $service_rate_plan->service_cancellation_policy_id = $date_range_service->service_cancellation_policy_id;
                                            $service_rate_plan->status = 1;
                                            $service_rate_plan->flag_migrate = 1;
                                            $service_rate_plan->save();

                                            $total_tarifas_clonadas++;
                                            $servicio_actualizado = true;
                                        }
                                        $this->info('Servicio actualizado: ' . $service->id . ' - ' . ($service->aurora_code ?? '') . ' Markup aplicado: ' . $markup_actual);
                                    }
                                }
                            } else {
                                $real_count = DB::table('service_rate_plans')
                                    ->where('service_rate_id', $rate_plan->id)
                                    ->where('flag_migrate', '=', 0)
                                    ->whereNull('deleted_at')
                                    ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                    ->count();

                                if ($real_count > 0) {
                                    $this->info('Tarifa real encontrada 2 (flag_migrate=0) en ' . $year_to . ' para SERVICIO ' . $code . ' - RatePlan ' . $rate_plan->id);

                                    // Clonar rangos faltantes desde el año origen (flag_migrate=0) al año destino
                                    $existing_ranges = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('flag_migrate', '=', 0)
                                        ->whereNull('deleted_at')
                                        ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                        ->get(['date_from', 'date_to'])
                                        ->map(function ($r) {
                                            return $r->date_from . '|' . $r->date_to;
                                        })
                                        ->flip();

                                    $source_ranges = DB::table('service_rate_plans')
                                        ->where('service_rate_id', $rate_plan->id)
                                        ->where('flag_migrate', '=', 0)
                                        ->whereNull('deleted_at')
                                        ->where('date_from', 'LIKE', "%" . $year . "%")
                                        ->get();

                                    foreach ($source_ranges as $date_range_service) {
                                        $new_date_from = str_replace($year, $year_to, $date_range_service->date_from);
                                        $new_date_to = str_replace($year, $year_to, $date_range_service->date_to);

                                        if ($date_range_service->date_from == $year . '-02-29') {
                                            $fecha = Carbon::parse($year_to . '-02-01');
                                            $new_date_from = $fecha->endOfMonth();
                                        }

                                        if ($date_range_service->date_to == $year . '-02-29') {
                                            $fecha = Carbon::parse($year_to . '-02-01');
                                            $new_date_to = $fecha->endOfMonth();
                                        }

                                        // Ajuste puntual: para PM9P05 y PM9P07 mover inicio 04/01 -> 03/01 en el año destino
                                        if (
                                            in_array($code, ['PM9P05', 'PM9P07'], true) &&
                                            $new_date_from === $year_to . '-01-04'
                                        ) {
                                            $new_date_from = $year_to . '-01-03';
                                        }

                                        $range_key = $new_date_from . '|' . $new_date_to;
                                        if (isset($existing_ranges[$range_key])) {
                                            continue;
                                        }

                                        // Evitar clonar rangos que se solapan con tarifas reales ya existentes
                                        $overlap_count = DB::table('service_rate_plans')
                                            ->where('service_rate_id', $rate_plan->id)
                                            ->where('flag_migrate', '=', 0)
                                            ->whereNull('deleted_at')
                                            ->where('date_from', 'LIKE', "%" . $year_to . "%")
                                            ->where('date_from', '<=', $new_date_to)
                                            ->where('date_to', '>=', $new_date_from)
                                            ->count();

                                        if ($overlap_count > 0) {
                                            continue;
                                        }

                                        $service_rate_plan = new ServiceRatePlan();
                                        $service_rate_plan->service_rate_id = $date_range_service->service_rate_id;
                                        $service_rate_plan->date_from = $new_date_from;
                                        $service_rate_plan->date_to = $new_date_to;
                                        $service_rate_plan->price_adult = $date_range_service->price_adult * $markup_actual;
                                        $service_rate_plan->price_child = $date_range_service->price_child * $markup_actual;
                                        $service_rate_plan->price_infant = $date_range_service->price_infant * $markup_actual;
                                        $service_rate_plan->price_guide = $date_range_service->price_guide * $markup_actual;
                                        $service_rate_plan->pax_from = $date_range_service->pax_from;
                                        $service_rate_plan->pax_to = $date_range_service->pax_to;
                                        $service_rate_plan->monday = $date_range_service->monday;
                                        $service_rate_plan->tuesday = $date_range_service->tuesday;
                                        $service_rate_plan->wednesday = $date_range_service->wednesday;
                                        $service_rate_plan->thursday = $date_range_service->thursday;
                                        $service_rate_plan->friday = $date_range_service->friday;
                                        $service_rate_plan->saturday = $date_range_service->saturday;
                                        $service_rate_plan->sunday = $date_range_service->sunday;
                                        $service_rate_plan->user_id = $date_range_service->user_id;
                                        $service_rate_plan->service_cancellation_policy_id = $date_range_service->service_cancellation_policy_id;
                                        $service_rate_plan->status = 1;
                                        $service_rate_plan->flag_migrate = 1;
                                        $service_rate_plan->save();

                                        $total_tarifas_clonadas++;
                                        $servicio_actualizado = true;
                                    }
                                }
                            }
                        }
                    }

                    if ($servicio_actualizado) {
                        $total_servicios_procesados++;
                    } else {
                        $total_servicios_omitidos++;
                    }
                }
            });

        $this->info('--------------- RESUMEN DE EJECUCION ---------------');
        $this->info("Total servicios con clonacion procesada: {$total_servicios_procesados}");
        $this->info("Total servicios omitidos: {$total_servicios_omitidos}");
        $this->info("Total de tarifas de servicios insertadas: {$total_tarifas_clonadas}");
        $this->info('----------------------------------------------------');
        $this->info('Comando CloneServiceRateMarginOfProtectionSingle ejecutado correctamente.');

        return 0;
    }

    private function loadServiceMarkupsFromArray(array $data): array
    {
        $map = [];

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            $code = isset($row['codigo']) ? strtoupper(trim((string) $row['codigo'])) : '';
            $percent_raw = isset($row['proteccion']) ? strtoupper(trim((string) $row['proteccion'])) : '';

            if ($code === '' || $percent_raw === '' || $percent_raw === 'N.A.' || $percent_raw === 'N/A') {
                continue;
            }

            if (preg_match('/^([0-9]+(?:\\.[0-9]+)?)%$/', $percent_raw, $m)) {
                $percent = (float) $m[1];
                $map[$code] = 1 + ($percent / 100);
            }
        }

        return $map;
    }
}
