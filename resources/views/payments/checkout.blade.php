<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إتمام الدفع - مطعم حمزة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h3 class="card-title mb-4">فاتورة الدفع الإلكتروني 💳</h3>
                <p class="text-muted">رقم الطلب: <strong>#{{ $order->id }}</strong></p>
                <hr>
                <h4 class="text-success mb-4">المبلغ المطلوب: {{ $order->total_amount }} ريال</h4>

                <form action="{{ route('payment.process', $token) }}" method="POST">
                    @csrf
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">ادفع الآن (محاكاة بوابة الدفع)</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
