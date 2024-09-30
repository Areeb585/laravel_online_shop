@extends('admin.layouts.app')

@section('content')
	<!-- Content Header (Page header) -->
				<section class="content-header">					
					<div class="container-fluid my-2">
						<div class="row mb-2">
							<div class="col-sm-6">
								<h1>Edit Category</h1>
							</div>
							<div class="col-sm-6 text-right">
								<a href="{{route('admin.category.list')}}" class="btn btn-primary">Back</a>
							</div>
						</div>
					</div>
					<!-- /.container-fluid -->
				</section>
				<!-- Main content -->
				<section class="content">
					<!-- Default box -->
					<div class="container-fluid">
                        <form action="{{ route('admin.category.store')}}" method="post" id="categoryForm" name="categoryForm">
							@csrf   
						<div class="card">
							<div class="card-body">								
								<div class="row">
									<div class="col-md-6">
										<div class="mb-3">
											<label for="name">Name</label>
											<input type="text" name="name" id="name" 
                                            class="form-control" placeholder="Name" value="{{ $category->name}}">	
                                            <p></p>										
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<label for="slug">Slug</label>
											<input type="text" readonly name="slug" id="slug" 
                                            class="form-control" placeholder="Slug" value="{{ $category->slug}}">	
										    <p></p>
										</div>
									</div>
									<div class="col-md-6">
										<div class="mb-3">
											<input type="hidden" name="image_id" id="image_id">
											<label for="image">Image</label>
											<div id="image" class= "dropzone dz-clickable">
												<div class="dz-message needsclick">
													<br>Drop files here or click to upload. <br><br>
												</div>
											</div>
										</div>
                                        @if(!empty($category->image))
                                        <div>
                                            <img width="250" src="{{ asset('uploads/category/thumbs/'.
                                            $category->image)}}" alt="">
                                        </div>
                                        @endif
									</div>									
									<div class="col-md-6">
										<div class="mb-3">
											<label for="status">Status</label>
											<select name="status" id="status" class="form-control"
                                             placeholder="status" >
                                            <option {{ ($category->status == 1) ? 'selected': '' }} value="1">Active</option>	
                                            <option  {{ ($category->status == 0) ? 'selected': '' }} value="0">Block</option>	
										</select>
                                        </div>
									</div>
                                     <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status">Show on Home</label>
                                            <select name="showHome" id="showHome" class="form-control">
                                                <option {{ ($category->showHome == 'Yes') ? 'selected': '' }} value="Yes">Yes</option>
                                                <option {{ ($category->showHome == 'No') ? 'selected': '' }} value="No">No</option>
                                            </select>
                                        </div>
                                      </div>									
								</div>
							</div>							
						</div>
						<div class="pb-5 pt-3">
							<button type="submit" class="btn btn-primary">Update</button>
							<a href="{{route('admin.category.list')}}" class="btn btn-outline-dark ml-3">Cancel</a>
						</div>
                        </form>
					</div>
					<!-- /.card -->
				</section>
				<!-- /.content -->
                 @endsection
   @section('customJs')
   <script>
  $('#categoryForm').submit(function(event){
    event.preventDefault();
    let element = $(this);
    $("button[type=submit]").prop('disabled', true);
    
    $.ajax({
        url: "{{ route('admin.category.update', $category->id) }}",
        type: 'PUT',
        data: element.serialize(),
        dataType: 'json',
        success: function(response){
            $("button[type=submit]").prop('disabled', false);
            if (response.status) {
                window.location.href = "{{ route('admin.category.list') }}";
            } else {
                handleValidationErrors(response.errors);
            }
        },
        error: function(jqXHR, exception){
            $("button[type=submit]").prop('disabled', false);
            console.log('Something went wrong');
        }
    });
});

function handleValidationErrors(errors) {
    $("#name").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");
    $("#slug").removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html("");

    if (errors.name) {
        $("#name").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.name);
    }
    
    if (errors.slug) {
        $("#slug").addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors.slug);
    }
}


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

// Dropzone initialization
Dropzone.autoDiscover = false;

const dropzone = $("#image").dropzone({
    init: function() {
        this.on('addedFile', function(file) {
            if (this.files.length > 1) {
                this.removeFile(this.files[0]);
            }
        });
    },
    url: "{{ route('admin.category.temp-images.create') }}",
    maxFiles: 1,
    paramName: 'image',
    addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/png,image/gif",
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(file, response) {
        $("#image_id").val(response.image_id);
    }
    // error: function(file, response) {
    //     console.log('Error:', response);
    // }
});


   </script>
   @endsection              


