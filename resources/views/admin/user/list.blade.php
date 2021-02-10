@extends('admin.layouts.app')

@section('content')



<div class="page-breadcrumb border-bottom">
    <div class="row">
        <div class="col-lg-3 col-md-4 col-xs-12 justify-content-start d-flex align-items-center">
            <h5 class="font-medium text-uppercase mb-0"></h5>
        </div>
        <div class="col-lg-9 col-md-8 col-xs-12 d-flex justify-content-start justify-content-md-end align-self-center">
            <nav aria-label="breadcrumb" class="mt-2">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{url('login/dashboard')}}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Users</li>
                </ol>
            </nav>
            <!-- <button class="btn btn-danger text-white ml-3 d-none d-md-block"></button> -->
        </div>
    </div>
</div>

<div class="page-content container-fluid">
<div class="row">
    <div class="col-12">
        <div class="material-card card">
            <div class="card-body">
                <div class="left">
                    <h4 class="card-title" style="margin-left: 15px;">Alysei Users</h4>
                    <h6 class="card-subtitle"> </h6>
                </div>
                <div class="right" >
                    <select class="form-control" id= "action" onchange="return Action();">
                        <option value="">Action</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <div id="file_export_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4">
                    <table id="file_export" class="table table-striped border display dataTable" role="grid" aria-describedby="file_export_info">
                        <thead>

                        <tr role="row">
                            <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect">  All </th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Name: activate to sort column descending" style="width: 0px;">Name</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Email: activate to sort column descending" style="width: 0px;">Email</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Role: activate to sort column descending" style="width: 0px;">Role</th>
                            <!-- <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Status</th> -->

                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Alysei Review</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Alysei Certification</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Alysei Recognition</th>
                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Status: activate to sort column descending" style="width: 0px;">Alysei Quality Mark</th>

                            <th tabindex="0" aria-controls="zero_config" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Action: activate to sort column descending" style="width: 0px;">Action</th>
                        </tr>

                        </thead>
                        <tbody>
                            @foreach($users as $user)  
                            <tr role="row">
                                <td ><input type="checkbox" name="" class="singleSelect" data-id="{{$user->user_id}}"></td>
                                <td>{{($user->name) ? ($user->name) : ($user->first_name.' '.$user->last_name)}}</td>
                                <td>{{$user->email}}</td>
                                <td>@if($user->role_id == 3) Italian F&B Producers @elseif($user->role_id == 7) Voice Of Expert @elseif($user->role_id == 8) Travel Agencies @elseif($user->role_id == 9) Restaurants @elseif($user->role_id == 10) Voyagers @elseif($user->role_id == 6) Importer & Distributer @endif</td>
                                <!--<td>
                                    <select style="width:65%;" class="userstatus form-control"  data-status_id="{{$user->user_id}}">
                                      <option value="active" @if($user->account_enabled == 'active' )selected @endif">Active</option>
                                      <option value="inactive" @if($user->account_enabled == 'inactive' )selected @endif">Inactive</option>
                                      <option value="expired" @if($user->account_enabled == 'expired' )selected @endif">Expired</option>
                                      <option value="incomplete" @if($user->account_enabled == 'incomplete' )selected @endif">Incomplete</option>
                                    </select>
                                </td>-->

                                <td>
                                  @if($user->alysei_review == '1')
                                    <span class="badge bg-success" onclick="isReview({{$user->user_id}},'0')">Enabled</span>
                                  @else
                                    <span class="badge bg-danger" onclick="isReview({{$user->user_id}},'1')">Disabled</span>
                                  @endif
                                </td>

                                <td>
                                  @if($user->alysei_certification == '1')
                                    <span class="badge bg-success" onclick="isCertified({{$user->user_id}},'0')">Enabled</span>
                                  @else
                                    <span class="badge bg-danger" onclick="isCertified({{$user->user_id}},'1')">Disabled</span>
                                  @endif
                                </td>

                                <td>
                                  @if($user->alysei_recognition == '1')
                                    <span class="badge bg-success" onclick="isRecognised({{$user->user_id}},'0')">Enabled</span>
                                  @else
                                    <span class="badge bg-danger" onclick="isRecognised({{$user->user_id}},'1')">Disabled</span>
                                  @endif
                                </td>

                                <td>
                                  @if($user->alysei_qualitymark == '1')
                                    <span class="badge bg-success" onclick="isQM({{$user->user_id}},'0')">Enabled</span>
                                  @else
                                    <span class="badge bg-danger" onclick="isQM({{$user->user_id}},'1')">Disabled</span>
                                  @endif
                                </td>

                                <td>
                                    <a href="{{url('login/users/edit', [$user->user_id])}}" class="pr-2" data-toggle="tooltip" title="" data-original-title="Edit"><i class="ti-marker-alt"></i></a>

                                    <!-- <a href="" title=""data-toggle="tooltip" data-original-title="Delete"><i class="ti-trash"></i></a> -->
                                </td>
                            </tr>
                            @endforeach
                            
                        </tbody>
                       
                    </table>
                    <div class="dataTables_info" id="file_export_info" role="status" aria-live="polite">Showing {{$users->firstItem()}} to {{$users->lastItem()}} of {{$users->total()}} entries</div>
                    
                        {{$users->links()}}
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
                
@endsection            

@push('footer_script')
<script>

function isReview(id,status){
  if (confirm("Are you sure you want to change the status?") == true) {
   $.ajax({
          url:"{{url('/login/review-status')}}",
          type:'post',
          data:{'id':id,'status':status,'_token':'{{ csrf_token() }}'},
          success: function(path){
            location.reload();
          }
      });
  }  
 }

function isCertified(id,status){
  if (confirm("Are you sure you want to change the status?") == true) {
   $.ajax({
          url:"{{url('/login/certified-status')}}",
          type:'post',
          data:{'id':id,'status':status,'_token':'{{ csrf_token() }}'},
          success: function(path){
            location.reload();
          }
      });
  }
}

function isRecognised(id,status){
  if (confirm("Are you sure you want to change the status?") == true) {
   $.ajax({
          url:"{{url('/login/recognised-status')}}",
          type:'post',
          data:{'id':id,'status':status,'_token':'{{ csrf_token() }}'},
          success: function(path){
            location.reload();
          }
      });
  }
}

function isQM(id,status){
  if (confirm("Are you sure you want to change the status?") == true) {
   $.ajax({
          url:"{{url('/login/qm-status')}}",
          type:'post',
          data:{'id':id,'status':status,'_token':'{{ csrf_token() }}'},
          success: function(path){
            location.reload();
          }
      });
  }
}

$(document).ready(function(){
   $('.userstatus').change(function(){
     let status =$(this).val();
     let id =$(this).data("status_id");
       handleStatus(id,status);   
   });
});
var dataId = [];
 function handleStatus(id,status){
     if(id != ''){
        dataId=[id];
     }
     
     if (confirm("Are you sure you want to change the status?") == true) {
     $.ajax({
                url:"{{url('login/user-status')}}",
                type:'post',
                data:{'id':dataId,'status':status,'_token':'{{ csrf_token() }}'},
                success: function(path){
                  location.reload();
                }
            });
    }
    else
    {
        return false;
    }
}

function selectAll(){
   if($('.allSelect').is(':checked')){
            $('.singleSelect').prop('checked',true);
          }else{
            $('.singleSelect').prop('checked', false);
          }
}
function Action(e){
  var inputValue = $('#action').val();

  var arrayValue =['active','inactive','expired','incomplete'];
  var status='';
   if(inputValue == ''){
     return false;
   }else if(arrayValue.includes(inputValue)){

    var inputs = $(".singleSelect");
      for(var i = 0; i < inputs.length; i++){
          if($(inputs[i]).is(":checked")){
            dataId.push($(inputs[i]).data('id')); 
         }
      }
      if(dataId.length < 1){
        alert('Select Min 1 Row')
      }
      // else if(inputValue == 'Delete') {
      //     isDeleted();
      // }
      
      else if(inputValue == 'active'){
         status = 'active';
      }
      else if(inputValue == 'inactive'){
         status = 'inactive';
      }
      else if(inputValue == 'expired'){
         status = 'expired';
      }
      else if(inputValue == 'incomplete'){
         status = 'incomplete';
      }
      handleStatus('',status)
   }
}
</script>
@endpush