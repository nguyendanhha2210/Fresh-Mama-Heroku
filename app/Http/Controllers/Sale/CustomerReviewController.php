<?php

namespace App\Http\Controllers\Sale;

use App\Enums\StatusCode;
use App\Enums\StatusSale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Description;
use App\Models\Evaluate;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Type;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerReviewController extends Controller
{
    public function customerReview(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return view('admin.users.login');
        } else {
            try {
                $evaluate = new  Evaluate();
                $evaluate->user_id = Auth::guard('sales')->id();
                $evaluate->order_id = $request->order_id;
                $evaluate->star_vote = $request->star_vote;
                $evaluate->content = $request->content;

                $file_1 = $request->image_1;
                if ($file_1 != null) {
                    $fileName_1 = $file_1->getClientOriginalName();
                    $file_1->move('uploads', $fileName_1);
                    $evaluate->image_1 = $fileName_1;
                }

                $file_2 = $request->image_2;
                if ($file_2 != null) {
                    $fileName_2 = $file_2->getClientOriginalName();
                    $file_2->move('uploads', $fileName_2);
                    $evaluate->image_2 = $fileName_2;
                }

                $file_3 = $request->image_3;
                if ($file_3 != null) {
                    $fileName_3 = $file_3->getClientOriginalName();
                    $file_3->move('uploads', $fileName_3);
                    $evaluate->image_3 = $fileName_3;
                }

                $file_4 = $request->image_4;
                if ($file_4 != null) {
                    $fileName_4 = $file_4->getClientOriginalName();
                    $file_4->move('uploads', $fileName_4);
                    $evaluate->image_4 = $fileName_4;
                }

                $evaluate->save();
                return response()->json(StatusCode::OK);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
            }
        }
    }
}