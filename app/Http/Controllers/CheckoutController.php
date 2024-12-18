<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            )
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    public function momo_payment(Request $request)
    {
        $feeId = $request->input('fee_id');

        // Kiểm tra fee_id
        $fee = Fee::find($feeId);
        if (!$fee) {
            return redirect()->back()->with('error', 'Không tìm thấy hóa đơn học phí!');
        }
        if ($fee->total_amount - $fee->amount <= 0) {
            return dd('error', 'Không tìm thấy hóa đơn học phí!');
        }
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        // $amount = 10000;
        $amount = $fee->total_amount - $fee->amount;
        // $amount = $fee->total_amount ;
        $orderId = time() . "";
        // url khi thanh toan thanh cong
        $redirectUrl = url('/payment-success');
        $ipnUrl = url('/payment-callback');

        $extraData = json_encode(['fee_id' => $fee->id]);
        $requestId = time() . "";
        $requestType = "payWithATM";
        // $extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        // dd($signature);
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );
        $result = $this->execPostRequest($endpoint, json_encode($data));
        // return dd($amount,$result);
        $jsonResult = json_decode($result, true);  // decode json
        // dd($jsonResult);

        //Just a example, please check more in there
        return redirect()->to($jsonResult['payUrl']);
    }

    public function handleCallback(Request $request)
    {
        $data = $request->all();
        // return response()->json(['message' => $data]);
        // Kiểm tra dữ liệu có chứa tham số 'extraData' không
        if (!isset($data['extraData'])) {
            return response()->json(['message' => 'Missing extraData parameter'], 400);
        }

        $extraData = json_decode($data['extraData'], true);

        if (!isset($extraData['fee_id'])) {
            return response()->json(['error' => 'Không tìm thấy fee_id trong extraData!'], 400);
        }
        $fee = Fee::find($extraData['fee_id']);
        if (!$fee) {
            return response()->json(['error' => 'Không tìm thấy hóa đơn học phí!'], 404);
        }

        if ($data['resultCode'] == 0) {
            if ($fee) {
                $amount = $fee->amount += $data['amount'];
                $isFullyPaid = $amount >= $fee->total_amount;

                if ($isFullyPaid) {
                    $fee->status = 'paid';
                }
                // Tìm hoặc tạo ví tiền
                $wallet = Wallet::firstOrCreate(
                    ['user_code' => $fee->user_code],
                    ['total' => 0, 'paid' => 0]
                );

                // Cập nhật thông tin ví
                $wallet->total += $isFullyPaid ? $fee->total_amount : 0;
                $wallet->paid += $data['amount'];
                $wallet->save();
                $fee->save();
                // Tạo giao dịch
                $transactions = [];

                // Giao dịch nạp tiền
                $transactions[] = [
                    'fee_id' => $fee->id,
                    'payment_date' => now(),
                    'amount_paid' => $data['amount'],
                    'payment_method' => 'transfer',
                    'receipt_number' =>  $data['transId'],
                    'is_deposit' => 1,
                ];

                // Giao dịch thanh toán đầy đủ
                if ($isFullyPaid) {
                    $transactions[] = [
                        'fee_id' => $fee->id,
                        'payment_date' => now(),
                        'amount_paid' => $fee->total_amount,
                        'payment_method' => 'transfer',
                        'receipt_number' => "",
                        'is_deposit' => 0,
                    ];
                }

                // Lưu giao dịch
                Transaction::insert($transactions);

                return response()->json(['message' => 'Payment processed successfully'], 200);
            }

            return response()->json(['message' => 'Fee not found'], 400);
        }

        return response()->json(['message' => 'Payment failed'], 400);
    }


    public function vnpay_payment(){
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // trang tra ve khi thanh toan
        $vnp_Returnurl = "http://127.0.0.1:8000/return-vnpay"; // trang tra ve khi thanh cong
        $vnp_TmnCode = "9COLR8TJ";//Mã website tại VNPAY
        $vnp_HashSecret = "E67F1MFW7JK7PGV3TIVR9HUJAGZSOIMA"; //Chuỗi bí mật

        $vnp_TxnRef = Carbon::now();
        $vnp_OrderInfo = "Thanh toán hóa đơn phí dich vụ";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = 10000 * 100;
        $vnp_Locale = "VN";
        $vnp_BankCode = "NCB";
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        //Add Params of 2.0.1 Version
        // $vnp_ExpireDate = $_POST['txtexpire'];
        //Billing

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        // $returnData = array('code' => '00'
        //     , 'message' => 'success'
        //     , 'data' => $vnp_Url);
        //     if (isset($_POST['redirect'])) {
        //         header('Location: ' . $vnp_Url);
        //         die();
        //     } else {
        //         echo json_encode($returnData);
        //     }

        return redirect($vnp_Url);
    }

    public function vnpay_payment_return(Request $request){
        // $url = session('url_prev','/');
        if($request->vnp_ResponseCode == "00") {
            $this->thanhtoanonline(1);
            return response()->json(['success' =>'Đã thanh toán phí dịch vụ']);
        }
        session()->forget('url_prev');
        return response()->json(['errors' =>'Lỗi trong quá trình thanh toán phí dịch vụ']);

    }



    public function thanhtoanonline($costId)
    {
            return "hello";
    }
}



