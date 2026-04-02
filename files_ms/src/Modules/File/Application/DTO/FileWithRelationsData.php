<?php

namespace Src\Modules\File\Application\DTO;


use Src\Modules\File\Domain\ValueObjects\File\Adults;
use Src\Modules\File\Domain\ValueObjects\File\Applicant;
use Src\Modules\File\Domain\ValueObjects\File\BudgetNumber;
use Src\Modules\File\Domain\ValueObjects\File\Children;
use Src\Modules\File\Domain\ValueObjects\File\ClientId;
use Src\Modules\File\Domain\ValueObjects\File\Currency;
use Src\Modules\File\Domain\ValueObjects\File\DateIn;
use Src\Modules\File\Domain\ValueObjects\File\DateOut;
use Src\Modules\File\Domain\ValueObjects\File\Description;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCode;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeProcess;
use Src\Modules\File\Domain\ValueObjects\File\ExecutiveCodeSale;
use Src\Modules\File\Domain\ValueObjects\File\FileCodeAgency;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\File\FileNumber;
use Src\Modules\File\Domain\ValueObjects\File\Group;
use Src\Modules\File\Domain\ValueObjects\File\HaveInvoice;
use Src\Modules\File\Domain\ValueObjects\File\HaveQuote;
use Src\Modules\File\Domain\ValueObjects\File\HaveTicket;
use Src\Modules\File\Domain\ValueObjects\File\HaveVoucher;
use Src\Modules\File\Domain\ValueObjects\File\Infants;
use Src\Modules\File\Domain\ValueObjects\File\Lang;
use Src\Modules\File\Domain\ValueObjects\File\Observation;
use Src\Modules\File\Domain\ValueObjects\File\OrderNumber;
use Src\Modules\File\Domain\ValueObjects\File\Promotion;
use Src\Modules\File\Domain\ValueObjects\File\ReservationId;
use Src\Modules\File\Domain\ValueObjects\File\ReservationNumber;
use Src\Modules\File\Domain\ValueObjects\File\RevisionStages;
use Src\Modules\File\Domain\ValueObjects\File\SaleType;
use Src\Modules\File\Domain\ValueObjects\File\SectorCode;
use Src\Modules\File\Domain\ValueObjects\File\SerieReserveId;
use Src\Modules\File\Domain\ValueObjects\File\Status;
use Src\Modules\File\Domain\ValueObjects\File\Tariff;
use Src\Modules\File\Domain\ValueObjects\File\TotalPax;
use Src\Modules\File\Domain\ValueObjects\File\UseInvoice;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;

class FileWithRelationsData
{
    public function __construct(
        public readonly FileId $id,
        public readonly SerieReserveId $serieReserveId,
        public readonly ClientId $clientId,
        public readonly ReservationId $reservationId,
        public readonly OrderNumber $orderNumber,
        public readonly FileNumber $fileNumber,
        public readonly ReservationNumber $reservationNumber,
        public readonly BudgetNumber $budgetNumber,
        public readonly SectorCode $sectorCode,
        public readonly Group $group,
        public readonly SaleType $saleType,
        public readonly Tariff $tariff,
        public readonly Currency $currency,
        public readonly RevisionStages $revisionStages,
        public readonly ExecutiveCode $executiveCode,
        public readonly ExecutiveCodeSale $executiveCodeSale,
        public readonly ExecutiveCodeProcess $executiveCodeProcess,
        public readonly Applicant $applicant,
        public readonly FileCodeAgency $fileCodeAgency,
        public readonly Description $description,
        public readonly Lang $lang,
        public readonly DateIn $dateIn,
        public readonly DateOut $dateOut,
        public readonly Adults $adults,
        public readonly Children $children,
        public readonly Infants $infants,
        public readonly UseInvoice $useInvoice,
        public readonly Observation $observation,
        public readonly TotalPax $totalPax,
        public readonly HaveQuote $haveQuote,
        public readonly HaveVoucher $haveVoucher,
        public readonly HaveTicket $haveTicket,
        public readonly HaveInvoice $haveInvoice,
        public readonly Status $status,
        public readonly Promotion $promotion,
    ) {

    }

    public static function fromEloquent(FileEloquentModel $eloquentFileRepository): self
    {
        return new self(
            id: new FileId($eloquentFileRepository->id),
            serieReserveId: new SerieReserveId($eloquentFileRepository->serie_reserve_id),
            clientId: new ClientId($eloquentFileRepository->client_id),
            reservationId: new ReservationId($eloquentFileRepository->reservation_id),
            orderNumber: new OrderNumber($eloquentFileRepository->order_number),
            fileNumber: new FileNumber($eloquentFileRepository->file_number),
            reservationNumber: new ReservationNumber($eloquentFileRepository->reservation_number),
            budgetNumber: new BudgetNumber($eloquentFileRepository->budget_number),
            sectorCode: new SectorCode($eloquentFileRepository->sector_code),
            group: new Group($eloquentFileRepository->group),
            saleType: new SaleType($eloquentFileRepository->sale_type),
            tariff: new Tariff($eloquentFileRepository->tariff),
            currency: new Currency($eloquentFileRepository->currency),
            revisionStages: new RevisionStages($eloquentFileRepository->revision_stages),
            executiveCode: new ExecutiveCode($eloquentFileRepository->executive_code),
            executiveCodeSale: new ExecutiveCodeSale($eloquentFileRepository->executive_code_sale),
            executiveCodeProcess: new ExecutiveCodeProcess($eloquentFileRepository->executive_code_process),
            applicant: new Applicant($eloquentFileRepository->applicant),
            fileCodeAgency: new FileCodeAgency($eloquentFileRepository->file_code_agency),
            description: new Description($eloquentFileRepository->description),
            lang: new Lang($eloquentFileRepository->lang),
            dateIn: new DateIn($eloquentFileRepository->date_in),
            dateOut: new DateOut($eloquentFileRepository->date_out),
            adults: new Adults($eloquentFileRepository->adults),
            children: new Children($eloquentFileRepository->children),
            infants: new Infants($eloquentFileRepository->infants),
            useInvoice: new UseInvoice($eloquentFileRepository->use_invoice),
            observation: new Observation($eloquentFileRepository->observation),
            totalPax: new TotalPax($eloquentFileRepository->total_pax),
            haveQuote: new HaveQuote($eloquentFileRepository->have_quote),
            haveVoucher: new HaveVoucher($eloquentFileRepository->have_voucher),
            haveTicket: new HaveTicket($eloquentFileRepository->have_ticket),
            haveInvoice: new HaveInvoice($eloquentFileRepository->have_invoice),
            status: new Status($eloquentFileRepository->status),
            promotion: new Promotion($eloquentFileRepository->promotion),
        );
    }
}
