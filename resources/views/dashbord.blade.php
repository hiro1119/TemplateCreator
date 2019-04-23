@extends('layouts.app')
@include('layouts.side')

@include('layouts.header')

@push('css')
@endpush


@section('content')
<div class="row row-same-height">
    <div class="col-md-7 col-sm-7 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>プロジェクト一覧</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                        <table class="table">
                        <thead>
                            <tr>
                            <th>#</th>
                            <th>Project Name</th>
                            <th>Project Path</th>
                            <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($sites['info'] as $key => $row)
                            <tr>
                            <th scope="row">{{$key+1}}</th>
                            <td>{{$row['name']}}</td>
                            <td>{{$row['path']}}</td>
                            <td>
                            <a class="btn btn-xs btn-info" href="{{route('project.info', $row['id'])}}">開く</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                        </table>

            </div>
        </div>
    </div>

    <div class="col-md-5 col-sm-5 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>プロジェクト作成</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <br />
                <form action="{{route('project.create')}}" enctype="multipart/form-data" method="post" data-parsley-validate class="form-horizontal form-label-left">
                    @csrf
                    @method('POST')

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">プロジェクト名 <span class="required">*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input type="text" id="name" name="project[name]" required="required" class="form-control col-md-7 col-xs-12">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="path">フォルダパス <span class="required">*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input type="text" id="path" name="project[path]" placeholder="例）sample" required="required" class="form-control col-md-7 col-xs-12">
                            <small>※ フォルダ名のみ記載してください。</small>
                        </div>
                    </div>
                    <div id="temp#" class="form-group group-temp">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 input-label-temp" for="template1">テンプレート </label>
                        <div class="col-md-9 col-sm-9 col-xs-12 input-form-temp">
                            <input id="temp1" class="temp-file" type="file" name="templates[]" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-info btn-xs pull-right" id="file-up" type="button">テンプレート追加</button>
                        </div>
                    </div>
                    <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <button class="btn btn-primary" type="reset">クリア</button>
                                <button type="submit" class="btn btn-success">プロジェクト作成</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@include('layouts.footer')
@push('scripts')
<script>
  $(function(){
    var $cloneElem2 = $('#temp1').clone();
    var cnt = 1;
    $('#file-up').click(function(){
      cnt++;
      $cloneElem2.attr('id','temp'+cnt);
      $cloneElem2.css('margin-bottom', '10px');
      $('#temp'+(cnt-1)).before($cloneElem2.clone());
    })
  })
</script>
@endpush
