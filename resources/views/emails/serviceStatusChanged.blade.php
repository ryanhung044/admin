<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo thay đổi trạng thái dịch vụ</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            text-align: center;
            background-color: #007BFF;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .email-body {
            margin: 20px 0;
            font-size: 16px;
        }
        .email-body p {
            margin: 10px 0;
        }
        .status {
            font-weight: bold;
        }
        .status-approved {
            color: #28a745;
        }
        .status-rejected {
            color: #dc3545;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Thông báo thay đổi trạng thái dịch vụ</h1>
        </div>
        <div class="email-body">
            <p>Xin chào <strong>{{ $data['full_name'] }}</strong>,</p>
            <p>Trạng thái dịch vụ "<strong>{{ $data['service_name'] }}</strong>" của bạn đã thay đổi thành:</p>
            <p class="status @if($data['status'] == 'approved') status-approved @else status-rejected @endif">
                <strong>{{ $data['status'] }}</strong>
            </p>

            @if($data['reason'])
                <p><strong>Lý do:</strong> {{ $data['reason'] }}</p>
            @else
                <p><strong>Lý do:</strong> Không có lý do</p>
            @endif

            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
        </div>
        <div class="footer">
            <p>Đây là email tự động, vui lòng không trả lời.</p>
        </div>
    </div>
</body>
</html>

