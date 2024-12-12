<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Thông báo nộp học phí</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        h1,
        h2 {
            text-align: left;
            color: black;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .highlight {
            color: #d9534f;
            font-weight: bold;
        }

        .note {
            color: #888;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <h1>THÔNG BÁO NỘP HỌC PHÍ</h1>
    <p><i>(Sinh viên chuyển ngành, chuyển cơ sở và đã hoàn thành học phí vui lòng bỏ qua thông báo này)</i></p>

    <p><strong>Thân gửi sinh viên:</strong> {{ $data['full_name'] }}</p>
    <p><strong>Mã số sinh viên:</strong> {{ $data['user_code'] }}</p>

    <p>Theo kế hoạch của phòng Tổ chức & Quản lý Đào tạo. Trường Cao đẳng F-Education - Cơ sở Hà Nội gửi đến bạn thông
        tin học phí cần nộp như sau:</p>

    <h2>Thông tin học phí</h2>

    <table>
        <tr>
            <th>Nội dung</th>
            <th>Số tiền (VNĐ)</th>
        </tr>
        <tr>
            <td>Học phí phải nộp bao gồm học phí Tiếng Anh (đã trừ số dư Ví Học phí, ưu đãi, miễn giảm,...)</td>
            <td>{{ number_format($data['amount'], 0, ',', '.') }} VND</td>
        </tr>
    </table>

    <p><strong>3. Thời hạn nộp tiền:</strong> Từ ngày <strong>{{ $data['start_date'] }}</strong>
        đến trước 24h ngày <strong>{{ $data['due_date'] }}</strong></p>



    <p><strong>4. Đối với sinh viên thiếu nợ môn Tiếng Anh:</strong> Chủ động đăng ký trả nợ môn, sử dụng số dư Ví Học
        Lại (nếu có).</p>

    <p><strong>5. Hình thức nộp tiền:</strong> Sinh viên truy cập hệ thống để
        {{-- <a href="https://admin.feduvn.com/total_momo?fee_id={{ $data['id'] }}">dng.fpt.edu.vn</a> để xem hướng dẫn nộp tiền.  --}}
        <a href="https://admin.feduvn.com/total_momo?fee_id={{ $data['id'] }}">Thanh toán ngay</a>
    </p>

    <h2>Hướng dẫn tra cứu học phí đã nộp</h2>
    <ul>
        <li>Hóa đơn sẽ được gửi về địa chỉ Email sinh viên đã đăng ký. Thông thường hóa đơn sẽ được gửi trong vòng 7
            ngày kể từ thời điểm thanh toán thành công.</li>
        <li>Khi sinh viên hoàn thành học phí, phòng Hành Chính sẽ xuất hóa đơn và đẩy tiền lên ví Học Phí. Ví Học Phí
            hiển thị tiền => nộp học phí thành công.</li>
    </ul>

    <h2>Lưu ý</h2>
    <ul>
        <li>Các trường hợp không nộp học phí đúng thời gian quy định được coi như tự nguyện thôi học.</li>
        <li>Sinh viên đã thôi học có nguyện vọng tiếp tục học phải làm thủ tục nhập học trở lại, hồ sơ bao gồm Đơn xin
            nhập học trở lại và phí nhập học trở lại 500,000 VNĐ.</li>
    </ul>

    <p class="note">Sinh viên có thắc mắc hoặc cần hỗ trợ thêm thông tin vui lòng liên hệ theo thông tin dưới đây trong
        giờ hành chính:</p>
    <ul>
        <li><strong>Số điện thoại:</strong> 0398623059</li>
        <li><strong>Email:</strong> dvsvfedu.hn@fedu.edu.vn</li>
        <li><strong>Bộ phận liên hệ:</strong> Phòng Dịch vụ Sinh viên</li>
    </ul>
</body>

</html>
