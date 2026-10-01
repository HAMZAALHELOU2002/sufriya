@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark animate-fade-in">
          <i class="fas fa-hamburger text-success ml-2"></i> إدارة الأطباق والوجبات
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <button type="button" class="btn btn-success btn-sm shadow-sm font-weight-bold pulse-btn" data-toggle="modal" data-target="#addDishModal">
          <i class="fas fa-plus-circle ml-1"></i> إضافة طبق جديد
        </button>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- رسائل النجاح أو الخطأ -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    <!-- جدول عرض الأطباق العصري والمتحرك -->
    <div class="card card-outline card-success shadow-sm border-0 rounded-lg">
      <div class="card-header bg-white py-3">
        <h3 class="card-title font-weight-bold text-dark m-0">
          <i class="fas fa-utensils text-success ml-1"></i> قائمة الأطباق المتاحة
        </h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle text-right mb-0">
            <thead class="thead-light text-secondary font-weight-bold">
              <tr>
                <th class="py-3 text-center" style="width: 70px;">الرقم</th>
                <th class="py-3">الصورة</th>
                <th class="py-3">اسم الطبق</th>
                <th class="py-3">القسم</th>
                <th class="py-3">السعر</th>
                <th class="py-3 text-center">الحالة</th>
                <th class="py-3 text-center" style="width: 180px;">إجراءات</th>
              </tr>
            </thead>
            <tbody>
              @forelse($menuItems as $index => $item)
                <tr class="table-row-hover">
                  <td class="text-center font-weight-bold text-success align-middle">{{ $index + 1 }}</td>
                 <td class="align-middle">
  @if(!empty($item->image_path))
    {{-- طباعة الرابط للتأكد منه --}}
    <img src="{{ url('storage/' . $item->image_path) }}" alt="صورة الطبق" class="img-thumbnail rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
  @else
    <span class="badge badge-light border px-2 py-1 text-muted shadow-sm">بدون صورة</span>
  @endif
</td>
                  <td class="align-middle font-weight-bold text-dark">{{ $item->name }}</td>
                  <td class="align-middle">
                    <span class="badge badge-primary px-3 py-1 font-weight-bold rounded-pill">{{ $item->category_name }}</span>
                  </td>
                  <td class="align-middle font-weight-bold text-success">${{ number_format($item->price, 2) }}</td>
                  <td class="text-center align-middle">
                    <span class="badge badge-{{ $item->is_available ? 'success' : 'secondary' }} px-3 py-2 font-weight-bold shadow-sm rounded-pill" style="font-size: 0.85rem;">
                      {{ $item->is_available ? 'متوفر' : 'غير متوفر' }}
                    </span>
                  </td>
                  <td class="text-center align-middle">
                    <a href="{{ route('menu-items.edit', $item->id) }}" class="btn btn-info btn-sm shadow-sm action-btn mx-1" title="تعديل الطبق">
                      <i class="fas fa-edit"></i>
                    </a>

                    <form action="{{ route('menu-items.destroy', $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطبق؟');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm shadow-sm action-btn" title="حذف الطبق">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-4 text-muted">لا توجد أطباق مضافة حتى الآن.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- Modal: إضافة طبق جديد -->
<div class="modal fade" id="addDishModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-0 shadow-lg rounded-lg">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-plus-circle ml-1"></i> إضافة طبق أو وجبة جديدة
        </h5>
        <button type="button" class="close text-white m-0" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('menu-items.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-6 form-group">
              <label class="font-weight-bold text-dark">اسم الطبق <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control form-control-lg rounded-pill shadow-sm" placeholder="مثال: مسخن دجاج، برجر..." required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold text-dark">القسم <span class="text-danger">*</span></label>
              <select name="category_id" class="form-control form-control-lg rounded-pill shadow-sm" required>
                <option value="">اختر القسم المناسب</option>
                @foreach($categories ?? [] as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold text-dark">السعر ($) <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="price" class="form-control form-control-lg rounded-pill shadow-sm" placeholder="10.00" required>
            </div>
            <div class="col-md-6 form-group">
              <label class="font-weight-bold text-dark">صورة الطبق</label>
              <input type="file" name="image_path" class="form-control-file">
            </div>
            <div class="col-md-12 form-group">
              <label class="font-weight-bold text-dark">مكونات وتفاصيل الطبق</label>
              <textarea name="description" class="form-control rounded-lg shadow-sm" rows="3" placeholder="اذكر تفاصيل ومكونات الطبق..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light px-4 py-3">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">إلغاء</button>
          <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">حفظ الطبق</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .table td, .table th {
    vertical-align: middle !important;
  }
  .table-row-hover {
    transition: background-color 0.2s ease, transform 0.2s ease;
  }
  .table-row-hover:hover {
    background-color: rgba(40, 167, 69, 0.03) !important;
  }
  .action-btn {
    transition: transform 0.2s ease;
  }
  .action-btn:hover {
    transform: scale(1.15);
  }
</style>
@endpush
