@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark animate-fade-in">
          <i class="fas fa-sliders-h text-primary ml-2"></i> إعدادات المطعم
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <span class="text-muted small font-weight-bold">تحديث بيانات ومعلومات مطعمك اللحظية</span>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    {{-- رسالة النجاح بتصميم احترافي متحرك وجذاب --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-lg border-0 rounded-lg mb-4" role="alert" style="background: linear-gradient(135deg, #28a745, #20c997); color: white;">
        <div class="d-flex align-items-center">
          <i class="fas fa-check-circle fa-2x ml-3"></i>
          <div>
            <h5 class="mb-0 font-weight-bold">تم بنجاح!</h5>
            <span>{{ session('success') }}</span>
          </div>
        </div>
        <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    {{-- رسالة الأخطاء إن وجدت --}}
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-lg mb-4" role="alert">
        <div class="d-flex align-items-center mb-2">
          <i class="fas fa-exclamation-triangle fa-lg ml-2"></i>
          <span class="font-weight-bold">يرجى تصحيح الأخطاء التالية:</span>
        </div>
        <ul class="mb-0 pr-3">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    {{-- نموذج واحد متكامل لحفظ كافة البيانات معاً --}}
    <form action="{{ route('restaurant.settings.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="row">
        <!-- القسم الأيمن: معلومات المطعم الأساسية -->
        <div class="col-lg-8">
          <div class="card card-outline card-primary shadow-sm border-0 rounded-lg settings-card mb-4">
            <div class="card-header bg-white py-3">
              <h3 class="card-title font-weight-bold text-dark m-0">
                <i class="fas fa-store text-primary ml-1"></i> معلومات المطعم الأساسية
              </h3>
            </div>
            <div class="card-body p-4">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold text-dark">اسم المطعم <span class="text-danger">*</span></label>
                  <input type="text" name="name" value="{{ old('name', $restaurant->name ?? '') }}" class="form-control form-control-lg rounded-pill shadow-sm" required>
                </div>
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold text-dark">معرف هاتف واتساب (WhatsApp ID)</label>
                  <input type="text" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $restaurant->whatsapp_phone_number_id ?? '') }}" class="form-control form-control-lg rounded-pill shadow-sm text-right" dir="ltr" placeholder="مثال: 123456789">
                </div>
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold text-dark">معرف حساب أعمال واتساب (WABA ID)</label>
                  <input type="text" name="whatsapp_business_account_id" value="{{ old('whatsapp_business_account_id', $restaurant->whatsapp_business_account_id ?? '') }}" class="form-control form-control-lg rounded-pill shadow-sm" dir="ltr">
                </div>
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold text-dark">نسبة العمولة المفترضة (%)</label>
                  <input type="number" step="0.01" name="assumed_commission_rate" value="{{ old('assumed_commission_rate', $restaurant->assumed_commission_rate ?? 30.00) }}" class="form-control form-control-lg rounded-pill shadow-sm">
                </div>
                <div class="col-md-12 form-group">
                  <label class="font-weight-bold text-dark">عنوان / موقع المطعم</label>
                  <textarea name="location" class="form-control rounded-lg shadow-sm" rows="2" placeholder="أدخل عنوان المطعم التفصيلي">{{ old('location', $restaurant->location ?? '') }}</textarea>
                </div>
                <div class="col-md-12 form-group">
                  <label class="font-weight-bold text-dark">شعار المطعم (Logo)</label>
                  @if(!empty($restaurant->logo_path))
                    <div class="mb-3">
                      <img src="{{ asset('storage/' . $restaurant->logo_path) }}" alt="شعار المطعم" class="img-thumbnail rounded shadow-sm border" style="width: 70px; height: 70px; object-fit: cover;">
                    </div>
                  @endif
                  <input type="file" name="logo_path" class="form-control-file pt-1">
                  <small class="text-muted d-block mt-1">يُفضل أن تكون الصورة بصيغة PNG أو JPG وبحجم لا يتجاوز 2MB.</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- القسم الأيسر: التحكم بحالة المطعم ومعلومات المنظومة -->
        <div class="col-lg-4">
          <!-- بطاقة حالة المطعم وتفعيل الطلبات -->
          <div class="card card-outline card-success shadow-sm border-0 rounded-lg settings-card mb-4">
            <div class="card-header bg-white py-3">
              <h3 class="card-title font-weight-bold text-dark m-0">
                <i class="fas fa-toggle-on text-success ml-1"></i> حالة استقبال الطلبات
              </h3>
            </div>
            <div class="card-body p-4">
              <div class="form-group">
                <label class="font-weight-bold text-dark">اختر حالة المطعم الحالية:</label>
                <select name="status" class="form-control form-control-lg rounded-pill shadow-sm">
                  <option value="active" {{ old('status', $restaurant->status ?? 'active') == 'active' ? 'selected' : '' }}>🟢 نشط (يستقبل طلبات)</option>
                  <option value="closed" {{ old('status', $restaurant->status ?? '') == 'closed' ? 'selected' : '' }}>🔴 مغلق (اعتذار للعملاء)</option>
                </select>
              </div>
              <div class="form-group mt-3">
                <label class="font-weight-bold text-dark">رسالة الاعتذار عند الإغلاق:</label>
                <textarea name="close_message" class="form-control rounded-lg shadow-sm" rows="2" placeholder="عذراً، المطعم مغلق حالياً...">{{ old('close_message', $restaurant->close_message ?? 'عذراً، المطعم مغلق حالياً ونستقبل الطلبات أوقات العمل الرسمية.') }}</textarea>
              </div>
              <div class="text-center mt-3">
                @if(($restaurant->status ?? 'active') == 'active')
                  <span class="badge badge-success px-3 py-2 font-weight-bold rounded-pill shadow-sm">
                    <i class="fas fa-circle ml-1" style="font-size: 8px;"></i> المطعم نشط حالياً
                  </span>
                @else
                  <span class="badge badge-danger px-3 py-2 font-weight-bold rounded-pill shadow-sm">
                    <i class="fas fa-circle ml-1" style="font-size: 8px;"></i> المطعم مغلق حالياً
                  </span>
                @endif
              </div>
            </div>
          </div>

          <!-- معلومات النظام -->
          <div class="card shadow-sm border-0 rounded-lg bg-gradient-dark text-white mb-4">
            <div class="card-body p-4">
              <h5 class="font-weight-bold mb-3"><i class="fas fa-info-circle text-warning ml-1"></i> معلومات المنظومة</h5>
              <p class="small mb-2">إصدار النظام: <strong>v2.5 SaaS</strong></p>
              <p class="small mb-0">حالة الخادم: <span class="text-success font-weight-bold">متصل ومنتظم</span></p>
            </div>
          </div>
        </div>
      </div>

      <!-- زر الحفظ العام أسفل الصفحة -->
      <div class="row">
        <div class="col-12 text-left mb-5">
          <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 font-weight-bold shadow-lg pulse-btn">
            <i class="fas fa-save ml-2"></i> حفظ كافة التعديلات والإعدادات
          </button>
        </div>
      </div>
    </form>

  </div>
</section>
@endsection

@push('styles')
<style>
  .settings-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .settings-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
  }
</style>
@endpush
