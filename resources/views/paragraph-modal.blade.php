{{-- resources/views/details/_paragraph-modal.blade.php --}}
<div class="modal fade" id="paragraph-{{ $paragraph->id }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">salam</h5>
                <button type="button" data-dismiss="modal" class="close">&times;</button>
            </div>
            <div class="modal-body">
                <form action="{{ url('/details/update/paragraph/' . $paragraph->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <input type="text" value="{{ $paragraph->paragraph_number }}">
                    <textarea name="updateEnglish" cols="30" rows="10">{{ $paragraph->text }}</textarea>
                    <textarea name="updatePersian" cols="30" rows="10">{{ $paragraph->persian_translation }}</textarea>
                    <div class="p-modal-Buttons mt-2 d-flex align-items-center justify-content-between">
                        <button class="btn btn-info" type="submit">Update</button>
                    </div>
                </form>

                <form action="{{ url('/details/delete/paragraph/' . $paragraph->id) }}" method="POST">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-danger">delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
