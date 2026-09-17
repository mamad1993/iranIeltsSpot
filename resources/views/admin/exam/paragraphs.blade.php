<div class="paragraph-container d-none">
    <div class="table-wrapper">
        <table class="paragraph-table table table-bordered">
            <thead>
            <tr>
                <th>paragraph number</th>
                <th>english paragraph</th>
                <th style="text-align: right">ترجمه فارسی</th>
            </tr>
            </thead>
            <tbody id="paragraphTableBody">

            </tbody>
        </table>
    </div>

    <button class="btn btn-info" type="button" id="addNewParagraph">
        <i class="fas fa-plus"></i> add paragraph
    </button>
    <button class="btn btn-primary" type="button" id="openImportJson">
        <i class="fas fa-file-import"></i> Import Json
    </button>
    <div class="modal fade" id="importJsonModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importJsonModalLabel">Import Questions JSON</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2" dir="rtl">
                        لطفاً ساختار استاندارد JSON حاوی پاراگراف‌ها، گروه‌های سوال و سوالات را در باکس زیر وارد کنید.
                    </div>
                    <textarea id="json-payload" class="form-control" rows="20"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" id="cancelJsonImport">cancel</button>
                    <button class="btn btn-success" type="button" id="submitJsonImport">Start Import</button>
                </div>
            </div>
        </div>
    </div>
    <div id="newParagraph" class="d-none">
        <input type="text" class="form-control" id="paragraphNumber" placeholder="enter paragraph number">
        <textarea name="" id="english-part" cols="30" rows="10" placeholder="english"></textarea>
        <textarea name="" id="persian-part" cols="30" rows="10" placeholder="فارسی" dir="rtl"></textarea>

        <div>
            <button class="btn btn-warning" type="button" id="submitParagraph">Add</button>
            <button class="btn btn-light" id="removeNewParagraph">cancel</button>
        </div>
    </div>

</div>

@include('admin.partials.update-paragraph-modal')


@push('script')
    <script>
        $(document).ready(function (){
            const tableBody = $('#paragraphTableBody');
            let currentParagraphs = [];


            /*temp json starts*/
            $('#openImportJson').on('click', function (){
                $('#newParagraph').addClass('d-none');
                $('#json-payload').val('');
                $('#importJsonModal').modal('show');

            });

            $('#cancelJsonImport').on('click', function (){
                $('#json-payload').val('');
                $('#importJsonModal').modal('hide');

            });

            $('#submitJsonImport').on('click', function (){
                const submitBtn = $(this);
                const jsonText = $('#json-payload').val().trim();
                const passageId = $('#passage-selector').val();


                if(!passageId){
                    showToast('warning', 'ابتدا یک Passage انتخاب کن.');
                    return;
                }

                if(!jsonText){
                    showToast('warning', 'لطفاً کد JSON را وارد کنید.');
                    return;
                }

                try{
                    JSON.parse(jsonText);

                }catch{
                    showToast('warning', 'فرمت فایل JSON نامعتبر است. لطفاً ساختار پرانتزها و کاماها را بررسی کنید.');
                    return;
                }

                $.ajax({
                    url: "{{ route('passage.import-all') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data:{
                      passage_id: passageId,
                      json: jsonText
                    },
                    beforeSend: function (){
                        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> در حال ایمپورت...');
                    },
                    success: function (response){
                        showToast('success', response.message);

                    },
                    error: function (xhr){
                        if (xhr.status === 422){
                            const response = xhr.responseJSON;

                            if (response.errors) {
                                const firstError = Object.values(response.errors).flat()[0];
                                showToast('warning', firstError);
                            }
                            else if (response.message) {
                                showToast('warning', response.message);
                            }
                        }else {
                            const response = xhr.responseJSON;
                            if (response && response.message) {
                                showToast('error', response.message);
                            } else {
                                showToast('error', 'خطایی در سرور رخ داده است.');
                            }
                        }
                    },
                    complete: function (){
                        submitBtn.prop('disabled', false).text('start Import');
                    }
                });
            });
            /*temp json ends*/


            $('#passage-selector').on('change', function (){
                const selectId = $(this).val();
                const container = $('.paragraph-container');


                if($(this).val() === ''){

                    container.addClass('d-none');
                    tableBody.empty();
                    return;
                }

                $('#paragraphNumber').val('');
                $('#english-part').val('');
                $('#persian-part').val('');

                $('#newParagraph').addClass('d-none');

                container.addClass('d-none');

                if(!selectId || String(selectId).startsWith('new-')){
                    container.removeClass('d-none');
                    tableBody.html(`
                        <tr class="no-data-row" dir="rtl">
                            <td colspan="3" class="text-center text-muted py-3">
                                هیچ پاراگرافی برای این passage ثبت نشده است.
                            </td>
                        </tr>
                    `);

                    return;


                }else{
                    $.ajax({
                        url: "{{ route('passage.paragraphs') }}",
                        type: 'GET',
                        data: {id: selectId},
                        success: function (response){

                            currentParagraphs = response.paragraphs ?? [];

                            container.removeClass('d-none');
                            tableBody.empty();

                            if(currentParagraphs.length === 0){
                                tableBody.html(`
                                    <tr class="no-data-row" dir="rtl">
                                        <td colspan="3" class="text-center text-muted py-3">
                                            هیچ پاراگرافی برای این passage ثبت نشده است.
                                        </td>
                                    </tr>
                                `);
                                return;
                            }else{
                                tableBody.empty();

                                currentParagraphs.forEach( function (paragraph){
                                    tableBody.append(`
                                        <tr class="paragraph-row"
                                        data-paragraph-id="${paragraph.id}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateParagModal"
                                        style="cursor: pointer"
                                        >
                                        <td>${paragraph.number}</td>
                                        <td>${limitWords(paragraph.english)}</td>
                                        <td dir="rtl">${limitWords(paragraph.persian)}</td>
                                        </tr>
                                    `);
                                });
                            }

                        }

                    });
                }
            });

            $('#addNewParagraph').on('click', function (){
                $('#newParagraph').removeClass('d-none');
                if(!hasParagraphGap()){
                    let lastNumber = getLastParagraphNumber() + 1;
                    $('#paragraphNumber').val(lastNumber);
                }

            });

            $('#removeNewParagraph').on('click', function (){
                $('#english-part').val('');
                $('#persian-part').val('');
                $('#newParagraph').addClass('d-none');
            });

            $('#submitParagraph').on('click', function (){
                const englishPart = $('#english-part').val();
                const persianPart = $('#persian-part').val();
                const paragraphNumber = $('#paragraphNumber').val();
                let passageId = $('#passage-selector').val();

                let enteredNumber = parseInt(paragraphNumber);

                if(isNaN(enteredNumber)){
                    showToast('warning', 'لطفا شماره پاراگراف را به صورت عدد وارد کن');
                    return;
                }

                if(!englishPart.trim() || !persianPart.trim()){
                    showToast('warning', 'لطفا هر دو قسمت را پر کن.');
                    return;
                }


                const gapExists = hasParagraphGap();

                const duplicate = isParagraphNumberDuplicate(enteredNumber);

                const lastNumber = getLastParagraphNumber();

                if(!gapExists){

                    if(duplicate){
                        showAlert(
                            'این قسمت قبلا استفاده شده عدد پیشنهادی: ' + (lastNumber + 1),
                            'warning'
                        ).then(() => {
                            $('#paragraphNumber').val(lastNumber + 1);
                        });
                        return;
                    }
                    if (enteredNumber !== lastNumber + 1){
                        showAlert(
                            'ترتیب رعایت نشده. عدد پیشنهادی: ' + (lastNumber + 1),
                            'warning'
                        ).then(() => {
                            $('#paragraphNumber').val(lastNumber + 1);
                        });
                        return;
                    }
                }

                if(gapExists && duplicate){
                    showAlert(
                        'این شماره پاراگراف قبلا استفاده شده.',
                        'warning'
                    );
                    return;
                }



                $.ajax({
                    url: "{{ route('add.paragraph') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'passageId': passageId,
                        'english': englishPart,
                        'persian': persianPart,
                        'paragraphNumber': paragraphNumber,
                    },

                    success: function (response){
                        const p = response.paragraph;
                        currentParagraphs.push({
                            id: p.id,
                            number: parseInt(p.number),
                            english: p.english,
                            persian: p.persian,

                        });

                        currentParagraphs.sort((a, b) => parseInt(a.number)
                         - parseInt(b.number));

                        $('.no-data-row').remove();

                        const newRow = `
                                        <tr data-paragraph-id="${p.id}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#updateParagModal"
                                        >
                                        <td>${p.number}</td>
                                        <td>${limitWords(p.english)}</td>
                                        <td dir="rtl">${limitWords(p.persian)}</td>
                                        </tr>
                                    `;

                        let inserted = false;
                        tableBody.find('tr').each(function (){
                            const rowNumber = parseInt($(this).find('td:first').text());
                            if(rowNumber > p.number){
                                $(this).before(newRow);
                                inserted = true;
                                return false;

                            }
                        });


                        if(!inserted){
                            tableBody.append(newRow);
                        }

                        $('#english-part').val('');
                        $('#persian-part').val('');

                        if(!hasParagraphGap()){
                            $('#paragraphNumber').val(getLastParagraphNumber() + 1);

                        }else{
                            $('#paragraphNumber').val('');
                        }
                        showToast('success', 'پاراگراف با موفقیت اضافه شد.');

                    }
                });





            });

            function hasParagraphGap(){
                let numbers = [];
                currentParagraphs.forEach(function (paragraph){
                    const num = parseInt(paragraph.number);
                    if(!isNaN(num)) numbers.push(num);

                });

                if (numbers.length === 0) return false;

                if(numbers[0] !== 1){
                    return true;

                }

                for (let i = 0; i < numbers.length; i++){
                    if (numbers[i+1] - numbers[i] > 1){
                        return true;
                    }
                }

                return false;

            }

            function getLastParagraphNumber(){
                let lastNumber = 0;

                currentParagraphs.forEach(function (paragraph){
                    const num = parseInt(paragraph.number);

                    if(num > lastNumber){
                        lastNumber = num;
                    }
                });
                return lastNumber;
            }


            function isParagraphNumberDuplicate(number){
                let isDuplicate = false;
                currentParagraphs.forEach(function (paragraph){
                    const rowNumber = parseInt(paragraph.number);

                    if(rowNumber === number){
                        isDuplicate = true;
                    }
                });

                return isDuplicate;
            }


            $('#updateParagModal').on('show.bs.modal', function (event){
                const clickedRow = $(event.relatedTarget);
                const paragraphId = parseInt(clickedRow.data('paragraph-id'));

                const paragraph = currentParagraphs.find(function (item){
                    return parseInt(item.id) === paragraphId;
                });

                if(!paragraph){
                    showToast('error', 'پاراگراف مورد نظر پیدا نشد!');
                    return;

                }

                $('#updateParagraphId').val(paragraph.id);
                $('#updateParagraphNumber').val(paragraph.number);
                $('#updateEnglish').val(paragraph.english);
                $('#updatePersian').val(paragraph.persian);
            });

            $('#submitParagraphUpdate').on('click', function (){
                const submitBtn = $(this);
                const paragraphId = $('#updateParagraphId').val();
                const paragraphNumber = parseInt($('#updateParagraphNumber').val());
                const english = $('#updateEnglish').val().trim();
                const persian = $('#updatePersian').val().trim();

                if (!paragraphNumber || isNaN(paragraphNumber)) {
                    showToast('warning', 'لطفاً شماره پاراگراف را وارد کنید.');
                    return;
                }

                if (!english || !persian) {
                    showToast('warning', 'لطفاً متن انگلیسی و فارسی را وارد کنید.');
                    return;
                }

                $.ajax({
                    url: "{{ route('update.paragraph') }}",
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        id: paragraphId,
                        number: paragraphNumber,
                        english: english,
                        persian: persian
                    },

                    beforeSend: function (){
                        submitBtn.prop('disabled', true).text('در حال ثبت...');
                    },

                    success: function (response){
                        const updatedParag = response.paragraph;
                        const index = currentParagraphs.findIndex(p =>
                            parseInt(p.id) === parseInt(updatedParag.id));
                        if(index !== -1){
                            currentParagraphs[index] = {
                                id: updatedParag.id,
                                number: parseInt(updatedParag.number),
                                english: updatedParag.english,
                                persian: updatedParag.persian
                            };
                        }

                        currentParagraphs.sort((a, b) => a.number - b.number);

                        const tableBody = $('#paragraphTableBody');
                        tableBody.empty();

                        currentParagraphs.forEach(function (paragraph){
                            tableBody.append(`
                                <tr class="paragraph-row"
                                    data-paragraph-id="${paragraph.id}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#updateParagModal"
                                    style="cursor: pointer"
                                >
                                    <td>${paragraph.number}</td>
                                    <td>${paragraph.english}</td>
                                    <td dir="rtl">${paragraph.persian}</td>
                                </tr>
                            `);
                        });

                        bootstrap.Modal.getInstance(document.getElementById('updateParagModal')).hide();

                        showToast('success', 'پاراگراف با موفقیت ویرایش شد.');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            const firstError = Object.values(errors).flat()[0];
                            showToast('warning', firstError);
                        } else {
                            showToast('error', 'خطایی در ثبت اطلاعات رخ داد.');
                        }
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('ثبت تغییرات');
                    }
                });
            });

            /*constraint number of words*/

            function limitWords(text, limit = 15){
                if(!text) return '';
                const words = text.split(/\s+/);
                if(words.length <= limit) return text;
                return words.slice(0, limit).join(' ') + '...';

            }

            /*end constraint number of words*/

        });
    </script>
@endpush
