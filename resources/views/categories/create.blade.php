@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">إضافة قسم جديد للمنيو</h1>
      </div>
      <div class="col-sm-6 text-left">
        <a href="{{ route('categories.index') }}" class="btn btn-secondary btn-sm">
          <i class="fas fa-arrow-right ml-1"></i> رجوع للأقسام
        </a>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="card shadow-sm border-0">
      <div class="card-body bg-white p-4">
        <form action="{{ route('categories.store') }}" method="POST">
          @csrf

          <div class="form-group">
            <label for="name">اسم القسم <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="description">وصف مختصر</label>
            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
            @error('description')
              <span class="invalid-feedback">{{ $message }}</span>
            @enderror
          </div>

          <div class="form-group">
            <label for="sort_order">الترتيب</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
          </div>

          <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
            <label class="form-check-label font-weight-bold" for="is_active">قسم مفعل</label>
          </div>

          <div class="form-group text-left mb-0">
            <button type="submit" class="btn btn-primary px-4 font-weight-bold">حفظ القسم</button>
            <a href="{{ route('categories.index') }}" class="btn btn-light px-3">إلغاء</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
