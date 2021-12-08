@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Add Recipe Ingredients</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe/ingredients')}}">Ingredients</a></li>
          <li class="breadcrumb-item active">Add</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
@if (\Session::has('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <ul>
            <li>{!! \Session::get('success') !!}</li>
        </ul>
    </div>
@endif

@if (\Session::has('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <ul>
            <li>{!! \Session::get('error') !!}</li>
        </ul>
    </div>
@endif

<section class="content">
  <div class="container-fluid">
      <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Add Ingredients</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/recipe/ingredient/store')}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" min="3" max="50" required>
                  </div>
                  <div class="form-group">
                    <label for="IngredientsImage">Ingredients Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" required >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
                  </div>

                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="Featured" checked="" name="featured">
                    <label class="form-check-label" for="Featured" >Featured</label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="parent" checked="" name="parent">
                    <label class="form-check-label" for="parent" >Parent</label>
                  </div>

                  <div class="form-group ingredient_list" style="display: none">
                    <label>Select Parent</label>
                    <select class="form-control" name="parent_id">
                      @foreach($ingredients as $ingredient)
                        <option value="{{ $ingredient->recipe_ingredient_id }}">{{ $ingredient->title }}</option>
                      @endforeach
                    </select>
                  </div>  
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
      </div>
  </div>
</section>
@endsection            

