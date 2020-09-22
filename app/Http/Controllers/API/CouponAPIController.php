<?php

namespace App\Http\Controllers\API;


use App\Criteria\Coupons\ValidCriteria;
use App\Criteria\Users\FilterByUserCriteria;
use App\Models\Coupon;
use App\Models\CouponHistory;
use App\Notifications\CouponUsed;
use App\Repositories\CouponRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Response;
use Prettus\Repository\Exceptions\RepositoryException;
use Flash;
use Auth;
/**
 * Class CouponController
 * @package App\Http\Controllers\API
 */

class CouponAPIController extends Controller
{
    /** @var  CouponRepository */
    private $couponRepository;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(CouponRepository $couponRepo, UserRepository $userRepo)
    {
        $this->couponRepository = $couponRepo;
        $this->userRepository = $userRepo;
    }

    /**
     * Display a listing of the Coupon.
     * GET|HEAD /coupons
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try{
            $this->couponRepository->pushCriteria(new RequestCriteria($request));
            $this->couponRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->couponRepository->pushCriteria(new ValidCriteria());
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        $coupon_code = substr($request->search, 5);
        $customer_id = auth()->id();
        $history = CouponHistory::where('coupon_code', $coupon_code)
                    ->where('customer_id', $customer_id)
                    ->get();
        
        if (count($history) > 0) {
            $customer = $this->userRepository->findWithoutFail($customer_id);
            if (!empty($customer)) {
                Notification::send([$customer], new CouponUsed($coupon_code));
            }
            return $this->sendResponse([], 'Coupons retrieved successfully');
        } else {
            $coupons = $this->couponRepository->all();
            return $this->sendResponse($history->toArray(), 'Coupons retrieved successfully');
        }
    }

    /**
     * Display the specified Coupon.
     * GET|HEAD /coupons/{id}
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        /** @var Coupon $coupon */
        if (!empty($this->couponRepository)) {
            $coupon = $this->couponRepository->findWithoutFail($id);
        }

        if (empty($coupon)) {
            return $this->sendError('Coupon not found');
        }

        return $this->sendResponse($coupon->toArray(), 'Coupon retrieved successfully');
    }
}
