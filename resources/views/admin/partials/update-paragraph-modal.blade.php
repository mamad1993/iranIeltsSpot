<div class="modal fade" id="updateParagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش پاراگراف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="updateParagraphId">

                <div class="mb-3">
                    <label for="updateParagraphNumber" class="form-label">شماره پاراگراف</label>
                    <input type="number" class="form-control" id="updateParagraphNumber" min="1">
                </div>

                <div class="mb-3">
                    <label for="updateEnglish" class="form-label">متن انگلیسی</label>
                    <textarea class="form-control" id="updateEnglish" rows="6"></textarea>
                </div>

                <div class="mb-3">
                    <label for="updatePersian" class="form-label">ترجمه فارسی</label>
                    <textarea class="form-control" id="updatePersian" rows="6" dir="rtl"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" id="submitParagraphUpdate">ثبت تغییرات</button>
            </div>
        </div>
    </div>
</div>
