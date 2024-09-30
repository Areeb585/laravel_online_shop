@extends('admin.layouts.app')

@section('content')
	<!-- Content Header (Page header) -->
				<section class="content-header">					
					<div class="container-fluid my-2">
						<div class="row mb-2">
							<div class="col-sm-6">
								<h1>Create Sub Category</h1>
							</div>
							<div class="col-sm-6 text-right">
								<a href="{{route('admin.sub-category.list')}}" class="btn btn-primary">Back</a>
							</div>
						</div>
					</div>
					<!-- /.container-fluid -->
				</section>
				<!-- Main content -->
				<section class="content">
					<!-- Default box -->
					<div class="container-fluid">
                        <form action="{{ route('admin.category.store')}}" method="post" name="subCategoryForm" id="subCategoryForm">
                            @csrf
						<div class="card">
							<div class="card-body">								
								<div class="row">
                                    <div class="col-md-12">
										<div class="mb-3">
											<label for="name">Category</label>
											<select name="category" id="category" class="form-control">
                                            <option value="">Select a category</option>   
                                            @if($categories->isNotEmpty())
                                                @foreach($categories as $category)
                                                <option value="{{ $category->id}}">{{ $category->name}}</option>
                                               @endforeach
                                               @endif
                                                <!-- <option value="">Mobile</option> -->
                                            </select>
                                            <p></p>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="name">Name</label>
											<input type="text" name="name" id="name" class="form-control" placeholder="Name">
                                            <p></p>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="email">Slug</label>
											<input type="text" readonly name="slug" id="slug" class="form-control" placeholder="Slug">
                                            <p></p>	
										</div>
									</div>	
                                    <div class="col-md-6">
										<div class="mb-3">
											<label for="status">Status</label>
											<select name="status" id="status" class="form-control" placeholder="status">
                                            <option value="1">Active</option>	
                                            <option value="0">Block</option>
										</select>
                                        <p></p>
                                        </div>
									</div>
                                     <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status">Show on Home</label>
                                    <select name="showHome" id="showHome" class="form-control">
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                            </div>								
								</div>
							</div>							
						</div>
						<div class="pb-5 pt-3">
							<button type="submit" class="btn btn-primary">Create</button>
							<a href="{{('admin.sub-category.list')}}" class="btn btn-outline-dark ml-3">Cancel</a>
						</div>
                        </form>
					</div>
					<!-- /.card -->
				</section>
				<!-- /.content -->
@endsection
   @section('customJs')
   <script>
   $('#subCategoryForm').submit(function(event){
    event.preventDefault();
    let element = $(this);
    $("button[type=submit]").prop('disabled', true);
    $.ajax({
        url: "{{ route('admin.sub-category.store') }}",
        type: 'post',
        data: element.serializeArray(),
        dataType: 'json',
        success: function(response){
            $("button[type=submit]").prop('disabled', false);
            if(response["status"] == true){
                // Redirect to the list page after successful submission
                window.location.href = response['redirect_url'];
            } else {
                var errors = response['errors'];
                
                if(errors['name']){
                    $("#name").addClass('is-invalid').siblings('p')
                    .addClass('invalid-feedback').html(errors['name']);
                } else {
                    $("#name").removeClass('is-invalid').siblings('p')
                    .removeClass('invalid-feedback').html("");
                }

                if(errors['slug']){
                    $("#slug").addClass('is-invalid').siblings('p')
                    .addClass('invalid-feedback').html(errors['slug']);
                } else {
                    $("#slug").removeClass('is-invalid').siblings('p')
                    .removeClass('invalid-feedback').html("");
                }

                if(errors['category']){
                    $("#category").addClass('is-invalid').siblings('p')
                    .addClass('invalid-feedback').html(errors['category']);
                } else {
                    $("#category").removeClass('is-invalid').siblings('p')
                    .removeClass('invalid-feedback').html("");
                }
            }
        },
        error: function(jqXHR, exception){
            console.log('Something went wrong');
        }
    });
});


    $('#name').change(function(){
    let element = $(this);
    $("button[type=submit]").prop('disabled', true);
    $.ajax({
        url: "{{ route('admin.category.getSlug') }}",
        type: 'get',
        data: {title: element.val()},
        dataType: 'json',
        success: function(response){
            $("button[type=submit]").prop('disabled', false);
            if(response["status"] == true){
                $('#slug').val(response["slug"]);
            }
        }
    });
});
   </script>
   @endsection              


