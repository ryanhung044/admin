<?php

namespace App\Http\Controllers;

use App\Models\Score;
use App\Models\Service;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckoutServiceController extends Controller
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
    try {
        $ServiceId = $request->input('id');
        $user_code = $request->input('user_code');

        $service = Service::find($ServiceId);
        if (!$service) {
            return redirect()->back()->with('error', 'Không tìm thấy dịch vụ!');
        }
        // return $service;

        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        $amount = $service->amount;
        $orderId = time() . "";
        $redirectUrl = url('/payment-callback/service');
        $ipnUrl = url('/payment-success/service');

        // $redirectUrl = url('/payment-success/service');
        // $ipnUrl = url('/payment-callback/service');
        $extraData = json_encode(['id' => $service->id, 'user_code' => $user_code]);

        $requestId = time() . "";
        $requestType = "payWithATM";
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

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

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        // dd($jsonResult);
        if (!isset($jsonResult['payUrl'])) {
            // Log::error("MoMo API Error: " . json_encode($jsonResult));
            return redirect()->back()->with('error', 'Không thể tạo liên kết thanh toán. Vui lòng thử lại!');
        }

        return redirect()->to($jsonResult['payUrl']);
    } catch (\Exception $e) {
        // Log::error("MoMo Payment Error: " . $e->getMessage());
        return redirect()->back()->with('error', 'Đã xảy ra lỗi trong quá trình xử lý thanh toán.');
    }
}


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
        $vnp_Returnurl = "http://127.0.0.1:8000/return-vnpay/service"; // trang tra ve khi thanh cong
        $vnp_TmnCode = "9COLR8TJ";//Mã website tại VNPAY
        $vnp_HashSecret = "E67F1MFW7JK7PGV3TIVR9HUJAGZSOIMA"; //Chuỗi bí mật
        $vnp_TxnRef = Carbon::now();
        $vnp_OrderInfo = "Thanh toán hóa đơn phí dich vụ";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $service->amount * 100;
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
            "id_service"    =>  $ServiceId,
            "user_code"     => $user_code,
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
        $id_service = $request->input('id_service'); // Lấy id
        $user_code = $request->input('user_code');
        $payDate = \Carbon\Carbon::createFromFormat('YmdHis', $request->input('vnp_PayDate'))
                                    ->format('Y-m-d H:i:s');
        $vnp_TransactionNo = $request->input('vnp_TransactionNo');
        $vnp_Amount        = $request->input('vnp_Amount');
        $vnp_ResponseCode  = $request->input('vnp_ResponseCode');

        $dataTransaction = [
            'service_id'        => $id_service,
            'payment_date'      => $payDate,
            'amount_paid'       => $vnp_Amount,
            'payment_method'    => 'transfer',
            'type'              => 'add',
            'receipt_number'    => $vnp_TransactionNo,
        ];
        Transaction::create($dataTransaction);
        $service = Service::findOrFail($id_service);

        $service->update('status','paid');

        return response()->json(['message' => 'giao dịch thành công']);
    }
}
