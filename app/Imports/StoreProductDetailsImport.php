<?php

namespace App\Imports;

use App\model\StoreProductDetails;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StoreProductDetailsImport implements ToModel, WithHeadingRow
{
    protected $store_id;

    public function __construct($store_id) {
        $this->store_id = $store_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $file_ext = pathinfo($row['image'], PATHINFO_EXTENSION);
        $file_name = rand(1,4) . "-" .Str::slug(trim($row['product_name'])) . "." . $file_ext;
        $file = file_get_contents($row['image']);
        $save_file = file_put_contents('assets/images/store-images/product-images/' . $file_name, $file);

        return new StoreProductDetails([
            'store_id' => $this->store_id,
            'product_cate_id' => $row['product_category'],
            'name' => $row['product_name'],
            'image' => $file_name,
            'amount' => round($row['amount'], 2),
            'discount_amount' => ($row['discount_amount'] != Null && $row['discount_type'] != Null) ? round($row['discount_amount'], 2) : 0,
            'discount_type' => ($row['discount_amount'] != Null && $row['discount_type'] != Null) ? $row['discount_type'] : 0,
            'food_type' => ($row['food_type'] == 1) ? 1 : 2,
            'description' => ($row['description'] != Null) ? $row['description'] : Null,
            'status' => $row['status'],
            'created_at' => date("Y-m-d h:i:s"),
            'updated_at' => date("Y-m-d h:i:s"),
        ]);
    }
}
