<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CouponStatus;
use App\Enums\OrderStatus;
use App\Enums\RoleStateType;
use App\Enums\StatusCode;
use App\Enums\StatusSale;
use App\Enums\StatusCoupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    public function updateCouponStatus(Request $request, $id)
    {
        try {
            $query = Coupon::findOrFail($id);
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
            $today = Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s');
            $breadcrumbs = ['Coupon List'];
            return view('admin.coupons.index', ['breadcrumbs' => $breadcrumbs, 'today' => $today]);
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
            if (!in_array($sort_field, ['time', 'start_date', 'end_date'])) {
                $sort_field = 'created_at';
            }

            $coupons = Coupon::where(function ($q) use ($search) {
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            })->orderBy($sort_field, $sort_direction)->paginate($paginate);

            return response()->json(["coupons" => $coupons], StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], StatusCode::NOT_FOUND);
        }
    }

    public function addForSendMail()
    {
        $breadcrumbs = ['Add For Send Mail'];
        $goBack = '/admin/coupon';
        return view('admin.coupons.sendCustomer', ['breadcrumbs' => $breadcrumbs, 'goBack' => $goBack]);
    }

    public function addForShowCustomer()
    {
        $breadcrumbs = ['Add to Show for Customer'];
        $goBack = '/admin/coupon';
        return view('admin.coupons.showCustomer', ['breadcrumbs' => $breadcrumbs, 'goBack' => $goBack]);
    }

    public function couponSend(CouponRequest $request)
    {
        try {
            $coupon = new coupon();
            $coupon->name = $request->name;
            $coupon->time = $request->time;
            $coupon->condition = $request->condition;
            $coupon->status = StatusSale::DOWN;
            $coupon->number = $request->number;
            $coupon->code = $request->code;
            $coupon->statusSendShow = StatusCoupon::SEND;
            $coupon->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            $coupon->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));
            $flag = $coupon->save();
            if ($flag) {
                return response()->json(route('admin.coupon.list'), StatusCode::OK);  //Lưu thành công gọi ra đg dẫn về list
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }

    public function couponShow(Request $request)
    {
        try {
            $countCustomer = User::where('role_id', '=', RoleStateType::SALER)->count();
            $coupon = new coupon();
            $coupon->name = $request->name;
            $coupon->time = $countCustomer;
            $coupon->condition = $request->condition;
            $coupon->status = StatusSale::DOWN;
            $coupon->number = $request->number;
            $coupon->code = $request->code;
            $coupon->statusSendShow = StatusCoupon::SHOW;
            $coupon->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            $coupon->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));
            $flag = $coupon->save();
            if ($flag) {
                return response()->json(route('admin.coupon.list'), StatusCode::OK);  //Lưu thành công gọi ra đg dẫn về list
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), StatusCode::INTERNAL_ERR);
        }
    }

    public function edit($id)
    {
        $coupon = Coupon::find($id);
        $breadcrumbs = ['Edit coupon'];
        $goBack = '/admin/coupon';
        return view('admin.coupons.edit', compact('coupon'), ['breadcrumbs' => $breadcrumbs, 'goBack' => $goBack]);
    }

    public function update(CouponRequest $request, $id)
    {
        try {
            $coupon = Coupon::find($id);
            $coupon->name = $request->name;
            $coupon->time = $request->time;
            $coupon->condition = $request->condition;
            $coupon->status = StatusSale::DOWN;
            $coupon->number = $request->number;
            $coupon->code = $request->code;
            $coupon->start_date = date("Y-m-d H:i:s", strtotime($request->start_date));
            $coupon->end_date = date("Y-m-d H:i:s", strtotime($request->end_date));
            $coupon->save();
            return response()->json(route('admin.coupon.list'), StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], StatusCode::INTERNAL_ERR);
        }
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
    }

    public function sendCustomer($id)
    {
        //Lấy ra các khách hàng có tổng đơn lớn nhất sắp xếp giảm dần
        $abc = Order::select('customer_id', DB::raw('Sum(total_bill)'))
            ->where('order_status', '=', OrderStatus::SUCCESS)->with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('role_id', RoleStateType::SALER);
            })->groupBy('orders.customer_id')->orderBy(DB::raw('Sum(total_bill)'), 'desc')
            ->get();

        $coupon = Coupon::where('id', $id)->first();
        $qualityCoupon = $coupon->time;

        //Xét dk để chia số vẽ có trong cửa hàng cho số người lấy ở trên
        $dataId = [];
        foreach ($abc as $key => $normal) {
            for ($i = 0; $i < $qualityCoupon; $i++) {
                if ($i == $key) {
                    $dataId[] = $normal->customer_id;
                }
            }
        }

        //Thêm cho mỗi người 1 vẽ
        foreach ($dataId as $usc) {
            $userCoupons = new UserCoupon();
            $userCoupons->user_id = $usc;
            $userCoupons->coupon_id = $coupon->id;
            $userCoupons->coupon_name = $coupon->name;
            $userCoupons->coupon_time = 1;
            $userCoupons->statusUse = StatusCoupon::SAVE;
            $userCoupons->save();
        }

        $dataMail = User::whereIn('id', $dataId)->pluck('email'); //dùng pluck lấy ra mảng
        $coupon = Coupon::where('id', $id)->first();
        $now = Carbon::now('Asia/Ho_Chi_Minh')->format('d-m-Y H:i:s');
        $title_mail = "Mã khuyến mãi ngày" . ' ' . $now;

        $coupon = array(
            'start_date' => $coupon->start_date,
            'end_date' => $coupon->end_date,
            'time' => $coupon->time,
            'condition' => $coupon->condition,
            'number' => $coupon->number,
            'code' => $coupon->code
        );

        if ($dataMail && $coupon) {
            Mail::send('admin.users.mail.sendCoupon',  ['coupon' => $coupon], function ($message) use ($title_mail, $dataMail) {
                $message->to($dataMail->toArray())->subject($title_mail); //send this mail with subject
                $message->from($dataMail->toArray(), $title_mail); //send from this mail
            });

            //Update trạng thái thành đã gửi
            $couponStatus = Coupon::where('id', $id)->first();
            $couponStatus->status = StatusSale::SENT;
            $couponStatus->save();

            return redirect()->back()->with('message', 'Gửi mã khuyến mãi khách hàng thành công !');
        } else {
            return redirect()->back()->with('message', 'Gửi mã khuyến mãi khách hàng thất bại !');
        }
    }

    public function viewCustomer($id)
    {
        $customers = User::where('role_id', '=', RoleStateType::SALER)->get();

        $coupon = Coupon::where('id', $id)->first();
        $qualityCoupon = $coupon->time;

        //Xét dk để chia số vẽ có trong cửa hàng cho số người lấy ở trên
        $dataId = [];
        foreach ($customers as $key => $normal) {
            for ($i = 0; $i < $qualityCoupon; $i++) {
                if ($i == $key) {
                    $dataId[] = $normal->id;
                }
            }
        }

        foreach ($dataId as $usc) {
            $userCoupons = new UserCoupon();
            $userCoupons->user_id = $usc;
            $userCoupons->coupon_id = $coupon->id;
            $userCoupons->coupon_name = $coupon->name;
            $userCoupons->coupon_time = 1;
            $userCoupons->statusUse = StatusCoupon::UNSAVED;
            $userCoupons->save();
        }

        //Update trạng thái thành đã gửi
        $couponStatus = Coupon::where('id', $id)->first();
        $couponStatus->status = StatusSale::SENT;
        $couponStatus->save();
    }
}
