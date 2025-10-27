@extends('customer.layout')

@section('customer-content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="mb-0">Mes Adresses</h2>
        <p class="text-muted mb-0">Gérez vos adresses de livraison</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i>Ajouter une adresse
    </button>
</div>

@if($addresses->count() > 0)
    <div class="row">
        @foreach($addresses as $address)
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 {{ $address->is_default ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $address->address_name }}
                        </h5>
                        @if($address->is_default)
                            <span class="badge bg-primary">Par défaut</span>
                        @endif
                    </div>

                    <p class="text-muted mb-2">
                        <i class="fas fa-location-dot me-2"></i>{{ $address->address }}
                    </p>

                    <p class="text-muted mb-3">
                        <i class="fas fa-phone me-2"></i>{{ $address->phone }}
                    </p>

                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" class="btn btn-outline-primary"
                                onclick="editAddress({{ $address->id }}, '{{ $address->address_name }}', '{{ $address->address }}', '{{ $address->phone }}', {{ $address->is_default ? 'true' : 'false' }})">
                            <i class="fas fa-edit me-1"></i>Modifier
                        </button>
                        @if(!$address->is_default)
                            <form action="{{ route('customer.address.update', $address->id) }}" method="POST" class="flex-fill">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="address_name" value="{{ $address->address_name }}">
                                <input type="hidden" name="address" value="{{ $address->address }}">
                                <input type="hidden" name="phone" value="{{ $address->phone }}">
                                <input type="hidden" name="is_default" value="1">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="fas fa-star me-1"></i>Définir par défaut
                                </button>
                            </form>
                        @endif
                        @if(!$address->is_default)
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="confirmDelete({{ $address->id }})">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Aucune adresse enregistrée</h5>
            <p class="text-muted">Ajoutez votre première adresse de livraison</p>
            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addressModal" onclick="resetForm()">
                <i class="fas fa-plus me-2"></i>Ajouter une adresse
            </button>
        </div>
    </div>
@endif

<!-- Modal d'Ajout/Modification d'Adresse -->
<div class="modal fade" id="addressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter une adresse</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addressForm" method="POST">
                @csrf
                <input type="hidden" id="formMethod" name="_method" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_name" class="form-label">Nom de l'adresse <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="address_name" name="address_name"
                               placeholder="Ex: Maison, Bureau, Chez mes parents..." required>
                        <small class="text-muted">Un nom pour identifier facilement cette adresse</small>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Adresse complète <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                                  placeholder="Numéro, rue, quartier, ville..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               placeholder="+225 XX XX XX XX XX" required>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                        <label class="form-check-label" for="is_default">
                            Définir comme adresse par défaut
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmation de Suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette adresse ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let addressModal, deleteModal;
document.addEventListener('DOMContentLoaded', function() {
    addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
});

function resetForm() {
    document.getElementById('modalTitle').textContent = 'Ajouter une adresse';
    document.getElementById('addressForm').action = '{{ route("customer.address.store") }}';
    document.getElementById('formMethod').value = '';
    document.getElementById('addressForm').reset();
}

function editAddress(id, name, address, phone, isDefault) {
    document.getElementById('modalTitle').textContent = 'Modifier l\'adresse';
    document.getElementById('addressForm').action = '/customer/addresses/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('address_name').value = name;
    document.getElementById('address').value = address;
    document.getElementById('phone').value = phone;
    document.getElementById('is_default').checked = isDefault;
    addressModal.show();
}

function confirmDelete(id) {
    document.getElementById('deleteForm').action = '/customer/addresses/' + id;
    deleteModal.show();
}
</script>
@endpush
@endsection
