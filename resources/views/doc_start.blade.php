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
        <div class="nav navbar-right">
            <a href="{{route('project.module', $id)}}" class="btn btn-xs btn-success">Module Up</a>
            <a href="{{route('project.output', $id)}}" class="btn btn-xs btn-info">Out Put</a>
        </div>
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
  <div class="col-md-8 col-sm-8 col-xs-12">
    <div class="x_panel">
      <form action="{{route('project.getModules', $id)}}" method="POST" class="form-horizontal">
        @csrf
        @method('POST')

      <div class="x_title">
        <h2>モジュール読み込み</h2>
        <div class="nav navbar-right">
            <button type="submit" class="btn btn-xs btn-success">Module Load</button>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="col-md-6 col-sm-6 col-xs-12">
              <div class="form-group">
                <label class="control-label" for="name">モジュールURL <span class="required">*</span></label>
              </div>
              <div class="form-group">
                  <textarea name="module-url" required="required" style="width: 100%;" rows="10"></textarea>
              </div>
        </div>
      </div>
      </form>
    </div>
  </div>
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>サイト情報(site_info.xlsx)</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content" style="overflow: auto;">
        <table class="table table-bordered site-info-table jambo_table">
        @if(count($headers) > 0)
        <thead>
            <tr>
            @foreach($headers as $h)
            @if($h === 'mods')
                @foreach($h as $m)
                    <th>{{ $m }}</th>
                @endforeach
            @else
                @if($h !== 'mod_cnt')
                    <th>{{ $h }}</th>
                @endif
            @endif
            @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($site_infos as $key => $row)
            <tr>
            <th scope="row">{{$row['no']}}</th>
            <td>{{$row['out']}}</td>
            <td>{{$row['phase']}}</td>
            <td>{{$row['path']}}</td>
            <td>{{$row['content']}}</td>
            <td>{{$row['id']}}</td>
            <td>{{$row['title']}}</td>
            <td>{{$row['title_breadcrumb']}}</td>
            <td>{{$row['title_h1']}}</td>
            <td>{{$row['title_label']}}</td>
            <td>{{$row['title_full']}}</td>
            <td>{{$row['logical_path']}}</td>
            <td>{{$row['list_flg']}}</td>
            <td>{{$row['layout']}}</td>
            <td>{{$row['orderby']}}</td>
            <td>{{$row['keywords']}}</td>
            <td>{{$row['description']}}</td>
            <td>{{$row['category_top_flg']}}</td>
            <td>{{$row['delete_flg']}}</td>
            <td>{{$row['ogtitle']}}</td>
            <td>{{$row['ogdescription']}}</td>
            <td>{{$row['ogimage']}}</td>
            <td>{{$row['ogtype']}}</td>
            <td>{{$row['ogsite_name']}}</td>
            <td>{{$row['ogurl']}}</td>
            <td>{{$row['oglocale']}}</td>
            <td>{{$row['fbapp_id']}}</td>
            <td>{{$row['apple_touch_icon']}}</td>
            <td>{{$row['copyright']}}</td>
            <td>{{$row['viewport']}}</td>
            <td>{{$row['sitecatalyst']}}</td>
            <td>{{$row['favicon']}}</td>
            <td>{{$row['breadcrumb_text_1']}}</td>
            <td>{{$row['breadcrumb_href_1']}}</td>
            <td>{{$row['breadcrumb_text_2']}}</td>
            <td>{{$row['breadcrumb_href_2']}}</td>
            <td>{{$row['breadcrumb_text_3']}}</td>
            <td>{{$row['breadcrumb_href_3']}}</td>
            <td>{{$row['breadcrumb_text_4']}}</td>
            <td>{{$row['breadcrumb_href_4']}}</td>
            <td>{{$row['breadcrumb_text_last']}}</td>
            <td>{{$row['breadcrumb_href_last']}}</td>
            @for($i = 1;$i <= $row['mod_cnt'];$i++)
                @if(array_key_exists('mod'.$i, $row))
                    <td>{{$row['mod'.$i]}}</td>
                @else
                    <td></td>
                @endif
            @endfor
            </tr>
        @endforeach
        </tbody>
        </table>
        @else
        <p>サイト情報が設定されていません。</p>
        @endif
      </div>
    </div>
  </div>
</div>


@endsection


@include('layouts.footer')
@push('scripts')
@endpush
