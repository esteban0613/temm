<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\Auth;

class ProductsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $collection = collect([["Nombre Producto", "Cantidad", "Precio Unitario", "Valor Total"]]);
        $products = Product::select(['name', 'cantidad', 'precio'])->where('parking_id',Auth::user()->parking_id)->orderBy('name','asc')->get();
        foreach ($products as $product){
            $product->valor = $product->cantidad =='-1'? '0':($product->cantidad * $product->precio);
            $collection->push($product);
        }
        return $collection;
    }
}
