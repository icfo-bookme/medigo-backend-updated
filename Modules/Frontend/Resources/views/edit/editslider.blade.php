@extends('layouts.app')

@section('title', $page_title)

@section('content')

    <form action="{{route('slider.update',$data->id)}}" method="POST" enctype="multipart/form-data" style="width:85%; margin-left: 35px; ">
        @csrf
        @method('PUT')
        <x-form.textbox labelName="URL" name="url" required="required" placeholder="Enter URL" value="{{ $data->url }}"/>
        <div class="form-group">
            <label for="formFile" class="form-label">Image</label>
            <input class="form-control" type="file" id="formFile" name="image" value=" {{( $data->image )}} ">
        </div>
        <button type="cancel" class="btn btn-danger">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>

@endsection
