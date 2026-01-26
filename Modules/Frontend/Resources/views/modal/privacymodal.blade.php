<!--begin::Modal-->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Privacy Policy</h5>
            </div>
            <div class="modal-body">
                <form action="{{route('privacy.create')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <textarea type="text" class="form-group" id="editor" name="details"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="close" id="close" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" id="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end: Modal-->