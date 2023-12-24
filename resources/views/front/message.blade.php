@if ( Session::has('success') )    
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ Session::get('success') }}
    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>
</div>
@endif

@if ( Session::has('error') )    
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ Session::get('error') }}
    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="close"></button>
</div>
@endif