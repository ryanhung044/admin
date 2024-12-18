<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeInfoRequest;
use App\Mail\SendEmailFeeService;
use App\Mail\ServiceStatusChanged;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Score;
use App\Models\Service;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ServiceController extends Controller
{


  public function getAllServices(Request $request)
  {
    try {

      $query = Service::query()
        ->select(['id', 'user_code', 'service_name', 'content', 'status', 'amount', 'created_at', 'updated_at',])
        ->with([
          'student:id,user_code,full_name,email,phone_number'
        ]);

      // Lọc theo trạng thái nếu có
      if ($request->has('status') && !empty($request->status)) {
        $query->where('status', $request->status);
      }

      if ($request->has('student_id') && !empty($request->student_id)) {
        $query->where('student_id', $request->student_id);
      }

      // Sắp xếp theo cột nếu có tham số 'sort_by' và 'order'
      if ($request->has('sort_by') && $request->has('order')) {
        $query->orderBy($request->sort_by, $request->order === 'desc' ? 'desc' : 'asc');
      } else {
        $query->orderBy('created_at', 'desc'); // Mặc định sắp xếp theo thời gian tạo
      }

      $data = $query->paginate($request->get('per_page', 25)); // Mặc định 25 bản ghi mỗi trang

      return response()->json([
        'success' => true,
        'message' => 'Lấy danh sách dịch vụ thành công.',
        'data' => $data,
      ], 200);
    } catch (\Throwable $th) {
      return response()->json([
        'success' => false,
        'message' => 'Đã xảy ra lỗi: ' . $th->getMessage(),
      ], 500);
    }
  }

  public function ServiceInformation(int $id)
  {
    try {
      $data = Service::with('student')->find($id);

      if (!$data) {
        return response()->json(['message' => 'Service not found'], 404);
      }
      return response()->json(['data' => $data]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 500);
    }
  }

  public function getAllServicesByStudent(Request $request)
  {
    try {

      $user_code = request()->user()->user_code;

      $data = Service::query()->where('user_code', $user_code);

      // Check if status is provided in the request
      if ($request->has('status') && $request['status']) {
        $data->where('status', $request['status']);
      }
      $data->orderBy('created_at', 'desc');
      // Paginate the results and return the paginated response
      $services = $data->paginate(25);

      return response()->json($services);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 500);
    }
  }

  public function getListLearnAgain(Request $request)
  {
    $user_code = request()->user()->user_code;
    $subject = Score::Where('is_pass', false)
      ->where('status', false)
      ->where('student_code', $user_code)
      ->with('Subject')->get();
    return response()->json(['data' => $subject], 200);
  }


  public function LearnAgain(Request $request)
  {
    try {
      $user_code = request()->user()->user_code;
      $subject_code = $request->subject_code;

      $subject = Subject::where('subject_code', $subject_code)->firstOrFail();
      $content = $subject->subject_code;
      $amount  = $subject->re_study_fee;
      $data = [
        'user_code'      =>    $user_code,
        'service_name'   =>    "Đăng kí học lại môn " . $subject->subject_name,
        'content'        =>    $content,
        'amount'         =>    $amount
      ];

      $service = Service::create($data);
      if ($service) {
        $redirectUrl = url("/send-email/learn-again/{$service->id}");


        // Gọi API gửi email
        $response = Http::post($redirectUrl, [
          'subject_code' => $subject_code,  // Gửi danh sách user_code
        ]);

        if ($response->successful()) {
          return response()->json(['message' => 'Gửi dịch vụ thành công và email đã được gửi', 'service' => $service]);
        } else {
          return response()->json(['message' => 'Gửi dịch vụ thành công nhưng không thể gửi email', 'service' => $service]);
        }
      }
      return response()->json(['message' => 'gửi dịch vụ thành công', 'service' => $service]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()]);
    }
  }

  // thay đổi trạng thái


  public function getAllServiesByStudent(Request $request)
  {
    try {
      $user_code = request()->user()->user_code;
      $data = Service::query()->where('user_code', $user_code);

      if (!$data) {
        return response()->json(['message' => 'Service not found'], 404);
      }

      return response()->json(['data' => $data]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], 500);
    }
  }


  public function changeStatus(int $id, Request $request)
  {
    try {
      $status = $request->input('status');
      $reason = $request->input('reason', null);
      if (!in_array($status, ['approved', 'rejected'])) {
        return response()->json(['message' => 'Trạng thái không hợp lệ'],400);
      }

      $service = Service::findOrFail($id);  // Lấy dịch vụ
      if($service->status == 'approved' || $service->status == 'rejected'){
            return response()->json(['message' => "Không được thay đổi trạng thái khi đã duyệt hoặc hủy"]);
      }

      $service->update([
        'status' => $status,
        'reason' => $reason
      ]);
      // Lấy thông tin người dùng liên quan đến dịch vụ
      $user = User::where('user_code', $service->user_code)->firstOrFail();
      // Dữ liệu cần gửi vào email
      $data = [
        "full_name" => $user->full_name,
        "user_code" => $user->user_code,
        "service_name" => $service->service_name,
        "status" => $status == "approved" ? "Đã được duyệt" : "Đã bị từ chối",
        "reason" => $reason ? $reason : 'Không có lý do',
      ];

      Mail::to($user->email)->send(new ServiceStatusChanged($data));

      return response()->json([
        'success' => true,
        'message' => "Đã gửi email thành công",
        'data' => ['reason' => $reason]
    ]);
    } catch (\Throwable $th) {
        Log::error('Error in changeStatus: ' . $th->getMessage());
        return response()->json(['message' => 'Có lỗi xảy ra, vui lòng thử lại sau'], 500);
    }
  }


  public function changeMajor(string $user_code, Request $request)
  {
    try {
      $old_major = Category::where('cate_code', $request->old_major)->first();
      $new_major = Category::where('cate_code', $request->new_major)->first();

      $content = "Mong mong muốn được chuyển Chuyên ngành: Từ " . $old_major->cate_name . " sang " . $new_major->cate_name . ".
        Lý do: {$request->reason}";


      $data = [
        'user_code' => $user_code,
        'name' => "Đăng kí dịch vụ chuyển Chuyên Ngành Học ",
        'content' => $content,
      ];

      $service = Service::create($data);
      return response()->json(['message' => 'gửi dịch vụ thành công', 'service' => $service]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()]);
    }
  }

  public function provideScoreboard(Request $request)
  {
    try {
      $validatedData = $request->validate([
        'number_board'     => 'required|integer|min:1',
        'number_phone'     => 'required|string|max:15',
        'receive_method'   => 'required|string',
        'amount'           => 'required',
        'receive_address'  => 'nullable|string|max:255',
        'note'             => 'nullable|string|max:500',
      ]);

      $user_code = request()->user()->user_code;
      // $user_code = $request->user_code;
      if (!$user_code) {
        return response()->json(['message' => 'không tìm thấy user_code']);
      }

      $content = "Số lượng bảng điểm: {$validatedData['number_board']} \n";
      $content .= "Số điện thoại: {$validatedData['number_phone']} \n";
      $content .= "Hình thức nhận: {$validatedData['receive_method']} \n";

      if (!empty($validatedData['receive_address'])) {
        $content .= "Địa chỉ nhận: {$validatedData['receive_address']} \n";
      }

      if (!empty($validatedData['note'])) {
        $content .= "Ghi chú: {$validatedData['note']} \n";
      }

      $service_name = "Đăng ký cấp bảng điểm";
      $amount = $validatedData['amount'];
      $data = [
        'user_code'     => $user_code,
        'service_name'  => $service_name,
        'content'       => $content,
        'amount'        => $amount
      ];
      $service = Service::create($data);
      $user = User::where('user_code', $service->user_code)->firstOrFail();

      $dataEmail = [
        'id'            => $service->id,
        'student_name'  => $user->full_name,
        'user_code'     => $user_code,
        'service_name'  => $service_name,
        'content'       => $content,
        'status'        => $service->status,
        'amount'        => $amount
      ];
      Mail::to($user->email)->send(new SendEmailFeeService($dataEmail));
      return response()->json(['message' => 'gửi dịch vụ thành công', 'service' => $service]);
    } catch (\Throwable $th) {
      return response()->json([
        'message' => 'Đã xảy ra lỗi',
        'error'   => $th->getMessage(),
      ], 500);
    }
  }

  public function changeInfo(ChangeInfoRequest $request)
{
    try {
        // Lấy dữ liệu đã validate từ ChangeInfoRequest
        $validatedData = $request->validated();

        // Lấy user_code của người dùng hiện tại
        $user_code = $request->user()->user_code;
        if (!$user_code) {
            return response()->json(['message' => 'Không tìm thấy user_code'], 404);
        }

        $service_name = "Đăng kí thay đổi thông tin";

        $existingService = Service::where('user_code', $user_code)
            ->where('service_name', $service_name)
            ->first();


        if ($existingService && $existingService->status == 'pending' && $existingService->user_code = $user_code) {
            return response()->json(['message' => 'Yêu cầu thay đổi thông tin đã tồn tại. Vui lòng chờ xử lý.'], 409);
        }


        $contentParts = [];

        if (!empty($validatedData['full_name'])) {
            $contentParts[] = "Họ và tên cũ: {$validatedData['full_name_old']} __ Họ và tên mới: {$validatedData['full_name']}";
        }

        if (!empty($validatedData['sex'])) {
            $contentParts[] = "Giới tính cũ: {$validatedData['sex_old']} __ Giới tính mới: {$validatedData['sex']}";
        }

        if (!empty($validatedData['date_of_birth'])) {
            $contentParts[] = "Ngày sinh cũ: {$validatedData['date_of_birth_old']} __ Ngày sinh mới: {$validatedData['date_of_birth']}";
        }

        if (!empty($validatedData['address'])) {
            $contentParts[] = "Địa chỉ cũ: {$validatedData['address_old']}  __ Địa chỉ mới: {$validatedData['address']}";
        }

        if (!empty($validatedData['citizen_card_number'])) {
            $contentParts[] = "Số CMND/CCCD cũ: {$validatedData['citizen_card_number_old']}  __ Số CMND/CCCD mới: {$validatedData['citizen_card_number']}";
        }

        if (!empty($validatedData['note'])) {
            $contentParts[] = "Ghi chú: {$validatedData['note']}";
        }

        // Nếu không có gì thay đổi, trả về lỗi
        if (empty($contentParts)) {
            return response()->json(['message' => 'Không có thông tin nào được thay đổi.'], 400);
        }

        // Kết hợp nội dung
        $content = implode("\n", $contentParts);

        // Tạo dữ liệu dịch vụ mới
        $data = [
            'user_code'    => $user_code,
            'service_name' => $service_name,
            'content'      => $content,
        ];

        $service = Service::create($data);

        return response()->json(['message' => 'Gửi dịch vụ thành công', 'service' => $service], 201);

    } catch (\Throwable $th) {
        return response()->json(['message' => $th->getMessage()], 500);
    }
}

  public function provideStudentCard(string $user_code, Request $request)
  {
    try {
      $content = "Mong muốn được cung cấp thẻ sinh viên";
      $data = [
        'user_code' => $user_code,
        'name' => "Đăng kí dịch vụ Cung cấp thẻ sinh viên ",
        'content' => $content,
      ];

      $service = Service::create($data);
      return response()->json(['message' => 'gửi dịch vụ thành công', 'service' => $service]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()]);
    }
  }

  public function dropOutOfSchool(string $user_code, Request $request)
  {
    try {
      $content = "Mong muốn đăng ký dịch vụ thôi học. Lý do: {$request->reason}";
      $data = [
        'user_code' => $user_code,
        'name' => "Đăng kí dịch vụ Thôi học",
        'content' => $content,
      ];

      $service = Service::create($data);
      return response()->json(['message' => 'gửi dịch vụ thành công', 'service' => $service]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()]);
    }
  }

  public function cancelServiceByStudent(int $id)
  {
    try {
      $service = Service::find($id);
      if (!$service) {
        return response()->json(['message' => 'không tìm thấy dịch vụ']);
      }

      $status = $service->status;
      if ($status != "pending") {
        return response()->json(['message' => 'dịch vụ đã được chấp nhận hoặc bị hủy, không thể hủy dịch vụ']);
      }

      $service->delete();
      return response()->json(['message' => 'hủy dịch vụ thành công']);
    } catch (\Throwable $th) {
      Log::error('Cancel Service Error: ' . $th->getMessage());
      return response()->json(['message' => $th->getMessage()]);
    }
  }

  public function StudentsInfoOld()
  {
    try {
      $user_code = request()->user()->user_code;
      $studentOld = User::where('user_code', $user_code)->firstOrFail();
      // $studentOld = User::all();

      if (!$studentOld) {
        return response()->json(['message' => 'không tìm thấy thông tin cũ']);
      }

      $data = [
        'full_name'       => $studentOld->full_name,
        'sex'             => $studentOld->sex,
        'date_of_birth'   => $studentOld->birthday,
        'address'         => $studentOld->address,
        'citizen_card_number' => $studentOld->citizen_card_number,
      ];
      return response()->json(['data' =>  $data]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()]);
    }
  }
}
