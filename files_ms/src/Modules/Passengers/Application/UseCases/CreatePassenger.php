<?php

namespace Src\Modules\Passengers\Application\UseCases;

use Src\Modules\Passengers\Infrastructure\Persistence\PassengerRepository;
use Src\Modules\Passengers\Application\Validators\PassengerValidator;
use Src\Modules\Passengers\Application\Builders\FileDataBuilder;

class CreatePassenger
{
    private $repo;
    private $validator;
    
    public function __construct(PassengerRepository $repo, PassengerValidator $validator)
    {
        $this->validator = $validator;
        $this->repo = $repo;
    }

    public function execute(int $fileId, array $data)
    {
        $this->validator->validate($data);

        return $this->repo->create($fileId, $data);
    }
}