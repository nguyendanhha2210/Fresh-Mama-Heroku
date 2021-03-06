<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusCode;
use App\Enums\StatusSale;
use App\Enums\StatusWarehouse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\WareHouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WareHouseController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            $breadcrumbs = ['WareHouse'];
            return view('admin.warehouses.index', ['breadcrumbs' => $breadcrumbs]);
        }
    }

    public function getData(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        }
        try {
            $paginate = $request->paginate;
            $search = $request->search;
            $statusImport = $request->statusImport;

            $sort_direction = request('sort_direction', 'desc');
            if (!in_array($sort_direction, ['asc', 'desc'])) {
                $sort_direction = 'desc';
            }
            $sort_field = request('sort_field', 'created_at');
            if (!in_array($sort_field, ['import_price', 'import_quantity', 'inventory'])) {
                $sort_field = 'created_at';
            }

            $types = WareHouse::where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            })->where(function ($q) use ($statusImport) {
                if ($statusImport) {
                    $q->where('status', '=', $statusImport);
                }
            })->orderBy($sort_field, $sort_direction)->paginate($paginate);

            return response()->json($types, StatusCode::OK);
        } catch (\Exception$e) {
            return response()->json(['error' => $e->getMessage()], StatusCode::NOT_FOUND);
        }
    }

    public function create()
    {
        $breadcrumbs = ['Import Goods'];
        $goBack = '/admin/warehouse';
        return view('admin.warehouses.add', ['breadcrumbs' => $breadcrumbs, 'goBack' => $goBack]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $warehouse = new WareHouse();
            $warehouse->name = $request->name;
            $file = $request->images;
            $fileName = $file->getClientOriginalName();
            $file->move('uploads/products', $fileName);
            $warehouse->images = $fileName;
            $warehouse->import_price = $request->import_price;
            $warehouse->import_quantity = $request->import_quantity;
            $warehouse->inventory = $request->inventory + $request->import_quantity;
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $warehouse->import_date = now();
            $warehouse->status = StatusWarehouse::UP;
            $flagWarehouse = $warehouse->save();

            $product = new Product();
            $product->name = $request->name;
            $product->images = $fileName;
            $product->content = '';
            $product->price = 0;
            $product->status = StatusSale::DOWN;
            $product->quantity = $warehouse->inventory;
            $product->import_price = $request->import_price;
            $product->product_sold = 0;
            $product->ware_houses_id = $warehouse->id;
            $product->views = 0;
            $flagProduct = $product->save();

            $productImage = new ProductImage();
            $productImage->product_id = $product->id;
            $productImage->url = $fileName;
            $flagProductImage = $productImage->save();

            if ($flagWarehouse && $flagProduct && $flagProductImage) {
                DB::commit();
                return response()->json(route('admin.warehouse.list'), StatusCode::OK); //L??u th??nh c??ng g???i ra ??g d???n v??? list
            }
        } catch (\Exception$e) {
            DB::rollBack();
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }

    public function edit($id)
    {
        $warehouse = WareHouse::find($id);
        $breadcrumbs = ['Import Add'];
        $goBack = '/admin/warehouse';
        return view('admin.warehouses.edit', compact('warehouse'), ['breadcrumbs' => $breadcrumbs, 'goBack' => $goBack]);
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $warehouse = new WareHouse();
            $warehouse->name = $request->name;
            $warehouse->images = $request->images;
            $warehouse->import_price = $request->import_price;
            $warehouse->import_quantity = $request->import_quantity;
            $warehouse->inventory = $request->inventory + $request->import_quantity;
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $warehouse->import_date = now();
            $warehouse->status = StatusWarehouse::UP;
            $warehouse->save();

            //X??a b??? kho h??ng c??
            $warehouseOld = WareHouse::find($id);
            $warehouseOld->status = StatusWarehouse::DOWN;
            $warehouseOld->save();

            $product = Product::where('ware_houses_id', $id)->firstOrFail();
            $product->quantity = $request->inventory + $request->import_quantity;
            $product->import_price = $request->import_price;
            $product->ware_houses_id = $warehouse->id;
            $product->save();
            DB::commit();
            return response()->json(route('admin.warehouse.list'), StatusCode::OK);
        } catch (\Exception$e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], StatusCode::INTERNAL_ERR);
        }
    }

    public function excelImportImage(Request $request)
    {
        try {
            DB::beginTransaction();
            $warehouse = WareHouse::find($request->productId);
            $file = $request->images;
            $fileName = $file->getClientOriginalName();
            $file->move('uploads/products', $fileName);
            $warehouse->images = $fileName;
            $warehouse->status = StatusWarehouse::UP;
            $flagWarehouse = $warehouse->save();

            $product = new Product();
            $product->name = $request->name;
            $product->images = $fileName;
            $product->content = '';
            $product->price = 0;
            $product->status = StatusSale::DOWN;
            $product->quantity = $warehouse->inventory;
            $product->import_price = $request->import_price;
            $product->product_sold = 0;
            $product->ware_houses_id = $warehouse->id;
            $product->views = 0;
            $flagProduct = $product->save();

            $productImage = new ProductImage();
            $productImage->product_id = $product->id;
            $productImage->url = $fileName;
            $flagProductImage = $productImage->save();

            if ($flagWarehouse && $flagProduct && $flagProductImage) {
                DB::commit();
                return response()->json(route('admin.warehouse.list'), StatusCode::OK); //L??u th??nh c??ng g???i ra ??g d???n v??? list
            }
        } catch (\Exception$e) {
            DB::rollBack();
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }
}
