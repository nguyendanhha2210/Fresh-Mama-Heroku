<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusCode;
use App\Enums\StatusSale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Description;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Type;
use App\Models\Weight;
use App\Models\WeightProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    public function update_product_status(Request $request, $id)
    {
        try {
            $query = Product::findOrFail($id);
            if ($request->status == StatusSale::DOWN) {
                $query->status = StatusSale::UP;
            } else {
                $query->status = StatusSale::DOWN;
            }
            $query->save();
            return response()->json($query, StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }

    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            $breadcrumbs = ['Product List'];
            return view('admin.products.index', ['breadcrumbs' => $breadcrumbs]);
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

            $sort_direction = request('sort_direction', 'desc');
            if (!in_array($sort_direction, ['asc', 'desc'])) {
                $sort_direction = 'desc';
            }
            $sort_field = request('sort_field', 'created_at');
            if (!in_array($sort_field, ['price', 'quantity', 'import_price', 'product_sold', 'views'])) {
                $sort_field = 'created_at';
            }

            $products =  Product::where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            })->with(['type', 'description', 'warehouse'])->orderBy($sort_field, $sort_direction)->paginate($paginate);

            return response()->json($products, StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], StatusCode::NOT_FOUND);
        }
    }

    public function create()
    {
        $data = [];
        $type_product = Type::get();
        $description_product = Description::get();
        $weight_product = Weight::orderBy('weight', 'ASC')->get();
        $data = collect([
            'type_product' => $type_product,
            'description_product' => $description_product,
            'weight_product' => $weight_product,
        ]);
        return $data;
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::where('id', $request->productId)->firstOrFail();
            $product->name = $request->name;
            $file = $request->images;
            if ($file != null) {
                $fileName = $file->getClientOriginalName();
                $file->move('uploads/products', $fileName);
                $product->images = $fileName;
            }
            $product->type_id = $request->typeId;
            $product->description_id = $request->descriptionId;
            $product->content = $request->content;
            $product->status = StatusSale::DOWN;
            //T??m min price nh???p ????? l??u cho gi?? b??n show cho kh??ch h??ng
            $minPrice = $request->price[0];
            foreach ($request->price as $key => $price) {
                if ($minPrice > $price) {
                    $minPrice = $price;
                }
            }
            $product->price = $minPrice;
            $product->save();

            $existWeights = WeightProduct::where('product_id', $request->productId)->pluck('weight')->toArray();  //L???y ra t???t c??? c??c kh???i l?????ng theo product id th??nh 1 m???ng
            $inputWeights = $request->weight;
            $inputPrices = $request->price;

            $dataInput = [];
            foreach ($inputWeights as $key => $weight) {
                $dataInput[] = [
                    "weight" => $weight,
                    "price" => $inputPrices[$key]
                ];
            }

            $weightProducts = WeightProduct::where('product_id', $request->productId)->get();
            $dataExit = [];
            foreach ($weightProducts as $key => $weightProduct) {
                $dataExit[] = [
                    "weight" => $weightProduct->weight,
                    "price" => $weightProduct->price,
                ];
            }

            $deleteWeights =  array_diff($existWeights, $inputWeights);
            if (!empty($deleteWeights)) {
                foreach ($deleteWeights as $key => $weight) {
                    $result = WeightProduct::where('product_id', $request->productId)->Where('weight', $weight)->first();
                    $result->delete();
                }
            }

            $insertWeights =  array_diff($inputWeights, $existWeights);
            if (!empty($insertWeights)) {
                foreach ($insertWeights as $key => $value) {
                    foreach ($dataInput as $ab => $item) {
                        if ($value == $item["weight"]) {
                            $insertDataList[] = [
                                "product_id" => $request->productId,
                                "weight" => $item["weight"],
                                "price" => $item["price"],
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now(),
                            ];
                        }
                    }
                }
                WeightProduct::insert($insertDataList);
            }

            foreach ($dataInput as $input => $item) {
                foreach ($dataExit as $key => $value) {
                    if ($item["weight"] == $value["weight"]) {
                        if ($item["price"] != $value["price"]) {
                            $weightProducts = WeightProduct::where('product_id', $request->productId)->where('weight', $item["weight"])->first();
                            $weightProducts->price = $item["price"];
                            $weightProducts->update();
                        }
                    }
                }
            }

            DB::commit();
            return Redirect::back();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }

    public function saveProductImage(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            try {
                DB::beginTransaction();
                if (!empty($request->images)) {
                    $insertDataImages = [];
                    foreach ($request->images as $key => $file) {
                        $fileName = $file->getClientOriginalName();
                        $file->move('uploads/products', $fileName);
                        $insertDataImages[] = [
                            'product_id' => $request->productId,
                            'url' => $fileName,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    ProductImage::insert($insertDataImages);
                }
                DB::commit();
                return response()->json(route("admin.product.list"), StatusCode::OK);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
            }
        }
    }

    public function addProductImage($id)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            $breadcrumbs = ['Add Product Image'];
            $goBack = '/admin/product';
            return view('admin.products.imageadd', ['breadcrumbs' => $breadcrumbs, 'productId' => $id, 'goBack' => $goBack]);
        }
    }

    public function detailProductImage($id)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            $breadcrumbs = ['Detail Product Image'];
            $goBack = '/admin/product';
            $productImages = ProductImage::where('product_id', $id)->get();
            if ($productImages) {
                return view('admin.products.imagedetail', ['breadcrumbs' => $breadcrumbs, 'data' => $productImages, 'goBack' => $goBack]);
            }
        }
    }

    public function editProductImage($id)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            $breadcrumbs = ['Edit Product Image'];
            $goBack = "/admin/detail-image/$id";
            $productImages = ProductImage::where('product_id', $id)->get();
            if ($productImages) {
                return view('admin.products.imageedit', ['breadcrumbs' => $breadcrumbs, 'data' => $productImages, 'goBack' => $goBack]);
            }
        }
    }

    public function updateProductImage(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            try {
                DB::beginTransaction();
                $circleImages = ProductImage::where('product_id', $id)->get();
                if (!empty($circleImages)) {
                    foreach ($circleImages as $item) {
                        $oldImages[$item->id] = $item;
                    }
                }
                //list images
                if (!empty($request->images)) {
                    $insertDataImages = [];
                    $idImages = [];
                    $idImageUpdates = [];
                    $cases = [];
                    $params = [];
                    $isUpdateImages = false;
                    $path = 'uploads/products';
                    foreach ($request->images as $key => $image) {
                        if (gettype($key) == 'integer') {
                            $idImages[] = [$key];
                        }
                        if (strpos($key, 'create') !== false) {
                            $fileName = $image->getClientOriginalName();
                            $image->move('uploads/products', $fileName);
                            $insertDataImages[] = [
                                'product_id' => $id,
                                'url' => $fileName,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ];
                        } else {
                            if (gettype($image) != 'string') {
                                $isUpdateImages = true;
                                $imageName = $image->getClientOriginalName();
                                $image->move('uploads/products', $imageName);
                                $idImageUpdates[] = $key;
                                $cases[] = "WHEN {$key} THEN ?";
                                $params[] = url($path . '/' . $imageName);
                                if (!empty($oldImages)) {
                                    File::delete(public_path($oldImages[$key]['url']));
                                }
                            }
                        }
                    }
                    ProductImage::whereNotIn('id', $idImages)->where('product_id', $id)->delete();
                    if (!empty($insertDataImages)) {
                        ProductImage::insert($insertDataImages);
                    }
                    $idImageUpdates = implode(',', $idImageUpdates);
                    $cases = implode(' ', $cases);
                    if ($isUpdateImages) {
                        DB::update("UPDATE product_images SET `url` = CASE `id` {$cases} END WHERE `id` in ({$idImageUpdates})", $params);
                    }
                }
                DB::commit();
                $goBack = "/admin/detail-image/$id";
                return response()->json($goBack, StatusCode::OK);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
            }
        }
    }

    public function getDetailProductImage(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        }
        try {
            $productImages =  ProductImage::where('product_id', $id)->with(['product'])->whereHas('product', function ($query) {
                $query->where('deleted_at', NULL);
            })->orderBy('created_at', 'desc')->get();
            return response()->json($productImages, StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], StatusCode::NOT_FOUND);
        }
    }
}
