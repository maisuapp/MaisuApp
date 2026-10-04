<?php

namespace App\Jobs;

use App\Classes\NotificationClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NearestAlgoSendDriverNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;
        \Log::info("data");
        \Log::info($data);
        $ride_driver_token_id = $data['ride_driver_token_id'];
        \Log::info("ride_driver_token_id");
        \Log::info($ride_driver_token_id);
        $notificationClasses = new NotificationClass();
        \Log::info("NotificationClass");
        $notificationClasses->DriverNewRequestNotification($ride_driver_token_id);
    }
}
