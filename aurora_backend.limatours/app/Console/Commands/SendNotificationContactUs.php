<?php

namespace App\Console\Commands;

use App\AuroraContactUs;
use App\Mail\NotificationContactUs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendNotificationContactUs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aurora:send-contact-us {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un email a la ejecutiva';

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
        $id = $this->argument('id');
        $contact = AuroraContactUs::where('id', $id)->with([
            'user' => function ($query) {
                $query->select(['id', 'code', 'name']);
            }
        ])->first();
        if ($contact) {
            Mail::to('jgq@limatours.com.pe')->send(new NotificationContactUs($contact));
        }
    }
}
