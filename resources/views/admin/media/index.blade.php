@extends('admin.layout.default')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0 text-gray-800">{{ trans('installer_messages.media_management') }}</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fas fa-plus"></i> {{ trans('installer_messages.add_media') }}
    </button>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card shadow mb-4">
    <div class="card-body">
        @if(count($mediaFiles) > 0)
        <div class="row">
            @foreach($mediaFiles as $file)
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 200px; background-color: #f8f9fa;">
                        @if(in_array(pathinfo($file['name'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="img-fluid" style="max-height: 180px;">
                        @else
                            <i class="fas fa-file fa-3x text-muted"></i>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="card-title text-truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</h6>
                        <small class="text-muted">{{ number_format($file['size'] / 1024, 2) }} KB</small>
                        <div class="mt-2">
                            <a href="{{ route('admin.media.download', $file['id']) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $file['url'] }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="{{ route('admin.media.delete', $file['id']) }}"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('{{ trans('installer_messages.confirm_delete') }}')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-images fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">{{ trans('installer_messages.no_media') }}</h5>
            <p class="text-muted">{{ trans('installer_messages.upload_first_media') }}</p>
        </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('installer_messages.upload_media') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="image" class="form-label">{{ trans('installer_messages.select_image') }}</label>
                        <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
                        <div class="form-text">{{ trans('installer_messages.supported_formats') }}</div>
                    </div>
                    <div id="preview" class="text-center" style="display: none;">
                        <img id="imagePreview" src="" alt="Preview" class="img-fluid" style="max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('installer_messages.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('installer_messages.upload') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Preview image before upload
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ trans("installer_messages.uploading") }}';

    fetch('{{ route("admin.media.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ trans("installer_messages.upload_error") }}');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '{{ trans("installer_messages.upload") }}';
    });
});

// Copy URL to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => {
            event.target.innerHTML = originalText;
        }, 1000);
    });
}
</script>
@endsection
