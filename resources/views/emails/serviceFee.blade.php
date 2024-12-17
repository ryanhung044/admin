<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo dịch vụ đã đăng ký</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h1, h2 {
            color: #333;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        .highlight {
            color: #d9534f;
            font-weight: bold;
        }

        .note {
            color: #555;
            font-size: 14px;
            margin-top: 20px;
        }

    </style>
</head>

<body>
    <h1>THÔNG BÁO ĐĂNG KÝ DỊCH VỤ</h1>

    <p><strong>Thân gửi sinh viên: {{ $data['student_name'] }}</strong></p>
    <p><strong>Mã số sinh viên: {{ $data['user_code'] }}</strong></p>
    <p>Trường Cao đẳng F-Education - Cơ sở Hà Nội gửi đến bạn thông tin dịch vụ đã đăng ký.</p>
    <h2>Thông tin học phí</h2>

    <table>
        <tr>
            <th>Tên dịch vụ</th>
            <th>Nội dung</th>
            <th>Phí dịch vụ</th>
        </tr>
        <tr>
            <td>{{ $data['service_name'] }}</td>
            <td>{{ $data['content'] }}</td>
            <td>{{ number_format($data['amount'], 0, ',', '.') }} VND</td>
        </tr>
    </table>

    <a href="https://admin.feduvn.com/api/total_momo/service?id={{$data['id']}}&user_code={{$data['user_code']}}">
        Thanh toán MOMO
    </a>
    <br>
    <br>
    <a href="https://admin.feduvn.com/api/service?id={{$data['id']}}&user_code={{$data['user_code']}}">
        Thanh toán VNPAY
    </a>

    <p class="note">Sinh viên có thắc mắc hoặc cần hỗ trợ thêm thông tin vui lòng liên hệ theo thông tin dưới đây trong giờ hành chính:</p>
    <ul>
        <li><strong>Số điện thoại:</strong> 0398623059</li>
        <li><strong>Email:</strong> dvsvfedu.hn@fedu.edu.vn</li>
        <li><strong>Bộ phận liên hệ:</strong> Phòng Dịch vụ Sinh viên</li>
    </ul>
</body>

</html>
