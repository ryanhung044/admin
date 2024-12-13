<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Thông báo dịch vụ đã đăng kí</title>
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
    <h1>THÔNG BÁO ĐĂNG KÝ DỊCH VỤ</h1>

    <p><strong>Thân gửi sinh viên:  {{ $data['student_name'] }}</strong> </p>
    <p><strong>Mã số sinh viên: {{ $data['user_code']}}</strong> </p>

    <p>Trường Cao đẳng F-Education - Cơ sở Hà Nội gửi đến bạn thông
        tin dịch vụ đã đăng kí</p>

    <h2>Thông tin học phí</h2>

    <table>
        <tr>
            <th>Tên dịch vụ</th>
            <th>Nội dung</th>
            <th>Trạng thái</th>
            <th>Lí do</th>
            <th>Phí dịch vụ</th>
        </tr>
        <tr>
            <td> {{$data['service_name'] }}</td>
            <td>{{ $data['content'] }}</td>
            <td>  {{ $data['status'] }}  </td>
            <td></td>
            <td>{{ $data['amount'] }}</td>
        </tr>
    </table>
    <a href="https://admin.feduvn.com/total_momo/learn-again?id={{$data['id']}}&user_code={{$data['user_code']}}">
        Thanh toán ngay
    </a>
    <p class="note">Sinh viên có thắc mắc hoặc cần hỗ trợ thêm thông tin vui lòng liên hệ theo thông tin dưới đây trong
        giờ hành chính:</p>
    <ul>
        <li><strong>Số điện thoại:</strong> 0398623059</li>
        <li><strong>Email:</strong> dvsvfedu.hn@fedu.edu.vn</li>
        <li><strong>Bộ phận liên hệ:</strong> Phòng Dịch vụ Sinh viên</li>
    </ul>
</body>

</html>


