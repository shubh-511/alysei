@extends('admin.layouts.app')
<style>
.container {
      width: 600px;
      margin: 100px auto; 
  }
  .progressbar {
      counter-reset: step;
  }
  .progressbar li {
      list-style-type: none;
      width: 25%;
      float: left;
      font-size: 12px;
      position: relative;
      text-align: center;
      text-transform: uppercase;
      color: #7d7d7d;
  }
  .progressbar li:before {
      width: 30px;
      height: 30px;
      content: counter(step);
      counter-increment: step;
      line-height: 30px;
      border: 2px solid #7d7d7d;
      display: block;
      text-align: center;
      margin: 0 auto 10px auto;
      border-radius: 50%;
      background-color: white;
  }
  .progressbar li:after {
      width: 100%;
      height: 2px;
      content: '';
      position: absolute;
      background-color: #7d7d7d;
      top: 15px;
      left: -50%;
      z-index: -1;
  }
  .progressbar li:first-child:after {
      content: none;
  }
  .progressbar li.active {
      color: green;
  }
  .progressbar li.active:before {
      border-color: #55b776;
  }
  .progressbar li.active + li:after {
      background-color: #55b776;
  }
 
</style>
@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Users</h1>
        <span>User-#{{$user->user_id}}</span>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('login/dashboard')}}">Home</a></li>
          <li class="breadcrumb-item active">Users</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex p-0">
                <h3 class="card-title p-3">Manage user</h3>
                <ul class="nav nav-pills ml-auto p-2">
                  <li class="nav-item"><a class="nav-link active" href="#tab_1" data-toggle="tab">Account</a></li>
                  <li class="nav-item"><a class="nav-link" href="#tab_2" data-toggle="tab">Membership State</a></li>
                  <li class="nav-item"><a class="nav-link" href="#tab_3" data-toggle="tab">Profile</a></li>
                  <li class="nav-item"><a class="nav-link" href="#tab_4" data-toggle="tab">Privacy</a></li>
                  
                </ul>
            </div>

            <div class="card-body">

                <div class="tab-content">

                  <div class="tab-pane active" id="tab_1">
                    A wonderful serenity has taken possession of my entire soul,
                    like these sweet mornings of spring which I enjoy with my whole heart.
                    I am alone, and feel the charm of existence in this spot,
                    which was created for the bliss of souls like mine. I am so happy,
                    my dear friend, so absorbed in the exquisite sense of mere tranquil existence,
                    that I neglect my talents. I should be incapable of drawing a single stroke
                    at the present moment; and yet I feel that I never was a greater artist than now.
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_2" style="margin-top: 70px;margin-bottom: 50px;">
                    <div class="manage" style="float: right;"> 
                      <!-- <a href="javascript:void(0)" data-toggle="modal" data-target="#myModal"><i class="fa fa-desktop" title="View Details"></i></a> -->
                      <button class="manage form-control" data-toggle="modal" data-target="#myModal">Manage</button>
                    </div>
                     <ul class="progress-indicator stepped "><!-- stacked -->
                      <li @if($user->alysei_review == 1) class="completed" @else class="" @endif>
                          
                              <span class="bubble"></span>
                              <span class="stacked-text">
                                  <span class="fa fa-eye"></span> Review
                                  <!-- <span class="subdued">/ Added a thing. <em>Pssst... I'm a link!</em></span> -->
                              </span>
                          
                      </li>
                      <li @if($user->alysei_certification == 1) class="completed" @else class="" @endif>
                          <span class="bubble"></span>
                          <span class="stacked-text">
                              <span class="fa fa-certificate"></span> Alysei Certification

                             <!--  <span class="subdued">/ Some stuff happened. It was amazing.</span> -->
                          </span>
                      </li>
                      <li @if($user->alysei_recognition == 1) class="completed" @else class="" @endif>
                          <span class="bubble"></span>
                          <span class="stacked-text">
                              <span class="fa fa-id-card"></span> Recognition
                              <!-- <span class="subdued">/ What a wild day!</span> -->
                          </span>
                      </li>
                      <li @if($user->alysei_qualitymark == 1) class="completed" @else class="" @endif>
                          <span class="bubble"></span>
                          <span class="stacked-text">
                              <span class="fa fa-check"></span> Quality Mark
                              <!-- <span class="subdued">/ This day is toooo long.</span> -->
                          </span>
                      </li>
                      
                    </ul>



                    <div id="myModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <h3 class="modal-title" style="font-weight: 400;">Membership State</h3>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <form action="{{url('dashboard/update-progress',[$user->user_id])}}" method="post">
                          @csrf
                        <div class="modal-body">
                        <?php  
                          if($user->alysei_review == '0') 
                          {
                              $level = 'Alysei Review'; 
                              $setLevel = 'alysei_review';
                          }
                          elseif($user->alysei_certification == '0')
                          {
                              $level = 'Alysei Certification'; 
                              $setLevel = 'alysei_certification';
                          }
                          elseif($user->alysei_recognition == '0')
                          {
                              $level = 'Alysei Recognition'; 
                              $setLevel = 'alysei_recognition';
                          }
                          elseif($user->alysei_qualitymark == '0')
                          {
                              $level = 'Alysei Quality Mark'; 
                              $setLevel = 'alysei_qualitymark';
                          }
                          else
                          {
                              $level = ''; 
                              $setLevel = 'level_empty';
                          }
                        ?>  
                        <input type="hidden" value="{{$setLevel}}" name="progress_level">

                      <div class="alert alert-warning" role="alert">Setting user to <strong>{{$level}}</strong>.</div>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <textarea placeholder="Notification message to user." class="form-control"></textarea>
                            
                          </div>
                        </div>
                      </div>
                      <button class="btn btn-default" type="submit"> Submit</button>



                      </div>
                    </form>
                      <div class="modal-footer">
                      
                      </div>
                    </div>

                    </div>
                  </div>

                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="tab_3">
                    Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                    Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                    when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                    It has survived not only five centuries, but also the leap into electronic typesetting,
                    remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset
                    sheets containing Lorem Ipsum passages, and more recently with desktop publishing software
                    like Aldus PageMaker including versions of Lorem Ipsum.
                  </div>

                  <div class="tab-pane" id="tab_4">
                    Lorem Ipsum is simply dummy text of the printing and typesetting industry.
                    Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,
                    when an unknown printer took a galley of type and scrambled it to make a type specimen book.
                    It has survived not only five centuries, but also the leap into electronic typesetting,
                    remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset
                    sheets containing Lorem Ipsum passages, and more recently with desktop publishing software
                    like Aldus PageMaker including versions of Lorem Ipsum.
                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>

            
        </div>
    </div>
</section>

     
@endsection            

@push('footer_script')
<script>
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
</script>
@endpush