<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Service;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutServiceController extends Controller
{
//

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
        try {
            // Validate input
            $request->validate([
                'id' => 'required|integer',
                'user_code' => 'required|string',
            ]);

            // Lấy thông tin dịch vụ
            $ServiceId = $request->input('id');
            $user_code = $request->input('user_code');
            $service = Service::find($ServiceId);

            if (!$service) {
                return redirect()->back()->with('error', 'Không tìm thấy dịch vụ!');
            }

            // Lấy thông tin từ .env
            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
            $partnerCode = 'MOMOBKUN20180529';
            $accessKey = 'klm05TvNBzhg7h7j';
            $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
            $redirectUrl = url('/payment-callback/service');
            $ipnUrl = url('/payment-success/service');

            // Cấu hình thông tin thanh toán
            $orderInfo = "Thanh toán qua MoMo";
            $amount = $service->amount;
            $orderId = time(); // Mã đơn hàng duy nhất
            $requestId = time(); // Mã yêu cầu duy nhất
            $requestType = "payWithATM";

            // Extra data để lưu thêm thông tin
            $extraData = json_encode([
                'id' => $service->id,
                'user_code' => $user_code,
            ]);

            // Tạo chữ ký (signature)
            $rawHash = "accessKey=" . $accessKey
                . "&amount=" . $amount
                . "&extraData=" . $extraData
                . "&ipnUrl=" . $ipnUrl
                . "&orderId=" . $orderId
                . "&orderInfo=" . $orderInfo
                . "&partnerCode=" . $partnerCode
                . "&redirectUrl=" . $redirectUrl
                . "&requestId=" . $requestId
                . "&requestType=" . $requestType;
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // Dữ liệu gửi đến MoMo
            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                'storeId' => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature,
            ];

            // Gửi yêu cầu POST tới MoMo
            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            // Kiểm tra kết quả trả về
            if (isset($jsonResult['resultCode'])) {
                switch ($jsonResult['resultCode']) {
                    case 1001: // Insufficient funds
                        return redirect()->back()->with('error', 'Giao dịch thất bại: Số dư không đủ để thanh toán.');
                    case 0: // Success
                        return redirect()->to($jsonResult['payUrl']);
                    default:
                        Log::error("MoMo API Error: " . json_encode($jsonResult));
                        return redirect()->back()->with('error', 'Giao dịch thất bại. Vui lòng thử lại sau.');
                }
            }

            // Chuyển hướng tới URL thanh toán
            return redirect()->to($jsonResult['payUrl']);
        } catch (\Exception $e) {
            Log::error("MoMo Payment Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Đã xảy ra lỗi trong quá trình xử lý thanh toán.');
        }
    }

// Hàm gửi POST yêu cầu API




    public function handleCallback(Request $request)
    {
        $data = $request->all();

        if (!isset($data['extraData'])) {
            return response()->json(['message' => 'Missing extraData parameter'], 400);
        }

        $extraData = json_decode($data['extraData'], true);

        // dd($extraData);

        if (!isset($extraData['user_code']) || !isset($extraData['subject_code'])) {
            return response()->json(['error' => '2.Thiếu thông tin student_code hoặc subject_code!'], 400);
        }

        if (!isset($extraData['id'])) {
            return response()->json(['error' => 'Không tìm thấy id trong extraData!'], 400);
        }
        $service = Service::find($extraData['id']);

        if (!$service) {
            return response()->json(['error' => 'Không tìm thấy dịch vụ!'], 404);
        }

        $dataTransaction = [
            'service' => $service->id,
            'payment_date' => now(),
            'amount_paid'  => $data['amount'] ?? 0,
            'payment_method' => 'transfer',
            'type'          => 'add',
            'receipt_number' => $data['transId'] ?? '',
            'is_deposit'     => 1
        ];

        if ($data['resultCode'] == 0) {
            $service->status = 'paid'; //
            $service->save();

            Transaction::create($dataTransaction);
            // $service->save();
            return response()->json([
                'message' => 'Thanh toán thành công!',
                'service' => $service,
                'transaction' => $dataTransaction
            ], 200);
        } else {
            // Thanh toán thất bại, trả về thông báo lỗi
            return response()->json([
                'message' => 'Thanh toán thất bại',
                'error_message' => $data['message'] ?? 'Không xác định'
            ], 400);
        }
    }

    public function PaymentSuccess(){
        dd("thanh toán thành công");
    }


    public function vnpay_payment(Request $request){

        $ServiceId = $request->input('id');
        $user_code = $request->input('user_code');
        $service = Service::find($ServiceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Không tìm thấy dịch vụ!');
        }

        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // trang tra ve khi thanh toan
        $vnp_Returnurl = "https://admin.feduvn.com/api/return-vnpay/service"; // trang tra ve khi thanh cong
        $vnp_TmnCode = "9COLR8TJ";//Mã website tại VNPAY
        $vnp_HashSecret = "E67F1MFW7JK7PGV3TIVR9HUJAGZSOIMA"; //Chuỗi bí mật
        $vnp_TxnRef = Carbon::now();

        $vnp_OrderInfo = json_encode(['service_id' => $ServiceId, 'user_code' => $user_code]);

        $vnp_OrderType = "billpayment";
        $vnp_Amount = $service->amount * 100;
        // $vnp_Amount = 10000 * 100;
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
            "vnp_TxnRef"    => $vnp_TxnRef,
            // "id_service"    =>  $ServiceId,
            // "user_code"     => $user_code,
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
        $data = $request->all();
        dd($data);
        $vnp_TransactionNo = $request->input('vnp_TransactionNo');
        $vnp_Amount        = $request->input('vnp_Amount');

         $vnp_ResponseCode = $request->input('vnp_ResponseCode');
         $vnp_OrderInfo = $request->input('vnp_OrderInfo');

         if ($vnp_ResponseCode == '00') {

            $orderInfo  = json_decode($vnp_OrderInfo, true); // true chuyển đổi thành mảng PHP
            $serviceId  = $orderInfo['service_id']; // Lấy service_id
            $userCode   = $orderInfo['user_code'];

            // Thực hiện logic cập nhật dịch vụ trong bảng
            $service = Service::find($serviceId);
            if ($service) {
                $service->status = 'paid';
                $service->save();
            }

            $integerServiceId = intval($serviceId);
            $dataTransaction = [
                'fee_id'        => null,
                'service_id'    => $integerServiceId,
                'payment_date'  => now(),
                'amount_paid'   => $vnp_Amount,
                'payment_method'=> 'transfer',
                'type'          => 'add',
                'receipt_number'=> $vnp_TransactionNo,
            ];

            dd($dataTransaction);
            Transaction::create($dataTransaction);
            return redirect()->route('payment.success')->with('success', 'Thanh toán thành công!');
        } else {
            // Giao dịch thất bại
            return redirect()->route('payment.failed')->with('error', 'Giao dịch thất bại!');
        }
    }

    public function vnpay_payment_success(){
        dd('thành công');
    }

    public function vnpay_payment_fail(Request $request){
        $vnp_ResponseCode = $request->input('vnp_ResponseCode');
        $vnp_TxnRef = $request->input('vnp_TxnRef');
        $vnp_Message = $request->input('vnp_Message', 'Giao dịch thất bại không rõ lý do.');

        // Lưu log lỗi
        Log::error('VNPay Payment Failed', [
            'ResponseCode' => $vnp_ResponseCode,
            'TxnRef'       => $vnp_TxnRef,
            'Message'      => $vnp_Message,
            'RequestData'  => $request->all(), // Ghi toàn bộ dữ liệu request
            'IpAddress'    => $request->ip(),
        ]);

        return response()->json(['message'=> 'Thanh toán không thành công. Vui lòng thử lại!']);
    }
}


