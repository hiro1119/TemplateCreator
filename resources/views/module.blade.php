@extends('layouts.app')
@include('layouts.side')

@include('layouts.header')

@push('css')
@endpush


@section('content')
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>モジュールアップロード</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form action="{{route('project.moduleup', $id)}}" method="post" data-parsley-validate class="form-horizontal form-label-left">
            @csrf
            @method('POST')

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title" id="myModalLabel">Module Up</h4>
        </div>
        <div class="modal-body">
            <div id="temp1" class="module-box">
                <label for="fullname">Module ID * :</label>
                <input type="text" id="id" class="form-control" name="modules[id][]" />

                <label for="module">Module :</label>
                <textarea id="module" rows="10" class="form-control" name="modules[module][]" placeholder="モジュールのタグを貼り付けてください。"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button id="add-module" type="button" class="btn btn-dark">Add Module</button>
            <button type="submit" class="btn btn-primary">Up Module</button>
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
    $('#add-module').click(function(){
      cnt++;
      $cloneElem2.attr('id','temp'+cnt);
      $cloneElem2.css('margin-top', '10px');
      $cloneElem2.css('margin-bottom', '10px');
      $('#temp'+(cnt-1)).after($cloneElem2.clone());
    })
  })
</script>
@endpush
