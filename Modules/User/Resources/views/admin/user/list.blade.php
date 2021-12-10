@extends('admin.layouts.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Users</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item active">Users</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">User List</h3>
        <div style="float:right;">
        
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
          Filter <span class="caret"></span>
        </a>
          <div class="dropdown-menu">
            <div class="form-group">
              <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
            </div>
            
          </div>
        </div>
      </div>

      <!-- /.card-header -->
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect">  All </th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
              @foreach($users as $user)  
              <tr role="row">
                  <td ><input type="checkbox" name="" class="singleSelect" data-id="{{$user->user_id}}"></td>
                  <td>                  
                    <img style="width:10%;" src="http://localhost/alysei/public/images/user-unnamed.png" class="img-circle elevation-2" alt="User Image">{{($user->name) ? ($user->name) : ($user->first_name.' '.$user->last_name)}}Rahul
                  </td>
                  <td>{{$user->email}}
                    @if($user->alysei_review == '0')
                      <span class="badge bg-danger"> {{'Panding Review'}}</span>
                    @endif
                  </td>
                  <td>@if($user->role_id == 3) Italian F&B Producers @elseif($user->role_id == 7) Voice Of Expert @elseif($user->role_id == 8) Travel Agencies @elseif($user->role_id == 9) Restaurants @elseif($user->role_id == 10) Voyagers @elseif($user->role_id == 6) Importer & Distributer @endif</td>
                  
                  <td>
                    {{date('F j, Y', strtotime($user->created_at))}}
                  </td>

                  <td>
                      <a class="fa fa-edit" href="{{url('dashboard/users/edit', [$user->user_id])}}" title="Edit"></a> | 
                      <a class="fa fa-trash" onclick="deleteUser({{$user->user_id}})" title="Delete"></a>
                      
                  </td>
              </tr>
              @endforeach
          </tbody>
        </table>
        <div class="dataTables_info" id="file_export_info" role="status" aria-live="polite">Showing {{$users->firstItem()}} to {{$users->lastItem()}} of {{$users->total()}} entries</div>
                    
                        
      </div>
      <!-- /.card-body -->
      <div class="card-footer clearfix">
        {{$users->links()}}
      </div>
    </div>
  </div>
</section>

<script type="text/javascript">
  function deleteUser(id)
    {
        
        if (confirm("Are you sure you want to delete?") == true) {
        $.ajax({
        url:"{{url('dashboard/users/delete')}}",
        type:'post',
        data:{'id':id,'_token':'{{ csrf_token() }}'},
        success: function(path){
        location.reload();
        }
        });
        } else {
        return false;
        }
      }
</script>
                
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
                url:"{{url('dashboard/user-status')}}",
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