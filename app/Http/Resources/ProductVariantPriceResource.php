<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_one' => $this->product_variant_one,
            'product_variant_two' => $this->product_variant_two,
            'product_variant_three' => $this->product_variant_three,
            'product_variant_comb' => ($this->variant_one ? $this->variant_one->variant : '').'/'.($this->variant_two ? $this->variant_two->variant : '').'/'.($this->variant_three ? $this->variant_three->variant : '').'/',
            'stock' => $this->stock,
            'price' => $this->price
        ];
    }
}
