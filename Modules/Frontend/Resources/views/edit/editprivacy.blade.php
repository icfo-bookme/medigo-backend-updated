@extends('layouts.app')

@section('title', $page_title)

@section('content')
    <form action="{{route('privacy.update', $data->id)}}" method="POST" style="width:85%; margin-left: 35px; ">
        @csrf
        @method('PUT')
        <div class="form-group">
            <textarea type="text" class="form-group" id="editor" name="details" required>{{ $data->details }}</textarea>
        </div>
        <button type="cancel" class="btn btn-danger">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
    <script src="https://cdn.ckeditor.com/ckeditor5/35.3.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endsection
