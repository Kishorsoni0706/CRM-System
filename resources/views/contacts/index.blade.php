@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Contacts</h2>

    <!-- FILTER SECTION -->
    <div class="mb-3 row">
        <div class="col-md-3">
            <input type="text" class="form-control" id="filterName" placeholder="Name">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="filterEmail" placeholder="Email">
        </div>
        <div class="col-md-3">
            <select class="form-control" id="filterGender">
                <option value="">All Genders</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="filterBtn">Filter</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">Add Contact</button>
        </div>
        <div class="col-md-3 mt-2">
            <label>
                <input type="checkbox" id="showMerged"> Show merged contacts
            </label>
        </div>
    </div>

    <!-- CONTACT TABLE -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Status</th>
                <th width="260">Actions</th>
            </tr>
        </thead>
        <tbody id="contactTableBody"></tbody>
    </table>
</div>

<!-- ADD / EDIT MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addContactForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="contact_id" id="contact_id">

                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Contact</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name">
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label>Gender</label><br>
                            <div id="genderContainer">
                                <label class="me-2"><input type="radio" name="gender" value="male"> Male</label>
                                <label class="me-2"><input type="radio" name="gender" value="female"> Female</label>
                                <label><input type="radio" name="gender" value="other"> Other</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Profile Image</label>
                            <input type="file" class="form-control" name="profile_image">
                            <div id="currentProfileImage" class="mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <label>Additional File</label>
                            <input type="file" class="form-control" name="additional_file">
                            <div id="currentAdditionalFile" class="mt-1"></div>
                        </div>
                    </div>

                    <hr>
                    <h5>Custom Fields</h5>
                    <div class="row" id="customFieldsContainer"></div>

                    <div class="d-flex gap-2 mt-2">
                        <input type="text" id="newFieldName" class="form-control" placeholder="New Field Name">
                        <button type="button" id="addCustomFieldBtn" class="btn btn-secondary" style="height: 40px; min-width: 120px;">Add Field</button>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit" id="saveBtn">Save Contact</button>
                    <button class="btn btn-success d-none" type="submit" id="updateBtn">Update Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MERGE MODAL -->
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="mergeForm">
                @csrf
                <input type="hidden" name="secondary_id" id="secondary_id">

                <div class="modal-header">
                    <h5 class="modal-title">Merge Contacts</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Select Master Contact</label>
                    <select class="form-control" name="master_id" id="masterSelect" required></select>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-warning" type="submit">Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MERGE HISTORY MODAL -->
<div class="modal fade" id="mergeHistoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merge History</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="mergeHistoryBody">Loading...</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    const csrf = $('meta[name="csrf-token"]').attr('content');
    const addModal = new bootstrap.Modal('#addModal');
    const mergeModal = new bootstrap.Modal('#mergeModal');

    loadContacts();
    loadCustomFields();

    $('#filterBtn, #showMerged').on('click change', loadContacts);

    function loadContacts() {
        $.get("{{ route('contacts.index') }}", {
            name: $('#filterName').val(),
            email: $('#filterEmail').val(),
            gender: $('#filterGender').val(),
            show_merged: $('#showMerged').is(':checked') ? 1 : 0
        }, res => {
            let html = '';
            res.forEach(c => {
                const isMerged = c.is_merged ? true : false;

                html += `<tr
                    data-id="${c.id}"
                    data-gender="${c.gender ?? ''}"
                    data-profile_image="${c.profile_image ?? ''}"
                    data-additional_file="${c.additional_file ?? ''}"
                    data-custom-fields='${JSON.stringify(c.custom_fields_values || {}).replace(/'/g, "&apos;")}'>
                    <td>${c.id}</td>
                    <td>${c.name}</td>
                    <td>${c.email}</td>
                    <td>${c.phone ?? ''}</td>
                    <td>${c.gender ?? ''}</td>
                    <td>${isMerged ? '<span class="badge bg-secondary">Merged</span>' : ''}</td>
                    <td>
                        <button class="btn btn-sm btn-info editBtn">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn">Delete</button>
                        <button class="btn btn-sm btn-warning mergeBtn" ${isMerged ? 'disabled title="Already merged"' : ''}>Merge</button>
                        <button class="btn btn-sm btn-secondary mergeHistoryBtn">History</button>
                    </td>
                </tr>`;
            });
            $('#contactTableBody').html(html);
        });
    }

    function loadCustomFields() {
        $.get("{{ route('custom-fields.index') }}", res => {
            let html = '';
            res.forEach(f => {
                html += `
                    <div class="col-md-6 mb-2">
                        <label>${f.field_name}</label>
                        <input class="form-control" name="custom_fields[${f.id}]">
                    </div>`;
            });
            $('#customFieldsContainer').html(html);
        });
    }

    function resetContactForm() {
        $('#addContactForm')[0].reset();
        $('#contact_id').val('');
        $('#currentProfileImage').html('');
        $('#currentAdditionalFile').html('');
        $('#customFieldsContainer input').val('');
        $('.text-danger').remove();
    }

    $('#addModal').on('hidden.bs.modal', () => {
        resetContactForm();
        $('#saveBtn').removeClass('d-none');
        $('#updateBtn').addClass('d-none');
    });

    $('#addCustomFieldBtn').click(() => {
        const name = $('#newFieldName').val().trim();
        if (!name) return Swal.fire('Error', 'Field name cannot be empty', 'error');

        $.post("{{ route('custom-fields.store') }}", {
            _token: csrf,
            field_name: name
        }, () => {
            $('#newFieldName').val('');
            loadCustomFields();
            Swal.fire('Success', 'Custom field added', 'success');
        });
    });

    $('#addContactForm').validate({
        errorElement: 'div',
        errorClass: 'text-danger mt-1',
        rules: {
            name: "required",
            email: { required: true, email: true },
            phone: { required: true, digits: true, minlength: 10, maxlength: 15 },
            gender: { required: true },
            profile_image: { required: function() { return !$('#contact_id').val(); } },
            additional_file: { required: function() { return !$('#contact_id').val(); } }
        },
        messages: {
            name: "Please enter your name",
            email: { required: "Please enter your email", email: "Enter a valid email" },
            phone: { required: "Enter phone number", digits: "Only digits allowed", minlength: "At least 10 digits", maxlength: "Max 15 digits" },
            gender: "Please select your gender",
            profile_image: "Please upload a profile image",
            additional_file: "Please upload a document"
        },
        errorPlacement: function(error, element) {
            if(element.attr("type") === "radio") {
                element.closest('#genderContainer').append(error);
            } else if(element.attr("type") === "file") {
                element.closest('div').append(error);
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: form => {
            const id = $('#contact_id').val();
            const data = new FormData(form);
            if (id) data.append('_method', 'PUT');

            $.ajax({
                url: id ? `/contacts/${id}` : "{{ route('contacts.store') }}",
                method: 'POST',
                data,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': csrf },
                success: () => {
                    addModal.hide();
                    loadContacts();
                    Swal.fire('Success', id ? 'Contact updated successfully' : 'Contact saved successfully', 'success');
                },
                error: (xhr) => {
                    let errors = xhr.responseJSON?.errors || {};
                    Object.keys(errors).forEach(key => {
                        let el = $(`[name="${key}"]`);
                        if(el.length){
                            let errDiv = $('<div class="text-danger mt-1"></div>').text(errors[key][0]);
                            el.after(errDiv);
                        }
                    });
                }
            });
        }
    });

    // EDIT CONTACT
    $(document).on('click', '.editBtn', function () {
        resetContactForm();
        const tr = $(this).closest('tr');
        const customFields = tr.data('custom-fields') || {};

        $('#contact_id').val(tr.data('id'));
        $('input[name=name]').val(tr.find('td:eq(1)').text());
        $('input[name=email]').val(tr.find('td:eq(2)').text());
        $('input[name=phone]').val(tr.find('td:eq(3)').text());

        if (tr.data('gender')) {
            $(`input[name=gender][value="${tr.data('gender')}"]`).prop('checked', true);
        }

        if (tr.data('profile_image')) {
            $('#currentProfileImage').html(`<img src="/storage/${tr.data('profile_image')}" class="img-thumbnail" width="80">`);
        }

        if (tr.data('additional_file')) {
            $('#currentAdditionalFile').html(`<a href="/storage/${tr.data('additional_file')}" target="_blank">View current file</a>`);
        }

        Object.keys(customFields).forEach(id => {
            $(`input[name="custom_fields[${id}]"]`).val(customFields[id]);
        });

        $('#saveBtn').addClass('d-none');
        $('#updateBtn').removeClass('d-none');

        addModal.show();
    });

    // DELETE CONTACT
    $(document).on('click', '.deleteBtn', function () {
        const id = $(this).closest('tr').data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the contact",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then(r => {
            if(r.isConfirmed){
                $.ajax({
                    url: `/contacts/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    success: () => {
                        loadContacts();
                        Swal.fire('Deleted!', 'Contact has been deleted.', 'success');
                    }
                });
            }
        });
    });

    // MERGE CONTACTS
    $(document).on('click', '.mergeBtn:not(:disabled)', function () {
        const id = $(this).closest('tr').data('id');
        $('#secondary_id').val(id);

        $.get("{{ route('contacts.index') }}", { show_merged: 0 }, res => {
            let opt = '<option value="">Select master</option>';
            res.forEach(c => {
                if(c.id !== id) opt += `<option value="${c.id}">${c.name}</option>`;
            });
            $('#masterSelect').html(opt);
        });

        mergeModal.show();
    });

   $('#mergeForm').validate({
    submitHandler: form => {
        Swal.fire({
            title: 'Confirm Merge',
            text: 'Secondary contact will be merged into the master contact.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, merge',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $.post("{{ route('contacts.merge') }}", $(form).serialize(), () => {
                    mergeModal.hide();
                    loadContacts();
                    Swal.fire('Success', 'Contacts merged successfully', 'success');
                });
            }
        });
    }
});


    // MERGE HISTORY
    $(document).on('click', '.mergeHistoryBtn', function () {
        const id = $(this).closest('tr').data('id');

        $.get(`/contacts/${id}/merge-history`, logs => {
            if (!logs || logs.length === 0) {
                $('#mergeHistoryBody').html('<p>No merge history</p>');
                new bootstrap.Modal('#mergeHistoryModal').show();
                return;
            }

            let html = '';
            logs.forEach(l => {
                const secondary = l.secondary_contact_data || {};
                const emails = l.merged_emails.length ? l.merged_emails.map(e => `<li>${e}</li>`).join('') : '<li>None</li>';
                const phones = l.merged_phones.length ? l.merged_phones.map(p => `<li>${p}</li>`).join('') : '<li>None</li>';
                const customFields = Object.keys(l.merged_custom_fields).length
                    ? Object.entries(l.merged_custom_fields).map(([k,v]) => `<li><b>${k}:</b> ${v}</li>`).join('')
                    : '<li>None</li>';

                html += `<div class="border p-3 mb-3">
                    <h6>Secondary Contact: ${secondary.name ?? 'N/A'} (ID: ${l.secondary_contact_id})</h6>
                    <ul>
                        <li><b>Email:</b> ${secondary.email ?? 'N/A'}</li>
                        <li><b>Phone:</b> ${secondary.phone ?? 'N/A'}</li>
                        <li><b>Gender:</b> ${secondary.gender ?? 'N/A'}</li>
                    </ul>
                        <b>Merged Custom Fields:</b><ul>${customFields}</ul>
                </div>`;
            });

            $('#mergeHistoryBody').html(html);
            new bootstrap.Modal('#mergeHistoryModal').show();
        });
    });
});
</script>
@endsection
