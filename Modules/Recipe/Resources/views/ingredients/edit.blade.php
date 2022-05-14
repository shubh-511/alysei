@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Recipe Ingredients</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe/ingredients')}}">Ingredients</a></li>
          <li class="breadcrumb-item active">Edit</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
  <div class="container-fluid">
      <div class="col-md-12">
            @if (\Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {!! \Session::get('success') !!}
                </div>
            @endif

            @if (\Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {!! \Session::get('error') !!}
                </div>
            @endif
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Edit Ingredients</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/recipe/ingredient/update',['id'=>$id])}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" min="3" max="50" value="{{ $ingredient->title }}" required>
                  </div>
                  <div class="form-group">
                    <label for="IngredientsImage">Ingredients Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
                    <div>
                      <img src="{{ $ingredient->attachment->base_url }}{{ $ingredient->attachment->attachment_url }}" width="75px">
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="name">Priority</label>
                    <input type="number" class="form-control" id="priority" placeholder="Enter Priority" name="priority" value="{{ $ingredient->priority }}" required>
                  </div>

                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="Featured"  name="featured" {{ ($ingredient->featured == '1') ? "checked" : "" }}>
                    <label class="form-check-label" for="Featured" >Featured</label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="parent" name="parent" {{ ($ingredient->parent == 0) ? "checked" : "" }}>
                    <label class="form-check-label" for="parent">Parent</label>
                  </div>

                  <div class="form-group ingredient_list" {{ ($ingredient->parent == 0) ? $displayNone : "" }}>
                    <label>Select Parent</label>
                    <select class="form-control" name="parent_id">
                      @foreach($parentIngredients as $value)
                        <option value="{{ $value->recipe_ingredient_id }}" {{ ($ingredient->parent == $value->recipe_ingredient_id) ? 'selected': '' }}>
                          {{ $value->title }}
                        </option>
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

  