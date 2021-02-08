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
                    <li class="breadcrumb-item"><a href="{{url('login/users')}}">Users</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
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
                                <form action="#">
                                    <div class="form-body">
                                        <h5 class="card-title">Edit User</h5>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">First Name</label>
                                                    <input type="text" id="firstName" class="form-control" placeholder="Rounded Chair"> </div>
                                            </div>
                                            <!--/span-->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Last name</label>
                                                    <input type="text" id="lastName" class="form-control" placeholder="globe type chair for rest"> </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label"></label>
                                                    <input type="text" id="lastName" class="form-control" placeholder="globe type chair for rest"> </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Last name</label>
                                                    <input type="text" id="lastName" class="form-control" placeholder="globe type chair for rest"> </div>
                                            </div>
                                            <!--/span-->
                                        </div>
                                        <!--/row-->
                                        <!--/row-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Category</label>
                                                    <select class="form-control" data-placeholder="Choose a Category" tabindex="1">
                                                        <option value="Category 1">Category 1</option>
                                                        <option value="Category 2">Category 2</option>
                                                        <option value="Category 3">Category 5</option>
                                                        <option value="Category 4">Category 4</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <!--/span-->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status</label>
                                                    <br>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="customRadioInline1" name="customRadioInline1" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadioInline1">Publish</label>
                                                    </div>
                                                    <div class="custom-control custom-radio custom-control-inline">
                                                        <input type="radio" id="customRadioInline2" name="customRadioInline1" class="custom-control-input">
                                                        <label class="custom-control-label" for="customRadioInline2">Draft</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--/span-->
                                        </div>
                                        <!--/row-->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Price</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon1"><i class="ti-money"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" placeholder="price" aria-label="price" aria-describedby="basic-addon1">
                                                    </div>
                                                </div>
                                            </div>
                                            <!--/span-->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Discount</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text" id="basic-addon2"><i class="ti-cut"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" placeholder="Discount" aria-label="Discount" aria-describedby="basic-addon2">
                                                    </div>
                                                </div>
                                            </div>
                                            <!--/span-->
                                        </div>
                                        <h5 class="card-title mt-5">Product Description</h5>
                                        <div class="row">
                                            <div class="col-md-12 ">
                                                <div class="form-group">
                                                    <textarea class="form-control" rows="4">Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. but the majority have suffered alteration in some form, by injected humour</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <!--/row-->
                                        
                                        
                                         </div>
                                    <div class="form-actions mt-5">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Save</button>
                                        <button type="button" class="btn btn-dark">Cancel</button>
                                    </div>
                                </form>
                            </div>
        </div>
    </div>
</div>
</div>
                
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