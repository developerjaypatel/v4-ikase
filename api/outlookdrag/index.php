<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Outlook Mail Upload</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" />
    <style>
        .dropzone-card {
            margin: 40px auto;
            padding: 1rem;
        }

        .dz-message {
            font-size: 1.05rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Uploader -->
        <div class="card dropzone-card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Drag & Drop Outlook Email(s)</h5>
                <p class="text-muted small mb-3">
                    Drop one or more <strong>.msg</strong> or <strong>.eml</strong> files here. They will be saved in database.
                </p>
                <form action="upload.php" class="dropzone" id="emailDropzone" enctype="multipart/form-data">
                    <div class="dz-message" data-dz-message>
                        <span>Drop .msg / .eml files here or click to browse</span>
                    </div>
                </form>
                <div class="mt-3">
                    <button id="clearAllBtn" class="btn btn-sm btn-outline-secondary">Clear Queue</button>
                </div>
                <div id="alerts" class="mt-3"></div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card shadow-sm my-4">
            <div class="card-body">
                <h5 class="card-title">Stored Emails</h5>

                <div class="mb-3">
                    <input type="text" id="search" class="form-control" placeholder="Search by subject or filename...">
                </div>

                <div id="tableContainer"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>

    <script>
        Dropzone.autoDiscover = false;

        $(function() {
            const dz = new Dropzone("#emailDropzone", {
                url: "upload.php",
                paramName: "emailFile",
                maxFilesize: 10, // max file size in MB
                maxFiles: 5, // limit to 5 files at a time
                acceptedFiles: ".eml,.msg",
                addRemoveLinks: true,
                dictDefaultMessage: 'Drop .msg / .eml files here or click to upload',
                init: function() {
                    this.on("success", function(file, response) {
                        showAlert('success', response);
                        loadEmails(1);
                    });
                    this.on("error", function(file, errMsg, xhr) {
                        showAlert('danger', xhr ? xhr.responseText : errMsg);
                    });
                }
            });

            $('#clearAllBtn').on('click', function() {
                dz.removeAllFiles(true);
                $('#alerts').empty();
            });

            function showAlert(type, message) {
                $('#alerts').prepend(`
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
            }

            // === TABLE HANDLING ===
            function loadEmails(page = 1, search = '') {
                $.get('fetch_emails.php', {
                    page,
                    search
                }, function(data) {
                    $('#tableContainer').html(data);
                });
            }

            loadEmails();

            $('#search').on('keyup', function() {
                const s = $(this).val();
                loadEmails(1, s);
            });

            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                const search = $('#search').val();
                loadEmails(page, search);
            });
        });
    </script>
</body>

</html>