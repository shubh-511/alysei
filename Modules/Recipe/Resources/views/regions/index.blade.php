@extends('admin.layouts.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Recipe Regions</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
          <li class="breadcrumb-item active">Regions</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3>Melas</h3>
        <div style="float:right;">
            <a class="nav-link" href="{{url('dashboard/recipe/region/add')}}">
              Add New
            </a>
        </div>


      <!-- /.card-header -->
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect">  All </th>
              <th>Name</th>
              <th>Image</th>
              <th>Featured</th>
              <th>Priority</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
              @foreach($regions as $region)  
              <tr role="row">
                  <td ><input type="checkbox" name="" class="singleSelect" data-id="{{$region->recipe_region_id}}"></td>
                  <td>                  
                    {{ $region->name }}
                  </td>
                  <td>                  
                        <img src="{{ $region->attachment->base_url }}{{ $region->attachment->attachment_url }}" width="50px">
                  </td>
                  <td>
                    {{ $region->featured == 1 ? 'yes' : 'No' }}
                  </td>
                  <td>
                    {{ $region->priority }}
                  </td>

                  <td>
                      <a class="fa fa-edit" href="{{url('dashboard/recipe/region/edit', [$region->recipe_region_id])}}" title="Edit"></a> | 
                      <a class="fa fa-trash" title="Delete"></a>
                      
                  </td>
              </tr>
              @endforeach
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
      <div class="card-footer clearfix">
        {{$regions->links()}}
      </div>
    </div>
  </div>
</section>
                
@endsection            

@push('footer_script')
<script>
</script>
@endpush