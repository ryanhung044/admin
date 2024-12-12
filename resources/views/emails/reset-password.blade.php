<!-- resources/views/emails/reset-password.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        /* Styling email */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: #ffffff;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .footer {
            font-size: 12px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Yêu cầu đặt lại mật khẩu</h1>
        <p>Hello,</p>
        <p>Chúng tôi đã nhận được yêu cầu đặt lại mật khẩu của bạn. Nhấp vào nút bên dưới để đặt lại mật khẩu của bạn.</p>
        {{-- <p><a href="{{route("reset.password",$token)}}" class="btn">Đặt lại mật khẩu</a></p> --}}
        <p><a href="{{ $url }}" class="btn">Đặt lại mật khẩu</a></p>
        <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        <p>Cảm ơn,</p>
        <p>The {{ config('app.name') }} Team</p>
    </div>
    <div class="footer">
        <p>Đây là tin nhắn tự động, vui lòng không trả lời email này.</p>
    </div>
</body>
</html>
