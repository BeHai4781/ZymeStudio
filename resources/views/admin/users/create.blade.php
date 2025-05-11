@extends('index')

@section('content')
<div class="col-md-12">
    <div class="container">
        <h3></h3>
        <div class="col-md-12">
            <form action="{{ url('/users/store')}}" method="POST" enctype="mutipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group">
                    <label for="name">名前<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="熊田一聡（一般社員)">
                </div>
                <div class="form-group">
                    <label for="email">メール <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="example@gmail.com">
                </div>
                <div>
                    <label for="password">パスワード<span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="****">
                </div>
                <button class="btn btn-success">保存</button>
            </form>
        </div>
    </div>
</div>
@endsection