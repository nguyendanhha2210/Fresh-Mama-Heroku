<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Product extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'id', 'name', 'images', 'price', 'type_id', 'description_id', 'content', 'status', 'views', 'quantity', 'import_price', 'product_sold', 'ware_houses_id'
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\Type');
    }

    // public function weight()
    // {
    //     return $this->belongsTo('App\Models\Weight');
    // }

    public function description()
    {
        return $this->belongsTo('App\Models\Description');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\WareHouse', 'ware_houses_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'product_id', 'id');
    }

    public function orderDetail()
    {
        return $this->belongsTo('App\Models\OrderDetail');
    }

    public function productImages()
    {
        return $this->hasMany('App\Models\ProductImage', 'product_id', 'id');
    }

    public function evaluates()
    {
        return $this->hasMany('App\Models\Evaluate', 'product_id', 'id');
    }

    public function weightProducts()
    {
        return $this->hasMany('App\Models\WeightProduct', 'product_id', 'id');
    }

    public function minWeightProduct()
    {
        return $this->hasOne('App\Models\WeightProduct', 'product_id', 'id')->orderBy('price', 'ASC');
    }
}
