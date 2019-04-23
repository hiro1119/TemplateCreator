@extends('layouts.app')
@include('layouts.side')

@include('layouts.header')

@push('css')
@endpush


@section('content')
<div class="row">
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>{{ $data['name'] }}</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table class="table-bottom-border">
          <tbody>
            <tr>
              <th>プロジェクトパス</th>
              <td>{{$data['path']}}</td>
            </tr>
            <tr>
              <th>テンプレート</th>
              <td>
                @foreach($data['temps']['temp-name'] as $d)
                <p>{{ $d }}</p>
                @endforeach
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>サイト情報(site_info.xlsx)</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table class="table table-bordered">
        <thead>
            <tr>
            @foreach($headers as $h)
            <th>{{ $h }}</th>
            @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($site_infos as $key => $row)
            <tr>
            <th scope="row">{{$key+1}}</th>
            <td>{{$row['url']}}</td>
            <td>{{$row['path']}}</td>
        @endforeach
        </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection


@include('layouts.footer')
@push('scripts')
@endpush
