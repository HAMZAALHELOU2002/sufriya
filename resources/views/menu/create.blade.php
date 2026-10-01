@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">
          <i class="fas fa-plus-circle text-primary ml-2"></i> إضافة طبق جديد للمنيو
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <a href="{{ route('menu-items.index') }}" class="btn btn-secondary btn-sm shadow-sm font-weight-bold">
          <i class="fas fa-arrow-right ml-1"></i> رجوع للأطباق
        </a>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 mx-auto">

        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <ul class="mb-0 pr-3">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="card card-outline card-primary shadow-sm border-0 rounded-lg">
          <form action="{{ route('menu-items.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card-body p-4">
              <div class="form-group">
                <label class="font-weight-bold text-dark">القسم <span class="text-danger">*</span></label>
                <select name="category_id" class="form-control form-control-lg rounded-pill shadow-sm" required>
                  <option value="">اختر القسم التابع له الطبق</option>
                  @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label class="font-weight-bold text-dark">اسم الطبق <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control form-control-lg rounded-pill shadow-sm" placeholder="مثال: منسف لحم بلدي، بيتزا مارغريتتا..." required>
              </div>

              <div class="form-group">
                <label class="font-weight-bold text-dark">السعر (د.أ) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="form-control rounded-pill shadow-sm" placeholder="0.00" required>
              </div>

              <div class="form-group">
                <label class="font-weight-bold text-dark">وصف الطبق</label>
                <textarea name="description" class="form-control rounded-lg shadow-sm" rows="3" placeholder="مكونات الطبق أو وصف مختصر...">{{ old('description') }}</textarea>
              </div>

              <div class="form-group">
                <label class="font-weight-bold text-dark">صورة الطبق</label>
                <input type="file" name="image" class="form-control-file">
              </div>

              <div class="form-group form-check mt-3">
                <input type="checkbox" name="is_available" class="form-check-input" id="is_available" checked>
                <label class="form-check-label font-weight-bold text-dark" for="is_available">الطبق متاح للطلب حالياً</label>
              </div>
            </div>

            <div class="card-footer bg-light px-4 py-3 text-left">
              <a href="{{ route('menu-items.index') }}" class="btn btn-secondary rounded-pill px-4 ml-2">إلغاء</a>
              <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm font-weight-bold">حفظ الطبق</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>
@endsection
