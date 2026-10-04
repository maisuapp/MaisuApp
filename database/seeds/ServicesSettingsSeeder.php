<?php

use App\model\ServiceCategory;
use App\model\ServiceSettings;
use Illuminate\Database\Seeder;

class ServicesSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $get_services = ServiceCategory::get();
        if(!$get_services->isEmpty())
        {
            foreach ($get_services as $service)
            {
                $check_data = ServiceSettings::where('service_cat_id',$service->id)->first();
                if($check_data == Null)
                {
                    $add_setting = new ServiceSettings();
                    $add_setting->service_cat_id = $service->id;
                    $add_setting->driver_accept_timeout = 60;
                    $add_setting->driver_search_radius = 5;
                    $add_setting->tax = 5;
                    $add_setting->admin_commission = 5;
                    $add_setting->cancel_charge = 5;
                    $add_setting->status = 1;
                    $add_setting->save();
                }
            }
            echo "Service Category Settings added successfully!";
        }
        else{
            echo "Service Category not found!";
        }
    }
}
