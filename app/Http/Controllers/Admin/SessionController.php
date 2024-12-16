<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Http\Requests\StoreSessionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Repositories\Contracts\SessionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class SessionController extends Controller
{
    public $sessionRepository;

    public function __construct(SessionRepositoryInterface $sessionRepository){
        $this->sessionRepository = $sessionRepository;
    }

    public function index(){
        try{
            $model = $this->sessionRepository->getAll();
            return response()->json($model , 200);
        }catch(NotFoundHttpException $e){
            return response()->json(['message'=>$e->getMessage()],404);
        }
        catch(\Throwable $th){
            return response()->json(["message"=>$th],500);
        }
    }

    // public function show($cate_code)
    // {
    //     try {
    //         $model = $this->sessionRepository->getByCateCode($cate_code);

    //         if (!$model) {
    //             return response()->json(['message' => 'Data not found'], 404);
    //         }

    //         return response()->json($model, 200);
    //     } catch (NotFoundHttpException $e) {
    //         return response()->json(['message' => $e->getMessage()], 404);
    //     } catch (\Throwable $th) {
    //         return response()->json(['message' => $th->getMessage()], 500);
    //     }
    // }

    public function store(StoreSessionRequest $request){
        try{
            $timeStart = Carbon::parse($request->time_start);
            $timeEnd = Carbon::parse($request->time_end);

            $differenceInHours = $timeStart->diffInHours($timeEnd);
            if ($differenceInHours !== 2) {
                return response()->json(["message" => "Thời gian bắt đầu và kết thúc phải cách nhau 2 tiếng."], 500);
            }

            $existingSessions = $this->sessionRepository->getAll();

            // foreach ($existingSessions as $session) {ss
            //     // Tách chuỗi value thành start và end
            //     $value = json_decode($session->value, true); // Giải mã JSON
            //     $existingStart = Carbon::parse($value['start']);
            //     $existingEnd = Carbon::parse($value['end']);

            //     // Kiểm tra chồng chéo thời gian
            //     if ($timeStart < $existingEnd && $timeEnd > $existingStart) {
            //         return response()->json(["message" => "Thời gian bị trùng với ca học khác."], 400);
            //     }
            // }

            $value = [
                'start' => $request->time_start,
                'end'   => $request->time_end
            ];

            $value_json = json_encode($value);


            $cate_code = "TS".$request->session;
            $cate_name = "Ca ".$request->session;
            $value = $value_json;
            $data = [
                'cate_code'=>   $cate_code,
                'cate_name'=>   $cate_name,
                'value'    =>   $value,
                'type'     =>   'session'
            ];

            $model = $this->sessionRepository->create($data);
            return response()->json(["message"=> "thêm thành công"], 200);
        }catch(\Throwable $th){
            return response()->json($th->getMessage(), 400);
        }
    }

    public function update(Request $request, $code)
    {
        try {
            // Lấy bản ghi ca học cần cập nhật
            $session = $this->sessionRepository->getModel()->where('cate_code', $code)->first();
            // Kiểm tra xem ca học có tồn tại không
            if (!$session) {
                return response()->json(["message" => "Ca học không tồn tại."], 404);
            }
            // Chuyển đổi thời gian bắt đầu và kết thúc từ request
            $timeStart = Carbon::parse($request->time_start);
            $timeEnd = Carbon::parse($request->time_end);

            // Kiểm tra thời gian cách nhau đúng 2 tiếng
            $differenceInHours = $timeStart->diffInHours($timeEnd);
            if ($differenceInHours !== 2) {
                return response()->json(["message" => "Thời gian bắt đầu và kết thúc phải cách nhau 2 tiếng."], 400);
            }

            // Lấy tất cả các ca học khác ngoài ca học hiện tại để kiểm tra trùng lặp
            $existingSessions = $this->sessionRepository->getModel()
                ->where('cate_code', '!=', $code) // Loại bỏ bản ghi hiện tại
                ->where('type', 'session')
                ->get();

            // Duyệt qua các ca học để kiểm tra trùng lặp thời gian
            foreach ($existingSessions as $existingSession) {
                // Giải mã chuỗi value của ca học hiện tại
                $value = json_decode($existingSession->value, true);

                // Kiểm tra nếu value hợp lệ
                if ($value === null || !isset($value['start']) || !isset($value['end'])) {
                    continue; // Nếu không hợp lệ, bỏ qua
                }

                // Chuyển đổi thời gian bắt đầu và kết thúc của ca học hiện tại
                $existingStart = Carbon::parse($value['start']);
                $existingEnd = Carbon::parse($value['end']);

                // Kiểm tra chồng chéo thời gian
                if ($timeStart < $existingEnd && $timeEnd > $existingStart) {
                    return response()->json(["message" => "Thời gian bị trùng với ca học khác."], 400);
                }
            }


            // Tạo chuỗi value với định dạng "start-end"
            $value = [
                'start' => $request->time_start,
                'end'   => $request->time_end
            ];
            $value_json = json_encode($value);

            // Dữ liệu cần cập nhật
            $data = [
                'cate_code' => "TS" . $request->session,
                'cate_name' => "Ca " . $request->session,
                'value'     => $value_json,
                'type'      => 'session',
            ];

            // Cập nhật bản ghi
            $this->sessionRepository->update($data, $code);

            // Trả về phản hồi thành công
            return response()->json(["message" => "Cập nhật thành công"], 200);

        } catch (\Throwable $th) {
            // Xử lý lỗi
            return response()->json(["message" => $th->getMessage()], 400);
        }
    }


    public function destroy(string $code){
        try{
            $this->sessionRepository->delete($code);
            return response()->json(["message"=> "xóa thành công"], 200);
        }catch(\Throwable $th){
            return response()->json($th->getMessage(), 400);
        }
    }
}
