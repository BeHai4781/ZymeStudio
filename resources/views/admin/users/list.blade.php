@extends('index')

@section('content')
    <div class="col-md-12">
        <div class="container">
            <h3>ユーザ追加</h3>
            <a class="btn btn-success f-right" href="{{ 'users/create' }}">ユーザ追加</a>

            <div class="col-md-12">
                <table class="table table-bordered table-hover">
                    <thead>
                        <th>社員番号</th>
                        <th>名前</th>
                        <th>メール</th>
                        <th>完了</th>
                        <th>操作</th>

                    </thead>
                    <tbody>
                        @if (count($users) > 0)
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td> {{ $user->file_completed }} / {{ count($user->files) }}</td>
                                    <td>
                                        <a class="btn btn-primary" href="{{ url('users/show/' .$user->id) }}">状態</>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <div class="paginate f-right ">
                    {!! $users->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection